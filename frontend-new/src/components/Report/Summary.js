"use client";

// app/executive-attendance/page.tsx
// Next.js App Router page component (React)
// If you prefer a reusable component, move JSX into /components and import it here.

import React, { useEffect, useState } from 'react';
import { getCompanyStats } from '@/lib/endpoint/dashboard';
import {
  ResponsiveContainer,
  BarChart,
  Bar,
  XAxis,
  YAxis,
  Tooltip,
} from 'recharts';

export default function ExecutiveAttendanceDashboardPage() {

  const chartData = [
    { label: "01", present: 85, late: 5, absent: 10 },
    { label: "02", present: 80, late: 8, absent: 12 },
    { label: "03", present: 90, late: 2, absent: 8 },
    { label: "04", present: 75, late: 15, absent: 10 },
    { label: "05", present: 88, late: 4, absent: 8 },
    { label: "06", present: 92, late: 3, absent: 5 },
    { label: "07", present: 92, late: 3, absent: 5 },
    { label: "08", present: 92, late: 3, absent: 5 },
    { label: "09", present: 92, late: 3, absent: 5 },
    { label: "10", present: 92, late: 3, absent: 5 },
    { label: "11", present: 92, late: 3, absent: 5 },
    { label: "12", present: 92, late: 3, absent: 5 },

  ];

  const topStaff = [
    { name: "Sarah Miller", dept: "Marketing", score: "100%", img: "https://i.pravatar.cc/150?u=1" },
    { name: "John Doe", dept: "Sales", score: "99%", img: "https://i.pravatar.cc/150?u=2" },
    { name: "Alex Lee", dept: "Dev", score: "98%", initial: "AL" },
  ];

  const defaultStats = [
    {
      title: "Total Staff",
      value: "142",
      icon: "groups",
      color: "blue",
      trend: "2%",
      trendUp: true,
      subText: "Active members",
      type: "sparkline",
      path: "M0 20 C 20 25, 40 10, 60 15 C 80 20, 90 5, 100 10"
    },
    {
      title: "Attendance",
      value: "96.5%",
      icon: "donut_large",
      color: "emerald",
      trend: "1.5%",
      trendUp: true,
      type: "progress",
      progress: "96.5%"
    },
    {
      title: "Overtime",
      value: "320h",
      icon: "schedule",
      color: "purple",
      trend: "5%",
      trendUp: false,
      subText: "Monthly total"
    },
    {
      title: "Late In",
      value: "12",
      icon: "warning",
      color: "orange",
      trend: "10%",
      trendUp: false,
      type: "progress",
      progress: "35%"
    },
    {
      title: "Early Out",
      value: "8",
      icon: "logout",
      color: "orange",
      trend: "4%",
      trendUp: true,
      subText: "Unplanned"
    },
    {
      title: "Avg Work Hrs",
      value: "8.2h",
      icon: "timelapse",
      color: "blue",
      trend: "0%",
      subText: "Daily average"
    },
    {
      title: "Absent",
      value: "4",
      icon: "person_off",
      color: "emerald",
      trend: "2%",
      trendUp: false,
      subText: "Unexcused"
    },
    {
      title: "Leave",
      value: "6",
      icon: "flight_takeoff",
      color: "emerald",
      trend: "0%",
      subText: "Approved"
    },
    {
      title: "Manual Punch",
      value: "15",
      icon: "pan_tool",
      color: "purple",
      trend: "12%",
      trendUp: true,
      subText: "Corrections"
    }
  ];

  const [stats, setStats] = useState([]);
  const [topPunctual, setTopPunctual] = useState([]);
  const [topAbsentLate, setTopAbsentLate] = useState([]);

  useEffect(() => {
    let isMounted = true;

    const fetchStats = async () => {
      try {
        const data = await getCompanyStats();
        if (isMounted) {
          setStats(Array.isArray(data?.stats) && data.stats.length > 0 ? data.stats : defaultStats);
          setTopPunctual(Array.isArray(data?.top_3_punctual) ? data.top_3_punctual : []);
          setTopAbsentLate(Array.isArray(data?.top_3_absent_late) ? data.top_3_absent_late : []);
        }
      } catch (error) {
        console.error('Failed to fetch company stats:', error);
        if (isMounted) {
          setStats(defaultStats);
          setTopPunctual([]);
          setTopAbsentLate([]);
        }
      }
    };

    fetchStats();

    return () => {
      isMounted = false;
    };
  }, []);

  // Helper for color logic
  const colors = {
    blue: "bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400",
    emerald: "bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400",
    orange: "bg-orange-50 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400",
    purple: "bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400",
  };

  const getPersonName = (item = {}) => item.name || item.employee_name || item.title || "Unknown";
  const getPersonRole = (item = {}) => item.designation || item.department || item.role || "--";
  const getAvatarUrl = (item = {}) => item.photo || item.avatar || item.profile_image || "";

  return (
    <div className="bg-background-light dark:bg-background-dark text-slate-800 dark:text-white  min-h-screen flex flex-col antialiased selection:bg-accent/20 overflow-y-auto ">
      <main className="relative z-10 flex-1 w-full  mx-auto px-6 py-8 flex flex-col gap-8 max-h-[calc(100vh-100px)]">
        <div className="flex flex-wrap items-end justify-between gap-4">
          <div className="flex flex-col gap-1">
            <h1 className="text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white">
              October 25, 2023
            </h1>
            <p className="text-slate-500 dark:text-slate-400 font-medium text-lg">
              Daily attendance performance overview.
            </p>
          </div>

          <div className="flex gap-3">
            <button className="glass-panel px-4 py-2.5 rounded-lg text-sm font-bold text-slate-700 dark:text-white hover:bg-white hover:shadow-md transition-all flex items-center gap-2">
              <span className="material-symbols-outlined text-[18px]">
                calendar_today
              </span>
              Change Date
            </button>

            <button className="bg-primary text-white px-5 py-2.5 rounded-lg text-sm font-bold shadow-lg shadow-primary/25 hover:bg-slate-800 transition-all flex items-center gap-2">
              <span className="material-symbols-outlined text-[18px]">
                download
              </span>
              Export Report
            </button>
          </div>
        </div>

        {/* KPIs */}
        <section className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 xl:grid-cols-9 gap-3">
          {stats.map((item, idx) => (
            <div key={idx} className="glass-panel p-4 rounded-xl flex flex-col gap-1 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
              {/* Header */}
              <div className="flex items-center justify-between mb-2 z-10">
                <p className="text-slate-500 dark:text-slate-400 text-[10px] font-semibold uppercase tracking-wide truncate">
                  {item.title}
                </p>
                <div className={`p-1 rounded-md ${colors[item.color]}`}>
                  <span className="material-symbols-outlined text-[18px] block">
                    {item.icon}
                  </span>
                </div>
              </div>

              {/* Bottom Section */}
              <div className="flex items-end justify-between gap-2 z-10">
                <div className="flex-1 min-w-0">
                  <p className="text-xl font-bold text-slate-900 dark:text-white leading-none">
                    {item.value}
                  </p>

                  {/* Conditional Trend Badge */}
                  {item.trend !== "0%" ? (
                    <div className={`flex items-center text-[9px] font-bold mt-1 px-1 py-0.5 rounded w-fit ${item.trendUp ? 'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20' : 'text-orange-600 bg-orange-50 dark:bg-orange-900/20'
                      }`}>
                      <span className="material-symbols-outlined text-[10px]">
                        {item.trendUp ? 'trending_up' : 'trending_down'}
                      </span>
                      <span>{item.trend}</span>
                    </div>
                  ) : (
                    <p className="text-[9px] text-slate-400 mt-1 font-medium">{item.subText}</p>
                  )}
                </div>

                {/* Conditional Visuals (Sparkline or Progress) */}
                {item.type === 'sparkline' && (
                  <svg className="h-6 w-12 text-blue-500/30" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 100 40">
                    <path d={item.path} vectorEffect="non-scaling-stroke" />
                  </svg>
                )}

                {item.type === 'progress' && (
                  <div className="h-1.5 w-10 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden mb-1">
                    <div
                      className={`h-full rounded-full ${item.color === 'orange' ? 'bg-orange-500' : 'bg-emerald-500'}`}
                      style={{ width: item.progress }}
                    />
                  </div>
                )}
              </div>
            </div>
          ))}
        </section>

        <section className="grid grid-cols-1 lg:grid-cols-4 gap-4 mt-6">
      {/* LEFT: MAIN TRENDS CHART */}
      <div className="lg:col-span-3 flex flex-col glass-panel rounded-xl p-5 shadow-sm">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
          <div>
            <h3 className="text-slate-900 dark:text-white text-sm font-bold uppercase tracking-wide">
              Attendance Trends
            </h3>
            <p className="text-slate-500 dark:text-slate-400 text-xs mt-0.5">
              Stacked breakdown (Present vs Late vs Absent)
            </p>
          </div>
          
          <div className="flex items-center gap-4">
            <div className="flex items-center gap-1.5 text-xs font-medium text-slate-500">
              <span className="h-2.5 w-2.5 rounded-sm bg-blue-500" /> Present
            </div>
            <div className="flex items-center gap-1.5 text-xs font-medium text-slate-500">
              <span className="h-2.5 w-2.5 rounded-sm bg-orange-500" /> Late
            </div>
            <div className="flex items-center gap-1.5 text-xs font-medium text-slate-500">
              <span className="h-2.5 w-2.5 rounded-sm bg-rose-400" /> Absent
            </div>
          </div>
        </div>

        <div className="h-64 w-full pt-2">
          <ResponsiveContainer width="100%" height="100%">
            <BarChart data={chartData} margin={{ top: 8, right: 8, left: -16, bottom: 0 }} barCategoryGap="28%">
              <XAxis
                dataKey="label"
                axisLine={false}
                tickLine={false}
                tick={{ fontSize: 10, fill: '#94a3b8' }}
              />
              <YAxis
                domain={[0, 100]}
                axisLine={false}
                tickLine={false}
                tick={{ fontSize: 10, fill: '#94a3b8' }}
              />
              <Tooltip
                cursor={{ fill: 'rgba(148,163,184,0.08)' }}
                contentStyle={{ borderRadius: '8px', fontSize: '12px' }}
              />
              <Bar dataKey="present" stackId="attendance" fill="#3b82f6" radius={[3, 3, 0, 0]} barSize={50} />
              <Bar dataKey="late" stackId="attendance" fill="#f97316" barSize={50} />
              <Bar dataKey="absent" stackId="attendance" fill="#fb7185" barSize={50} />
            </BarChart>
          </ResponsiveContainer>
        </div>
      </div>

      {/* RIGHT: INSIGHTS COLUMN */}
      <div className="flex flex-col gap-4">
        {/* Dept Donut */}
        <div className="glass-panel rounded-xl p-5 shadow-sm">
          <h3 className="text-slate-900 dark:text-white text-sm font-bold uppercase tracking-wide mb-4">
            By Department
          </h3>
          <div className="flex items-center gap-4">
            <div className="relative w-20 h-20 shrink-0">
              <svg className="rotate-[-90deg]" viewBox="0 0 42 42">
                <circle cx="21" cy="21" r="15.9" fill="transparent" stroke="currentColor" strokeWidth="6" className="text-slate-100 dark:text-slate-800" />
                <circle cx="21" cy="21" r="15.9" fill="transparent" stroke="currentColor" strokeWidth="6" strokeDasharray="40 60" className="text-blue-500" />
                <circle cx="21" cy="21" r="15.9" fill="transparent" stroke="currentColor" strokeWidth="6" strokeDasharray="30 70" strokeDashoffset="-40" className="text-sky-400" />
              </svg>
            </div>
            <div className="flex flex-col gap-1 w-full">
              <div className="flex items-center justify-between text-xs">
                <span className="text-slate-500">Eng</span>
                <span className="font-bold dark:text-white">40%</span>
              </div>
              <div className="flex items-center justify-between text-xs">
                <span className="text-slate-500">Sales</span>
                <span className="font-bold dark:text-white">30%</span>
              </div>
            </div>
          </div>
        </div>

        {/* Punctuality List */}
        <div className="glass-panel rounded-xl p-5 shadow-sm flex-1">
          <div className="flex items-center justify-between mb-4">
            <h3 className="text-slate-900 dark:text-white text-sm font-bold uppercase tracking-wide">Punctuality</h3>
            <button className="text-[10px] text-blue-500 font-bold hover:underline">VIEW ALL</button>
          </div>
          <div className="flex flex-col gap-4">
            {topStaff.map((staff, idx) => (
              <div key={idx} className="flex items-center gap-3">
                {staff.img ? (
                  <img src={staff.img} className="h-8 w-8 rounded-full ring-2 ring-emerald-500/20" alt="" />
                ) : (
                  <div className="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-[10px] font-bold ring-2 ring-blue-500/20">
                    {staff.initial}
                  </div>
                )}
                <div className="flex flex-col flex-1 min-w-0">
                  <span className="text-xs font-bold truncate dark:text-white">{staff.name}</span>
                  <span className="text-[10px] text-slate-400">{staff.dept}</span>
                </div>
                <span className="text-xs font-bold text-emerald-500">{staff.score}</span>
              </div>
            ))}
          </div>
        </div>
      </div>
    </section>

        {/* Table */}
        <div className="glass-panel rounded-2xl p-6 flex flex-col gap-6 mb-8">
          <div className="flex flex-col sm:flex-row justify-between items-center gap-4">
            <h3 className="text-lg font-bold text-slate-900 dark:text-white self-start sm:self-center">
              Daily Attendance Detail
            </h3>

            <div className="flex items-center gap-3 w-full sm:w-auto">
              <div className="relative flex-1 sm:flex-none">
                <span className="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">
                  search
                </span>
                <input
                  className="w-full sm:w-64 pl-10 pr-4 py-2 rounded-lg bg-white/50 dark:bg-white/5 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm transition-all outline-none"
                  placeholder="Search employee..."
                  type="text"
                />
              </div>

              <button className="flex items-center justify-center size-9 rounded-lg border border-slate-200 dark:border-slate-700 bg-white/50 dark:bg-white/5 hover:bg-slate-50 dark:hover:bg-white/10 text-slate-600 dark:text-slate-300 transition-colors">
                <span className="material-symbols-outlined text-[20px]">
                  filter_list
                </span>
              </button>
            </div>
          </div>

          <div className="overflow-x-auto rounded-lg border border-slate-200/60 dark:border-slate-700/60">
            <table className="w-full text-left border-collapse min-w-[800px]">
              <thead>
                <tr className="bg-slate-50/50 dark:bg-white/5 border-b border-slate-200/60 dark:border-slate-700/60">
                  <th className="px-4 py-3 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Employee
                  </th>
                  <th className="px-4 py-3 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Department
                  </th>
                  <th className="px-4 py-3 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-center">
                    Check-In Time
                  </th>
                  <th className="px-4 py-3 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-center">
                    Status
                  </th>
                  <th className="px-4 py-3 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-center">
                    Expected Hours
                  </th>
                  <th className="px-4 py-3 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-right w-32">
                    Actions
                  </th>
                </tr>
              </thead>

              <tbody className="divide-y divide-slate-200/60 dark:divide-slate-700/60 bg-white/30 dark:bg-white/5">
                <tr className="hover:bg-white/50 dark:hover:bg-white/10 transition-colors group relative">
                  <td className="px-4 py-3 whitespace-nowrap">
                    <div className="flex items-center gap-3">
                      <div
                        className="bg-center bg-no-repeat bg-cover rounded-full size-9 ring-1 ring-slate-200 dark:ring-slate-700"
                        aria-label="Avatar"
                        style={{
                          backgroundImage:
                            'url("https://lh3.googleusercontent.com/aida-public/AB6AXuChVikZvPzOLb_18GdwqnI8huX9r1MvP50iNfxOb5LTCNXJ8c_omAG3jLpy802ZKkDPznVfnkuI_WeO260W5_OEcjcQI0h79MuacNeXyIESBkasVC2WjJtPXe_v5l5j5MCK5egCvjQm5hNbbBmEJc4_sukBy4il6wCyB8RLXXX_31B4FnCBZtdPqzpzW53mp50TZqMoqB0u3c3OKiOSaMH1DT_58vkf5Gw_9xUfJFljhSdNj0B4OoaXSavopcj3NtAP3yFTbVVSNw3B")',
                        }}
                      />
                      <div>
                        <p className="text-sm font-bold text-slate-900 dark:text-white">
                          Isabella Chavez
                        </p>
                        <p className="text-xs text-slate-500 dark:text-slate-400">
                          #EMP-0042
                        </p>
                      </div>
                    </div>
                  </td>

                  <td className="px-4 py-3 whitespace-nowrap text-xs text-slate-600 dark:text-slate-300">
                    Operations
                  </td>

                  <td className="px-4 py-3 whitespace-nowrap text-center">
                    <div className="text-sm font-bold text-slate-900 dark:text-white">
                      08:45 AM
                    </div>
                  </td>

                  <td className="px-4 py-3 whitespace-nowrap text-center">
                    <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200">
                      On-Time
                    </span>
                  </td>

                  <td className="px-4 py-3 whitespace-nowrap text-xs text-slate-500 dark:text-slate-400 text-center font-medium">
                    8.0h
                  </td>

                  <td className="px-4 py-3 whitespace-nowrap text-right">
                    <button className="bg-white border border-slate-200 text-slate-600 hover:text-primary hover:border-primary/50 text-xs font-bold py-1.5 px-3 rounded-lg transition-all shadow-sm">
                      Notify Manager
                    </button>
                  </td>
                </tr>

                <tr className="hover:bg-white/50 dark:hover:bg-white/10 transition-colors group relative">
                  <td className="px-4 py-3 whitespace-nowrap">
                    <div className="flex items-center gap-3">
                      <div
                        className="bg-center bg-no-repeat bg-cover rounded-full size-9 ring-1 ring-slate-200 dark:ring-slate-700"
                        aria-label="Avatar"
                        style={{
                          backgroundImage:
                            'url("https://lh3.googleusercontent.com/aida-public/AB6AXuB2u9wlGVNfDR0QuwnkyRdkr-9hrgCmNGG_RhdMXl6guyBVhNN2wUC9FiX4V5PiQZHJjGgLDSKTTlBbe81nrGAoi2zUr9zYUQzIWUs4ckIKLOxOHJ6XkLIPNTArs0Ta9pKwh54rkDH3rQJKUqap6Ewlt_clKrlY-W4Y6YmrTirbOaHd3GmAFpEiNvxtNGv--vvaeDVs582hyG5AtuarrQvw57bw03GCZzGzFTgDkChOteTLpFy0XI0ZFylHq55diBC98R4xJcVAi7za")',
                        }}
                      />
                      <div>
                        <p className="text-sm font-bold text-slate-900 dark:text-white">
                          Marcus Johnson
                        </p>
                        <p className="text-xs text-slate-500 dark:text-slate-400">
                          #EMP-0105
                        </p>
                      </div>
                    </div>
                  </td>

                  <td className="px-4 py-3 whitespace-nowrap text-xs text-slate-600 dark:text-slate-300">
                    Engineering
                  </td>

                  <td className="px-4 py-3 whitespace-nowrap text-center">
                    <div className="text-sm font-bold text-slate-900 dark:text-white">
                      09:12 AM
                    </div>
                  </td>

                  <td className="px-4 py-3 whitespace-nowrap text-center">
                    <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200">
                      Late (12m)
                    </span>
                  </td>

                  <td className="px-4 py-3 whitespace-nowrap text-xs text-slate-500 dark:text-slate-400 text-center font-medium">
                    8.0h
                  </td>

                  <td className="px-4 py-3 whitespace-nowrap text-right">
                    <button className="bg-white border border-slate-200 text-slate-600 hover:text-primary hover:border-primary/50 text-xs font-bold py-1.5 px-3 rounded-lg transition-all shadow-sm">
                      Notify Manager
                    </button>
                  </td>
                </tr>

                <tr className="hover:bg-white/50 dark:hover:bg-white/10 transition-colors group relative">
                  <td className="px-4 py-3 whitespace-nowrap">
                    <div className="flex items-center gap-3">
                      <div
                        className="bg-center bg-no-repeat bg-cover rounded-full size-9 ring-1 ring-slate-200 dark:ring-slate-700"
                        aria-label="Avatar"
                        style={{
                          backgroundImage:
                            'url("https://lh3.googleusercontent.com/aida-public/AB6AXuB-YoUqjybTqXeveQQevfLioIREtrnaW8d2Ot-yn5oTv4C02NUyrfjAKLijbatWLxOAXXlnygg9BupybdWp5HwrGaITZPRx6QQi5WV3cJYhQDKV6V8Paqn-lDhWbQgSr1CIJeGexEBWYTHESlLnehgO_0cTzJTC04ZtegbUoAxAwQDh5i7_JEUqUi0LrpbZ-4nXMB1L9g-wIi4wbnR4eZ_CHNFB98RLBF23jY31hABNoslxa0mMekr9BZC1rJ5-AXrQ0V_Vy3ADBuvD")',
                        }}
                      />
                      <div>
                        <p className="text-sm font-bold text-slate-900 dark:text-white">
                          Robert Fox
                        </p>
                        <p className="text-xs text-slate-500 dark:text-slate-400">
                          #EMP-0112
                        </p>
                      </div>
                    </div>
                  </td>

                  <td className="px-4 py-3 whitespace-nowrap text-xs text-slate-600 dark:text-slate-300">
                    Finance
                  </td>

                  <td className="px-4 py-3 whitespace-nowrap text-center">
                    <div className="text-sm font-bold text-slate-400">--:--</div>
                  </td>

                  <td className="px-4 py-3 whitespace-nowrap text-center">
                    <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200">
                      Not Checked In
                    </span>
                  </td>

                  <td className="px-4 py-3 whitespace-nowrap text-xs text-slate-500 dark:text-slate-400 text-center font-medium">
                    8.0h
                  </td>

                  <td className="px-4 py-3 whitespace-nowrap text-right">
                    <button className="bg-white border border-slate-200 text-slate-600 hover:text-primary hover:border-primary/50 text-xs font-bold py-1.5 px-3 rounded-lg transition-all shadow-sm">
                      Notify Manager
                    </button>
                  </td>
                </tr>

                <tr className="hover:bg-white/50 dark:hover:bg-white/10 transition-colors group relative">
                  <td className="px-4 py-3 whitespace-nowrap">
                    <div className="flex items-center gap-3">
                      <div
                        className="bg-center bg-no-repeat bg-cover rounded-full size-9 ring-1 ring-slate-200 dark:ring-slate-700"
                        aria-label="Avatar"
                        style={{
                          backgroundImage:
                            'url("https://lh3.googleusercontent.com/aida-public/AB6AXuB2fa5OfjSPNv4bba9DENcTDThLB6vSHf0MwvBPqao8y5lYxZv6UhIfvl9eyhNiV7sUuFN8TrkMUMPrVDDc7UaVF4mNRLEY7Zz4X1MtsPTJLtgogdk7LvIOvLE-cjDYpX1tRM4t5gjEFxQtX6ZEUZJ9LmZHKn5U7bhvotskTGRm3apag159wnm57RaqPz1XyCNZBuwWpUtyyHUF8jhLV_MqZ57Iwk2t_7-1l0EEySTF1-idGSK54ieCm4CIO3B4RP8ef0UG9KLSjRuG")',
                        }}
                      />
                      <div>
                        <p className="text-sm font-bold text-slate-900 dark:text-white">
                          Sophia Miller
                        </p>
                        <p className="text-xs text-slate-500 dark:text-slate-400">
                          #EMP-0033
                        </p>
                      </div>
                    </div>
                  </td>

                  <td className="px-4 py-3 whitespace-nowrap text-xs text-slate-600 dark:text-slate-300">
                    Marketing
                  </td>

                  <td className="px-4 py-3 whitespace-nowrap text-center">
                    <div className="text-sm font-bold text-slate-900 dark:text-white">
                      08:50 AM
                    </div>
                  </td>

                  <td className="px-4 py-3 whitespace-nowrap text-center">
                    <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200">
                      On-Time
                    </span>
                  </td>

                  <td className="px-4 py-3 whitespace-nowrap text-xs text-slate-500 dark:text-slate-400 text-center font-medium">
                    8.0h
                  </td>

                  <td className="px-4 py-3 whitespace-nowrap text-right">
                    <button className="bg-white border border-slate-200 text-slate-600 hover:text-primary hover:border-primary/50 text-xs font-bold py-1.5 px-3 rounded-lg transition-all shadow-sm">
                      Notify Manager
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div className="flex items-center justify-between pt-2">
            <p className="text-sm text-slate-500">
              Showing{" "}
              <span className="font-bold text-slate-900 dark:text-white">
                1-4
              </span>{" "}
              of{" "}
              <span className="font-bold text-slate-900 dark:text-white">
                142
              </span>{" "}
              employees
            </p>

            <div className="flex gap-2">
              <button
                className="px-3 py-1 text-sm font-medium rounded-md border border-slate-200 dark:border-slate-700 text-slate-500 disabled:opacity-50"
                disabled
              >
                Prev
              </button>
              <button className="px-3 py-1 text-sm font-medium rounded-md bg-white border border-slate-200 dark:border-slate-700 dark:bg-white/10 dark:text-white shadow-sm hover:bg-slate-50">
                1
              </button>
              <button className="px-3 py-1 text-sm font-medium rounded-md border border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-50 dark:hover:bg-white/10 dark:hover:text-white">
                2
              </button>
              <button className="px-3 py-1 text-sm font-medium rounded-md border border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-50 dark:hover:bg-white/10 dark:hover:text-white">
                Next
              </button>
            </div>
          </div>
        </div>
      </main>
    </div>
  );
}
