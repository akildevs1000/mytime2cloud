// device-gateway.js
// Class-based MQTT gateway for face terminals
// Express HTTP API for Laravel to call

const mqtt = require("mqtt");
const express = require("express");
const cors = require("cors");
const { v4: uuidv4 } = require("uuid");

// ========== CONFIG ==========
const MQTT_HOST = process.env.MQTT_HOST || "mqtt://127.0.0.1";
const MQTT_PORT = process.env.MQTT_PORT || 1883;
const MQTT_USERNAME = process.env.MQTT_USERNAME || "admin";
const MQTT_PASSWORD = process.env.MQTT_PASSWORD || "123456";

const HTTP_PORT = process.env.HTTP_PORT || 4000;

// ========== DEVICE GATEWAY CLASS ==========

class DeviceGateway {
  /**
   * @param {mqtt.MqttClient} client
   */
  constructor(client) {
    this.client = client;

    /** @type {Map<string, {resolve, reject, timeout, expectedOperator?: string}>} */
    this.pendingRequests = new Map();

    this._setupListeners();
  }

  _setupListeners() {
    this.client.on("connect", () => {
      console.log("✅ MQTT connected");

      // subscribe to device upstream topics
      // Adjust patterns according to protocol
      this.client.subscribe("mytimemqttattendance/face/heartbeat");
      this.client.subscribe("mytimemqttattendance/face/basic");

      // Ack + logs per device
      this.client.subscribe("mytimemqttattendance/face/+/Ack");
      this.client.subscribe("mytimemqttattendance/face/+/Rec");
      this.client.subscribe("mytimemqttattendance/face/+/Snap");
      this.client.subscribe("mytimemqttattendance/face/+/Alarm");
    });

    this.client.on("error", (err) => {
      console.error("❌ MQTT error:", err.message);
    });

    this.client.on("message", (topic, messageBuf) => {
      let payload;
      try {
        payload = JSON.parse(messageBuf.toString());
      } catch (err) {
        console.error("❌ Invalid JSON from MQTT:", topic, err.message);
        return;
      }

      const { operator, messageId } = payload || {};
      // console.log("MQTT IN:", topic, operator, messageId);

      if (messageId && this.pendingRequests.has(messageId)) {
        const entry = this.pendingRequests.get(messageId);
        this.pendingRequests.delete(messageId);
        clearTimeout(entry.timeout);

        if (entry.expectedOperator && entry.expectedOperator !== operator) {
          return entry.reject(
            new Error(
              `Unexpected operator for messageId ${messageId}. Expected ${entry.expectedOperator}, got ${operator}`
            )
          );
        }

        entry.resolve(payload);
      }

      // Here you can also forward Rec/Snap/Alarm to a websocket or DB if you want.
    });
  }

  /**
   * Core method to send a command to the device and wait for Ack.
   *
   * @param {string} deviceId
   * @param {string} operator
   * @param {object} info
   * @param {object} extra
   * @param {object} options { expectedAckOperator?: string, timeoutMs?: number }
   * @returns {Promise<object>}
   */
  sendCommand(deviceId, operator, info = {}, extra = {}, options = {}) {
    const messageId = extra.messageId || `ID:${uuidv4()}`;
    const timeoutMs = options.timeoutMs || 15000;
    const expectedOperator = options.expectedAckOperator;

    const payload = {
      messageId,
      operator,
      info,
      ...extra,
    };

    const topicDown = `mytimemqttattendance/face/${deviceId}`;

    return new Promise((resolve, reject) => {
      const timeout = setTimeout(() => {
        this.pendingRequests.delete(messageId);
        reject(new Error(`Timeout waiting for Ack for messageId ${messageId}`));
      }, timeoutMs);

      this.pendingRequests.set(messageId, {
        resolve,
        reject,
        timeout,
        expectedOperator,
      });
      console.log("MQTT OUT:", topicDown, operator, messageId, payload);
      this.client.publish(
        topicDown,
        JSON.stringify(payload),
        { qos: 1 },
        (err) => {
          if (err) {
            clearTimeout(timeout);
            this.pendingRequests.delete(messageId);
            return reject(err);
          }
        }
      );
    });
  }

  // ========== High-Level Methods (match Laravel & Postman) ==========

  // ---- Door Control ----

  async openDoor(deviceId) {
    return this.sendCommand(
      deviceId,
      "OpenDoor",
      { facesluiceId: deviceId },
      {},
      { expectedAckOperator: "OpenDoor-Ack" }
    );
  }

  // Optional: if you implement any custom "close door" behaviour on device
  async closeDoor(deviceId) {
    // Many protocols don't have an explicit CloseDoor operator.
    // You can either not implement this or map it to a custom function.
    return this.sendCommand(
      deviceId,
      "CloseDoor",
      { facesluiceId: deviceId },
      {},
      { expectedAckOperator: "CloseDoor-Ack" }
    );
  }

  async getDoorConfig(deviceId) {
    return this.sendCommand(
      deviceId,
      "GetDoorconfig",
      { facesluiceId: deviceId },
      {},
      { expectedAckOperator: "GetDoorconfig-Ack" }
    );
  }

  async setDoorConfig(deviceId, config) {
    return this.sendCommand(
      deviceId,
      "UpDoorconfig",
      { facesluiceId: deviceId, ...config },
      {},
      { expectedAckOperator: "UpDoorconfig-Ack" }
    );
  }

  // ---- Time / Timezone ----

  async getTime(deviceId) {
    return this.sendCommand(
      deviceId,
      "GetSysTime",
      { facesluiceId: deviceId },
      {},
      { expectedAckOperator: "GetSysTime-Ack" }
    );
  }

  async setTime(deviceId, sysTime) {
    // sysTime like "2025-11-22T18:30:00"
    return this.sendCommand(
      deviceId,
      "SetSysTime",
      { facesluiceId: deviceId, sysTime },
      {},
      { expectedAckOperator: "SetSysTime-Ack" }
    );
  }

  // ---- Personnel ----

  async savePerson(deviceId, personInfo) {
    // EditPerson: single add/update
    return this.sendCommand(
      deviceId,
      "EditPerson",
      personInfo,
      {},
      { expectedAckOperator: "EditPerson-Ack", timeoutMs: 30000 }
    );
  }

  async deletePerson(deviceId, customId) {
    return this.sendCommand(
      deviceId,
      "DelPerson",
      { customId },
      {},
      { expectedAckOperator: "DelPerson-Ack", timeoutMs: 15000 }
    );
  }

  async batchSavePersons(deviceId, personsArray) {
    // EditPersonsNew with Begin/End flags and PersonNum
    return this.sendCommand(
      deviceId,
      "EditPersonsNew",
      {}, // info is actually personsArray but we send through extra to match doc style
      {
        messageId: `EditPersonsNew-${Date.now()}`,
        DataBegin: "BeginFlag",
        PersonNum: personsArray.length,
        info: personsArray,
        DataEnd: "EndFlag",
      },
      { expectedAckOperator: "EditPersonsNew-Ack", timeoutMs: 60000 }
    );
  }

  async batchDeletePersons(deviceId, customIds) {
    return this.sendCommand(
      deviceId,
      "DeletePersons",
      { customId: customIds },
      {
        messageId: `DeletePersons-${Date.now()}`,
        DataBegin: "BeginFlag",
        PersonNum: customIds.length,
        DataEnd: "EndFlag",
      },
      { expectedAckOperator: "DeletePersons-Ack", timeoutMs: 30000 }
    );
  }

  async deleteAllPersons(deviceId) {
    return this.sendCommand(
      deviceId,
      "DeleteAllPerson",
      { deleteall: 1 },
      {},
      { expectedAckOperator: "DeleteAllPerson-Ack", timeoutMs: 30000 }
    );
  }

  async getPerson(deviceId, customId, includePicture = false) {
    return this.sendCommand(
      deviceId,
      "SearchPerson",
      { customId, Picture: includePicture ? 1 : 0 },
      {},
      { expectedAckOperator: "SearchPerson-Ack", timeoutMs: 15000 }
    );
  }

  async searchPersonList(deviceId, params) {
    // params: personType, BeginTime, EndTime, name, BeginNO, RequestCount, etc.
    return this.sendCommand(
      deviceId,
      "SearchPersonList",
      params,
      {},
      { expectedAckOperator: "SearchPersonList-Ack", timeoutMs: 30000 }
    );
  }

  // ---- Snapshot & QR ----

  async snapshot(deviceId, { imgType = 2, imgQuality = 55 } = {}) {
    return this.sendCommand(
      deviceId,
      "GetSceneSnap",
      {
        facesluiceId: deviceId,
        ImgType: imgType,
        ImgQuality: imgQuality,
      },
      {},
      { expectedAckOperator: "GetSceneSnap-Ack", timeoutMs: 30000 }
    );
  }

  async showQRCode(deviceId, payload) {
    // payload: { ImageType, AbsX, AbsY, ImageW, ImageH, QRCodeData, ShowStatus }
    return this.sendCommand(
      deviceId,
      "ShowQRCode",
      { facesluiceId: deviceId, ...payload },
      {},
      { expectedAckOperator: "ShowQRCode-Ack", timeoutMs: 15000 }
    );
  }

  // ---- Advertisements ----

  async saveAd(deviceId, { adslot, path, polltime = 10, adid = "" }) {
    return this.sendCommand(
      deviceId,
      "EditAD",
      {
        facesluiceId: deviceId,
        adslot,
        path,
        polltime,
        adid,
      },
      {},
      { expectedAckOperator: "EditAD-Ack", timeoutMs: 30000 }
    );
  }

  async deleteAd(deviceId, { adslot, adid = "" }) {
    return this.sendCommand(
      deviceId,
      "DelAD",
      {
        facesluiceId: deviceId,
        adslot,
        adid,
      },
      {},
      { expectedAckOperator: "DelAD-Ack", timeoutMs: 15000 }
    );
  }

  // ---- Access Strategy ----

  async saveStrategy(deviceId, strategy) {
    // strategy: {strategyID, strategyName, accessNumLimit, allowCnt, startDate, endDate, monday[], ...}
    return this.sendCommand(
      deviceId,
      "AddAccessStrategy",
      strategy,
      { bIsInt: 0 },
      { expectedAckOperator: "AddAccessStrategy-Ack", timeoutMs: 30000 }
    );
  }

  async deleteStrategies(deviceId, strategyIds) {
    return this.sendCommand(
      deviceId,
      "DelAccessStrategy",
      { strategyID: strategyIds },
      {},
      { expectedAckOperator: "DelAccessStrategy-Ack", timeoutMs: 30000 }
    );
  }

  async bindStrategies(deviceId, personsInfo) {
    // personsInfo: [{ customId, strategyID: [1,2,...] }, ...]
    return this.sendCommand(
      deviceId,
      "PersonsBindStrategyID",
      { personsInfo },
      {},
      { expectedAckOperator: "PersonsBindStrategyID-Ack", timeoutMs: 60000 }
    );
  }

  async unbindStrategies(deviceId, personsInfo) {
    return this.sendCommand(
      deviceId,
      "PersonsUnbindStrategyID",
      { personsInfo },
      {},
      { expectedAckOperator: "PersonsUnbindStrategyID-Ack", timeoutMs: 60000 }
    );
  }

  // ---- Temperature Config (TPT) ----

  async getTemperatureConfig(deviceId) {
    return this.sendCommand(
      deviceId,
      "GetTPTconfig",
      { facesluiceId: deviceId },
      {},
      { expectedAckOperator: "GetTPTconfig-Ack", timeoutMs: 15000 }
    );
  }

  async setTemperatureConfig(deviceId, config) {
    return this.sendCommand(
      deviceId,
      "UpTPTconfig",
      { facesluiceId: deviceId, ...config },
      {},
      { expectedAckOperator: "UpTPTconfig-Ack", timeoutMs: 30000 }
    );
  }

  // ---- GPS ----

  async getGps(deviceId) {
    return this.sendCommand(
      deviceId,
      "GetGpsInfo",
      { facesluiceId: deviceId },
      {},
      { expectedAckOperator: "GetGpsInfo-Ack", timeoutMs: 15000 }
    );
  }

  async setGps(deviceId, config) {
    // config: { GpsType, Longitude, Latitude, UTCOffset, ... }
    return this.sendCommand(
      deviceId,
      "UpGpsConfig",
      { facesluiceId: deviceId, ...config },
      {},
      { expectedAckOperator: "UpGpsConfig-Ack", timeoutMs: 30000 }
    );
  }

  // ---- Sound & UI ----

  async getSoundConfig(deviceId) {
    return this.sendCommand(
      deviceId,
      "GetSoundconfig",
      { facesluiceId: deviceId },
      {},
      { expectedAckOperator: "GetSoundconfig-Ack", timeoutMs: 15000 }
    );
  }

  async setSoundConfig(deviceId, config) {
    return this.sendCommand(
      deviceId,
      "UpSoundconfig",
      { facesluiceId: deviceId, ...config },
      {},
      { expectedAckOperator: "UpSoundconfig-Ack", timeoutMs: 30000 }
    );
  }

  // ---- System ----

  async reboot(deviceId) {
    return this.sendCommand(
      deviceId,
      "Reboot",
      { facesluiceId: deviceId },
      {},
      { expectedAckOperator: "Reboot-Ack", timeoutMs: 30000 }
    );
  }

  async factoryReset(deviceId, payload = {}) {
    // payload like { keepNetwork: 1 }
    return this.sendCommand(
      deviceId,
      "RestoreFactory",
      { facesluiceId: deviceId, ...payload },
      {},
      { expectedAckOperator: "RestoreFactory-Ack", timeoutMs: 60000 }
    );
  }
}

// ========== CREATE MQTT CLIENT & GATEWAY SINGLETON ==========

const mqttClient = mqtt.connect(`${MQTT_HOST}:${MQTT_PORT}`, {
  username: MQTT_USERNAME,
  password: MQTT_PASSWORD,
  clientId: `gateway-${uuidv4()}`,
  keepalive: 30,
});

const gateway = new DeviceGateway(mqttClient);

// ========== EXPRESS HTTP API (MATCH LARAVEL CONTROLLER) ==========

const app = express();
app.use(cors());
app.use(express.json({ limit: "10mb" }));

// health
app.get("/health", (req, res) => {
  res.json({ status: "ok" });
});

// Helper to wrap async route handlers
function asyncHandler(fn) {
  return (req, res) => {
    Promise.resolve(fn(req, res)).catch((err) => {
      console.error("❌ Route error:", err);
      res.status(500).json({ error: err.message || "Internal error" });
    });
  };
}

// ---- Door ----
app.post(
  "/api/device/:deviceId/open-door",
  asyncHandler(async (req, res) => {
    const { deviceId } = req.params;
    const result = await gateway.openDoor(deviceId);
    res.json(result);
  })
);

app.post(
  "/api/device/:deviceId/close-door",
  asyncHandler(async (req, res) => {
    const { deviceId } = req.params;
    const result = await gateway.closeDoor(deviceId);
    res.json(result);
  })
);

app.get(
  "/api/device/:deviceId/door-config",
  asyncHandler(async (req, res) => {
    const { deviceId } = req.params;
    const result = await gateway.getDoorConfig(deviceId);
    res.json(result);
  })
);

app.post(
  "/api/device/:deviceId/door-config",
  asyncHandler(async (req, res) => {
    const { deviceId } = req.params;
    const result = await gateway.setDoorConfig(deviceId, req.body);
    res.json(result);
  })
);

// ---- Time ----
app.get(
  "/api/device/:deviceId/time",
  asyncHandler(async (req, res) => {
    const { deviceId } = req.params;
    const result = await gateway.getTime(deviceId);
    res.json(result);
  })
);

app.post(
  "/api/device/:deviceId/time",
  asyncHandler(async (req, res) => {
    const { deviceId } = req.params;
    const { sysTime } = req.body;
    const result = await gateway.setTime(deviceId, sysTime);
    res.json(result);
  })
);

// ---- Personnel ----
app.post(
  "/api/device/:deviceId/person",
  asyncHandler(async (req, res) => {
    const { deviceId } = req.params;
    const result = await gateway.savePerson(deviceId, req.body);
    res.json(result);
  })
);

app.delete(
  "/api/device/:deviceId/person/:customId",
  asyncHandler(async (req, res) => {
    const { deviceId, customId } = req.params;
    const result = await gateway.deletePerson(deviceId, customId);
    res.json(result);
  })
);

app.post(
  "/api/device/:deviceId/persons/batch",
  asyncHandler(async (req, res) => {
    const { deviceId } = req.params;
    const persons = req.body.persons || [];
    const result = await gateway.batchSavePersons(deviceId, persons);
    res.json(result);
  })
);

app.post(
  "/api/device/:deviceId/persons/batch-delete",
  asyncHandler(async (req, res) => {
    const { deviceId } = req.params;
    const { customIds } = req.body;
    const result = await gateway.batchDeletePersons(deviceId, customIds || []);
    res.json(result);
  })
);

app.post(
  "/api/device/:deviceId/persons/delete-all",
  asyncHandler(async (req, res) => {
    const { deviceId } = req.params;
    const result = await gateway.deleteAllPersons(deviceId);
    res.json(result);
  })
);

app.get(
  "/api/device/:deviceId/person/:customId",
  asyncHandler(async (req, res) => {
    const { deviceId, customId } = req.params;
    const includePicture = req.query.picture === "1";
    const result = await gateway.getPerson(deviceId, customId, includePicture);
    res.json(result);
  })
);

app.post(
  "/api/device/:deviceId/persons/search",
  asyncHandler(async (req, res) => {
    const { deviceId } = req.params;
    const result = await gateway.searchPersonList(deviceId, req.body);
    res.json(result);
  })
);

// ---- Snapshot & QR ----
app.post(
  "/api/device/:deviceId/snapshot",
  asyncHandler(async (req, res) => {
    const { deviceId } = req.params;
    const { imgType, imgQuality } = req.body;
    const result = await gateway.snapshot(deviceId, { imgType, imgQuality });
    res.json(result);
  })
);

app.post(
  "/api/device/:deviceId/qrcode",
  asyncHandler(async (req, res) => {
    const { deviceId } = req.params;
    const result = await gateway.showQRCode(deviceId, req.body);
    res.json(result);
  })
);

// ---- Ads ----
app.post(
  "/api/device/:deviceId/ad",
  asyncHandler(async (req, res) => {
    const { deviceId } = req.params;
    const result = await gateway.saveAd(deviceId, req.body);
    res.json(result);
  })
);

app.delete(
  "/api/device/:deviceId/ad",
  asyncHandler(async (req, res) => {
    const { deviceId } = req.params;
    const result = await gateway.deleteAd(deviceId, req.body);
    res.json(result);
  })
);

// ---- Strategy ----
app.post(
  "/api/device/:deviceId/strategy",
  asyncHandler(async (req, res) => {
    const { deviceId } = req.params;
    const result = await gateway.saveStrategy(deviceId, req.body);
    res.json(result);
  })
);

app.delete(
  "/api/device/:deviceId/strategy",
  asyncHandler(async (req, res) => {
    const { deviceId } = req.params;
    const { strategyIds } = req.body;
    const result = await gateway.deleteStrategies(deviceId, strategyIds || []);
    res.json(result);
  })
);

app.post(
  "/api/device/:deviceId/strategy/bind",
  asyncHandler(async (req, res) => {
    const { deviceId } = req.params;
    const { personsInfo } = req.body;
    const result = await gateway.bindStrategies(deviceId, personsInfo || []);
    res.json(result);
  })
);

app.post(
  "/api/device/:deviceId/strategy/unbind",
  asyncHandler(async (req, res) => {
    const { deviceId } = req.params;
    const { personsInfo } = req.body;
    const result = await gateway.unbindStrategies(deviceId, personsInfo || []);
    res.json(result);
  })
);

// ---- Temperature ----
app.get(
  "/api/device/:deviceId/temperature-config",
  asyncHandler(async (req, res) => {
    const { deviceId } = req.params;
    const result = await gateway.getTemperatureConfig(deviceId);
    res.json(result);
  })
);

app.post(
  "/api/device/:deviceId/temperature-config",
  asyncHandler(async (req, res) => {
    const { deviceId } = req.params;
    const result = await gateway.setTemperatureConfig(deviceId, req.body);
    res.json(result);
  })
);

// ---- GPS ----
app.get(
  "/api/device/:deviceId/gps",
  asyncHandler(async (req, res) => {
    const { deviceId } = req.params;
    const result = await gateway.getGps(deviceId);
    res.json(result);
  })
);

app.post(
  "/api/device/:deviceId/gps",
  asyncHandler(async (req, res) => {
    const { deviceId } = req.params;
    const result = await gateway.setGps(deviceId, req.body);
    res.json(result);
  })
);

// ---- Sound ----
app.get(
  "/api/device/:deviceId/sound-config",
  asyncHandler(async (req, res) => {
    const { deviceId } = req.params;
    const result = await gateway.getSoundConfig(deviceId);
    res.json(result);
  })
);

app.post(
  "/api/device/:deviceId/sound-config",
  asyncHandler(async (req, res) => {
    const { deviceId } = req.params;
    const result = await gateway.setSoundConfig(deviceId, req.body);
    res.json(result);
  })
);

// ---- System ----
app.post(
  "/api/device/:deviceId/reboot",
  asyncHandler(async (req, res) => {
    const { deviceId } = req.params;
    const result = await gateway.reboot(deviceId);
    res.json(result);
  })
);

app.post(
  "/api/device/:deviceId/factory-reset",
  asyncHandler(async (req, res) => {
    const { deviceId } = req.params;
    const result = await gateway.factoryReset(deviceId, req.body);
    res.json(result);
  })
);

// ========== START SERVER ==========

app.listen(HTTP_PORT, () => {
  console.log(
    `🚀 MQTT HTTP gateway listening on http://localhost:${HTTP_PORT}`
  );
});

module.exports = { DeviceGateway, gateway };
