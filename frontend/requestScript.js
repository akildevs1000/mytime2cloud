const axios = require('axios');


const fs = require("fs");

// Define the URL you want to hit
const url = 'https://backend.ideahrms.com/api/test';

// Function to make the HTTP request
const makeHttpRequest = () => {
    axios
        .get(url)
        .then(({ data }) => {
            // Handle the response if needed
            console.log('Request successful:', data);

            fs.appendFileSync("./logs.csv", data + "\n");
        })
        .catch((error) => {
            // Handle any errors
            console.error('Request error:', error.message);
        });
};

const interval = 2000;
setInterval(makeHttpRequest, interval);

// Log a message to indicate that the script is running
console.log(`Requesting ${url} every ${interval / 1000} seconds...`);
