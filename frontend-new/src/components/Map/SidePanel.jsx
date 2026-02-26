export default function SidePanel({ selectedEmployee, sidePanelOpen, closePanel }) {
    return (
        <aside
            className="absolute top-0 right-0 h-full w-[400px] bg-slate-900 border-l border-slate-800 z-[60] shadow-2xl transition-transform duration-500"
            style={{ transform: sidePanelOpen ? "translateX(0)" : "translateX(100%)" }}
        >
            {/* Close Button */}
            <button
                onClick={closePanel}
                className="absolute top-6 left-0 -translate-x-full p-2 bg-slate-900 border border-r-0 border-slate-800 rounded-l-xl"
            >
                <svg viewBox="0 0 24 24" fill="currentColor" className="w-5 h-5">
                    <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z" />
                </svg>
            </button>

            {selectedEmployee && (
                <div className="p-8 h-full flex flex-col">
                    {/* Profile Header */}
                    <div className="flex items-center gap-4 mb-8">
                        <div className="w-20 h-20 rounded-2xl border-2 border-[#1152d4] overflow-hidden">
                            <img src={selectedEmployee.avatar} alt={selectedEmployee.name} className="w-full h-full object-cover" />
                        </div>
                        <div>
                            <h2 className="text-2xl font-bold">{selectedEmployee.name}</h2>
                            <p className="text-slate-500 font-medium">{selectedEmployee.role}</p>
                            <div className="flex items-center gap-2 mt-1">
                                <span className="w-2 h-2 rounded-full bg-amber-400" />
                                <span className="text-xs font-bold text-amber-400 uppercase tracking-wide">On Duty</span>
                            </div>
                        </div>
                    </div>

                    <div className="space-y-6 flex-1 overflow-y-auto pr-2 custom-scrollbar">
                        {/* Verification Status */}
                        {selectedEmployee.refPhoto && (
                            <section>
                                <h4 className="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-4">
                                    Verification Status
                                </h4>
                                <div className="bg-slate-800/50 p-4 rounded-xl border border-slate-700">
                                    <div className="flex items-center justify-between mb-4">
                                        <div className="flex items-center gap-3">
                                            <svg viewBox="0 0 24 24" fill="currentColor" className="w-5 h-5 text-green-500">
                                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                                            </svg>
                                            <span className="text-sm font-bold">Face Recognition Pass</span>
                                        </div>
                                        <span className="text-[10px] font-bold text-slate-400">{selectedEmployee.matchScore} Match</span>
                                    </div>
                                    <div className="grid grid-cols-2 gap-2">
                                        <div className="aspect-square rounded-lg bg-slate-700 overflow-hidden">
                                            <img src={selectedEmployee.refPhoto} alt="Reference" className="w-full h-full object-cover" />
                                        </div>
                                        <div className="aspect-square rounded-lg bg-slate-700 overflow-hidden relative">
                                            <img src={selectedEmployee.livePhoto} alt="Live" className="w-full h-full object-cover" />
                                            <div className="absolute bottom-0 inset-x-0 bg-black/60 p-1 text-center">
                                                <span className="text-[8px] text-white font-bold">Live Feed Capture</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        )}

                        {/* Shift Details */}
                        <section>
                            <h4 className="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-3">Shift Details</h4>
                            <div className="grid grid-cols-2 gap-3">
                                <div className="bg-slate-800/50 p-3 rounded-xl border border-slate-700">
                                    <p className="text-[10px] text-slate-500 font-medium mb-1">Clock In</p>
                                    <p className="text-sm font-bold">{selectedEmployee.clockIn}</p>
                                </div>
                                <div className="bg-slate-800/50 p-3 rounded-xl border border-slate-700">
                                    <p className="text-[10px] text-slate-500 font-medium mb-1">Expected Out</p>
                                    <p className="text-sm font-bold">{selectedEmployee.expectedOut}</p>
                                </div>
                            </div>
                        </section>

                        {/* Device & Safety */}
                        <section>
                            <h4 className="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-3">
                                Device &amp; Safety
                            </h4>
                            <div className="flex items-center justify-between p-3 bg-slate-800/50 rounded-xl border border-slate-700">
                                <div className="flex items-center gap-3">
                                    <svg viewBox="0 0 24 24" fill="currentColor" className="w-5 h-5 text-slate-400">
                                        <path d="M15.67 4H14V2h-4v2H8.33C7.6 4 7 4.6 7 5.33v15.33C7 21.4 7.6 22 8.33 22h7.33c.74 0 1.34-.6 1.34-1.33V5.33C17 4.6 16.4 4 15.67 4z" />
                                    </svg>
                                    <span className="text-xs font-bold">Battery Level</span>
                                </div>
                                <span className={`text-xs font-bold ${selectedEmployee.battery > 60 ? "text-green-500" : selectedEmployee.battery > 30 ? "text-amber-400" : "text-red-500"}`}>
                                    {selectedEmployee.battery}%
                                </span>
                            </div>
                        </section>
                    </div>

                    {/* Actions */}
                    <div className="pt-6 border-t border-slate-800 grid grid-cols-2 gap-4">
                        <button className="flex items-center justify-center gap-2 py-3 px-4 bg-slate-800 rounded-xl font-bold text-sm hover:bg-slate-700 transition-colors">
                            <svg viewBox="0 0 24 24" fill="currentColor" className="w-4 h-4">
                                <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z" />
                            </svg>
                            Message
                        </button>
                        <button className="flex items-center justify-center gap-2 py-3 px-4 bg-[#1152d4] text-white rounded-xl font-bold text-sm hover:bg-blue-600 transition-all shadow-lg">
                            <svg viewBox="0 0 24 24" fill="currentColor" className="w-4 h-4">
                                <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z" />
                            </svg>
                            Call Now
                        </button>
                    </div>
                </div>
            )}
        </aside>
    );
}