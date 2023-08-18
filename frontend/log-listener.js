const WebSocket = require("ws");
const fs = require("fs");
require("dotenv").config();

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
    const date = new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 10).split('-');
    const formattedDate = `${date[2]}-${date[1]}-${date[0]}`;
    const logFilePath = `../backend/storage/app/logs-${formattedDate}.csv`;

    try {
        const jsonData = JSON.parse(data).Data;
        const { UserCode, SN, RecordDate, RecordNumber } = jsonData;

        if (UserCode > 0) {
            const logEntry = `${UserCode},${SN},${RecordDate},${RecordNumber}`;
            fs.appendFileSync(logFilePath, logEntry + "\n");
            console.log(logEntry);
        }
    } catch (error) {
        console.error("Error processing message:", error.message);
    }
};