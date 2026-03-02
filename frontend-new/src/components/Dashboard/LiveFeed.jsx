import React, { useEffect, useState } from "react";
import { useDarkMode } from "@/context/DarkModeContext";
import ProfilePicture from "../ProfilePicture";
import { getDeviceJson, getDeviceLogs, getEmployeesJson } from "@/lib/api";
import useMqtt from "@/hooks/useMqtt";
import { getLateMinutes } from "@/hooks/useLateMinutes";
import {
  Smartphone,
  Contact,
  Fingerprint,
  ScanFace,
  Hash,
  RefreshCw,
  Edit3,
  Monitor,
  User,
} from "lucide-react";
import { caps } from "@/lib/utils";
import { getUser } from "@/config";
import IconButton from "../Theme/IconButton";
import { useRouter } from "next/navigation";

// 1. Define the base icon mapping
const baseIcons = {
  Card: <Contact size={16} title="Card" />,
  Fing: <Fingerprint size={16} title="Fingerprint" />,
  Face: <ScanFace size={16} title="Face" />,
  Pin: <Hash size={16} title="PIN" />,
  Manual: <Edit3 size={16} title="Manual" />,
  Repeated: <RefreshCw size={16} title="Repeated" />,
  Mobile: <Smartphone size={16} title="Mobile" />,
  Device: <Monitor size={16} title="Monitor" />,
};

// 2. Define how each mode maps to those icons
const iconGroups = {
  Card: [baseIcons.Card],
  Fing: [baseIcons.Fing],
  Face: [baseIcons.Face],
  "Fing + Card": [baseIcons.Fing, baseIcons.Card],
  "Face + Fing": [baseIcons.Face, baseIcons.Fing],
  "Face + Card": [baseIcons.Face, baseIcons.Card],
  "Card + Pin": [baseIcons.Card, baseIcons.Pin],
  "Face + Pin": [baseIcons.Face, baseIcons.Pin],
  "Fing + Pin": [baseIcons.Fing, baseIcons.Pin],
  "Fing + Card + Pin": [baseIcons.Fing, baseIcons.Card, baseIcons.Pin],
  "Face + Card + Pin": [baseIcons.Face, baseIcons.Card, baseIcons.Pin],
  "Face + Fing + Pin": [baseIcons.Face, baseIcons.Fing, baseIcons.Pin],
  "Face + Fing + Card": [baseIcons.Face, baseIcons.Fing, baseIcons.Card],
  Manual: [baseIcons.Manual],
  Repeated: [baseIcons.Repeated],
};

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

function LiveFeed({ branch_ids, department_ids }) {
  const router = useRouter();

  const user = getUser();

  const { isDark } = useDarkMode();

  // Helper to determine Status Badge Styles
  const getStatusStyles = (type) => {
    const themes = {
      Allowed: isDark
        ? "bg-emerald-500/5 border-emerald-500/20 text-emerald-400"
        : "bg-emerald-50 border-emerald-200 text-emerald-600",
      "Access Denied": "bg-amber-500/5 border-amber-500/20 text-amber-400",
      neutral: isDark
        ? "bg-slate-500/50 border-slate-600/50 text-slate-100"
        : "bg-slate-100 border-slate-200 text-slate-500",
    };
    return themes[type] || themes.neutral;
  };

  const getPunctualityDot = (punctuality = "On Time") => {
    const themes = {
      "On Time": "bg-emerald-500",
      Late: "bg-amber-500",
      Early: "bg-cyan-500",
    };
    return themes[punctuality] || themes.neutral;
  };

  const getPunctualityColor = (punctuality = "On Time") => {
    const themes = {
      "On Time": "text-emerald-600",
      Late: "text-amber-600",
      Early: "text-cyan-600",
    };
    return themes[punctuality] || themes.neutral;
  };

  const [records, setRecords] = useState([]);
  const [isLoading, setIsLoading] = useState(false);

  // Fetch device logs API
  const fetchRecords = async () => {
    setIsLoading(true);
    const today = new Date().toISOString().split("T")[0];
    const { data } = await getDeviceLogs({
      page: 1,
      per_page: 50,
      from_date: today,
      to_date: today,

      branch_ids,
      department_ids,
    });

    //26&from_date_txt=2026-02-26&to_date_txt=2026-02-26
    let result = data.map((e) => ({
      ...e,
      id: e?.employee?.employee_id,
      name: e?.employee?.first_name,
      type: "Entry",
      punctuality: "On Time",
      punctualityColor: "text-emerald-600",
      punctualityDot: "bg-emerald-500",
      status: e.status,
      statusType: "neutral",
      time: `${e.date} ${e.time}`,

      dept: `${e?.employee?.branch?.branch_name} ${e?.employee?.branch?.branch_name ? " / " + e?.employee?.department?.name : ""}`,
      location: e.DeviceID?.includes("Mobile")
        ? e.gps_location
        : e.device?.location,

      modes: e.DeviceID?.includes(`Mobile`)
        ? [baseIcons.Mobile]
        : iconGroups[baseIcons.Device],
    }));
    setRecords(result);
    setIsLoading(false);
  };

  useEffect(() => {
    fetchRecords();
  }, [branch_ids, department_ids]);

  const [deviceJson, setDeviceJson] = useState(null);
  const [employeesJson, setEmployeesJson] = useState(null);

  const fetchJson = async () => {
    setDeviceJson(await getDeviceJson(user.company_id));
    setEmployeesJson(await getEmployeesJson(user.company_id));
  };

  useEffect(() => {
    fetchJson();
  }, []);

  const { lastMessage } = useMqtt(["mqtt/face/+/+"]);

  useEffect(() => {
    // Only process if we have a message and it's NOT a heartbeat
    if (!lastMessage || lastMessage.topic.includes("heartbeat")) return;

    const {
      data: { customId, personName, facesluiceId, time, VerifyStatus, ...rest },
    } = lastMessage;

    if (!deviceJson) return;

    if (!employeesJson) return;

    let foundInfo = deviceJson[facesluiceId];
    let foundEmployeeInfo = employeesJson[customId];

    if (!foundInfo) return;
    if (!foundEmployeeInfo) return;

    const shift = foundEmployeeInfo?.schedule?.shift;
    const { punctuality, punctualityColor, punctualityDot } =
      getPunctualityFromShift(shift, time);

    // Insert new real-time record at the top, matching existing structure
    setRecords((prev) => [
      {
        id: customId,
        name: personName,
        dept: `${foundEmployeeInfo?.branch?.branch_name} ${foundEmployeeInfo?.branch?.branch_name ? " / " + foundEmployeeInfo?.department?.name : ""}`,
        location: foundInfo?.location || "-", // You can update this if you have location info
        type: "Entry",
        punctuality,
        punctualityColor,
        punctualityDot,
        status: VerifyStatus == "1" ? "Allowed" : "",
        statusType: "neutral",
        LogTime: time,
        modes: [baseIcons.Device],
      },
      ...prev,
    ]);
  }, [lastMessage]);

  return (
    <div className="flex flex-col h-full w-full">
      {/* Header */}
      <div className="p-5 border-b border-white/5 flex justify-between items-center bg-white/[0.01]">
        <div className="flex items-center gap-3">
          <div className="size-2 rounded-full bg-emerald-500 animate-pulse"></div>
          <h3 className="text-base font-bold text-gray-600 dark:text-gray-300 font-display tracking-wide">
            Live Recognition Feed
          </h3>

          <RefreshCw
            className={`${isLoading ? "animate-spin" : ""}`}
            onClick={fetchRecords}
            size={14}
          />
        </div>
        <div className="flex gap-4 items-center">
          {/* <span className="text-[11px] text-slate-400 font-mono">
            Refreshing in 5s...
          </span> */}
          <button
            onClick={() => router.push("/logs")}
            className="text-xs font-bold text-primary hover:text-gray-600 dark:text-gray-300 transition-colors uppercase tracking-wider"
          >
            View Full Log
          </button>
        </div>
      </div>

      {/* Table Header - Spans adjusted to sum to 12 */}
      <div className="grid grid-cols-12 px-6 py-3 border-y border-gray-200 dark:border-white/5 text-[11px] font-bold text-slate-500 uppercase tracking-wider bg-white/[0.02]">
        <div className="col-span-2 pl-2">Identity</div>
        <div className="col-span-1">Branch / Dept</div>
        <div className="col-span-4">Loc</div>
        {/* Increased space for long content */}
        <div className="col-span-1">Mode</div>
        <div className="col-span-1">Time</div>
        <div className="col-span-1">Punctuality</div>
        <div className="col-span-2 text-right pr-2">Status</div>
      </div>

      {/* List Body */}
      <div className="flex-1 overflow-y-auto px-2">
        {records.map((item, index) => (
          <div
            key={index}
            className={`grid grid-cols-12 py-4 items-center cursor-pointer group gap-2 transition-colors hover:bg-slate-50 dark:hover:bg-white/5 ${
              index !== records.length - 1
                ? "border-b border-gray-100 dark:border-white/5"
                : ""
            }`}
          >
            {/* Identity */}
            <div className="col-span-2 flex items-center gap-3 pl-2">
              <div className="size-8 min-w-[32px] rounded-full overflow-hidden relative border border-border flex items-center justify-center">
                <ProfilePicture />
              </div>
              <div className="flex flex-col min-w-0">
                <span className="text-[11px] font-bold text-gray-600 dark:text-gray-300 group-hover:text-slate-950 dark:group-hover:text-white transition-colors truncate">
                  {item.name}
                </span>
                <span className="text-[9px] text-slate-500">ID: {item.id}</span>
              </div>
            </div>
            <div className="col-span-1 text-[11px] text-slate-500 truncate">
              {item.dept}
            </div>
            {/* Location - Matches header span of 4 */}
            <div className="col-span-4 text-[11px] text-slate-500 truncate pr-4">
              {item.location}
            </div>
            <div className="col-span-1 flex items-center text-slate-400">
              {item?.modes?.map((icon, index) => (
                <span key={index}>{icon}</span>
              ))}
              {/* Optional: Add text label next to mobile icon */}
              {/* {isMobile && <span className="text-xs font-medium ">Mobile</span>} */}
            </div>
            {/* Date Time */}
            <div className="col-span-1 text-[11px] font-mono text-slate-500">
              {item.LogTime}
            </div>
            {/* Punctuality */}
            <div className="col-span-1">
              <span
                className={`inline-flex items-center gap-1.5 text-[11px] font-medium ${getPunctualityColor(item.punctuality)}`}
              >
                <span
                  className={`size-1 rounded-full ${getPunctualityDot(item.punctuality)}`}
                ></span>
                {item.punctuality}
              </span>
            </div>
            {/* Status */}
            <div className="col-span-2 text-right pr-2">
              <span
                className={`inline-flex items-center gap-1.5 px-2 py-1 rounded-full font-medium text-[9px] border ${getStatusStyles(item.status)}`}
              >
                {item.statusType !== "neutral" && (
                  <span
                    className={`w-1.5 h-1.5 rounded-full ${item.status === "Allowed" ? "bg-emerald-500" : "bg-amber-500"}`}
                  ></span>
                )}
                {item.status}
              </span>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}

export default LiveFeed;
