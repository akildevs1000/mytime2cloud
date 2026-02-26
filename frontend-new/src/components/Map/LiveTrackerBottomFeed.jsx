function CheckIcon() {
    return (
        <svg viewBox="0 0 24 24" fill="currentColor" className="w-full h-full">
            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
        </svg>
    );
}

function ClockIcon() {
    return (
        <svg viewBox="0 0 24 24" fill="currentColor" className="w-full h-full">
            <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7z" />
        </svg>
    );
}

export default function LiveTrackerBottomFeed({ employees, openPanel }) {
    return (
        <div
            className="absolute bottom-20 inset-x-0 z-40 border-t border-slate-800 px-6 py-4 shadow-2xl"
            style={{ background: "rgba(17,19,24,0.95)", backdropFilter: "blur(20px)" }}
        >
            <div className="max-w-[1400px] mx-auto">
                <div className="flex items-center justify-between mb-4">
                    <div className="flex items-center gap-2">
                        <svg viewBox="0 0 24 24" fill="currentColor" className="w-5 h-5 text-[#1152d4]">
                            <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm7 13H5v-.23c0-.62.28-1.2.76-1.58C7.47 15.82 9.64 15 12 15s4.53.82 6.24 2.19c.48.38.76.97.76 1.58V19z" />
                        </svg>
                        <h3 className="text-sm font-bold tracking-tight">Recent Check-ins</h3>
                        <span className="ml-2 px-2 py-0.5 rounded bg-[#1152d4]/10 text-[#1152d4] text-[10px] font-bold">
                            LIVE FEED
                        </span>
                    </div>
                    <button className="text-xs font-semibold text-[#1152d4] hover:underline transition-all">
                        View All Events
                    </button>
                </div>

                <div className="flex gap-4 overflow-x-auto pb-2 custom-scrollbar">
                    {employees.map((emp) => (
                        <button
                            key={emp.id}
                            onClick={() => openPanel(emp)}
                            className="flex-shrink-0 flex items-center gap-3 bg-slate-800/50 p-3 rounded-xl border border-slate-700/50 w-72 hover:border-[#1152d4]/50 transition-all cursor-pointer text-left"
                        >
                            <div className="relative flex-shrink-0">
                                <div
                                    className="w-11 h-11 rounded-lg bg-center bg-cover border border-slate-700"
                                    style={{ backgroundImage: `url('${emp.feedThumb}')` }}
                                />
                                <div
                                    className={`absolute -bottom-1 -right-1 rounded-full border-2 border-[#101622] p-0.5 flex items-center justify-center ${emp.verified ? "bg-green-500" : "bg-slate-500"}`}
                                    style={{ width: 18, height: 18 }}
                                >
                                    <div className="w-2.5 h-2.5 text-white">
                                        {emp.verified ? <CheckIcon /> : <ClockIcon />}
                                    </div>
                                </div>
                            </div>
                            <div className="flex-1 min-w-0">
                                <p className="text-xs font-bold truncate">{emp.name}</p>
                                <p className="text-[10px] text-slate-500 truncate mb-1">{emp.location}</p>
                                <div className="flex items-center justify-between">
                                    <span className="text-[9px] font-medium text-slate-400">
                                        {emp.checkinTime} • {emp.verified ? "Verified" : "Pending"}
                                    </span>
                                    <span
                                        className={`text-[9px] font-bold ${emp.checkinType === "CHECK-IN" ? "text-amber-400" : "text-[#1152d4]"}`}
                                    >
                                        {emp.checkinType}
                                    </span>
                                </div>
                            </div>
                        </button>
                    ))}
                </div>
            </div>
        </div>
    );
}