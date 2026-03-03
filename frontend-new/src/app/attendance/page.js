"use client";

import React, { useState } from 'react';
import ExecutiveAttendanceDashboardPage from '@/components/Report/Summary';
import AttendanceTable from '@/components/Report/Report';
import PerformanceReport from '@/components/Report/Performance';

// Define the available tabs
const TABS = ['Attendance', 'Summary', 'Performance'];

export default function AttendancePage() {
    const [activeTab, setActiveTab] = useState('Attendance');

    const renderContent = () => {
        switch (activeTab) {
            case 'Attendance': return <AttendanceTable />;
            case 'Summary': return <ExecutiveAttendanceDashboardPage />;
            case 'Performance': return <PerformanceReport />;
            default: return null;
        }
    };

    // return <AttendanceTable />;

    return (
        <div className="w-full p-4 pb-24 overflow-y-auto max-h-[calc(100vh)]">
            {/* Header and Tab Bar */}
            <div className="flex flex-col md:flex-row md:items-center justify-between  mb-6">
                <h1 className="text-xl font-bold text-slate-800 dark:text-white mb-4 md:mb-0">
                </h1>

                <div className="flex space-x-8">
                    {TABS.map((tab) => (
                        <button
                            key={tab}
                            onClick={() => setActiveTab(tab)}
                            className={`pb-3 px-1 text-sm font-bold tracking-wide uppercase transition-colors border-b-[3px] ${
                                activeTab === tab
                                    ? 'border-[#7f19e6] text-[#7f19e6]'
                                    : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'
                            }`}
                        >
                            {tab}
                        </button>
                    ))}
                </div>
            </div>

            {/* Render Component Content */}
            <div className="animate-in fade-in duration-500 overflow-y-auto max-h-[calc(100vh-100px)]">
                {renderContent()}
            </div>
        </div>
    );
}