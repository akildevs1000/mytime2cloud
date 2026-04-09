const { Client } = require("pg");
const fs = require("fs");
const path = require("path");
require("dotenv").config();

const allowedDevices = ["OX-900"];

// ========== ERROR LOGGING ==========
function nowGMT4() {
  const now = new Date();
  return new Date(now.toLocaleString("en-US", { timeZone: "Asia/Dubai" }));
}

function logError(message) {
  const file = path.join(__dirname, `error-${nowGMT4().toISOString().slice(0, 10)}.log`);
  const line = `[${nowGMT4().toISOString()}] ${message}\n`;
  try { fs.appendFileSync(file, line); } catch { }
  console.error("❌ ERROR:", message);
}

// ========== CONFIG ==========
const PUSH_URL = process.env.PUSH_URL || "https://push.mytime2cloud.com/notify";
const PUSH_SECRET = process.env.PUSH_SECRET || "";

console.table({
  DB_HOST: process.env.DB_HOST,
  DB_PORT: process.env.DB_PORT,
  DB_USERNAME: process.env.DB_USERNAME,
  DB_PASSWORD: process.env.DB_PASSWORD ? "***hidden***" : "NOT SET",
  DB_DATABASE: process.env.DB_DATABASE,
  PUSH_URL,
  PUSH_SECRET: PUSH_SECRET ? "***hidden***" : "NOT SET",
});

// ========== PUSH NOTIFICATION ==========
async function sendPushNotification(row) {
  try {
    const payload = {
      clientId: `${row.company_id}_${row.UserID}`,
      type: "clock",
      message: "mobile clock in/out",
      data: {
        user_id: row.UserID,
        name: "",
        avatar: "",
        timestamp: row.LogTime
      },
    };

    console.log(`📤 Sending push:`);
    console.log(JSON.stringify(payload, null, 2));

    const response = await fetch(PUSH_URL, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        ...(PUSH_SECRET ? { "Authorization": `Bearer ${PUSH_SECRET}` } : {}),
      },
      body: JSON.stringify(payload),
    });

    const responseText = await response.text();

    if (!response.ok) {
      logError(`Push failed (${response.status}): ${responseText}`);
      return;
    }

    console.log(`🔔 Push sent → UserID: ${row.UserID} | Device: ${row.DeviceID} | Status: ${row.status}`);
    console.log(`📬 Push server response: ${responseText}`);

  } catch (err) {
    logError(`sendPushNotification failed: ${err.message}`);
  }
}

// ========== PG NOTIFY LISTENER ==========
async function start() {
  const pgClient = new Client({
    host: process.env.DB_HOST,
    port: process.env.DB_PORT,
    user: process.env.DB_USERNAME,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_DATABASE,
  });

  try {
    await pgClient.connect();
    console.log("✅ PostgreSQL NOTIFY listener connected");

    await pgClient.query("LISTEN attendance_inserted");
    console.log("👂 Listening for attendance_inserted notifications...");

    pgClient.on("notification", async (msg) => {
      try {
        const row = JSON.parse(msg.payload);
        if (allowedDevices.some(id => row.DeviceID.includes(id))) {
          await sendPushNotification(row);
        } else {
          console.log(`⚠️ Ignored notification from DeviceID: ${JSON.stringify(row, null, 2)}`);
        }
      } catch (err) {
        logError(`Notification parse failed: ${err.message}`);
      }
    });

    pgClient.on("error", async (err) => {
      logError(`PG client error: ${err.message}`);
      try { await pgClient.end(); } catch { }
      console.log("🔄 Reconnecting in 5 seconds...");
      setTimeout(start, 5000);
    });

  } catch (err) {
    logError(`PG connect failed: ${err.message}`);
    console.log("🔄 Retrying in 5 seconds...");
    setTimeout(start, 5000);
  }
}

start();

// ========== GRACEFUL SHUTDOWN ==========
process.on("SIGTERM", () => {
  console.log("Process killed (SIGTERM)");
  process.exit(0);
});

process.on("SIGINT", () => {
  console.log("Process killed (SIGINT)");
  process.exit(0);
});