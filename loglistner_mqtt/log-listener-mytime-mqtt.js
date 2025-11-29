const mqtt = require("mqtt");
const { Pool } = require("pg");
const fs = require("fs");
const path = require("path");

// ========== ERROR LOGGING ==========

function getErrorFile() {
  const today = new Date().toISOString().slice(0, 10);
  return path.join(__dirname, `error-${today}.log`);
}

function logError(message) {
  const datetime = new Date().toISOString();
  const line = `[${datetime}] ${message}\n`;
  try {
    fs.appendFileSync(getErrorFile(), line);
  } catch (e) {
    console.error("❌ FAILED to write error log:", e.message);
  }
  console.error("❌ ERROR:", message);
}

// Helper: sanitize info object for logging (remove big pic field)
function sanitizeInfo(info) {
  if (!info) return info;
  const clone = { ...info };
  if (clone.pic) {
    clone.pic = "[BINARY_PIC_OMITTED]";
  }
  return clone;
}

// ========== CONFIG ==========

// MQTT
const MQTT_HOST = "mqtt://192.168.2.55:1883";
const MQTT_TOPIC_ATT = "mytimemqttattendance/face/+/+";
const MQTT_TOPIC_HEARTBEAT = "mytimemqttattendance/face/heartbeat";

// Use model_number to identify product type
const HEARTBEAT_MODEL_NUMBER = "MYTIME1"; // must match devices.model_number exactly

// PostgreSQL
const dbPool = new Pool({
  host: "127.0.0.1",
  port: 5432,
  user: "postgres",
  password: "test123",
  database: "hrms",
  max: 10,
  idleTimeoutMillis: 0,
});

// CSV logs directory
const logDir = path.join(__dirname, "../backend/storage/app/mqtt-mytime-logs");

// Track last heartbeat info for that device
let lastHeartbeatTs = 0; // timestamp in ms
let lastHeartbeatSerial = null; // facesluiceId (can match serial_number or device_id)

// ========== INIT ==========

if (!fs.existsSync(logDir)) {
  fs.mkdirSync(logDir, { recursive: true });
}

function getTodayFile() {
  const today = new Date().toISOString().slice(0, 10); // YYYY-MM-DD
  return path.join(logDir, `logs-${today}.csv`);
}

// Test DB connection
dbPool
  .connect()
  .then((client) => {
    console.log("✅ PostgreSQL connected");
    client.release();
  })
  .catch((err) => logError("PostgreSQL connection error: " + err.message));

// ========== MQTT CLIENT ==========

const client = mqtt.connect(MQTT_HOST);

client.on("connect", () => {
  console.log("📡 MQTT connected to", MQTT_HOST);

  client.subscribe([MQTT_TOPIC_ATT, MQTT_TOPIC_HEARTBEAT], (err) => {
    if (err) {
      logError("Subscribe error: " + err.message);
    } else {
      console.log(
        "✅ Subscribed to:",
        MQTT_TOPIC_ATT,
        "and",
        MQTT_TOPIC_HEARTBEAT
      );
    }
  });
});

client.on("error", (err) => logError("MQTT error: " + err.message));

// ========== HEARTBEAT STATUS CHECK (EVERY HOUR) ==========

async function verifyDevicesOnlineStatus() {
  const now = Date.now();
  const ONE_HOUR_MS = 60 * 60 * 1000;

  // If we never saw a heartbeat for this device yet:
  if (!lastHeartbeatSerial) {
    console.log("⚠️ No heartbeat received yet. Skipping status check.");
    return;
  }

  let sql, params;

  if (lastHeartbeatTs && now - lastHeartbeatTs <= ONE_HOUR_MS) {
    // This device seen within last hour → ONLINE
    sql = `
      UPDATE devices 
      SET status_id = 1 
      WHERE model_number = $1 
        AND (serial_number = $2 OR device_id = $2)
    `;
    params = [HEARTBEAT_MODEL_NUMBER, lastHeartbeatSerial];
    console.log(
      "✅ Heartbeat OK in last hour. Setting device ONLINE:",
      lastHeartbeatSerial
    );
  } else {
    // No recent heartbeat → OFFLINE
    sql = `
      UPDATE devices 
      SET status_id = 2 
      WHERE model_number = $1 
        AND (serial_number = $2 OR device_id = $2)
    `;
    params = [HEARTBEAT_MODEL_NUMBER, lastHeartbeatSerial];
    console.log(
      "⚠️ No heartbeat in last hour. Setting device OFFLINE:",
      lastHeartbeatSerial
    );
  }

  try {
    await dbPool.query(sql, params);
  } catch (err) {
    logError("Failed to update devices status: " + err.message);
  }
}

// Run check every 1 hour
setInterval(() => {
  verifyDevicesOnlineStatus().catch((err) =>
    logError("verifyDevicesOnlineStatus error: " + err.message)
  );
}, 60 * 60 * 1000);

// ========== MQTT MESSAGE HANDLER ==========

client.on("message", async (receivedTopic, messageBuffer) => {
  // 1) HEARTBEAT MESSAGE
  if (receivedTopic === MQTT_TOPIC_HEARTBEAT) {
    let hbJson;

    try {
      hbJson = JSON.parse(messageBuffer.toString());
    } catch {
      logError("Invalid HeartBeat JSON payload: " + messageBuffer.toString());
      return;
    }

    if (!hbJson.info) {
      logError("HeartBeat JSON missing 'info' field");
      return;
    }

    const hbInfo = hbJson.info;
    const serialNumber = hbInfo.facesluiceId || null; // device serial_number or device_id
    const hbTimeStr = hbInfo.time || null; // "2025-11-22 15:49:29"

    if (!serialNumber) {
      logError(
        "HeartBeat missing facesluiceId/serial_number. Payload: " +
          JSON.stringify(sanitizeInfo(hbInfo))
      );
      return;
    }

    if (!hbTimeStr || hbTimeStr.trim() === "") {
      logError(
        "HeartBeat missing time field. Payload: " +
          JSON.stringify(sanitizeInfo(hbInfo))
      );
      return;
    }

    // Use time from JSON to track last heartbeat for this device
    const parsedMs = Date.parse(hbTimeStr);
    if (!isNaN(parsedMs)) {
      lastHeartbeatTs = parsedMs;
    } else {
      lastHeartbeatTs = Date.now();
      logError("HeartBeat time parse failed, using now(). time=" + hbTimeStr);
    }
    lastHeartbeatSerial = serialNumber;

    console.log(
      "💓 Heartbeat received. Serial/device_id:",
      serialNumber,
      "| JSON time:",
      hbTimeStr,
      "| stored lastHeartbeatTs:",
      new Date(lastHeartbeatTs).toISOString()
    );

    try {
      await dbPool.query(
        `UPDATE devices 
         SET status_id = 1,
             last_live_datetime = $3
         WHERE model_number = $1 
           AND (serial_number = $2 OR device_id = $2)`,
        [HEARTBEAT_MODEL_NUMBER, serialNumber, hbTimeStr]
      );

      console.log(
        "✅ Device ONLINE & last_live_datetime updated (model:",
        HEARTBEAT_MODEL_NUMBER,
        "key:",
        serialNumber + ")"
      );
    } catch (err) {
      logError("Failed to update devices (heartbeat): " + err.message);
    }

    return; // don't process heartbeat as attendance
  }

  // 2) ATTENDANCE / OTHER FACE TOPICS
  let json;

  try {
    json = JSON.parse(messageBuffer.toString());
  } catch {
    logError("Invalid JSON payload: " + messageBuffer.toString());
    return;
  }

  if (!json.info) {
    logError("Missing .info field. Topic: " + receivedTopic);
    return;
  }

  const info = json.info;
  const safeInfo = sanitizeInfo(info);

  // Require RFIDCard or personId
  const userId = info.RFIDCard || info.personId || null;

  if (!userId) {
    logError(
      "Skipping insert: No RFIDCard + No personId. Payload: " +
        JSON.stringify(safeInfo)
    );
    return;
  }

  const timeStr = info.time || null;

  if (!timeStr || timeStr.trim() === "") {
    logError(
      "Skipping insert: Missing timeStr. Payload: " + JSON.stringify(safeInfo)
    );
    return;
  }

  const logDate = timeStr.split(" ")[0];

  const values = [
    userId, // "UserID"
    info.facesluiceId || null, // "DeviceID" (can also be serial_number)
    timeStr, // "LogTime"
    info.RecordID || null, // "SerialNumber"
    info.VerifyStatus == 1 ? "Allowed" : "Denied", // "status"
    "Face", // "mode"
    "---", // "reason"
    timeStr, // "log_date_time"
    info.RecordID || null, // "index_serial_number"
    logDate, // "log_date"
    new Date(), // "created_at"
    new Date(), // "updated_at"
  ];

  const sql = `
    INSERT INTO attendance_logs (
      "UserID","DeviceID","LogTime","SerialNumber",
      "status","mode","reason","log_date_time",
      "index_serial_number","log_date",
      "created_at","updated_at"
    )
    VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12)
    RETURNING id
  `;

  try {
    const result = await dbPool.query(sql, values);
    const insertedId = result.rows[0].id;
    console.log("✅ Inserted attendance_logs ID:", insertedId);

    // Normalize device name: if contains "face" → "Face"
    const devName = (info.facesluiceName || "").toLowerCase().includes("face")
      ? "Face"
      : info.facesluiceName || "";

    // CSV row: RFIDCard,facesluiceId,time,RecordID,VerifyStatus,facesluiceName,DB_ID
    const csvRow =
      [
        info.RFIDCard || "",
        info.facesluiceId || "",
        timeStr,
        info.RecordID || "",
        info.VerifyStatus || "",
        devName,
        insertedId,
      ].join(",") + "\n";

    fs.appendFileSync(getTodayFile(), csvRow);
    console.log("📝 CSV Logged:", csvRow.trim());
  } catch (err) {
    logError("DB insert / CSV error: " + err.message);
  }
});
