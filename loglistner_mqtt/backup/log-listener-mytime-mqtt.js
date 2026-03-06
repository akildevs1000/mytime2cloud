require("dotenv").config();
const mqtt = require("mqtt");
const { Pool } = require("pg");
const fs = require("fs");

const path = require("path");

// ========== ERROR LOGGING ==========

function getErrorFile() {
  const today = todayGMT4().toISOString().slice(0, 10);
  return path.join(__dirname, `error-${today}.log`);
}

function logError(message) {
  const datetime = todayGMT4().toISOString();
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
const uniqueId =
  "mqtt-loglistner-mytim2cloud-" +
  Math.random().toString(36).substring(2, 10) +
  "-" +
  Date.now();
// ========== CONFIG ==========

// MQTT
// const MQTT_HOST = process.env.MYTIME_MQTT_HOST || "";

// ======= CONFIG =======
const MQTT_HOST = process.env.MQTT_HOST || "";
const MQTT_PORT = process.env.MQTT_PORT || 1883;
const MQTT_USERNAME = process.env.MQTT_USERNAME || "";
const MQTT_PASSWORD = process.env.MQTT_PASSWORD || "";

const MQTT_TOPIC_ATT = process.env.MYTIME_MQTT_TOPIC_ATT || "";
const MQTT_TOPIC_HEARTBEAT = process.env.MYTIME_MQTT_TOPIC_HEARTBEAT || "";

// Use model_number to identify product type
const HEARTBEAT_MODEL_NUMBER =
  process.env.MYTIME_HEARTBEAT_MODEL_NUMBER || "MYTIME1"; // must match devices.model_number exactly

// PostgreSQL
const dbPool = new Pool({
  host: process.env.PGHOST || "127.0.0.1",
  port: process.env.PGPORT ? Number(process.env.PGPORT) : 5432,
  user: process.env.PGUSER || "postgres",
  password: process.env.PGPASSWORD || "test123",
  database: process.env.PGDATABASE || "hrms",
  max: 10,
  idleTimeoutMillis: 0,
});

// CSV logs directory
const logDir =
  process.env.MYTIME_LOG_DIR ||
  path.join(__dirname, "../backend/storage/app/mqtt-mytime-logs");

// Track last heartbeat info for devices (multi-device support now)
const heartbeatMap = new Map(); // key: serial/device_id, value: { lastHeartbeatTs, lastHeartbeatTimeStr }

// ========== INIT ==========

if (!fs.existsSync(logDir)) {
  fs.mkdirSync(logDir, { recursive: true });
}

function getTodayFile() {
  const today = todayGMT4().toISOString().slice(0, 10); // YYYY-MM-DD
  return path.join(logDir, `logs-${today}.csv`);
}
function todayGMT4() {
  const now = new Date();
  const gmt4 = new Date(
    now.toLocaleString("en-US", { timeZone: "Asia/Dubai" }),
  );
  return gmt4;
}
// Test DB connection
dbPool
  .connect()
  .then((client) => {
    console.log("✅ PostgreSQL connected");
    client.release();
  })
  .catch((err) =>
    logError("PostgreSQL connection error: " + (err?.message || err)),
  );

// ========== MQTT CLIENT ==========

// const client = mqtt.connect(MQTT_HOST);

// ======= MQTT CLIENT =======
const client = mqtt.connect(`${MQTT_HOST}:${MQTT_PORT}`, {
  username: MQTT_USERNAME || undefined,
  password: MQTT_PASSWORD || undefined,
  clientId: `gateway-${uniqueId}`,
  keepalive: 30,
});

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
        MQTT_TOPIC_HEARTBEAT,
      );
    }
  });
});

client.on("error", (err) => logError("MQTT error: " + err.message));

// ========== HEARTBEAT STATUS CHECK (EVERY HOUR) ==========

async function verifyDevicesOnlineStatus() {
  const now = Date.now();
  const ONE_HOUR_MS = 60 * 60 * 1000;

  if (heartbeatMap.size === 0) {
    console.log(
      "⚠️ No heartbeat received for any device yet. Skipping status check.",
    );
    return;
  }

  for (const [serial, hbInfo] of heartbeatMap.entries()) {
    const { lastHeartbeatTs, lastHeartbeatTimeStr } = hbInfo || {};
    let sql, params;

    if (lastHeartbeatTs && now - lastHeartbeatTs <= ONE_HOUR_MS) {
      // This device seen within last hour → ONLINE
      sql = `
        UPDATE devices 
        SET status_id = 1 
        WHERE model_number = $1 
          AND (serial_number = $2 OR device_id = $2)
      `;
      params = [HEARTBEAT_MODEL_NUMBER, serial];

      console.log(
        "✅ Heartbeat OK in last hour. Setting device ONLINE:",
        serial,
        "| last HB:",
        new Date(lastHeartbeatTs).toISOString(),
      );
    } else {
      // No recent heartbeat → OFFLINE
      sql = `
        UPDATE devices 
        SET status_id = 2 
        WHERE model_number = $1 
          AND (serial_number = $2 OR device_id = $2)
      `;
      params = [HEARTBEAT_MODEL_NUMBER, serial];

      console.log(
        "⚠️ No heartbeat in last hour. Setting device OFFLINE:",
        serial,
        "| last HB:",
        lastHeartbeatTs ? new Date(lastHeartbeatTs).toISOString() : "never",
      );
    }

    try {
      await dbPool.query(sql, params);
    } catch (err) {
      logError(
        "Failed to update devices status (" +
          serial +
          "): " +
          (err?.message || err),
      );
    }
  }
}

// Run check every 1 hour
setInterval(
  () => {
    verifyDevicesOnlineStatus().catch((err) =>
      logError("verifyDevicesOnlineStatus error: " + (err?.message || err)),
    );
  },
  60 * 60 * 1000,
);

// ========== MQTT MESSAGE HANDLER ==========

client.on("message", async (receivedTopic, messageBuffer) => {
  console.log(
    "-----------------------------------------------------------LOG Listner - receivedTopic",
    receivedTopic,
  );

  // 1) HEARTBEAT MESSAGE
  if (receivedTopic === MQTT_TOPIC_HEARTBEAT) {
    let hbJson;

    try {
      hbJson = JSON.parse(messageBuffer.toString());

      console.log("hbJson", hbJson);
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
          JSON.stringify(sanitizeInfo(hbInfo)),
      );
      return;
    }

    if (!hbTimeStr || hbTimeStr.trim() === "") {
      logError(
        "HeartBeat missing time field. Payload: " +
          JSON.stringify(sanitizeInfo(hbInfo)),
      );
      return;
    }

    // Use time from JSON to track last heartbeat for this device
    let lastHeartbeatTs = Date.now();
    const parsedMs = Date.parse(hbTimeStr);
    if (!isNaN(parsedMs)) {
      lastHeartbeatTs = parsedMs;
    } else {
      logError("HeartBeat time parse failed, using now(). time=" + hbTimeStr);
    }

    heartbeatMap.set(serialNumber, {
      lastHeartbeatTs,
      lastHeartbeatTimeStr: hbTimeStr,
    });

    console.log(
      "  Heartbeat received. Serial/device_id:",
      serialNumber,
      "| JSON time:",
      hbTimeStr,
      "| stored lastHeartbeatTs:",
      new Date(lastHeartbeatTs).toISOString(),
    );

    try {
      await dbPool.query(
        `UPDATE devices 
           SET status_id = 1,
               last_live_datetime = $3
         WHERE model_number = $1 
           AND (serial_number = $2 OR device_id = $2)`,
        [HEARTBEAT_MODEL_NUMBER, serialNumber, hbTimeStr],
      );

      console.log(
        "✅ Device ONLINE & last_live_datetime updated (model:",
        HEARTBEAT_MODEL_NUMBER,
        "key:",
        serialNumber + ")",
      );
    } catch (err) {
      logError(
        "Failed to update devices (heartbeat): " + (err?.message || err),
      );
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

  if (!userId || userId == 0 || userId < 0) {
    logError(
      "Skipping insert: No RFIDCard + No personId. Payload: " +
        JSON.stringify(safeInfo),
    );
    return;
  }

  const timeStr = info.time || null;

  if (!timeStr || timeStr.trim() === "") {
    logError(
      "Skipping insert: Missing timeStr. Payload: " + JSON.stringify(safeInfo),
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
    todayGMT4(), // "created_at"
    todayGMT4(), // "updated_at"
  ];

  const deviceId = String(info.facesluiceId || "");
  // const userId = String(info.customId || info.RFIDCard || info.personId || "");
  const logTime = info.time; // must match the exact value you insert into "LogTime"

  const checkSql = `
  SELECT id
  FROM attendance_logs
  WHERE "DeviceID" = $1::text
    AND "LogTime"  = $2
    AND "UserID"   = $3
  LIMIT 1
`;

  const insertSql = `
  INSERT INTO attendance_logs (
  
    "UserID",
    "DeviceID",
    "company_id",
    "LogTime",
    "SerialNumber",
    "status",
    "mode",
    "reason",
    "log_date_time",
    "index_serial_number",
    "log_date",
    "created_at",
    "updated_at"
  )
  VALUES (
    $1,
    $2::text,
    (SELECT d.company_id FROM devices d WHERE d.serial_number = $2::text LIMIT 1),
    $3,$4,$5,$6,$7,$8,$9,$10,$11,$12
  )
  RETURNING id
`;

  let status = "Allowed";
  let message = "success";
  let insertedId = null;

  try {
    // 1) Check duplicate
    const exists = await dbPool.query(checkSql, [deviceId, logTime, userId]);

    if (exists.rows.length > 0) {
      status = "duplicate";
      message = "Duplicate log skipped";
      insertedId = exists.rows[0].id; // optional (existing row id)
    } else {
      // 2) Insert
      /*const values = [
        userId, // $1 UserID
        deviceId, // $2 DeviceID
        logTime, // $3 LogTime
        info.facesluiceId, // $4 SerialNumber
        info.VerifyStatus || "", // $5 status (your DB column)
        info.PersonType || "", // $6 mode
        info.reason || "", // $7 reason
        info.time, // $8 log_date_time
        indexSerialNumberVal, // $9 index_serial_number
        logDate, // $10 log_date
        todayGMT4(), // $11 created_at
        todayGMT4(), // $12 updated_at
      ];
*/
      const result = await dbPool.query(insertSql, values);
      insertedId = result.rows[0].id;
    }
  } catch (err) {
    status = "error";
    message = `DB insert/check error: ${err?.message || err}`;
  }

  //publish logs
  // let test = {    //   customId: "4000",    //   personId: "9",    //   RecordID: "30",   //   VerifyStatus: "1",   //   PersonType: "0",   //   similarity1: "94.199997",    //   similarity2: "0.000000",   //   Sendintime: 1,   //   direction: "entr",   //   otype: "1",   //   persionName: "Venu Jakku",   //   personName: "Venu Jakku",    //   facesluiceId: "1635728",    //   facesluiceName: "Face1",    //   idCard: " ",    //   telnum: " ",    //   time: "2026-02-16 13:01:09",    //   PushType: "0",    //   OpendoorWay: "0",    //   cardNum2: "1",    //   RFIDCard: "4000",    //   szQrCodeData: "",    //   wFileIndex: "0",    //   dwFilePos: "3342336",    // };
  if (client.connected) {
    console.log("MQTT client is   connected");

    // const userId = (info.RFIDCard && String(info.RFIDCard).trim()) || "";

    const fullName = (info.personName || info.persionName || "").trim();
    const firstName = fullName ? fullName.split(/\s+/)[0] : "";
    const lastName = fullName ? fullName.split(/\s+/).slice(1).join(" ") : "";

    const inout = "";

    const payload = {
      UserID: userId,
      employee: {
        first_name: firstName,
        last_name: lastName,
        full_name: fullName,
      },
      LogTime: info.time || null,
      device: {
        id: info.facesluiceId || null,
        name: info.facesluiceName || null,
      },
      inout,
      gps_location: "",

      // optional extras (useful for UI badge/filter)
      RecordID: String(info.RecordID || ""),
      is_live: String(info.PushType) === "0",
      message: message,
    };
    if (payload.is_live === "true") {
      // publish
      client.publish(
        `mqtt/face/${info.facesluiceId}/recods/livelogs`,
        JSON.stringify(payload),
        {
          qos: 1,
        },
      );
    } else {
      // publish
      client.publish(
        `mqtt/face/${info.facesluiceId}/recods/missinglogs`,
        JSON.stringify(payload),
        {
          qos: 1,
        },
      );
    }

    console.log("MQTT client published message", payload);
  } else {
    console.log("MQTT client is not connected");

    logError(
      "MQTT publish failed: client not connected. Payload: " +
        JSON.stringify(sanitizeInfo(info)),
    );
  }
});
