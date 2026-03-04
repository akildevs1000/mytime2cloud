"use client";

// app/executive-attendance/page.tsx
// Next.js App Router page component (React)
// If you prefer a reusable component, move JSX into /components and import it here.

import React, { useEffect, useState, useRef } from 'react';
import { getBranches, getDepartmentsByBranchIds } from '@/lib/api';
import { getReportNotifications } from '@/lib/endpoint/automation';


export default function ExecutiveAttendanceDashboardPage() {


    const [stats, setStats] = useState([]);
    const [chartData, setChartData] = useState([]);
    const [departmentBreakdown, setDepartmentBreakdown] = useState([]);
    const [punctualityData, setPunctualityData] = useState([]);
    const [dailyAttendanceRows, setDailyAttendanceRows] = useState([]);
    const [attendanceSearchInput, setAttendanceSearchInput] = useState('');
    const [attendanceSearchText, setAttendanceSearchText] = useState('');
    const [attendancePage, setAttendancePage] = useState(1);
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

    // setAttendanceMeta
    const [attendanceMeta, setAttendanceMeta] = useState({
        total: 0,
        page: 1,
    });

    const fetchAllData = async () => {

        try {


            const params = {
                page: 1,
                per_page: 1000,
                sortDesc: 'false',
                branch_ids: selectedBranchIds,
                search: null, // Only include search if it's not empty
                types: [] // Only fetch absent type notifications for this page
            };
            const result = await getReportNotifications(params);

            console.log(result);



            setDailyAttendanceRows(result.data);
            setAttendancePage(1);
            setAttendanceMeta({
                total: Number(result?.meta?.total || 0),
                page: 1,
                per_page: Number(result?.meta?.per_page || 10),
                last_page: Number(result?.meta?.last_page || 1),
                from: Number(result?.meta?.from || 0),
                to: Number(result?.meta?.to || 0),
            });
        } catch (error) {
            console.error('Failed to fetch data:', error);
        }
    };

    useEffect(() => {
        fetchAllData();
    }, [selectedBranchIds, selectedDepartmentIds, selectedMonthRange]);

    useEffect(() => {
        const debounceTimer = setTimeout(() => {
            setAttendancePage(1);
            setAttendanceSearchText(attendanceSearchInput.trim());
        }, 350);

        return () => clearTimeout(debounceTimer);
    }, [attendanceSearchInput]);


    return (
        <>
            <div className="overflow-auto">
                <table className="w-full text-left border-collapse min-w-[200px]">
                    <thead>
                        <tr className="bg-slate-100 dark:bg-slate-800 border-y border-slate-200 dark:border-slate-700">
                            <th className="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Branch</th>
                            <th className="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Type</th>
                            <th className="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Subject</th>
                            <th className="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-center whitespace-nowrap">Time</th>
                            {/* <th className="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-center whitespace-nowrap">Medium</th> */}
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-900">
                        {dailyAttendanceRows.length > 0 ? dailyAttendanceRows.map((row) => (
                            <tr key={row.id} className="hover:bg-slate-50 dark:hover:bg-slate-800/70 transition-colors group relative">
                                <td className="px-6 py-4 whitespace-nowrap text-xs text-slate-600 dark:text-slate-300">{row?.branch?.branch_name || 'N/A'}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-xs text-slate-600 dark:text-slate-300">{row?.type || 'N/A'}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-xs text-slate-600 dark:text-slate-300">{row?.subject || 'N/A'}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-xs text-slate-600 dark:text-slate-300">{row?.time || row?.from_time + " - "+ row?.to_time || 'N/A'}</td>
                                {/* <td className="px-6 py-4 whitespace-nowrap text-xs text-slate-600 dark:text-slate-300">  {Array.isArray(row.mediums)
                                    ? row.mediums.join(", ")
                                    : row.medium || row.mediums || "N/A"}</td> */}
                            </tr>
                        )) : (
                            <tr>
                                <td colSpan={5} className="px-6 py-12 text-center text-sm text-slate-500 dark:text-slate-400">No attendance detail found for selected filters.</td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </>
    );
}
