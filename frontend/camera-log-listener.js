const net = require("net");
const fs = require("fs");

const server = net.createServer((socket) => {
  console.log("Client connected");

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
  const logFilePath = `../backend/storage/app/camera-logs-${formattedDate}.csv`;

  var fs = require("fs");

  socket.on("data", (data) => {
    let TodayDatetime = getTime();
    const filePath = "xml/camera-log" + TodayDatetime + ".txt";

    xmlData = data.toString(); // Append data to the image data string

    fs.appendFile(filePath, xmlData, function (err) {
      if (err) {
        //console.log("Error" + err);
      } else {
        //console.log("Done");
      }
    });

    let RegisterIdArray = checkMultipleOccurrences(xmlData, "RegisteredID");
    let macArray = checkMultipleOccurrences(xmlData, "MACAddress");
    let CardNumArray = checkMultipleOccurrences(xmlData, "CardNum");
    let TimeArray = checkMultipleOccurrences(xmlData, "Time");
    let arrayCounter = 0;
    CardNumArray.forEach((element) => {
      try {
        let UserCode = CardNumArray[arrayCounter];
        let SN = macArray[arrayCounter];
        let RecordDate = formatdate(TimeArray[arrayCounter]);
        let RecordNumber = RegisterIdArray[arrayCounter];

        if (UserCode > 0) {
          const logEntry = `${UserCode},${SN},${RecordDate},${RecordNumber}`;
          fs.appendFileSync(logFilePath, logEntry + "\n");
          console.log(logEntry);
        } else {
        }
      } catch (error) {
        console.error("Error processing message:", error.message);
      }

      arrayCounter++;
    });
  });

  // When the socket connection ends
  socket.on("end", () => {});
});

const PORT = 4802; // Port on which the server will listen
server.listen(PORT, () => {
  console.log(`Server is listening on port ${PORT}`);
});
function formatdate(originalDateTime) {
  console.log(originalDateTime);
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
  //let tagName = "<RegisteredID";
  let matchExpression = new RegExp("<" + tagName + "[^\\s]+", "g");
  //console.log(matchExpression);
  //console.log(/(<RegisteredID?[^\s]+)/g);
  //matchExpression = /(<RegisteredID?[^\s]+)/g;
  let array = sentence.match(matchExpression);
  //console.log(array);
  return readElementValue(array, tagName);
  //return sentence.match(matchExpression);
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
  //console.log(year + "-" + month + "-" + date);

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
