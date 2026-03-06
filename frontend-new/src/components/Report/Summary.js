"use client";

// app/executive-attendance/page.tsx
// Next.js App Router page component (React)
// If you prefer a reusable component, move JSX into /components and import it here.

import React, { useEffect, useState, useRef } from 'react';
import { getCompanyStats, getCompanyStatsDailyAttendance, getCompanyStatsDepartmentBreakdown, getCompanyStatsHourlyTrends, getCompanyStatsPunctuality, getCompanyStatsSummaryPayload } from '@/lib/endpoint/dashboard';
import MonthPicker from '@/components/ui/MonthPicker';
import MultiDropDown from '@/components/ui/MultiDropDown';
import { getBranches, getDepartmentsByBranchIds } from '@/lib/api';
import {
  ResponsiveContainer,
  BarChart,
  Bar,
  XAxis,
  YAxis,
  Tooltip,
  CartesianGrid,
} from 'recharts';
import ProfilePicture from '../ProfilePicture';
import { Download, MoreVertical } from 'lucide-react';
import { getUser } from '@/config/index';
import DatePicker from '../ui/DatePicker';
import DropDown from '../ui/DropDown';
import { set } from 'date-fns';

import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
} from "@/components/ui/dropdown-menu";

export default function ExecutiveAttendanceDashboardPage() {

  const [reportType, setReportType] = useState('monthly'); // 'daily' or 'monthly'

  const [selectedDate, setSelectedDate] = useState(() => {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  });

  const [stats, setStats] = useState([]);
  const [chartData, setChartData] = useState([]);
  const [departmentBreakdown, setDepartmentBreakdown] = useState([]);
  const [punctualityData, setPunctualityData] = useState([]);
  const [dailyAttendanceRows, setDailyAttendanceRows] = useState([]);
  const [attendanceSearchInput, setAttendanceSearchInput] = useState('');
  const [attendanceSearchText, setAttendanceSearchText] = useState('');
  const [attendancePage, setAttendancePage] = useState(1);
  const [attendanceMeta, setAttendanceMeta] = useState({ total: 0, page: 1, per_page: 10, last_page: 1, from: 0, to: 0 });
  const [branches, setBranches] = useState([]);
  const [departments, setDepartments] = useState([]);
  const [selectedBranchIds, setSelectedBranchIds] = useState([]);
  const [selectedDepartmentIds, setSelectedDepartmentIds] = useState([]);
  const [isExporting, setIsExporting] = useState(false);
  const [pdfData, setPdfData] = useState(null);
  const pdfContainerRef = useRef(null);
  const [selectedMonthRange, setSelectedMonthRange] = useState(() => {
    const now = new Date();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const currentMonth = `${now.getFullYear()}-${month}`;

    return {
      from: currentMonth,
      to: currentMonth,
    };
  });

  const getMonthBounds = (monthValueFrom, monthValueTo) => {
    const [yearFrom, monthFrom] = monthValueFrom.split('-').map(Number);
    const [yearTo, monthTo] = monthValueTo.split('-').map(Number);

    if (!yearFrom || !monthFrom || !yearTo || !monthTo) {
      const now = new Date();
      const start = new Date(now.getFullYear(), now.getMonth(), 1);
      const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
      return {
        from_date: `${start.getFullYear()}-${String(start.getMonth() + 1).padStart(2, '0')}-${String(start.getDate()).padStart(2, '0')}`,
        to_date: `${end.getFullYear()}-${String(end.getMonth() + 1).padStart(2, '0')}-${String(end.getDate()).padStart(2, '0')}`,
      };
    }

    const fromDate = new Date(yearFrom, monthFrom - 1, 1);
    const toDate = new Date(yearTo, monthTo, 0);

    const start = fromDate <= toDate ? fromDate : toDate;
    const end = fromDate <= toDate ? toDate : fromDate;

    return {
      from_date: `${start.getFullYear()}-${String(start.getMonth() + 1).padStart(2, '0')}-${String(start.getDate()).padStart(2, '0')}`,
      to_date: `${end.getFullYear()}-${String(end.getMonth() + 1).padStart(2, '0')}-${String(end.getDate()).padStart(2, '0')}`,
    };
  };

  const selectedMonthLabel = (() => {
    const fromMonth = selectedMonthRange?.from;
    const toMonth = selectedMonthRange?.to || fromMonth;

    if (!fromMonth || !toMonth) {
      return 'Select month range';
    }

    const [fromYear, fromMonthValue] = fromMonth.split('-').map(Number);
    const [toYear, toMonthValue] = toMonth.split('-').map(Number);

    const fromDate = new Date(fromYear, (fromMonthValue || 1) - 1, 1);
    const toDate = new Date(toYear, (toMonthValue || 1) - 1, 1);

    const start = fromDate <= toDate ? fromDate : toDate;
    const end = fromDate <= toDate ? toDate : fromDate;

    const startLabel = start.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
    const endLabel = end.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });

    return startLabel === endLabel ? startLabel : `${startLabel} - ${endLabel}`;
  })();

  useEffect(() => {
    let isMounted = true;

    const fetchBranches = async () => {
      try {
        const branchList = await getBranches();
        if (isMounted) {
          setBranches(Array.isArray(branchList) ? branchList : []);
        }
      } catch (error) {
        console.error('Failed to fetch branches:', error);
        if (isMounted) {
          setBranches([]);
        }
      }
    };

    fetchBranches();

    return () => {
      isMounted = false;
    };
  }, []);

  useEffect(() => {
    let isMounted = true;

    const fetchDepartments = async () => {
      if (!selectedBranchIds.length) {
        if (isMounted) {
          setDepartments([]);
          setSelectedDepartmentIds([]);
        }
        return;
      }

      try {
        const departmentList = await getDepartmentsByBranchIds(selectedBranchIds);
        if (isMounted) {
          const normalizedDepartments = Array.isArray(departmentList) ? departmentList : [];
          setDepartments(normalizedDepartments);

          const validDepartmentIds = new Set(normalizedDepartments.map((department) => department.id));
          setSelectedDepartmentIds((currentSelectedIds) => currentSelectedIds.filter((id) => validDepartmentIds.has(id)));
        }
      } catch (error) {
        console.error('Failed to fetch departments:', error);
        if (isMounted) {
          setDepartments([]);
          setSelectedDepartmentIds([]);
        }
      }
    };

    fetchDepartments();

    return () => {
      isMounted = false;
    };
  }, [selectedBranchIds]);

  // Data is fetched only when user clicks Submit button via fetchAllData()

  // Daily attendance data is fetched only when user clicks Submit button via fetchAllData()

  useEffect(() => {
    const debounceTimer = setTimeout(() => {
      setAttendancePage(1);
      setAttendanceSearchText(attendanceSearchInput.trim());
    }, 350);

    return () => clearTimeout(debounceTimer);
  }, [attendanceSearchInput]);

  const handleMonthChange = (monthRangeValue) => {
    if (!monthRangeValue?.from) {
      return;
    }

    setSelectedMonthRange({
      from: monthRangeValue.from,
      to: monthRangeValue.to || monthRangeValue.from,
    });
  };

  const fetchAllData = async () => {

    const user = await getUser();

    try {
      let isDaily = reportType === 'daily';

      let dateRange = getMonthBounds(selectedMonthRange?.from, selectedMonthRange?.to || selectedMonthRange?.from);

      if (isDaily) {

        dateRange = {
          from_date: selectedDate,
          to_date: selectedDate,
        }

      }

      const payload = {
        ...dateRange,
        branch_ids: selectedBranchIds,
        department_ids: selectedDepartmentIds,
        company_id: user?.company_id || 0,
      };

      const [statsResponse, hourlyTrendResponse, departmentResponse, punctualityResponse] = await Promise.all([
        getCompanyStats(payload),
        getCompanyStatsHourlyTrends(payload),
        getCompanyStatsDepartmentBreakdown(payload),
        getCompanyStatsPunctuality(payload),
      ]);

      setStats(Array.isArray(statsResponse?.stats) && statsResponse.stats.length > 0 ? statsResponse.stats : []);

      const normalizedChartData = Array.isArray(hourlyTrendResponse?.data)
        ? hourlyTrendResponse.data.map((item) => ({
          label: item?.label,
          present: Number(item?.present || 0),
          late: Number(item?.late || 0),
          absent: Number(item?.absent || 0),
        }))
        : [];

      const normalizedDepartmentData = Array.isArray(departmentResponse?.data)
        ? departmentResponse.data.map((item) => ({
          name: item?.name || 'Unknown',
          count: Number(item?.count || 0),
          percentage: Number(item?.percentage || 0),
        }))
        : [];

      const normalizedPunctualityData = Array.isArray(punctualityResponse?.data)
        ? punctualityResponse.data.map((item) => {
          const displayName = item?.name || 'Unknown';
          const initials = displayName
            .split(' ')
            .filter(Boolean)
            .slice(0, 2)
            .map((part) => part[0]?.toUpperCase())
            .join('') || 'NA';

          return {
            name: displayName,
            dept: item?.dept || '---',
            score: item?.score || '0%',
            img: item?.img || null,
            initial: initials,
          };
        })
        : [];

      setChartData(normalizedChartData);
      setDepartmentBreakdown(normalizedDepartmentData);
      setPunctualityData(normalizedPunctualityData);

      // Also fetch daily attendance
      const dailyPayload = {
        ...dateRange,
        branch_ids: selectedBranchIds,
        department_ids: selectedDepartmentIds,
        search: attendanceSearchText.trim(),
        page: 1,
        per_page: attendanceMeta.per_page || 10,
      };

      const dailyAttendanceResponse = await getCompanyStatsDailyAttendance(dailyPayload);

      const normalizedDailyAttendanceRows = Array.isArray(dailyAttendanceResponse?.data)
        ? dailyAttendanceResponse.data.map((item) => ({
          id: item?.system_user_id,
          employeeCode: item?.employee_code || '---',
          name: item?.name || 'Unknown',
          department: item?.department || '---',
          daysPresent: Number(item?.days_present ?? item?.daysPresent ?? 0),
          totalDays: Number(item?.total_days ?? item?.totalDays ?? 0),
          rate: Number(item?.rate || 0),
          trend: Number(item?.trend || 0),
          status: item?.status || 'CRITICAL',
          img: item?.img || null,
        }))
        : [];

      setDailyAttendanceRows(normalizedDailyAttendanceRows);
      setAttendancePage(1);
      setAttendanceMeta({
        total: Number(dailyAttendanceResponse?.meta?.total || 0),
        page: 1,
        per_page: Number(dailyAttendanceResponse?.meta?.per_page || 10),
        last_page: Number(dailyAttendanceResponse?.meta?.last_page || 1),
        from: Number(dailyAttendanceResponse?.meta?.from || 0),
        to: Number(dailyAttendanceResponse?.meta?.to || 0),
      });
    } catch (error) {
      console.error('Failed to fetch data:', error);
    }
  };

  const handleExportPdf = async () => {
    try {
      setIsExporting(true);
      const user = await getUser();

      let isDaily = reportType === 'daily';

      let dateRange = getMonthBounds(selectedMonthRange?.from, selectedMonthRange?.to || selectedMonthRange?.from);

      if (isDaily) {

        dateRange = {
          from_date: selectedDate,
          to_date: selectedDate,
        }

      }

      // Build URL parameters for the HTML template
      const params = new URLSearchParams();
      params.append('api_base', 'https://backend.mytime2cloud.com/api');
      params.append('company_id', user?.company_id || 0);
      params.append('from_date', dateRange.from_date);
      params.append('to_date', dateRange.to_date);

      if (selectedBranchIds && selectedBranchIds.length > 0) {
        params.append('branch_ids', selectedBranchIds.join(','));
      }
      if (selectedDepartmentIds && selectedDepartmentIds.length > 0) {
        params.append('department_ids', selectedDepartmentIds.join(','));
      }

      // Open the standalone HTML template
      const templateUrl = `https://summary-report.netlify.app/${reportType == 'daily' ? "daily" : "monthly"}/index.html?${params.toString()}`;
      window.open(templateUrl, '_blank');

    } catch (error) {
      console.error('Failed to export PDF:', error);
    } finally {
      setIsExporting(false);
    }
  };

  // Helper for color logic
  const colors = {
    blue: "bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400",
    emerald: "bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400",
    orange: "bg-orange-50 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400",
    purple: "bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400",
  };

  const departmentSegmentColors = ['text-blue-500', 'text-sky-400', 'text-emerald-500', 'text-orange-500'];
  const departmentDonutData = departmentBreakdown.slice(0, 4);
  const departmentDonutSegments = departmentDonutData.reduce(
    (acc, department, index) => {
      acc.segments.push({
        key: `${department.name}-${index}`,
        dashArray: `${department.percentage} ${100 - department.percentage}`,
        dashOffset: -acc.offset,
        colorClass: departmentSegmentColors[index % departmentSegmentColors.length],
      });
      acc.offset += department.percentage;
      return acc;
    },
    { offset: 0, segments: [] }
  ).segments;

  const getAttendanceStatusUi = (row) => {
    const status = (row.status || '').toUpperCase();

    if (status === 'GOOD') {
      return {
        label: 'GOOD',
        className: 'bg-emerald-100 border-emerald-200 text-emerald-700 dark:bg-emerald-900/30 dark:border-emerald-800/40 dark:text-emerald-300',
      };
    }

    if (status === 'WARNING') {
      return {
        label: 'WARNING',
        className: 'bg-amber-100 border-amber-200 text-amber-700 dark:bg-amber-900/30 dark:border-amber-800/40 dark:text-amber-300',
      };
    }

    return {
      label: 'CRITICAL',
      className: 'bg-red-100 border-red-200 text-red-700 dark:bg-red-900/30 dark:border-red-800/40 dark:text-red-300',
    };
  };

  const getRateUi = (rate) => {
    if (rate >= 90) return { barClass: 'bg-emerald-500' };
    if (rate >= 75) return { barClass: 'bg-amber-500' };
    return { barClass: 'bg-red-500' };
  };

  const getTrendUi = (trend) => {
    const trendValue = Math.round(Number(trend || 0));

    if (trendValue > 0) {
      return { label: `↗ +${trendValue}%`, className: 'text-emerald-600 dark:text-emerald-400' };
    }
    if (trendValue < 0) {
      return { label: `↘ ${trendValue}%`, className: 'text-red-600 dark:text-red-400' };
    }
    return { label: '— 0%', className: 'text-slate-600 dark:text-slate-300' };
  };

  const currentAttendancePage = attendanceMeta.page || attendancePage;
  const canGoPrevAttendancePage = currentAttendancePage > 1;
  const canGoNextAttendancePage = currentAttendancePage < attendanceMeta.last_page;


  return (
    <div className="bg-background-light dark:bg-background-dark text-slate-800 dark:text-white  min-h-screen flex flex-col antialiased selection:bg-accent/20 overflow-y-auto ">
      {/* Hidden PDF Template Container */}
      <main className="relative z-10 flex-1 w-full  mx-auto px-6 py-8 flex flex-col gap-8 max-h-[calc(100vh-100px)]">
        <div className="flex flex-wrap items-end justify-between gap-4">
          <div className="flex flex-col gap-1">
            <h1 className="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
              {selectedMonthLabel}
            </h1>
            <p className="text-slate-600 dark:text-slate-300 font-medium text-lg">
              Monthly attendance performance overview.
            </p>
          </div>

          <div className="flex gap-3 items-center">
            <MultiDropDown
              placeholder={'Select Branch'}
              items={branches}
              value={selectedBranchIds}
              onChange={setSelectedBranchIds}
              width="w-[250px]"
              badgesCount={1}
            />

            <MultiDropDown
              placeholder={'Select Department'}
              items={departments}
              value={selectedDepartmentIds}
              onChange={setSelectedDepartmentIds}
              width="w-[250px]"
              badgesCount={1}
            />

            <div>
              <DropDown
                value={reportType}
                items={[
                  { name: 'Daily', id: 'daily' },
                  { name: 'Monthly', id: 'monthly' },
                ]}
                onChange={setReportType}
                placeholder="Pick a Report type"
              />
            </div>

            {
              /* For daily report, we show date picker. For monthly report, we show month picker */
              reportType === 'daily' ? (
                <div className=''>
                  <DatePicker
                    value={selectedDate}
                    onChange={setSelectedDate}
                    placeholder="Pick a date"
                    className="w-[250px]"
                  />
                </div>
              ) : <MonthPicker
                value={selectedMonthRange}
                onChange={handleMonthChange}
                className="min-w-[240px]"
              />
            }





            <button onClick={fetchAllData} className="bg-primary text-white px-5 py-2.5 rounded-lg text-sm font-bold shadow-lg shadow-primary/25 hover:bg-slate-800 transition-all flex items-center gap-2">
              Submit
            </button>


            <DropdownMenu>
              <DropdownMenuTrigger
                asChild
                /* This prevents the dropdown trigger itself from triggering the row click */
                onClick={(e) => e.stopPropagation()}
              >
                <div className="p-2 rounded-full cursor-pointer w-fit">
                  <MoreVertical className="w-5 h-5 text-gray-400" />
                </div>
              </DropdownMenuTrigger>

              <DropdownMenuContent
                align="end"
                className="w-32 bg-white dark:bg-gray-900 shadow-md rounded-md py-1"
              /* This prevents clicking inside the menu from triggering the row click */
              >
                <DropdownMenuItem
                  onClick={handleExportPdf}
                  className="flex items-center border border-border gap-2 px-3 py-2 text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
                >
                  <span className="text-primary font-medium">PDF</span>
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>

          </div>
        </div>

        {/* KPIs */}
        <section className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 xl:grid-cols-9 gap-3">
          {stats.map((item, idx) => (
            <div key={idx} className="glass-panel p-4 rounded-xl flex flex-col gap-1 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
              {/* Header */}
              <div className="flex items-center justify-between mb-2 z-10">
                <p className="text-slate-600 dark:text-slate-300 text-[10px] font-semibold uppercase tracking-wide truncate">
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
                <p className="text-slate-600 dark:text-slate-300 text-xs mt-0.5">
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
                  <CartesianGrid strokeDasharray="3 3" stroke="#e2e8f0" />
                  <XAxis
                    dataKey="label"
                    axisLine={false}
                    tickLine={false}
                    tick={{ fontSize: 10, fill: '#94a3b8' }}
                  />
                  <YAxis
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
              {departmentDonutData.length > 0 ? (
                <div className="flex items-center gap-4">
                  <div className="relative w-20 h-20 shrink-0">
                    <svg className="rotate-[-90deg]" viewBox="0 0 42 42">
                      <circle cx="21" cy="21" r="15.9" fill="transparent" stroke="currentColor" strokeWidth="6" className="text-slate-100 dark:text-slate-800" />
                      {departmentDonutSegments.map((segment) => (
                        <circle
                          key={segment.key}
                          cx="21"
                          cy="21"
                          r="15.9"
                          fill="transparent"
                          stroke="currentColor"
                          strokeWidth="6"
                          strokeDasharray={segment.dashArray}
                          strokeDashoffset={segment.dashOffset}
                          className={segment.colorClass}
                        />
                      ))}
                    </svg>
                  </div>
                  <div className="flex flex-col gap-1 w-full">
                    {departmentDonutData.map((department) => (
                      <div key={department.name} className="flex items-center justify-between text-xs">
                        <span className="text-slate-500 truncate max-w-[120px]">{department.name}</span>
                        <span className="font-bold dark:text-white">{department.percentage}%</span>
                      </div>
                    ))}
                  </div>
                </div>
              ) : (
                <p className="text-xs text-slate-600 dark:text-slate-300">No department data found for selected filters.</p>
              )}
            </div>

            {/* Punctuality List */}
            <div className="glass-panel rounded-xl p-5 shadow-sm flex-1">
              <div className="flex items-center justify-between mb-4">
                <h3 className="text-slate-900 dark:text-white text-sm font-bold uppercase tracking-wide">Punctuality</h3>
                <button className="text-[10px] text-blue-500 font-bold hover:underline">VIEW ALL</button>
              </div>
              <div className="flex flex-col gap-4">
                {punctualityData.length > 0 ? punctualityData.map((staff, idx) => (
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
                )) : (
                  <p className="text-xs text-slate-600 dark:text-slate-300">No punctuality data found for selected filters.</p>
                )}
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
                  value={attendanceSearchInput}
                  onChange={(event) => setAttendanceSearchInput(event.target.value)}
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

          <div className="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 overflow-y-auto">
            <table className="w-full text-left border-collapse min-w-[800px]">
              <thead>
                <tr className="bg-slate-100 dark:bg-slate-800 border-y border-slate-200 dark:border-slate-700">
                  <th className="px-4 py-3 text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                    Employee
                  </th>
                  <th className="px-4 py-3 text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                    Department
                  </th>
                  <th className="px-4 py-3 text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 text-center">
                    Days Present
                  </th>
                  <th className="px-4 py-3 text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 text-center">
                    Rate
                  </th>
                  <th className="px-4 py-3 text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 text-center">
                    Trend
                  </th>
                  <th className="px-4 py-3 text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 text-center">
                    Status
                  </th>
                  <th className="px-4 py-3 text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 text-right w-32">
                    Action
                  </th>
                </tr>
              </thead>

              <tbody className="divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-900">
                {dailyAttendanceRows.length > 0 ? dailyAttendanceRows.map((row) => {
                  const statusUi = getAttendanceStatusUi(row);
                  const trendUi = getTrendUi(row.trend);
                  const rateUi = getRateUi(row.rate);
                  const daysPresentValue = Number.isFinite(Number(row.daysPresent)) ? Number(row.daysPresent) : 0;
                  const totalDaysValue = Number.isFinite(Number(row.totalDays)) ? Number(row.totalDays) : 0;
                  const initials = row.name
                    .split(' ')
                    .filter(Boolean)
                    .slice(0, 2)
                    .map((part) => part[0]?.toUpperCase())
                    .join('') || 'NA';

                  return (
                    <tr key={row.id} className="hover:bg-slate-50 dark:hover:bg-slate-800/70 transition-colors group relative">
                      <td className="px-4 py-3 whitespace-nowrap">
                        <div className="flex items-center gap-3">
                          <ProfilePicture src={row.img} />
                          <div>
                            <p className="text-sm font-bold text-slate-800 dark:text-slate-100 group-hover:text-slate-950 dark:group-hover:text-white transition-colors">
                              {row.name}
                            </p>
                            <p className="text-xs text-slate-600 dark:text-slate-300">
                              #{row.employeeCode}
                            </p>
                          </div>
                        </div>
                      </td>

                      <td className="px-4 py-3 whitespace-nowrap text-xs text-slate-600 dark:text-slate-300">
                        {row.department}
                      </td>

                      <td className="px-4 py-3 whitespace-nowrap text-center text-sm font-semibold text-slate-700 dark:text-slate-200">
                        {daysPresentValue}/{totalDaysValue}
                      </td>

                      <td className="px-4 py-3 whitespace-nowrap text-center">
                        <div className="flex items-center justify-center gap-3">
                          <div className="h-2 w-20 rounded-full bg-slate-200 dark:bg-white/10 overflow-hidden">
                            <div
                              className={`h-full rounded-full ${rateUi.barClass}`}
                              style={{ width: `${Math.max(0, Math.min(100, row.rate))}%` }}
                            />
                          </div>
                          <span className="text-sm font-semibold text-slate-700 dark:text-slate-200">{Math.round(Number(row.rate || 0))}%</span>
                        </div>
                      </td>

                      <td className={`px-4 py-3 whitespace-nowrap text-center text-sm font-semibold ${trendUi.className}`}>
                        {trendUi.label}
                      </td>

                      <td className="px-4 py-3 whitespace-nowrap text-center">
                        <span className={`inline-flex items-center px-3 py-1 rounded-md text-[11px] font-semibold border ${statusUi.className}`}>
                          {statusUi.label}
                        </span>
                      </td>

                      <td className="px-4 py-3 whitespace-nowrap text-right">
                        <button className="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 px-2">
                          <span className="material-symbols-outlined text-[20px]">more_vert</span>
                        </button>
                      </td>
                    </tr>
                  );
                }) : (
                  <tr>
                    <td colSpan={7} className="px-4 py-8 text-center text-sm text-slate-600 dark:text-slate-300">
                      No attendance detail found for selected filters.
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>

          <div className="flex items-center justify-between pt-2">
            <p className="text-sm text-slate-500">
              Showing{" "}
              <span className="font-bold text-slate-900 dark:text-white">
                {attendanceMeta.from}-{attendanceMeta.to}
              </span>{" "}
              of{" "}
              <span className="font-bold text-slate-900 dark:text-white">
                {attendanceMeta.total}
              </span>{" "}
              employees
            </p>

            <div className="flex gap-2">
              <button
                className="px-3 py-1 text-sm font-medium rounded-md border border-slate-200 dark:border-slate-700 text-slate-500 disabled:opacity-50"
                onClick={() => setAttendancePage((current) => Math.max(1, current - 1))}
                disabled={!canGoPrevAttendancePage}
              >
                Prev
              </button>
              <span className="px-3 py-1 text-sm font-medium rounded-md bg-white border border-slate-200 dark:border-slate-700 dark:bg-white/10 dark:text-white shadow-sm">
                Page {currentAttendancePage} of {attendanceMeta.last_page}
              </span>
              <button
                className="px-3 py-1 text-sm font-medium rounded-md border border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-50 dark:hover:bg-white/10 dark:hover:text-white disabled:opacity-50"
                onClick={() => setAttendancePage((current) => Math.min(attendanceMeta.last_page, current + 1))}
                disabled={!canGoNextAttendancePage}
              >
                Next
              </button>
            </div>
          </div>
        </div>
      </main>
    </div>
  );
}
