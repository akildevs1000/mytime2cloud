"use client";

import { createContext, useContext, useEffect, useMemo, useState } from "react";
import useMqtt from "@/hooks/useMqtt";
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
  if (!shift || shift.shift_type_id === 1 || shift.shift_type_id === 2) {
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

  const [deviceJson, setDeviceJson] = useState(null);
  const [employeesJson, setEmployeesJson] = useState(null);
  const [lastAttendanceEvent, setLastAttendanceEvent] = useState(null);

  useEffect(() => {
    const fetchJson = async () => {
      const companyId = user?.company_id;
      if (!companyId) return;

      setDeviceJson(await getDeviceJson(companyId));
      setEmployeesJson(await getEmployeesJson(companyId));
    };

    fetchJson();
  }, [user?.company_id]);

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

    setLastAttendanceEvent({
      eventId: `${customId}-${facesluiceId}-${time}`,
      customId,
      personName,
      time,
      pic,
      status: VerifyStatus == "1" ? "Allowed" : "",
      punctuality,
      punctualityColor,
      punctualityDot,
      dept: `${foundEmployeeInfo?.branch?.branch_name} ${
        foundEmployeeInfo?.branch?.branch_name
          ? " / " + foundEmployeeInfo?.department?.name
          : ""
      }`,
      location: foundInfo?.location || "-",
    });
  }, [lastMessage, deviceJson, employeesJson]);

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
