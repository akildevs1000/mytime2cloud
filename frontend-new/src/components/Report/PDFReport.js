'use client';

import React, { useState } from 'react';

const PDFReport = () => {
  const [isDarkMode, setIsDarkMode] = useState(false);

  const handlePrint = () => {
    window.print();
  };

  const handleDownloadPDF = () => {
    // PDF download functionality can be added here
    console.log('Downloading PDF...');
    // You can use libraries like html2pdf or jsPDF for this
  };

  const handleEmail = () => {
    console.log('Sending email...');
  };

  const handleClose = () => {
    console.log('Closing preview...');
  };

  const attendanceData = [
    {
      id: 1,
      name: 'John Doe',
      department: 'Logistics',
      shift: '09:00 - 18:00',
      checkIn: '09:12',
      checkOut: '18:05',
      late: 12,
      status: 'Late',
      statusColor: 'yellow',
    },
    {
      id: 2,
      name: 'Alice Smith',
      department: 'Marketing',
      shift: '09:00 - 18:00',
      checkIn: '08:55',
      checkOut: '18:10',
      late: null,
      status: 'Present',
      statusColor: 'green',
    },
    {
      id: 3,
      name: 'Robert Johnson',
      department: 'Sales',
      shift: '10:00 - 19:00',
      checkIn: '09:58',
      checkOut: '19:02',
      late: null,
      status: 'Present',
      statusColor: 'green',
    },
    {
      id: 4,
      name: 'Emily Davis',
      department: 'HR',
      shift: '09:00 - 18:00',
      checkIn: '-',
      checkOut: '-',
      late: null,
      status: 'Absent',
      statusColor: 'red',
    },
    {
      id: 5,
      name: 'Michael Brown',
      department: 'Logistics',
      shift: '07:00 - 16:00',
      checkIn: '06:50',
      checkOut: '16:15',
      late: null,
      status: 'Present',
      statusColor: 'green',
    },
    {
      id: 6,
      name: 'Jessica Wilson',
      department: 'Marketing',
      shift: '09:00 - 18:00',
      checkIn: '09:02',
      checkOut: '18:01',
      late: 2,
      status: 'Present',
      statusColor: 'green',
    },
    {
      id: 7,
      name: 'David Martinez',
      department: 'Sales',
      shift: '10:00 - 19:00',
      checkIn: '10:45',
      checkOut: '19:30',
      late: 45,
      status: 'Late (Severe)',
      statusColor: 'red',
    },
    {
      id: 8,
      name: 'Sarah Connor',
      department: 'Sales',
      shift: '09:00 - 18:00',
      checkIn: '09:45',
      checkOut: '18:15',
      late: 45,
      status: 'Late',
      statusColor: 'yellow',
    },
  ];

  const getStatusColor = (color) => {
    switch (color) {
      case 'green':
        return 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300';
      case 'yellow':
        return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300';
      case 'red':
        return 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  return (
    <div className={`${isDarkMode ? 'dark' : 'light'}`}>
      <style>{`
        @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@200;300;400;500;600;700;800&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap');

        .a4-screen {
          width: 210mm;
          min-height: 297mm;
          margin: auto;
        }
      `}</style>

      <div className="bg-background-light dark:bg-background-dark  text-text-main flex flex-col items-center min-h-screen p-10 overflow-y-atuo  max-h-[calc(100vh-100px)]" >
        {/* Sticky Toolbar */}
        {/* <div className="no-print sticky top-0 z-50 w-full bg-white/90 dark:bg-[#1a202c]/90 backdrop-blur-md border-b border-gray-200 dark:border-gray-700 shadow-sm mb-8">
          <div className="max-w-7xl mx-auto px-4 py-3 flex flex-wrap items-center justify-between gap-4">
            <div className="flex items-center gap-3">
              <span className="material-symbols-outlined text-primary text-3xl">
                description
              </span>
              <div className="flex flex-col">
                <h2 className="font-bold text-lg leading-tight dark:text-white">
                  Daily Attendance Report
                </h2>
                <span className="text-xs text-text-sub dark:text-gray-400">
                  Ready to export
                </span>
              </div>
            </div>
            <div className="flex items-center gap-2">
              <button
                onClick={handlePrint}
                className="p-2 text-text-sub hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 rounded-lg transition-colors"
                title="Print"
              >
                <span className="material-symbols-outlined">print</span>
              </button>
              <button
                onClick={handleEmail}
                className="p-2 text-text-sub hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 rounded-lg transition-colors"
                title="Email"
              >
                <span className="material-symbols-outlined">mail</span>
              </button>
              <div className="h-6 w-px bg-gray-300 dark:bg-gray-600 mx-1"></div>
              <button
                onClick={handleDownloadPDF}
                className="group flex items-center justify-center rounded-lg h-10 bg-primary hover:bg-primary-dark text-white gap-2 text-sm font-bold px-4 transition-colors shadow-sm"
              >
                <span className="material-symbols-outlined text-[20px]">
                  download
                </span>
                <span>Download PDF</span>
              </button>
              <button
                onClick={handleClose}
                className="p-2 text-text-sub hover:text-red-500 hover:bg-red-50 dark:text-gray-300 dark:hover:text-red-400 dark:hover:bg-red-900/20 rounded-lg transition-colors ml-2"
                title="Close Preview"
              >
                <span className="material-symbols-outlined">close</span>
              </button>
            </div>
          </div>
        </div> */}

        {/* Main Content / A4 Preview */}
        <div className="print-container a4-screen bg-paper dark:bg-[#1e2532] shadow-paper rounded-none sm:rounded-sm flex flex-col relative ">
          {/* Header Section */}
      

          {/* Body Content */}
          <main className="flex-1 px-10 py-6 flex flex-col gap-6">
            {/* KPI Stats */}
            <section className="grid grid-cols-4 gap-4">
              <div className="flex flex-col gap-1 p-4 rounded-lg bg-gray-50 dark:bg-[#2a303c] border border-gray-100 dark:border-gray-700">
                <span className="text-text-sub dark:text-gray-400 text-xs font-semibold uppercase tracking-wider">
                  Headcount
                </span>
                <span className="text-text-main dark:text-white text-3xl font-bold">
                  142
                </span>
              </div>
              <div className="flex flex-col gap-1 p-4 rounded-lg bg-green-50/50 dark:bg-green-900/10 border border-green-100 dark:border-green-800/30">
                <span className="text-green-700 dark:text-green-400 text-xs font-semibold uppercase tracking-wider">
                  Present
                </span>
                <span className="text-green-800 dark:text-green-300 text-3xl font-bold">
                  130
                </span>
              </div>
              <div className="flex flex-col gap-1 p-4 rounded-lg bg-yellow-50/50 dark:bg-yellow-900/10 border border-yellow-100 dark:border-yellow-800/30">
                <span className="text-yellow-700 dark:text-yellow-400 text-xs font-semibold uppercase tracking-wider">
                  Late
                </span>
                <span className="text-yellow-800 dark:text-yellow-300 text-3xl font-bold">
                  8
                </span>
              </div>
              <div className="flex flex-col gap-1 p-4 rounded-lg bg-red-50/50 dark:bg-red-900/10 border border-red-100 dark:border-red-800/30">
                <span className="text-red-700 dark:text-red-400 text-xs font-semibold uppercase tracking-wider">
                  Absent
                </span>
                <span className="text-red-800 dark:text-red-300 text-3xl font-bold">
                  4
                </span>
              </div>
            </section>

            {/* Critical Exceptions */}
            <section className="rounded-lg border-l-4 border-red-500 bg-red-50 dark:bg-red-900/20 p-4">
              <div className="flex items-center gap-2 mb-3">
                <span className="material-symbols-outlined text-red-600 dark:text-red-400 text-xl">
                  warning
                </span>
                <h3 className="text-red-900 dark:text-red-200 font-bold text-sm uppercase tracking-wide">
                  Critical Exceptions
                </h3>
              </div>
              <div className="space-y-2">
                <div className="flex items-center justify-between text-sm bg-white dark:bg-[#1a202c] p-2 rounded border border-red-100 dark:border-red-800/30 shadow-sm">
                  <div className="flex items-center gap-3">
                    <div className="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-600">
                      JM
                    </div>
                    <span className="font-semibold text-text-main dark:text-gray-200">
                      James Miller
                    </span>
                    <span className="text-gray-500 text-xs">• Logistics</span>
                  </div>
                  <span className="text-red-600 dark:text-red-400 font-medium text-xs px-2 py-1 bg-red-50 dark:bg-red-900/30 rounded">
                    Absent No Leave
                  </span>
                </div>
                <div className="flex items-center justify-between text-sm bg-white dark:bg-[#1a202c] p-2 rounded border border-red-100 dark:border-red-800/30 shadow-sm">
                  <div className="flex items-center gap-3">
                    <div className="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-600">
                      SC
                    </div>
                    <span className="font-semibold text-text-main dark:text-gray-200">
                      Sarah Connor
                    </span>
                    <span className="text-gray-500 text-xs">• Sales</span>
                  </div>
                  <span className="text-red-600 dark:text-red-400 font-medium text-xs px-2 py-1 bg-red-50 dark:bg-red-900/30 rounded">
                    Late &gt; 30 mins (45m)
                  </span>
                </div>
              </div>
            </section>

            {/* Main Data Table */}
            <section className="mt-2">
              <h3 className="text-text-main dark:text-gray-200 font-bold text-lg mb-4">
                Detailed Attendance
              </h3>
              <div className="border rounded-lg overflow-hidden border-gray-200 dark:border-gray-700">
                <table className="w-full text-left text-sm">
                  <thead className="bg-gray-50 dark:bg-[#2a303c] border-b border-gray-200 dark:border-gray-700">
                    <tr>
                      <th className="px-4 py-3 font-bold text-text-sub dark:text-gray-300">
                        Employee Name
                      </th>
                      <th className="px-4 py-3 font-bold text-text-sub dark:text-gray-300">
                        Department
                      </th>
                      <th className="px-4 py-3 font-bold text-text-sub dark:text-gray-300">
                        Shift
                      </th>
                      <th className="px-4 py-3 font-bold text-text-sub dark:text-gray-300">
                        Check-In
                      </th>
                      <th className="px-4 py-3 font-bold text-text-sub dark:text-gray-300">
                        Check-Out
                      </th>
                      <th className="px-4 py-3 font-bold text-text-sub dark:text-gray-300 text-center">
                        Late (m)
                      </th>
                      <th className="px-4 py-3 font-bold text-text-sub dark:text-gray-300 text-right">
                        Status
                      </th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-[#1e2532]">
                    {attendanceData.map((row) => (
                      <tr
                        key={row.id}
                        className="hover:bg-gray-50 dark:hover:bg-[#252b36]"
                      >
                        <td className="px-4 py-3 font-semibold text-text-main dark:text-gray-200">
                          {row.name}
                        </td>
                        <td className="px-4 py-3 text-text-sub dark:text-gray-400">
                          {row.department}
                        </td>
                        <td className="px-4 py-3 text-text-sub dark:text-gray-400">
                          {row.shift}
                        </td>
                        <td className="px-4 py-3 text-text-main dark:text-gray-300 font-medium">
                          {row.checkIn}
                        </td>
                        <td className="px-4 py-3 text-text-main dark:text-gray-300 font-medium">
                          {row.checkOut}
                        </td>
                        <td className="px-4 py-3 text-center">
                          {row.late !== null ? (
                            <span
                              className={
                                row.late > 30
                                  ? 'text-red-600 dark:text-red-400 font-bold'
                                  : 'text-yellow-600 dark:text-yellow-400 font-bold'
                              }
                            >
                              {row.late}
                            </span>
                          ) : (
                            <span className="text-gray-400">-</span>
                          )}
                        </td>
                        <td className="px-4 py-3 text-right">
                          <span
                            className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusColor(
                              row.statusColor
                            )}`}
                          >
                            {row.status}
                          </span>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </section>
            <div className="flex-grow"></div>
          </main>

          {/* Footer */}
          <footer className="px-10 py-6 mt-auto border-t border-gray-100 dark:border-gray-700">
            <div className="flex justify-between items-center text-xs text-text-sub dark:text-gray-500 font-medium">
              <div className="flex gap-4">
                <span className="uppercase tracking-widest font-bold text-gray-400">
                  Confidential
                </span>
                <span>|</span>
                <span>Generated on Oct 26, 2023 08:00 AM</span>
              </div>
              <div>Page 1 of 1</div>
            </div>
          </footer>
        </div>
      </div>
    </div>
  );
};

export default PDFReport;
