const WebSocket = require("ws");
require("dotenv").config();
const axios = require("axios");
const fs = require("fs");

let path = null;
process.argv.forEach((v, i) => {
  if (i == 2) path = v;
});

let socket = new WebSocket(path);

socket.onopen = () => console.log("connected\n");
socket.onerror = () => console.log("error\n");

let url = "../backend/logs/logs.csv";

socket.onmessage = ({ data }) => {
  let {
    UserCode: UserID,
    DeviceID,
    RecordDate: LogTime,
    RecordNumber: SerialNumber
  } = JSON.parse(data).Data;

  let str = `${UserID},${DeviceID},${LogTime},${SerialNumber}\n`;

  if (UserID > 0) {
    console.log(str);
    fs.appendFileSync(url, str);
  }
};
