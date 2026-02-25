// app/job-sites/geofencing/page.tsx
import React, { useState } from "react";
import { LocateIcon, LocationEdit, Map } from "lucide-react";
import RightSection from "./RightSection";
import GeoMap from "./GeoMap";

export default function GeoFencing() {
  const [radius, setRadius] = useState(150);
  // Default to Dubai coordinates
  const [center, setCenter] = useState({ lat: 25.2048, lng: 55.2708 });

  // convert meters -> pixels based on default visual size
  const DEFAULT_RADIUS_METERS = 150; // corresponds to current default visual size
  const DEFAULT_DIAMETER_PX = 256; // tailwind h-64 w-64 -> 256px
  const PIXELS_PER_METER = DEFAULT_DIAMETER_PX / (DEFAULT_RADIUS_METERS * 2);
  const diameterPx = Math.max(16, Math.round(radius * 2 * PIXELS_PER_METER));

  return (
    <div className="h-screen w-full overflow-hidden">
      <main className="flex h-full w-full">
        {/* Left Side: Interactive Map */}
        <div className="relative flex-1 h-full bg-slate-200 dark:bg-slate-900 overflow-hidden">
          {/* Google Maps JS API (controlled) */}
          <div className="absolute inset-0">
            <GeoMap center={center} radius={radius} />

            {/* subtle overlay for dark mode readability */}
            <div className="absolute inset-0 bg-black/20 pointer-events-none" />
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

          {/* Visual Branch (Overlay) */}
          <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 pointer-events-none">
            <div
              className="relative flex items-center justify-center"
              style={{ width: diameterPx + "px", height: diameterPx + "px" }}
              aria-hidden
            >
              {/* translucent ring */}
              <div
                className="absolute inset-0 rounded-full"
                style={{
                  backgroundColor: "rgba(250,204,21,0.12)",
                  border: "2px solid rgba(250,204,21,0.9)",
                }}
              />

              {/* center marker removed — using default map marker */}
            </div>
          </div>

          <div className="absolute top-1/4 right-1/4">
            {/* Decorative overlay removed to avoid duplicate highlight */}
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
            <RightSection radius={radius} setRadius={setRadius} setCenter={setCenter} />
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