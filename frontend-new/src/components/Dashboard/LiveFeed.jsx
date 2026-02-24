import React, { useEffect, useState } from "react";
import { useDarkMode } from "@/context/DarkModeContext";
import ProfilePicture from "../ProfilePicture";
import { getDeviceLogs } from "@/lib/api";

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

      const { data } = await getDeviceLogs({
        page: 1,
        per_page: 10,
        from_date: "2026-02-24",
        to_date: "2026-02-24",
      });

      let result = data.map((e) => ({
        id: e?.employee?.employee_id,
        name: e?.employee?.first_name,
        dept: e?.employee?.department?.name,
        location: e?.gps_location || e?.device?.location,
        method: e.mode,
        methodTitle: e.mode,
        time: e.time,
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

  return (
    <div className="flex flex-col h-full">
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

      {/* Table Header */}
      <div className="grid grid-cols-12 px-6 py-3 border-y border-gray-200 dark:border-white/5 text-[11px] font-bold text-slate-500 uppercase tracking-wider bg-white/[0.02]">
        <div className="col-span-3 pl-2">Identity</div>
        <div className="col-span-1">Dept</div>
        <div className="col-span-2">Loc</div>
        <div className="col-span-1">Method</div>
        <div className="col-span-1">Time</div>
        <div className="col-span-2">Punctuality</div>
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
            <div className="col-span-3 flex items-center gap-3 pl-2">
              <div className="size-8 rounded-full overflow-hidden relative border border-border flex items-center justify-center">
                <ProfilePicture />
              </div>
              <div className="flex flex-col">
                <span className="text-[11px] font-bold text-gray-600 dark:text-gray-300 group-hover:text-slate-950 dark:group-hover:text-white transition-colors">
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
            {/* Location */}
            <div
              className="col-span-2 text-[11px] text-slate-500 truncate"
              title={item.location}
            >
              {item.location}
            </div>
            {/* Method */}
            <div className="col-span-1 flex items-center text-slate-400">
              <span
                className="material-symbols-outlined text-[16px]"
                title={item.methodTitle}
              >
                {item.method}
              </span>
            </div>
            {/* Time */}
            <div className="col-span-1 text-[11px] font-mono text-slate-500">
              {item.time}
            </div>
            {/* Punctuality */}
            <div className="col-span-2">
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
