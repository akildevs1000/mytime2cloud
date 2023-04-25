const WebSocket = require("ws");
const fs = require("fs");
require("dotenv").config();
const { Agent } = require('https');

const axios = require("axios");

const agent = new Agent({
  rejectUnauthorized: false
});

let { Pool } = require("pg");
const format = require('pg-format');


const pool = new Pool({
  host: process.env.DB_HOST,
  user: process.env.DB_USERNAME,
  port: process.env.DB_PORT,
  password: process.env.DB_PASSWORD,
  database: process.env.DB_DATABASE,
  max: 100,
  idleTimeoutMillis: 30000,
});

pool.on('error', (err, client) => {
  console.error('Unexpected error on idle client', err);
  process.exit(-1);
});
let { WHATSAPP_ENDPOINT, NUMBER, INSTANCE_ID, TOKEN, SOCKET_ENDPOINT } = process.env;

let socket = new WebSocket(SOCKET_ENDPOINT);
socket.onopen = () => {
  const msg = "connected to live\n";
  // axios.get(`${WHATSAPP_ENDPOINT}?number=${NUMBER}&type=text&message=${msg}message&instance_id=${INSTANCE_ID}&access_token=${TOKEN}`, { httpsAgent: agent })
  //   .then(({ data }) => console.log(msg))
  //   .catch(error => console.error(error));
};
socket.onerror = ({ message: msg }) => {


  // axios.get(`${WHATSAPP_ENDPOINT}?number=${NUMBER}&type=text&message=${msg}message&instance_id=${INSTANCE_ID}&access_token=${TOKEN}`, { httpsAgent: agent })
  //   .then(({ data }) => console.log(msg))
  //   .catch(error => console.error(error));
};

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
      await pool.query(query);

      sanitizedValues.pop()

      let str = sanitizedValues.join(",");

      console.log("Data Inserted. " + str);

      fs.appendFileSync("logs.csv", str + "\n");
    }
  } catch ({ message }) {
    // axios.get(`${WHATSAPP_ENDPOINT}?number=${NUMBER}&type=text&message=${message}message&instance_id=${INSTANCE_ID}&access_token=${TOKEN}`, { httpsAgent: agent })
    //   .then(({ data }) => console.log(msg))
    //   .catch(error => console.error(error));
  }
};
