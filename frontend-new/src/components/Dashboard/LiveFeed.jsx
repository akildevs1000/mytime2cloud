import React, { useEffect, useState } from "react";
import { useDarkMode } from "@/context/DarkModeContext";
import ProfilePicture from "../ProfilePicture";
import { getDeviceLogs } from "@/lib/api";
import useMqtt from "@/hooks/useMqtt";
import {
  Smartphone,
  Contact,
  Fingerprint,
  ScanFace,
  Hash,
  RefreshCw,
  Edit3,
  Monitor,
  User
} from "lucide-react";

const DeviceInfo = ({ item, caps }) => {
  const isMobile = item.DeviceID?.includes("Mobile");

  return (
    <>
      {/* 1. The Icon */}
      {/* {isMobile ? <Smartphone size={16} /> : <Monitor size={16} />} */}

      {isMobile ? (
        <>
          Mobile <br />
          {item.gps_location}
        </>
      ) : (
        <>
          {item.device ? caps(item.device.name) : "---"} <br />
          {item.device?.location || "---"}
        </>
      )}
    </>
  );
};

const ModeInfo = ({ item }) => {
  // Check for mobile first
  const isMobile = item.DeviceID?.includes(`Mobile`);
  const mode = item.mode;

  // 1. Define the base icon mapping
  const baseIcons = {
    Card: <Contact size={16} title="Card" />,
    Fing: <Fingerprint size={16} title="Fingerprint" />,
    Face: <ScanFace size={16} title="Face" />,
    Pin: <Hash size={16} title="PIN" />,
    Manual: <Edit3 size={16} title="Manual" />,
    Repeated: <RefreshCw size={16} title="Repeated" />,
    Mobile: <Smartphone size={16} title="Mobile" />,
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

  // 3. Logic: If mobile, show mobile icon. Otherwise, get group icons.
  const activeIcons = isMobile ? [baseIcons.Mobile] : (iconGroups[mode] || []);

  return (
    <div className="flex items-center gap-1" title={isMobile ? "Mobile" : mode}>
      {activeIcons.map((icon, index) => (
        <span key={index}>
          {icon}
        </span>
      ))}
      {/* Optional: Add text label next to mobile icon */}
      {isMobile && <span className="text-xs font-medium ">Mobile</span>}
    </div>
  );
};

function LiveFeed({ branch_id }) {
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

  useEffect(() => {
    const fetchRecords = async () => {
      // Assuming getAttendanceCount is imported or defined globally

      const today = new Date().toISOString().split("T")[0];

      const { data } = await getDeviceLogs({
        page: 1,
        per_page: 10,
        from_date: today,
        to_date: today,
      });

      console.log(data);

      let result = data.map((e) => ({
        ...e,
        id: e?.employee?.employee_id,
        name: e?.employee?.first_name,
        dept:
          e?.employee?.branch?.branch_name +
          " / " +
          e?.employee?.department?.name,
        location: e?.gps_location || e?.device?.location,
        type: "Entry",
        punctuality: "On Time",
        punctualityColor: "text-emerald-600",
        punctualityDot: "bg-emerald-500",
        status: e.status,
        statusType: "neutral",
      }));

      setRecords(result);
    };
    fetchRecords();
  }, [branch_id]);

  const { lastMessage } = useMqtt(["mqtt/face/+/+"]);

  useEffect(() => {
    // Only process if we have a message and it's NOT a heartbeat
    if (!lastMessage || lastMessage.topic.includes("heartbeat")) return;

    const { data } = lastMessage;
    const user = data.personName || `User ${data.personId || "Unknown"}`;
    const device = data.facesluiceName || data.facesluiceId || "Main Gate";
    const logEntry = `🎯 ATTENDANCE: ${user} at ${device}`;

    console.log(logEntry);

    // setLogs((prev) => [logEntry, ...prev].slice(0, 50));
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
        </div>
        <div className="flex gap-4 items-center">
          <span className="text-[11px] text-slate-400 font-mono">
            Refreshing in 5s...
          </span>
          <button className="text-xs font-bold text-primary hover:text-gray-600 dark:text-gray-300 transition-colors uppercase tracking-wider">
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
            {/* Dept */}
            <div
              className="col-span-1 text-[11px] text-slate-500 truncate"
              title={item.dept}
            >
              {item.dept}
            </div>
            {/* Location - Matches header span of 4 */}
            <div className="col-span-4 text-[11px] text-slate-500 truncate pr-4">
              <DeviceInfo item={item} />
            </div>
            <div className="col-span-1 flex items-center text-slate-400">
              <ModeInfo item={item} />
            </div>
            {/* Date Time */}
            <div className="col-span-1 text-[11px] font-mono text-slate-500">
              {item.date} {item.time}
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
