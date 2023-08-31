const WebSocket = require("ws");
const fs = require("fs");
require("dotenv").config();

const options = {
    year: '2-digit',
    month: '2-digit',
    day: '2-digit',
    timeZone: "Asia/Dubai"
};

const [d, m, y] = new Intl.DateTimeFormat("en-US", options).format(new Date()).split("/");
const formattedDate = `${d}-${m}-${y}`;
const logFilePath = `../backend/storage/app/logs-${formattedDate}.csv`;
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

socket.onmessage = ({ data }) => {

    try {
        const jsonData = JSON.parse(data).Data;
        const { UserCode, SN, RecordDate, RecordNumber } = jsonData;

        if (UserCode > 0) {
            const logEntry = `${UserCode},${SN},${RecordDate},${RecordNumber}`;
            fs.appendFileSync(logFilePath, logEntry + "\n");
            console.log(logEntry);
            console.log(logFilePath);
        }
    } catch (error) {
        console.error("Error processing message:", error.message);
    }
};