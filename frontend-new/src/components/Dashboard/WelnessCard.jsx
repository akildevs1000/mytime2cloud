import { useDarkMode } from "@/context/DarkModeContext";
import { getAttendanceCount } from "@/lib/api/dashboard";
import { useEffect, useState, useMemo } from "react";
import { PieChart, Pie, ResponsiveContainer, Cell } from "recharts";

function WelnessCard({ branch_id }) {
  const { isDark } = useDarkMode();
  const [stats, setStats] = useState({
    employeeCount: 0,
    presentCount: 0,
    absentCount: 0,
    leaveCount: 0,
    vaccationCount: 0,
    offlineDevices: 0,
  });

  useEffect(() => {
    const fetchAttendanceCounts = async () => {
      // Assuming getAttendanceCount is imported or defined globally
      const data = await getAttendanceCount(branch_id);
      setStats(data);
    };
    fetchAttendanceCounts();
  }, [branch_id]);

  // 1. Calculate Wellness Value Dynamically
  const wellnessValue = useMemo(() => {
    if (!stats.employeeCount || stats.employeeCount === 0) return 0;
    
    // Logic: Wellness is high when people are present or on scheduled vacation, 
    // and low when there is high unplanned absence or leave.
    const positiveFactors = stats.presentCount + stats.vaccationCount;
    const score = Math.round((positiveFactors / stats.employeeCount) * 100);
    
    return Math.min(100, Math.max(0, score)); // Clamp between 0-100
  }, [stats]);

  // 2. Determine Status Styling
  const getStatus = (value) => {
    if (value >= 70) return { label: "Optimal", color: "#10b981", bg: "bg-emerald-500/10", text: "text-emerald-400" };
    if (value >= 50) return { label: "Stable", color: "#f59e0b", bg: "bg-amber-500/10", text: "text-amber-400" };
    return { label: "Critical", color: "#ef4444", bg: "bg-rose-500/10", text: "text-rose-400" };
  };

  const status = getStatus(wellnessValue);

  return (
    <>
      <div className="absolute top-5 left-5 z-10">
        <h3 className="text-sm font-bold text-gray-600 dark:text-gray-300 font-display">
          Workforce Wellness
        </h3>
        <p className="text-[10px] text-slate-400">Burnout Risk Monitor</p>
      </div>

      <button className="absolute top-5 right-5 text-slate-500 hover:text-gray-600 dark:text-gray-300">
        <span className="material-symbols-outlined text-sm">more_horiz</span>
      </button>

      <div className="relative w-40 h-40 mt-4 group">
        <ResponsiveContainer width="100%" height="100%">
          <PieChart>
            <Pie
              data={[{ value: 100 }]}
              dataKey="value"
              innerRadius={60}
              outerRadius={75}
              fill={isDark ? "#2f3848" : "#e5e7eb"}
              stroke="none"
              isAnimationActive={false}
            />
            <Pie
              data={[{ value: wellnessValue }]}
              dataKey="value"
              innerRadius={60}
              outerRadius={75}
              startAngle={90}
              endAngle={90 - (wellnessValue / 100) * 360}
              fill={status.color} // Dynamic Color
              stroke="none"
              cornerRadius={20}
              isAnimationActive
            />
          </PieChart>
        </ResponsiveContainer>

        <div className="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
          <span className="text-3xl font-bold text-gray-600 dark:text-gray-300 font-display tracking-tight">
            {wellnessValue}%
          </span>
          <span className={`text-[10px] font-bold uppercase tracking-wider ${status.bg} ${status.text} px-2 py-0.5 rounded-full border border-current/20 mt-1`}>
            {status.label}
          </span>
        </div>
      </div>

      <div className="mt-4 w-full px-1">
        <div className={`flex items-start gap-2 bg-white/[0.03] border border-white/5 p-2 rounded-lg ${wellnessValue < 70 ? 'animate-pulse' : ''}`}>
          <div className={`${status.text} p-1 rounded-md bg-current/10 flex-shrink-0`}>
            <span className="material-symbols-outlined text-[14px]">
              {wellnessValue < 85 ? 'warning' : 'verified_user'}
            </span>
          </div>
          <div>
            <p className="text-[10px] text-slate-300 font-medium leading-tight">
              {wellnessValue < 85 ? "Attention Required" : "System Healthy"}
            </p>
            <p className="text-[9px] text-slate-500 mt-0.5">
              {stats.absentCount} Unplanned absences today
            </p>
          </div>
        </div>
      </div>
    </>
  );
}

export default WelnessCard;