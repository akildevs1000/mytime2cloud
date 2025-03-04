
import axios from 'axios';

const port = process.env.DEEPSEEK_PORT;
console.log("🚀 ~ port:", port)

export default function (req, res) {
  (async () => {
    try {
      // Parse JSON body
      const buffers = [];
      for await (const chunk of req) {
        buffers.push(chunk);
      }
      const { question } = JSON.parse(Buffer.concat(buffers).toString());

      // Access the port from the .env file directly

      // Send request using Axios
      const response = await axios.post(
        `http://127.0.0.1:${port}/api/generate`,  // Use the port from the environment variable
        {
          model: 'deepseek-r1:1.5b',
          prompt: question,
          stream: true,
        },
        {
          headers: { 'Content-Type': 'application/json' },
          responseType: 'stream',
        }
      );

      // Set response headers
      res.writeHead(200, { 'Content-Type': 'text/plain' });

      // Stream response directly
      response.data.pipe(res);
    } catch (error) {
      res.writeHead(500, { 'Content-Type': 'application/json' });
      res.end(JSON.stringify({ error: error.message }));
    }
  })();
}
