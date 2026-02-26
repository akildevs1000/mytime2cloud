import { useState, useEffect, useRef } from 'react';
import mqtt from 'mqtt';

const useMqtt = (topics) => {
    const MQTT_WS_URL = process.env.NEXT_PUBLIC_MQTT_WS_URL;
    console.log(`MQTT_WS_URL`, MQTT_WS_URL);
    const [status, setStatus] = useState({ connected: false, error: null });
    const [lastMessage, setLastMessage] = useState(null);
    const clientRef = useRef(null);

    useEffect(() => {
        // Initialize client
        const client = mqtt.connect(MQTT_WS_URL, {
            clientId: `web_client_${Math.random().toString(16).slice(2, 8)}`,
            clean: true,
            connectTimeout: 4000,
        });

        clientRef.current = client;

        client.on("connect", () => {
            setStatus({ connected: true, error: null });
            client.subscribe(topics);
        });

        client.on("error", (err) => {
            setStatus({ connected: false, error: err.message });
        });

        client.on("message", (topic, messageBuffer) => {
            try {
                const payload = JSON.parse(messageBuffer.toString());
                // We return an object containing the topic and the parsed data
                setLastMessage({ topic, data: payload.info || {} });
            } catch (e) {
                console.error("MQTT Parse Error:", e);
            }
        });

        // Cleanup: Close connection when component unmounts
        return () => {
            if (clientRef.current) {
                clientRef.current.end();
            }
        };
    }, []); // Only re-run if URL or Topics change

    return { status, lastMessage };
};

export default useMqtt;