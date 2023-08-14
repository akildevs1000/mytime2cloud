const WebSocket = require("ws");
const fs = require("fs");
require("dotenv").config();

// const { SOCKET_ENDPOINT } = `wss://sdk.ideahrms.com/WebSocket`; //process.env;

// Create a WebSocket connection
const socket = new WebSocket(`wss://sdk.ideahrms.com/WebSocket`);

// Handle WebSocket connection events
socket.onopen = () => {
    console.log(`Connected to ${`wss://sdk.ideahrms.com/WebSocket`}`);
};

socket.onerror = (error) => {
    console.error("WebSocket error:", error.message);
};

// Create and append headers to the CSV log file
const logFilePath = "../backend/storage/logs.csv";
// const header = "UserID,LogTime,DeviceID,SerialNumber\n";
// fs.writeFileSync(logFilePath, header, { flag: "w" }); // Use "w" flag to overwrite/create the file

// Handle incoming WebSocket messages
socket.onmessage = ({ data }) => {
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