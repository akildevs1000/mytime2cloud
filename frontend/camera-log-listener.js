const net = require("net");
const fs = require("fs");
const xml2js = require("xml2js");
const server = net.createServer((socket) => {
  logConsoleStatus("Client connected");

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
  //let GlobalformattedDate = `${d.padStart(2, 0)}-${m.padStart(2, 0)}-${y}`;
  const logFilePath = `../backend/storage/app/camera-logs-${formattedDate}.csv`;

  socket.on("data", (data) => {
    try {
      let TodayDatetime = getTime();
      const filePath = "../camera-xml-logs/camera-log" + TodayDatetime + ".txt";

      xmlData = data.toString(); // Append data to the image data string

      //saveXMlToLog(filePath, xmlData);
      saveRegisteredMemberstoCSV(xmlData, logFilePath, TodayDatetime);
    } catch (error) {
      console.error("Error processing Data: " + TodayDatetime);
      logConsoleStatus("Error" + error);
    }
  });

  // When the socket connection ends
  socket.on("end", () => {});
});

const PORT = 4802; // Port on which the server will listen
server.listen(PORT, () => {
  logConsoleStatus(`Server is listening on port ${PORT}`);
});

function saveXMlToLog(filePath, xmlData) {
  fs.appendFile(filePath, xmlData, function (err) {
    if (err) {
      logConsoleStatus("Error" + err);
    }
  });
}
function saveUNRegisteredMemberstoImage(xmlData, TodayDatetime) {
  logConsoleStatus(`${TodayDatetime} - Saving unregistered member`);

  // Regular expression to match content between <?xml version="1.0" encoding="utf-8"?> and </DetectedFaceList>

  let firsttag = `<?xml version="1.0" encoding="utf-8"?><DetectedFaceList>`;
  let endTag = `</DetectedFaceList>`;
  let regex = /<DetectedFaceList>([\s\S]*?)<\/DetectedFaceList>/g;
  let matches;
  while ((matches = regex.exec(xmlData)) !== null) {
    const contentBetweenTags = matches[1];

    if (contentBetweenTags) {
      let xmlString = firsttag + contentBetweenTags + endTag;

      // Parsing the XML string
      xml2js.parseString(xmlString, (err, result) => {
        if (err) {
          console.error("Error parsing XML:", err);
        } else {
          const DeviceID = result.DetectedFaceList.DeviceID[0];
          const FaceId = result.DetectedFaceList.Face_0[0].FaceID[0];
          const Snapshot = result.DetectedFaceList.Face_0[0].Snapshot[0];
          const SnapshotNum = result.DetectedFaceList.Face_0[0].SnapshotNum[0];
          const Quality = result.DetectedFaceList.Face_0[0].Quality[0];

          if (Quality >= 0.9) {
            logConsoleStatus(
              `${TodayDatetime} - Saved unregistered member - Face Id:  ${FaceId} - Quality:${Quality}`
            );

            // Example: Log the extracted values

            logConsoleStatus("FaceId:", FaceId);
            logConsoleStatus("Device ID:", DeviceID);

            let pictureName =
              DeviceID +
              "_" +
              FaceId +
              "_" +
              SnapshotNum +
              "_" +
              TodayDatetime +
              "_.jpg";
            // Convert base64 to a Buffer
            const buffer = Buffer.from(Snapshot, "base64");

            // Write the Buffer content to an image file
            fs.writeFileSync(
              "../camera-unregsitered-faces-logs/" + pictureName,
              buffer
            );
          } else {
            logConsoleStatus(
              "No image saved due to lessthan 90% quality",
              Quality
            );
          }
        }
      });
    }
  }

  // } catch (error) {
  //   console.error("Error while saving image:" + TodayDatetime);
  //   logConsoleStatus("Error" + error);
  // }
}

function getDate() {
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
  return (formattedDate = `${d.padStart(2, 0)}-${m.padStart(2, 0)}-${y}`);
}
function saveRegisteredMemberstoCSV(xmlData, logFilePath, TodayDatetime) {
  try {
    let RegisterIdArray = checkMultipleOccurrences(xmlData, "RegisteredID");
    let macArray = checkMultipleOccurrences(xmlData, "MACAddress");
    let CardNumArray = checkMultipleOccurrences(xmlData, "CardNum");
    let TimeArray = checkMultipleOccurrences(xmlData, "Time");
    let arrayCounter = 0;

    if (CardNumArray.length == 0) {
      saveUNRegisteredMemberstoImage(xmlData, TodayDatetime);
    }
    CardNumArray.forEach((element) => {
      try {
        let UserCode = CardNumArray[arrayCounter];
        let SN = macArray[arrayCounter];
        let RecordDate = TodayDatetime;
        if (TimeArray.length == 0) {
          RecordDate = getTime2();
        }

        let RecordNumber = RegisterIdArray[arrayCounter];

        if (UserCode > 0) {
          const logEntry = `${UserCode},${SN},${RecordDate},${RecordNumber}`;
          fs.appendFileSync(logFilePath, logEntry + "\n");

          logConsoleStatus(" Registered Log recorded " + logEntry);
        } else {
        }
      } catch (error) {
        console.error(
          "Error processing message:",
          TodayDatetime,
          error.message,
          RegisterIdArray,
          macArray,
          CardNumArray,
          TimeArray
        );
      }

      arrayCounter++;
    });
  } catch (error) {
    console.error("Error while saveRegisteredMemberstoCSV:" + TodayDatetime);
    logConsoleStatus("Error" + error);
  }
}

function logConsoleStatus(message) {
  console.log(message);
  const logFilePath = `camera-live-status-${getDate()}.log`;
  fs.appendFileSync(logFilePath, message + "\n");
}
function formatdate(originalDateTime) {
  originalDateTime = originalDateTime.replace("T", " ");
  originalDateTime = originalDateTime.replace("Z", "");

  const date = new Date(originalDateTime);
  const formattedDateTime =
    date.getFullYear() +
    "-" +
    String(date.getMonth() + 1).padStart(2, "0") +
    "-" +
    String(date.getDate()).padStart(2, "0") +
    " " +
    String(date.getHours()).padStart(2, "0") +
    ":" +
    String(date.getMinutes()).padStart(2, "0");

  return formattedDateTime;
}
function checkMultipleOccurrences(sentence, tagName) {
  let matchExpression = new RegExp("<" + tagName + "[^\\s]+", "g");

  let array = sentence.match(matchExpression);

  return readElementValue(array, tagName);
}

function readElementValue(inputarray, tagName) {
  let returnArray = [];
  if (inputarray) {
    inputarray.forEach((element) => {
      let xmlSnippet = element;

      let matchExpression = new RegExp(
        "<" + tagName + ">(.*?)</" + tagName + ">"
      );

      match = xmlSnippet.match(matchExpression);

      if (match[1]) returnArray.push(match[1]);
    });
  }
  return returnArray;
}
function getTime2() {
  let date_ob = new Date();

  // current date
  // adjust 0 before single digit date
  let date = ("0" + date_ob.getDate()).slice(-2);

  // current month
  let month = ("0" + (date_ob.getMonth() + 1)).slice(-2);

  // current year
  let year = date_ob.getFullYear();

  // current hours
  let hours = date_ob.getHours();

  // current minutes
  let minutes = date_ob.getMinutes();

  // current seconds
  let seconds = date_ob.getSeconds();

  // prints date in YYYY-MM-DD format
  //logConsoleStatus(year + "-" + month + "-" + date);

  // prints date & time in YYYY-MM-DD HH:MM:SS format
  return year + "-" + month + "-" + date + " " + hours + ":" + minutes;
}
function getTime() {
  let date_ob = new Date();

  // current date
  // adjust 0 before single digit date
  let date = ("0" + date_ob.getDate()).slice(-2);

  // current month
  let month = ("0" + (date_ob.getMonth() + 1)).slice(-2);

  // current year
  let year = date_ob.getFullYear();

  // current hours
  let hours = date_ob.getHours();

  // current minutes
  let minutes = date_ob.getMinutes();

  // current seconds
  let seconds = date_ob.getSeconds();

  // prints date in YYYY-MM-DD format
  //logConsoleStatus(year + "-" + month + "-" + date);

  // prints date & time in YYYY-MM-DD HH:MM:SS format
  return (
    year +
    "-" +
    month +
    "-" +
    date +
    " " +
    hours +
    "-" +
    minutes +
    "-" +
    seconds
  );
}
