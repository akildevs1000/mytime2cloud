"use client";

import { createContext, useContext, useEffect, useMemo, useState } from "react";
import useMqtt from "@/hooks/useMqtt";
import useSse from "@/hooks/useSse";
import { getDeviceJson, getEmployeesJson } from "@/lib/api";
import { getUser } from "@/config";
import { getLateMinutes } from "@/hooks/useLateMinutes";

const LiveAttendanceContext = createContext({
  lastAttendanceEvent: null,
});

const defaultPunctuality = {
  punctuality: "On Time",
  punctualityColor: "text-emerald-600",
  punctualityDot: "bg-emerald-500",
};

function getPunctualityFromShift(shift, logTime) {
  if (!shift || shift.shift_type_id == 1 || shift.shift_type_id == 2) {
    return defaultPunctuality;
  }

  const arrivalDateTime = typeof logTime === "string" ? logTime : "";
  const hasDatePart = arrivalDateTime.includes(" ");
  const dutyTime = shift?.on_duty_time
    ? shift.on_duty_time.length === 5
      ? `${shift.on_duty_time}:00`
      : shift.on_duty_time
    : null;

  if (!hasDatePart || !dutyTime) {
    return defaultPunctuality;
  }

  const shiftDate = arrivalDateTime.split(" ")[0];
  const shiftStartDateTime = `${shiftDate} ${dutyTime}`;
  const lateMinutes = getLateMinutes(
    arrivalDateTime,
    shiftStartDateTime,
    shift?.grace_time || "00:00",
  );

  if (lateMinutes > 0) {
    return {
      punctuality: "Late",
      punctualityColor: "text-amber-600",
      punctualityDot: "bg-amber-500",
    };
  }

  return defaultPunctuality;
}

export function LiveAttendanceProvider({ children }) {
  const user = getUser();
  const { lastMessage } = useMqtt(["mqtt/face/+/+"]);
  const companyId = user?.company_id;

  const [deviceJson, setDeviceJson] = useState(null);
  const [employeesJson, setEmployeesJson] = useState(null);
  const [lastAttendanceEvent, setLastAttendanceEvent] = useState(null);

  useEffect(() => {
    const fetchJson = async () => {
      if (!companyId) return;

      setDeviceJson(await getDeviceJson(companyId));
      setEmployeesJson(await getEmployeesJson(companyId));
    };

    fetchJson();
  }, [companyId]);

  useEffect(() => {
    if (!lastMessage || lastMessage.topic.includes("heartbeat")) return;
    if (!deviceJson || !employeesJson) return;

    const {
      data: { customId, personName, facesluiceId, time, VerifyStatus, pic },
    } = lastMessage;

    const foundInfo = deviceJson[facesluiceId];
    const foundEmployeeInfo = employeesJson[customId];

    if (!foundInfo || !foundEmployeeInfo) return;

    const shift = foundEmployeeInfo?.schedule?.shift;
    const { punctuality, punctualityColor, punctualityDot } =
      getPunctualityFromShift(shift, time);


    const customDate = time;

    let customTime = new Date(customDate.replace(' ', 'T')).toLocaleTimeString('en-GB', {
      timeZone: 'Asia/Dubai',
      hour: '2-digit',
      minute: '2-digit'
    });

    setLastAttendanceEvent({
      eventId: `${customId}-${facesluiceId}-${customTime}`,
      source: "mqtt",
      customId,
      personName,
      time: customTime,
      profile_picture: foundEmployeeInfo.profile_picture,
      status: VerifyStatus == "1" ? "Allowed" : "",
      punctuality,
      punctualityColor,
      punctualityDot,
      dept: `${foundEmployeeInfo?.branch?.branch_name} ${foundEmployeeInfo?.branch?.branch_name
        ? " / " + foundEmployeeInfo?.department?.name
        : ""
        }`,
      location: foundInfo?.name || "-",
    });
  }, [lastMessage, deviceJson, employeesJson]);


  useSse({
    clientId: companyId,
    storeMessages: false,
    enabled: !!companyId,
    onMessage: (incoming) => {
      if (!incoming || typeof incoming !== "object") return;
      if (incoming.type && incoming.type !== "clock") return;
      if (!deviceJson || !employeesJson) return;

      const rawPayload =
        incoming.data && typeof incoming.data === "object"
          ? incoming.data
          : incoming;

      const payloadList = Array.isArray(rawPayload) ? rawPayload : [rawPayload];

      payloadList.forEach((payload) => {
        if (!payload || typeof payload !== "object") return;


        const [hours, minutes] = payload.time.split(":");
        const myDate = new Date();
        myDate.setHours(hours, minutes);
        const customId = payload.user_id;
        const personName = payload.name;
        const facesluiceId = payload.device_id;
        const time = myDate.toLocaleTimeString("en-GB", {
          hour: "2-digit",
          minute: "2-digit",
        });

        if (!customId || !personName || !time) return;

        const foundDeviceInfo = facesluiceId ? deviceJson?.[facesluiceId] : null;

        console.log(foundDeviceInfo);

        const foundEmployeeInfo = employeesJson?.[customId];
        if (!foundEmployeeInfo) return;

        const shift = foundEmployeeInfo?.schedule?.shift;

        const { punctuality, punctualityColor, punctualityDot } = getPunctualityFromShift(shift, time);

        setLastAttendanceEvent({
          ...payload,
          eventId: `${customId}-${facesluiceId || "sse"}-${time}`,
          source_type: "sse",
          customId,
          personName,
          time,
          profile_picture: payload.avatar,
          status: "Allowed",
          punctuality,
          punctualityColor,
          punctualityDot,
          dept:
            payload.dept ||
            `${foundEmployeeInfo?.branch?.branch_name} ${foundEmployeeInfo?.branch?.branch_name
              ? " / " + foundEmployeeInfo?.department?.name
              : ""
            }`,
          location: payload.location,
        });
      });
    },
  });

  const value = useMemo(
    () => ({
      lastAttendanceEvent,
    }),
    [lastAttendanceEvent],
  );

  return (
    <LiveAttendanceContext.Provider value={value}>
      {children}
    </LiveAttendanceContext.Provider>
  );
}

export function useLiveAttendance() {
  return useContext(LiveAttendanceContext);
}
