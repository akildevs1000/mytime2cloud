import mqtt from "mqtt";

let client = null;
const topicHandlers = new Map(); // topic -> Set(callback)

export function connectMQTT(options = {}) {
  if (client && client.connected) return client;

  const url = process.env.MQTT_SOCKET_HOST; // ws://ip:9001

  client = mqtt.connect(url, {
    clientId: "vue-ui-" + Math.random().toString(16).slice(2),

    clean: true,
    reconnectPeriod: 3000,
    connectTimeout: 8000,
    resubscribe: true,
    keepalive: 60,
  });

  // ✅ Attach ONE global message handler
  client.on("message", (topic, message) => {
    const handlers = topicHandlers.get(topic);
    if (!handlers || handlers.size === 0) return;

    let payload = message.toString();
    try {
      payload = JSON.parse(payload);
    } catch (_) {}

    handlers.forEach((cb) => {
      try {
        cb(payload, topic);
      } catch (e) {
        console.error(e);
      }
    });
  });

  client.on("connect", () => console.log("✅ MQTT connected"));
  client.on("reconnect", () => console.log("🔄 MQTT reconnecting"));
  client.on("close", () => console.log("🔌 MQTT closed"));
  client.on("error", (e) => console.log("❌ MQTT error:", e.message));

  return client;
}

export function subscribeTopic(topic, callback, qos = 1) {
  if (!client)
    throw new Error("MQTT client not connected. Call connectMQTT() first.");

  // store handler
  if (!topicHandlers.has(topic)) topicHandlers.set(topic, new Set());
  topicHandlers.get(topic).add(callback);

  // subscribe once
  client.subscribe(topic, { qos }, (err) => {
    if (err) console.error("❌ Subscribe error:", err.message);
    else console.log("📡 Subscribed:", topic);
  });

  // ✅ return unsubscribe function to avoid leaks on route change
  return () => {
    const set = topicHandlers.get(topic);
    if (!set) return;

    set.delete(callback);

    // If no handlers left, unsubscribe from broker
    if (set.size === 0) {
      topicHandlers.delete(topic);
      client.unsubscribe(topic, (err) => {
        if (err) console.error("❌ Unsubscribe error:", err.message);
        else console.log("🧹 Unsubscribed:", topic);
      });
    }
  };
}
export function disconnectMQTT(force = true) {
  if (!client) return;

  console.log("🔌 Disconnecting MQTT...");

  try {
    // Stop auto reconnect
    client.options.reconnectPeriod = 0;

    // Remove all topic handlers
    topicHandlers.clear();

    // Remove all listeners to prevent memory leaks
    client.removeAllListeners("message");
    client.removeAllListeners("connect");
    client.removeAllListeners("reconnect");
    client.removeAllListeners("close");
    client.removeAllListeners("offline");
    client.removeAllListeners("error");

    // End connection
    client.end(force, () => {
      console.log("✅ MQTT Disconnected");
    });
  } catch (err) {
    console.log("❌ Error while disconnecting:", err.message);
  }

  client = null;
}
