// app/job-sites/geofencing/page.tsx
import React, { useState } from "react";
import RightSection from "./RightSection";

export default function GeoFencing() {
  const [radius, setRadius] = useState(150);

  return (
    <div className="h-screen w-full overflow-hidden">
      <main className="flex h-full w-full">
        {/* Left Side: Interactive Map */}
        <div className="relative flex-1 h-full bg-slate-200 dark:bg-slate-900 overflow-hidden">
          {/* Map Background */}
          <div
            className="absolute inset-0 bg-cover bg-center"
            style={{
              backgroundImage:
                "url('https://lh3.googleusercontent.com/aida-public/AB6AXuD12SiOHLPbriJwiw3eFYQ-fBLxnwBrWKmDXA-PiYIRP3i7wtPRlCIp-zbb366uyij-t5KasNwfL6Y9X4BuAaR9HGWDrAZABe2gp-ios24Le2ZlDcnOXKhpgEQ4IiisdTuFFSggi1Af8bBngIKl4ZzeEDn2dyHzrCZz8R1HQ-ZeGwQZRs33kQ-ElfFQSxhMsoZu-XkbQsxcpx6T2L99zYaBMDOqxx1MVnGOVzQpWBGHWdfSOeUmVGCLJ_M_zXSRJxwIZ0N57p3EIotx')",
            }}
          >
            <div className="absolute inset-0 bg-black/40" />
          </div>

          {/* Floating Drawing Controls */}
          <div className="absolute top-6 left-6 flex flex-col gap-2 z-10">
            <div className="bg-white dark:bg-slate-900 rounded-xl shadow-xl p-1.5 flex flex-col gap-1 border border-border">
              <button
                className="p-2 bg-primary/10 text-primary rounded-lg flex items-center justify-center hover:bg-primary/20 transition-colors"
                title="Select Tool"
                type="button"
              >
                <span className="material-symbols-outlined">near_me</span>
              </button>

              <hr className="border-border mx-1" />

              <button
                className="p-2 text-slate-600 dark:text-slate-300 rounded-lg flex items-center justify-center hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                title="Draw Circular Branch"
                type="button"
              >
                <span className="material-symbols-outlined">
                  radio_button_unchecked
                </span>
              </button>

              <button
                className="p-2 text-slate-600 dark:text-slate-300 rounded-lg flex items-center justify-center hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                title="Draw Polygonal Branch"
                type="button"
              >
                <span className="material-symbols-outlined">polyline</span>
              </button>

              <button
                className="p-2 text-slate-600 dark:text-slate-300 rounded-lg flex items-center justify-center hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                title="Place Marker"
                type="button"
              >
                <span className="material-symbols-outlined">location_on</span>
              </button>
            </div>

            <div className="bg-white dark:bg-slate-900 rounded-xl shadow-xl p-1.5 flex flex-col gap-1 border border-border mt-4">
              <button
                className="p-2 text-slate-600 dark:text-slate-300 rounded-lg flex items-center justify-center hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                type="button"
              >
                <span className="material-symbols-outlined">add</span>
              </button>
              <button
                className="p-2 text-slate-600 dark:text-slate-300 rounded-lg flex items-center justify-center hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                type="button"
              >
                <span className="material-symbols-outlined">remove</span>
              </button>
            </div>
          </div>

          {/* Visual Branchs (Simulated) */}
          <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
            <div className="h-64 w-64 rounded-full map-overlay-yellow relative flex items-center justify-center group cursor-move">
              <div className="absolute -top-10 bg-accent-yellow text-slate-900 text-xs font-bold px-2 py-1 rounded shadow-lg uppercase tracking-wider">
                Active Branch: HQ Central
              </div>
              <div className="h-4 w-4 bg-accent-yellow border-2 border-white rounded-full shadow-lg animate-pulse" />
            </div>
          </div>

          <div className="absolute top-1/4 right-1/4">
            <div
              className="h-32 w-48 map-overlay-yellow opacity-70 flex items-center justify-center"
              style={{
                clipPath: "polygon(25% 0%, 100% 0%, 75% 100%, 0% 100%)",
              }}
            >
              <span className="material-symbols-outlined text-accent-yellow">
                location_city
              </span>
            </div>
          </div>

          {/* Location Search Bar */}
          <div className="absolute top-6 left-1/2 -translate-x-1/2 w-full max-w-md px-4 z-10">
            <div className="flex items-center bg-white dark:bg-slate-900 rounded-xl shadow-2xl p-1 border border-border">
              <div className="flex items-center flex-1 px-3">
                <span className="material-symbols-outlined text-slate-400 mr-2">
                  search
                </span>
                <input
                  className="bg-transparent border-none text-sm w-full focus:ring-0 focus:outline-none dark:text-white"
                  placeholder="Find address or coordinates..."
                  type="text"
                />
              </div>
              <button
                className="bg-primary text-white text-xs font-bold px-4 py-2 rounded-lg hover:bg-primary/90 transition-colors"
                type="button"
              >
                SEARCH
              </button>
            </div>
          </div>
        </div>

        {/* Right Side: Sidebar Management */}
        <aside className="w-96 h-full border-l border-border flex flex-col bg-white dark:bg-slate-900 z-10 overflow-hidden">
          {/* Let RightSection handle its own scrolling: */}
          <div className="flex-1 overflow-y-auto">
            <RightSection />
          </div>
        </aside>
      </main>

      <style jsx global>{`
        .material-symbols-outlined {
          font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24;
        }
        .map-overlay-yellow {
          background-color: rgba(250, 204, 21, 0.2);
          border: 2px solid #facc15;
        }
      `}</style>
    </div>
  );
}