const WebSocket = require("ws");
require("dotenv").config();
const axios = require("axios");
const fs = require("fs");

let { Client } = require("pg");

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

socket.onmessage = ({ data }) => {
  let {
    UserCode: UserID,
    DeviceID,
    RecordDate: LogTime,
    RecordNumber: SerialNumber
  } = JSON.parse(data).Data;

  LogTime = LogTime.replace("T", " ");

  if (UserID > 0) {
    let str = `${UserID},${DeviceID},${LogTime},${SerialNumber}`;

    client.query(
      `INSERT INTO public.attendance_logs("UserID", "LogTime", "DeviceID", "SerialNumber",checked)
      VALUES ('${UserID}', '${LogTime}', '${DeviceID}', '${SerialNumber}', '${false}')`,
      (err, result) => {
        if (err) {
          console.log(err);
          return;
        }
        console.log("Data Insert. " + str);
      }
    );

    // fs.appendFileSync("logs.csv", str + "\n");

    // fs.appendFileSync("/var/www/ideahrms/backend/logs/logs.csv", str + "\n");
    // fs.appendFileSync(
    //   "/var/www/staging/ideahrms/backend/logs/logs.csv",
    //   str + "\n"
    // );
  }
};
