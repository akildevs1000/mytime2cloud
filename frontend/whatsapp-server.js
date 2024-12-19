const fs = require("fs");
const https = require("https");
const WebSocket = require("ws");
const { Pool } = require("pg");
require("dotenv").config();
// Read SSL certificate and key
const serverOptions = {
  key: fs.readFileSync("/etc/letsencrypt/live/mytime2cloud.com/privkey.pem"),
  cert: fs.readFileSync("/etc/letsencrypt/live/mytime2cloud.com/cert.pem"),
};

// Create an HTTPS server
const server = https.createServer(serverOptions);

// WebSocket server
const wss = new WebSocket.Server({ server });

// PostgreSQL connection pool
const pool = new Pool({
  host: process.env.DB_HOST || "localhost",
  user: process.env.DB_USERNAME || "user",
  port: process.env.DB_PORT || 5432,
  password: process.env.DB_PASSWORD || "password",
  database: process.env.DB_DATABASE || "database",
  max: 100,
  idleTimeoutMillis: 30000,
});

pool.on("error", (err) => {
  console.error("Unexpected error on idle client", err);
  process.exit(-1);
});

// Handle WebSocket connections
wss.on("connection", async (ws) => {
  console.log("Client connected");

  callClientService(ws);

  setInterval(() => {
    callClientService(ws);
  }, 1000 * 15);

  // Listen for client messages
  ws.on("message", (message) => {
    console.log("Received message From Client :", message.toString("utf-8"));

    message = message.toString("utf-8");

    if (isValidJson(message)) {
      try {
        let jsonData = JSON.parse(message);

        const id = jsonData.id;
        const cmd = jsonData.cmd;
        if (cmd === "sent") {
          setTimeout(() => {
            try {
              //update Table Log status id
              updateMessageStatusTable(id);
            } catch (error) {
              console.error("Error sending message on reconnect:", error);
            }
          }, 5000);
        }
      } catch (e) {
        //console.error("Invalid JSON Format:", e);
      }
    } else {
      console.error("Invalid JSON Format:");
    }
  });

  // Handle connection close
  ws.on("close", () => {
    console.log("Client disconnected");
  });
});
function isValidJson(jsonString) {
  try {
    JSON.parse(jsonString); // Try to parse the string
    return true; // If no error is thrown, it's valid JSON
  } catch (error) {
    return false; // If an error is thrown, it's invalid JSON
  }
}

const jsonString = '{"name":"John","age":30,"city":"New York"}';
const result = isValidJson(jsonString);

console.log(result); // true if valid JSON, false otherwise

async function updateMessageStatusTable(id) {
  try {
    const query = `
  UPDATE whatsapp_notifications_logs 
  SET sent_status = TRUE, status_datetime = $1 
  WHERE id = $2
`;

    const result = await pool.query(query, [getFormattedDate(), id]);
  } catch (e) {
    console.error("Database Update Error", e);
  }
}
async function callClientService(ws) {
  try {
    console.log(`Fetching Database Data`);
    // Fetch WhatsApp notification logs
    const query =
      "SELECT * FROM whatsapp_notifications_logs WHERE sent_status=false ORDER BY created_at DESC LIMIT 1";

    // const query =
    //   "SELECT * FROM whatsapp_notifications_logs   ORDER BY created_at DESC LIMIT 1";
    const result = await pool.query(query);
    console.log(`Pending Messages count` + result.rows.length);
    for (const row of result.rows) {
      if (row.whatsapp_number.length > 10) {
        const data = {
          whatsapp_number: row.whatsapp_number,
          message: row.message,
          id: row.id,
          company_id: row.company_id,
          cmd: "new-message",
        };

        try {
          await ws.send(JSON.stringify(data)); // Send data to client
          console.log(
            `Sent to client System - WhatsApp Number: ${
              row.whatsapp_number
            } at ${getFormattedDate()} `
          );
        } catch (error) {
          console.error("Error sending data to client:", error);
        }
      } else {
        console.warn(
          `Invalid WhatsApp Number: ${row.whatsapp_number}, Message: ${row.message}`
        );
      }
    }
  } catch (error) {
    console.error("Error fetching users:", error);
  }
}
function getFormattedDate() {
  const now = new Date();

  // Get year, month, day, hours, minutes, and seconds
  const year = now.getFullYear();
  const month = String(now.getMonth() + 1).padStart(2, "0"); // Months are 0-indexed
  const day = String(now.getDate()).padStart(2, "0");
  const hours = String(now.getHours()).padStart(2, "0");
  const minutes = String(now.getMinutes()).padStart(2, "0");
  const seconds = String(now.getSeconds()).padStart(2, "0");

  // Format the date as 'YYYY-MM-DD HH:MM:SS'
  return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}
// Start the server
server.listen(7777, () => {
  console.log("WSS server listening on port 7777");
});
