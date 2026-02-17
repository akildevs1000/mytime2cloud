// app/executive-attendance/page.tsx
// Next.js App Router page component (React)
// If you prefer a reusable component, move JSX into /components and import it here.

import React from "react";

export default function ExecutiveAttendanceDashboardPage() {
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
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div className="glass-panel p-5 rounded-xl flex flex-col gap-1 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
            <div className="flex items-center justify-between mb-2 z-10">
              <p className="text-slate-500 dark:text-slate-400 text-sm font-medium">
                Total Present
              </p>
              <div className="p-1.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-md">
                <span className="material-symbols-outlined text-[20px] block">
                  person_check
                </span>
              </div>
            </div>

            <div className="flex items-end justify-between gap-3 z-10">
              <div>
                <p className="text-3xl font-bold text-slate-900 dark:text-white">
                  128
                </p>
                <div className="flex items-center text-emerald-600 dark:text-emerald-400 text-sm font-bold mb-1 bg-emerald-50 dark:bg-emerald-900/20 px-1.5 py-0.5 rounded w-fit mt-1">
                  <span className="material-symbols-outlined text-[16px]">
                    trending_up
                  </span>
                  <span>4% vs yesterday</span>
                </div>
              </div>

              <svg
                className="h-10 w-20 text-blue-500/20 dark:text-blue-400/20"
                fill="none"
                stroke="currentColor"
                strokeWidth={2}
                viewBox="0 0 100 40"
                aria-hidden="true"
              >
                <path
                  d="M0 20 C 20 25, 40 10, 60 15 C 80 20, 90 5, 100 10"
                  vectorEffect="non-scaling-stroke"
                />
              </svg>
            </div>
          </div>

          <div className="glass-panel p-5 rounded-xl flex flex-col gap-1 shadow-sm hover:shadow-md transition-shadow overflow-hidden group">
            <div className="flex items-center justify-between mb-2">
              <p className="text-slate-500 dark:text-slate-400 text-sm font-medium">
                Late Arrivals Today
              </p>
              <div className="p-1.5 bg-orange-50 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 rounded-md">
                <span className="material-symbols-outlined text-[20px] block">
                  timer_off
                </span>
              </div>
            </div>

            <div className="flex items-end justify-between gap-3">
              <div>
                <p className="text-3xl font-bold text-slate-900 dark:text-white">
                  14
                </p>
                <p className="text-xs text-slate-400 mt-1 font-medium">
                  Attention needed
                </p>
              </div>

              <div className="h-2 w-24 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden self-end mb-2">
                <div className="h-full w-[35%] bg-orange-500 rounded-full" />
              </div>
            </div>
          </div>

          <div className="glass-panel p-5 rounded-xl flex flex-col gap-1 shadow-sm hover:shadow-md transition-shadow overflow-hidden group">
            <div className="flex items-center justify-between mb-2">
              <p className="text-slate-500 dark:text-slate-400 text-sm font-medium">
                On Leave Today
              </p>
              <div className="p-1.5 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-md">
                <span className="material-symbols-outlined text-[20px] block">
                  beach_access
                </span>
              </div>
            </div>

            <div className="flex items-end justify-between gap-3">
              <div>
                <p className="text-3xl font-bold text-slate-900 dark:text-white">
                  8
                </p>
                <span className="text-slate-400 text-sm mb-1 block mt-1">
                  Approved absences
                </span>
              </div>
            </div>
          </div>

          <div className="glass-panel p-5 rounded-xl flex flex-col gap-1 shadow-sm hover:shadow-md transition-shadow overflow-hidden group">
            <div className="flex items-center justify-between mb-2">
              <p className="text-slate-500 dark:text-slate-400 text-sm font-medium">
                Active Overtime
              </p>
              <div className="p-1.5 bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-md">
                <span className="material-symbols-outlined text-[20px] block">
                  schedule
                </span>
              </div>
            </div>

            <div className="flex items-end justify-between gap-3">
              <div>
                <p className="text-3xl font-bold text-slate-900 dark:text-white">
                  5
                </p>
                <span className="text-slate-400 text-sm mb-1 block mt-1">
                  Employees clocked out late
                </span>
              </div>
            </div>
          </div>
        </div>

        {/* Two columns */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {/* Punctuality */}
          <div className="glass-panel rounded-2xl p-6 flex flex-col gap-6 shadow-sm">
            <div className="flex items-center justify-between border-b border-slate-200/60 dark:border-slate-700/60 pb-4">
              <div className="flex items-center gap-3">
                <div className="p-2 bg-emerald-100 dark:bg-emerald-900/40 rounded-full text-emerald-700 dark:text-emerald-400">
                  <span className="material-symbols-outlined text-xl block">
                    verified
                  </span>
                </div>
                <div>
                  <h3 className="text-lg font-bold text-slate-900 dark:text-white">
                    Punctuality Shoutouts
                  </h3>
                  <p className="text-xs text-slate-500 font-medium">
                    Earliest arrivals today
                  </p>
                </div>
              </div>
            </div>

            <div className="flex flex-col gap-3">
              <div className="flex items-center justify-between p-3 rounded-xl bg-white/50 dark:bg-white/5 border border-white/60 dark:border-white/10 hover:bg-white/80 transition-colors">
                <div className="flex items-center gap-4">
                  <div
                    className="bg-center bg-no-repeat bg-cover rounded-full size-12 shadow-sm"
                    aria-label="Portrait of Sarah Jenkins"
                    style={{
                      backgroundImage:
                        'url("https://lh3.googleusercontent.com/aida-public/AB6AXuDpVajIAANdPvPVKlYSJffxdUDh61X9tGHApK4BRapjQy-KYR2VrqObfx-VXzHKRJSWS_FY2JTqXIVY0eR8PTLIsXkV1MSlM5-ZtwHEMeJ3JNljQT_krfbFnwXe3HVHfUK9FNEPDyQfS-PGmMCD1XzZZ-jgmtQdzjyMvxD0vmmaQnE_MxhG5pgfG6j7-0m7kaM_SnqYf0qLS_iV4kLk_CoF5KUApqxltLfuXfEdJNw-kd0sAU77igbOh7WA3C_bee42O6RgSM2oGg__")',
                    }}
                  />
                  <div>
                    <p className="font-bold text-slate-900 dark:text-white">
                      Sarah Jenkins
                    </p>
                    <p className="text-xs text-slate-500">Engineering Lead</p>
                  </div>
                </div>
                <div className="flex flex-col items-end">
                  <span className="text-sm font-bold text-emerald-600 dark:text-emerald-400">
                    07:45 AM
                  </span>
                  <span className="text-[10px] text-slate-400">45m Early</span>
                </div>
              </div>

              <div className="flex items-center justify-between p-3 rounded-xl bg-white/50 dark:bg-white/5 border border-white/60 dark:border-white/10 hover:bg-white/80 transition-colors">
                <div className="flex items-center gap-4">
                  <div
                    className="bg-center bg-no-repeat bg-cover rounded-full size-12 shadow-sm"
                    aria-label="Portrait of David Chen"
                    style={{
                      backgroundImage:
                        'url("https://lh3.googleusercontent.com/aida-public/AB6AXuBZoi34EXObzaZFq5sPoNtwsG3gpJ229fGyDeqCLfbtKegxNJJbDh98qmPUwCTmhStfwLwGAI0tieibJH0KGbqXJfmi3mvu-1i9aSJ6i-I81r8-DKf2etU0mQX0eTVmTeZ6-XClY4uABbkBbyTIVFUbXQPyE2d_Go1Qnr-NhwGLv5Sus2RHqZw8SiMk_HXCeaVkFnA9pAZEjOTR-Lbo3CaXeHs5ebiu2bbkB4bsOG-nWXxgQ7CXdatTW4xTBnGmBKRVcGKjcdWH4iMi")',
                    }}
                  />
                  <div>
                    <p className="font-bold text-slate-900 dark:text-white">
                      David Chen
                    </p>
                    <p className="text-xs text-slate-500">Product Designer</p>
                  </div>
                </div>
                <div className="flex flex-col items-end">
                  <span className="text-sm font-bold text-emerald-600 dark:text-emerald-400">
                    07:52 AM
                  </span>
                  <span className="text-[10px] text-slate-400">38m Early</span>
                </div>
              </div>
            </div>
          </div>

          {/* Absence/Late */}
          <div className="glass-panel rounded-2xl p-6 flex flex-col gap-6 shadow-sm">
            <div className="flex items-center justify-between border-b border-slate-200/60 dark:border-slate-700/60 pb-4">
              <div className="flex items-center gap-3">
                <div className="p-2 bg-amber-100 dark:bg-amber-900/40 rounded-full text-amber-700 dark:text-amber-400">
                  <span className="material-symbols-outlined text-xl block">
                    warning
                  </span>
                </div>
                <div>
                  <h3 className="text-lg font-bold text-slate-900 dark:text-white">
                    Current Absence/Late
                  </h3>
                  <p className="text-xs text-slate-500 font-medium">
                    Employees not yet checked in
                  </p>
                </div>
              </div>

              <button className="text-primary text-sm font-bold hover:underline">
                View All
              </button>
            </div>

            <div className="flex flex-col gap-3">
              <div className="flex items-center justify-between p-3 rounded-xl bg-white/50 dark:bg-white/5 border border-white/60 dark:border-white/10 hover:bg-white/80 transition-colors group">
                <div className="flex items-center gap-4">
                  <div
                    className="bg-center bg-no-repeat bg-cover rounded-full size-12 grayscale opacity-80"
                    aria-label="Portrait of Michael Ross"
                    style={{
                      backgroundImage:
                        'url("https://lh3.googleusercontent.com/aida-public/AB6AXuD_GBzYbVdd6BK8yRD3QWZ8VVfEUJLf7ewCmUHdfDgdDZqvmNapi0boSk89cQT9QdcatiagrCAxmR-jgSwh94WeAkX3FunIDgKOxznh_wRcBhINehmFcgUINBsqHPW5_DhB9bHtdVthriLc98pGOEDdQWG8bzVHJgOSDP5xrEJLBxcUXm05IrFOU4YnBkGv1nJEHMtWPz6KS9EwxQgglWeE-5aiqk43Q3qGQ9quc7Ca7npFV1cyT7J_fGqZVitxFD2lcgBZQbY7PLXE")',
                    }}
                  />
                  <div>
                    <p className="font-bold text-slate-900 dark:text-white">
                      Michael Ross
                    </p>
                    <p className="text-xs text-slate-500">Sales Executive</p>
                  </div>
                </div>

                <div className="flex flex-col items-end gap-1">
                  <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200">
                    Not Checked In
                  </span>
                  <button className="text-[10px] font-bold text-primary hover:text-primary/80 flex items-center gap-1 opacity-100 transition-opacity">
                    Contact{" "}
                    <span className="material-symbols-outlined text-[10px]">
                      call
                    </span>
                  </button>
                </div>
              </div>

              <div className="flex items-center justify-between p-3 rounded-xl bg-white/50 dark:bg-white/5 border border-white/60 dark:border-white/10 hover:bg-white/80 transition-colors group">
                <div className="flex items-center gap-4">
                  <div
                    className="bg-center bg-no-repeat bg-cover rounded-full size-12 grayscale opacity-80"
                    aria-label="Portrait of Linda Kim"
                    style={{
                      backgroundImage:
                        'url("https://lh3.googleusercontent.com/aida-public/AB6AXuCAuEK2shAOlbgzd7osB3MjtNYAt0jNq2R4SNHWsx1pPhAJ6pgUSlvpC8EOYIzdZ8VVlaLJkss32xoY7lUL9BQXGCwDfaVNnfBvxJs33ZZejVQLzbFLnewNuwMFbu7ZkpphwnerJcMsRIZpCsPhX-uPK2ONeeSynC7qCBFh3U9YsB_-KECiSZ-z3my8unETQQ2KgVw3FhOOMtNrAlO_rm0UQmL-1Ods4GYeXJ_VtFGJFfAyq8pWkffud-6qK4poZpKLoKF6M7cZI8B_")',
                    }}
                  />
                  <div>
                    <p className="font-bold text-slate-900 dark:text-white">
                      Linda Kim
                    </p>
                    <p className="text-xs text-slate-500">Recruiter</p>
                  </div>
                </div>

                <div className="flex flex-col items-end gap-1">
                  <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200">
                    Late (No Notice)
                  </span>
                  <button className="text-[10px] font-bold text-primary hover:text-primary/80 flex items-center gap-1 opacity-100 transition-opacity">
                    Contact{" "}
                    <span className="material-symbols-outlined text-[10px]">
                      call
                    </span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

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
