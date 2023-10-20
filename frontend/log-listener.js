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

const [newDate, newTime] = new Intl.DateTimeFormat("en-US", options)
  .format(new Date())
  .split(",");
const [m, d, y] = newDate.split("/");
const formattedDate = `${d.padStart(2, 0)}-${m.padStart(2, 0)}-${y}`;
const logFilePath = `../backend/storage/app/logs-${formattedDate}.csv`;

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
    const jsonData = JSON.parse(data).Data;
    const { UserCode, SN, RecordDate, RecordNumber } = jsonData;

    if (UserCode > 0) {
      const logEntry = `${UserCode},${SN},${RecordDate},${RecordNumber}`;
      fs.appendFileSync(logFilePath, logEntry + "\n");
      console.log(logEntry);
    } else {
      console.log(data);
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
