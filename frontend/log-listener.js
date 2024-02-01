const WebSocket = require("ws");
const fs = require("fs");
require("dotenv").config();

const options = {
  year: "numeric",
  month: "2-digit",
  day: "2-digit",
  hour: "2-digit",
  minute: "2-digit",
  second: "2-digit",
  hour12: false, // Use 24-hour format
  timeZone: "Asia/Dubai",
};

const verification_methods = {
  1: "Card",
  2: "Fingerprint",
  3: "Face",
  4: "Fingerprint + Card",
  5: "Face + Fingerprint",
  6: "Face + Card",
  7: "Card + Password",
  8: "Face + Password",
  9: "Fingerprint + Password",
  10: "Manual",
  11: "Fingerprint + Card + Password",
  12: "Face + Card + Password",
  13: "Face + Fingerprint + Password",
  14: "Face + Fingerprint + Card",
  15: "Repeated",
};

const [newDate, newTime] = new Intl.DateTimeFormat("en-US", options)
  .format(new Date())
  .split(",");
const [m, d, y] = newDate.split("/");
const formattedDate = `${d.padStart(2, 0)}-${m.padStart(2, 0)}-${y}`;
const logFilePath = `../backend/storage/app/logs-${formattedDate}.csv`;
const logFilePathRawData = `../backend/storage/app/logs-data/logs-data-${formattedDate}.txt`;
const logFilePathAlarm = `../backend/storage/app/alarm/alarm-logs-${formattedDate}.csv`;
console.log(`Current Date: ${formattedDate}`);
console.log(`Current Time: ${newTime.trim()}`);
console.log(`logFilePath: ${logFilePath}`);

const { SOCKET_ENDPOINT } = process.env;

// Create a WebSocket connection
const socket = new WebSocket(SOCKET_ENDPOINT);

// Handle WebSocket connection events
socket.onopen = () => {
  console.log(`Connected to ${SOCKET_ENDPOINT}`);
};

socket.onerror = (error) => {
  console.error("WebSocket error:", error.message);
};
// Handle WebSocket close event
socket.onclose = (event) => {
  console.error(
    `WebSocket connection closed with code ${
      event.code
    } at ${formattedDate} ${newTime.trim()}`
  );
};

socket.onmessage = ({ data }) => {
  try {
    fs.appendFileSync(logFilePathRawData, data + "\n");
  } catch (error) {
    console.error("Error processing message:", error.message);
  }
  try {
    const jsonData = JSON.parse(data).Data;

    const { UserCode, SN, RecordDate, RecordNumber, RecordCode } = jsonData;

    if (UserCode > 0) {
      let status = RecordCode > 15 ? "Access Denied" : "Allowed";

      let mode = verification_methods[RecordCode] ?? "---";

      const logEntry = `${UserCode},${SN},${RecordDate},${RecordNumber},${status},${mode}`;
      fs.appendFileSync(logFilePath, logEntry + "\n");
      console.log(logEntry);
    } else {
      // console.log(data);
    }
    //Alarm Code
    if (UserCode == 0 && RecordCode == 19) {
      const alarm_logEntry = `${SN},${RecordDate}`;
      fs.appendFileSync(logFilePathAlarm, alarm_logEntry + "\n");
      console.log("Alarm", alarm_logEntry);
    }
  } catch (error) {
    console.error("Error processing message:", error.message);
  }
};

process.on("SIGTERM", () => {
  console.log(`Prcess killed at ${formattedDate} ${newTime.trim()}`);
  process.exit(0); // Exit the process gracefully
});

process.on("SIGINT", () => {
  console.log(`Prcess killed at ${formattedDate} ${newTime.trim()}`);
  process.exit(0); // Exit the process gracefully
});
