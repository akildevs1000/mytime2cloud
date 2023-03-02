const WebSocket = require("ws");
const fs = require("fs");
require("dotenv").config();

let { Client } = require("pg");
const format = require('pg-format');


const client = new Client({
  host: process.env.DB_HOST,
  user: process.env.DB_USERNAME,
  port: process.env.DB_PORT,
  password: process.env.DB_PASSWORD,
  database: process.env.DB_DATABASE
});

client.connect();

let socket = new WebSocket(process.env.SOCKET_ENDPOINT);
socket.onopen = () => console.log("connected\n");
socket.onerror = () => console.log("error\n");

socket.onmessage = async ({ data }) => {
  try {
    let { UserCode, DeviceID, RecordDate, RecordNumber } = JSON.parse(
      data
    ).Data;

    RecordDate = RecordDate.replace("T", " ");

    if (UserCode > 0) {
      const sanitizedValues = [
        UserCode,
        RecordDate,
        DeviceID,
        RecordNumber,
        false
      ];

      const query = format(
        'INSERT INTO attendance_logs("UserID", "LogTime", "DeviceID", "SerialNumber", checked) VALUES (%L)',
        sanitizedValues
      );
      await client.query(query);

      sanitizedValues.pop()

      let str = sanitizedValues.join(",");

      console.log("Data Insert. " + str);

      fs.appendFileSync("logs.csv", str + "\n");
    }
  } catch (err) {
    console.error(err);
  }
};
