const WebSocket = require("ws");
const fs = require("fs");
const axios = require("axios");
require("dotenv").config();

const existingEntries = [];

const verification_methods = {
  1: "Card",
  2: "Fing",
  3: "Face",
  4: "Fing + Card",
  5: "Face + Fing",
  6: "Face + Card",
  7: "Card + Pin",
  8: "Face + Pin",
  9: "Fing + Pin",
  10: "Manual",
  11: "Fing + Card + Pin",
  12: "Face + Card + Pin",
  13: "Face + Fing + Pin",
  14: "Face + Fing + Card",
  15: "Repeated",
};

const reasons = {
  16: "Date Expire",
  17: "Timezone Expire",
  18: "Holiday",
  19: "Unregistered",
  20: "Detection lock",
  23: "Loss Card",
  24: "Blacklisted",
  25: "Without Verification",
  26: "No Card Verification",
  27: "No Fingerprint",
};

const { SOCKET_ENDPOINT } = process.env;
// const  SOCKET_ENDPOINT = "wss://sdk.mytime2cloud.com/WebSocket";

let socket;
let reconnectInterval = 5000; // 5 seconds

function connectWebSocket() {
  // Create a WebSocket connection
  socket = new WebSocket(SOCKET_ENDPOINT);

  // Handle WebSocket connection events
  socket.onopen = () => {
    console.log(
      `Connected to ${SOCKET_ENDPOINT} at ${getFormattedDate().date}${getFormattedDate().time
      }`
    );
  };

  socket.onerror = (error) => {
    console.error(
      `WebSocket error ${error.message} at ${getFormattedDate().date} ${getFormattedDate().time
      }`
    );
  };

  socket.onclose = (event) => {
    console.warn(`WebSocket closed (code ${event.code}) at ${getFormattedDate().date} ${getFormattedDate().time}`);
    setTimeout(connectWebSocket, reconnectInterval);
  };

  socket.onmessage = ({ data }) => {
    const logFilePath = `../backend/storage/app/logs-${getFormattedDate().date
      }.csv`;
    const logFilePathAlarm = `../backend/storage/app/alarm/alarm-logs-${getFormattedDate().date
      }.csv`;

    try {
      const jsonData = JSON.parse(data).Data;

      const { UserCode, SN, RecordDate, RecordNumber, RecordCode } = jsonData;

      if (UserCode > 0) {
        let status = RecordCode > 15 ? "Access Denied" : "Allowed";

        let mode = verification_methods[RecordCode] ?? "---";

        let reason = reasons[RecordCode] ?? "---";

        const logEntry = `${UserCode},${SN},${RecordDate},${RecordNumber},${status},${mode},${reason}`;

        const uniqueKey = `${UserCode}_${SN}_${RecordDate.slice(0, -3).replace(" ", "")}`;

        if (!existingEntries.includes(uniqueKey)) {
          fs.appendFileSync(logFilePath, logEntry + "\n");
          existingEntries.push(uniqueKey)
          console.log(`New Key:`, uniqueKey);
        } else {
          console.log(`Duplicate Key:`, uniqueKey);
          console.log(existingEntries);
        }
      } else {
        // console.log(data);
      }
      //Alarm Code

      if (RecordCode == 19) {
        const alarm_logEntry = `${SN},${RecordDate}`;
        fs.appendFileSync(logFilePathAlarm, alarm_logEntry + "\n");
        console.log("Alarm", alarm_logEntry);

        const params = { 11111: "1111" };

        const url = "https://backend.mytime2cloud.com/api/loadalarm_csv";
        try {
          const response = axios.get(url, {
            params,
            timeout: 1000 * 30, // 30 seconds timeout
          });
          // console.log("Response from backend:", response);
        } catch (error) {
          // console.error("Error getting from backend:", error.message);
        } finally {
        }
      }
    } catch (error) {
      console.error("Error processing message:", error.message);
    }
  };

  // Separate function to format date
  function getFormattedDate() {
    const options = {
      year: "numeric",
      month: "2-digit",
      day: "2-digit",
      hour: "2-digit",
      minute: "2-digit",
      second: "2-digit",
      hour12: false, // Use 24-hour format
      // timeZone: "Asia/Dubai",
    };
    const [newDate, newTime] = new Intl.DateTimeFormat("en-US", options)
      .format(new Date())
      .split(",");
    const [m, d, y] = newDate.split("/");

    return {
      date: `${d.padStart(2, 0)}-${m.padStart(2, 0)}-${y}`,
      time: newTime,
    };
  }

  process.on("SIGTERM", () => {
    console.log(
      `Prcess killed at ${getFormattedDate().date} ${getFormattedDate().time}`
    );
    process.exit(0); // Exit the process gracefully
  });

  process.on("SIGINT", () => {
    console.log(
      `Prcess killed at ${getFormattedDate().date} ${getFormattedDate().time}`
    );
    process.exit(0); // Exit the process gracefully
  });
}

connectWebSocket();


