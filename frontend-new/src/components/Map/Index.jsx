"use client";

import { useState, useEffect, useRef } from "react";
import LiveTrackerBottomFeed from "./LiveTrackerBottomFeed";
import SidePanel from "./SidePanel";
import distanceMeters from "@/hooks/useDistance";

const employees = [
  {
    id: 1,
    name: "John Doe",
    role: "Senior Field Engineer",
    location: "West Branch Office",
    status: "active",
    shift: "09:00 - 17:00",
    clockIn: "08:58 AM",
    expectedOut: "05:00 PM",
    battery: 82,
    matchScore: "99.2%",
    checkinTime: "2m ago",
    checkinType: "CHECK-IN",
    verified: true,
    mapPos: { top: "35%", left: "45%" },
    lat: 25.2685,
    lng: 55.2882,
    avatar:
      "https://lh3.googleusercontent.com/aida-public/AB6AXuAgOmBDUE9YRPKrUELhubdiGKupJPt-_S1cAy0MCwnS4XLJ0F8HKYFSrehE-s5euFiPpgEgHiFZD1C4_azu015NF6eEUjCMMmf5ddSOmpi7ops0nKsPkh-1dy7Q1O1Pp1zJHGd2YLtIXjenPSPEq1tcWmZihbIU5Lihw_hliby7B7g5OIIOw7sSOcnp6QZ9Kaqnr238I7B2rX5VS7ZLN459F5CuA34Ygdr8rggzQtDdziWsB7Dzre13RYIJcDIEu1yRzWs-3KnWTG0_",
    refPhoto:
      "https://lh3.googleusercontent.com/aida-public/AB6AXuCpuHuPvKJ-OCU_SWCUKbTl9A1bkYIObn45SD6zIRyBPGxh9ozH5rliQZbKaWLnwieFy66QRxJs_rVxJ28X-QNhxfnCFVtp1sRXPST_p6tQZsWVJzFrXcjd9veTIERvC0oH1fzTv-6-askTYufViMnxeJlGipTNkjZgRj1ArZ_cc-7WafzJInZgxuypOW06H3lNN241UqYi-e4OBezv1-AtMkXejLMh0a7R-l4NHajnk7IOsEot82U8HzMsphPWLGTj-bVZbYu29kmW",
    livePhoto:
      "https://lh3.googleusercontent.com/aida-public/AB6AXuAB4uboUFzRdHOoKqfS3LX6c5lEvQ4iEMgt_2WI80FOmrymhPfLvnVg3MkIssLQXmr7NAP8zyxLVrhshpEioeGt3gv5nOPDLBrj6HulU-q7b_My2ofalSElnNwRDmPNfDJmXUNsGR71dBEyxgTASi9mA0oFc-aLwaveZzdXZCy9tLMIvCnEARDmiDWzRgDPIhWNarEaq3HnHW3oy7njRPCpWIY2_bV-Jz1Mv560hkzOiInB36okrjyZlbVdvRRUS6qtbHwY9ZO5YXlo",
    feedThumb:
      "https://lh3.googleusercontent.com/aida-public/AB6AXuC5sQ1au3fUOqOakeVlZYmy9oip1UWbBjFiB2_n6cY4RLEHoiPSkaTMuye7gzCkL3lSydkJG-FNf_HNdw6nX4krujcOx_OxZ3yxOxPBQ2WAwBTDeEm3Xy1PXfqTLwk4xTzfgq03Amg8u6Tt_bnCNWfBhD1nQhBxxXIeKRp4ZuayieOA07UWQj0OLvEPwj-vw7N2U3G6GJ7bqrAVXih3kPx5RliPZjS83EylAE4qDk8gu3R-MyRyaaf09E8r-b4wSZnG1XLdBL8rdCox",
  },
  {
    id: 2,
    name: "Sarah Smith",
    role: "Logistics Coordinator",
    location: "North Warehouse",
    status: "active",
    shift: "08:00 - 16:00",
    clockIn: "07:55 AM",
    expectedOut: "04:00 PM",
    battery: 67,
    matchScore: "98.1%",
    checkinTime: "5m ago",
    checkinType: "CHECK-IN",
    verified: true,
    mapPos: { top: "55%", left: "60%" },
    lat: 25.2312,
    lng: 55.3214,
    avatar:
      "https://lh3.googleusercontent.com/aida-public/AB6AXuAhS-rjJPilpVNSIf4S1vqyLYBjOahtdAeaKqmOsavmsWsy6lZeuR7sD6MN8XD63HAjqlt1EtHUfLHcYo0TWgH0b7dnytzUFy7dWzbY72R4_ecQrVPgFxq9qJcT6Gy85R9MatxIeAb0Z_McRlVUY6mFVEmtM_--OoDAfNfASkikA1iw4Cgit1p0Xhhm8Y-Qqs9T9s3RQYlbU0Oj1ZI8ocJe97z9Bd37VM-l0bqfk9Iylqr7tBHsRhImTlfKvKbk9bMZ-C8IQZvEOxPb",
    feedThumb:
      "https://lh3.googleusercontent.com/aida-public/AB6AXuAeweoCuz_VO4J0wKgiBPLxxEsGIzPr1TE3ItVOfeIZvcIKmeQqmpT0vBj5OHcVd0WkQH17gkQodvGq0LswAXroHGVWoEKaKCqvTUaFxzLCIHJX_HcwhwUW04yPlzMbXK6OvdwrNkaHgT64z6b9xGgWEfaUEbOoPC0AjP1JBQsmfifYjOcaos6Bjs-v61fo1_q6eQVd1R4bA-R-yGoK85XMy5oYzserGrvzvqgdRQw7kOi-y8jTs5i9chSH4AgWJhvCHHDoXxPGnpa0",
  },
];

export default function LiveTeamStatus() {
  const [selectedEmployee, setSelectedEmployee] = useState(null);
  const [sidePanelOpen, setSidePanelOpen] = useState(false);
  const [hoveredPin, setHoveredPin] = useState(null);
  const lastPositionsRef = useRef({});
  const [movingMap, setMovingMap] = useState({});
  const [employeesData, setEmployeesData] = useState(employees);
  const [bwMode, setBwMode] = useState(false);
  const mapContainerRef = useRef(null);
  const mapRef = useRef(null);
  const markersRef = useRef({});
  const timersRef = useRef({});
  const darkMapStyleRef = useRef([
    { elementType: 'geometry', stylers: [{ color: '#1f2937' }] },
    { elementType: 'labels.text.fill', stylers: [{ color: '#9ca3af' }] },
    { elementType: 'labels.text.stroke', stylers: [{ color: '#1f2937' }] },
    { featureType: 'administrative', elementType: 'geometry.stroke', stylers: [{ color: '#374151' }] },
    { featureType: 'administrative.land_parcel', elementType: 'labels.text.fill', stylers: [{ color: '#6b7280' }] },
    { featureType: 'poi', elementType: 'labels.text.fill', stylers: [{ color: '#9ca3af' }] },
    { featureType: 'poi.park', elementType: 'geometry', stylers: [{ color: '#111827' }] },
    { featureType: 'poi.park', elementType: 'labels.text.fill', stylers: [{ color: '#9ca3af' }] },
    { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#111827' }] },
    { featureType: 'road', elementType: 'labels.text.fill', stylers: [{ color: '#9ca3af' }] },
    { featureType: 'road.highway', elementType: 'geometry', stylers: [{ color: '#0b1220' }] },
    { featureType: 'transit', elementType: 'labels.text.fill', stylers: [{ color: '#9ca3af' }] },
    { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#0b1220' }] },
    { featureType: 'water', elementType: 'labels.text.fill', stylers: [{ color: '#4b5563' }] }
  ]);

  // Load Google Maps script (reuse pattern from GeoFencing.GeoMap)
  function loadGoogleMaps(apiKey) {
    if (typeof window === "undefined") return Promise.reject();
    if (window.google && window.google.maps) return Promise.resolve(window.google.maps);

    return new Promise((resolve, reject) => {
      const existing = document.getElementById("gmaps-script");
      if (existing) {
        existing.addEventListener("load", () => resolve(window.google.maps));
        existing.addEventListener("error", reject);
        return;
      }

      const script = document.createElement("script");
      script.id = "gmaps-script";
      script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}`;
      script.async = true;
      script.defer = true;
      script.onload = () => resolve(window.google.maps);
      script.onerror = reject;
      document.head.appendChild(script);
    });
  }



  // Detect movement: start a delayed flicker when lat/lng changes beyond threshold
  useEffect(() => {
    const thresholdMeters = 10; // consider movement if >10m
    employeesData.forEach((emp) => {
      const last = lastPositionsRef.current[emp.id];
      if (last) {
        const d = distanceMeters(last.lat, last.lng, emp.lat, emp.lng);
        if (d > thresholdMeters) {
          // clear any existing timers for this emp
          const existing = timersRef.current[emp.id];
          if (existing) {
            if (existing.delay) clearTimeout(existing.delay);
            if (existing.end) clearTimeout(existing.end);
          }

          // Delay 1s (stay 'stuck'), then start flicker/ping for ~1.8s
          const delay = setTimeout(() => {
            setMovingMap((prev) => ({ ...prev, [emp.id]: true }));
            const end = setTimeout(() => {
              setMovingMap((prev) => ({ ...prev, [emp.id]: false }));
              timersRef.current[emp.id] = null;
            }, 1800);
            timersRef.current[emp.id] = { delay: null, end };
          }, 1000);

          timersRef.current[emp.id] = { delay, end: null };
        }
      }
      lastPositionsRef.current[emp.id] = { lat: emp.lat, lng: emp.lng };
    });
    // cleanup on unmount
    return () => {
      Object.values(timersRef.current || {}).forEach((t) => {
        if (!t) return;
        if (t.delay) clearTimeout(t.delay);
        if (t.end) clearTimeout(t.end);
      });
    };
  }, [employeesData]);

  // Simulation: nudge one employee's position every 2s to demonstrate movement/ping
  useEffect(() => {
    const idToMove = 1; // simulate employee with id=1
    const interval = setInterval(() => {
      setEmployeesData((prev) => {
        return prev.map((e) => {
          if (e.id !== idToMove) return e;
          // small random delta in lat/lng to simulate movement
          const deltaLat = (Math.random() - 0.5) * 0.0004; // ~ up to ~44m
          const deltaLng = (Math.random() - 0.5) * 0.0004;
          const newLat = e.lat + deltaLat;
          const newLng = e.lng + deltaLng;
          // also slightly adjust mapPos for demo (clamp not necessary here)
          const topNum = parseFloat(e.mapPos.top) + (Math.random() - 0.5) * 1.2;
          const leftNum = parseFloat(e.mapPos.left) + (Math.random() - 0.5) * 1.2;
          return {
            ...e,
            lat: Number(newLat.toFixed(6)),
            lng: Number(newLng.toFixed(6)),
            mapPos: { top: `${topNum}%`, left: `${leftNum}%` },
          };
        });
      });
    }, 2000);
    return () => clearInterval(interval);
  }, []);

  // Initialize Google Map once
  useEffect(() => {
    const apiKey = process.env.NEXT_PUBLIC_GOOGLE_MAPS_API_KEY || "";
    if (!apiKey) {
      console.warn("Map: NEXT_PUBLIC_GOOGLE_MAPS_API_KEY not set");
      return;
    }

    let mounted = true;
    loadGoogleMaps(apiKey)
      .then((maps) => {
        if (!mounted) return;
        if (!mapRef.current && mapContainerRef.current) {
          const initial = employeesData[0] || { lat: 25.2048, lng: 55.2708 };
          mapRef.current = new maps.Map(mapContainerRef.current, {
            center: initial,
            zoom: 13,
            disableDefaultUI: true,
            styles: bwMode ? darkMapStyleRef.current : null,
          });
        }
      })
      .catch((err) => console.error("Failed to load Google Maps", err));

    return () => {
      mounted = false;
    };
  }, []);

  // Toggle CSS class on the map container so overlays can respond to B/W mode via CSS
  useEffect(() => {
    const node = mapContainerRef.current;
    if (!node) return;
    if (bwMode) node.classList.add("bw-mode");
    else node.classList.remove("bw-mode");
  }, [bwMode]);

  // Sync avatar overlays to employeesData
  useEffect(() => {
    const maps = window.google?.maps;
    if (!maps || !mapRef.current) return;

    const currentIds = new Set();

    employeesData.forEach((emp) => {
      currentIds.add(emp.id);
      const pos = { lat: emp.lat, lng: emp.lng };
      const exists = markersRef.current[emp.id];
      if (exists) {
        // update position and moving state
        if (typeof exists.setPosition === "function") exists.setPosition(pos);
        if (typeof exists.setMoving === "function") exists.setMoving(Boolean(movingMap[emp.id]));
      } else {
        // create a DOM overlay so we can render the avatar and custom ping
        class AvatarOverlay extends maps.OverlayView {
          constructor(employee) {
            super();
            this.employee = employee;
            this.position = pos;
            this.div = null;
          }
          onAdd() {
            this.div = document.createElement("div");
            this.div.style.position = "absolute";
            this.div.style.transform = "translate(-50%,-50%)";
            this.div.style.pointerEvents = "auto";

            const size = 48;
            const border = 4;

            // container
            const container = document.createElement("div");
            container.style.position = "relative";
            container.style.width = `${size}px`;
            container.style.height = `${size}px`;

            // ping layer (absolute, covers avatar)
            const ping = document.createElement("div");
            ping.className = "avatar-ping";
            ping.style.position = "absolute";
            ping.style.left = `-${border}px`;
            ping.style.top = `-${border}px`;
            ping.style.width = `${size + border * 2}px`;
            ping.style.height = `${size + border * 2}px`;
            ping.style.borderRadius = "50%";
            ping.style.pointerEvents = "none";

            // avatar element with border
            const avatarWrap = document.createElement("div");
            avatarWrap.style.width = `${size}px`;
            avatarWrap.style.height = `${size}px`;
            avatarWrap.style.borderRadius = "50%";
            avatarWrap.style.overflow = "hidden";
            avatarWrap.style.boxSizing = "border-box";
            avatarWrap.style.border = "4px solid #1152d4";
            avatarWrap.style.boxShadow = "0 6px 18px rgba(2,6,23,0.6)";
            avatarWrap.className = "avatar-wrap";

            const img = document.createElement("img");
            img.src = this.employee.avatar || "";
            img.alt = this.employee.name || "";
            img.style.width = "100%";
            img.style.height = "100%";
            img.style.objectFit = "cover";
            img.draggable = false;

            avatarWrap.appendChild(img);
            container.appendChild(ping);
            container.appendChild(avatarWrap);

            // Tooltip (hidden by default)
            const tooltip = document.createElement("div");
            tooltip.className = "pin-tooltip";
            tooltip.style.position = "absolute";
            tooltip.style.bottom = "100%";
            tooltip.style.left = "50%";
            tooltip.style.transform = "translate(-50%,8px)";
            tooltip.style.pointerEvents = "none";
            tooltip.style.opacity = "0";
            tooltip.style.transition = "opacity 0.18s, transform 0.18s";

            const glass = document.createElement("div");
            glass.className = "glass-panel rounded-xl p-3 w-48 shadow-2xl";
            glass.style.width = "12rem";
            glass.style.fontSize = "12px";

            const titleRow = document.createElement("div");
            titleRow.className = "flex items-start justify-between mb-2";
            const titleBlk = document.createElement("div");
            const h4 = document.createElement("h4");
            h4.className = "text-xs font-bold text-white";
            h4.textContent = this.employee.name || "";
            const p = document.createElement("p");
            p.className = "text-[10px] text-slate-400";
            p.textContent = this.employee.location || "";
            titleBlk.appendChild(h4);
            titleBlk.appendChild(p);
            titleRow.appendChild(titleBlk);

            const ico = document.createElement("svg");
            ico.setAttribute("viewBox", "0 0 24 24");
            ico.setAttribute("fill", "currentColor");
            ico.className = "w-4 h-4 text-green-400 flex-shrink-0";
            ico.innerHTML = '<path d="M23 12l-2.44-2.78.34-3.68-3.61-.82-1.89-3.18L12 3 8.6 1.54 6.71 4.72l-3.61.81.34 3.68L1 12l2.44 2.78-.34 3.69 3.61.82 1.89 3.18L12 21l3.4 1.46 1.89-3.18 3.61-.82-.34-3.68L23 12zm-10 3h-2v-2h2v2zm0-4h-2V7h2v4z"/>';
            titleRow.appendChild(ico);

            const badges = document.createElement("div");
            badges.className = "flex items-center gap-2 mb-3";
            const status = document.createElement("span");
            status.className = "text-[10px] font-medium px-2 py-0.5 rounded-full";
            status.style.background = "rgba(250,204,21,0.12)";
            status.style.color = "#facc15";
            status.textContent = "Active";
            const shift = document.createElement("span");
            shift.className = "text-[10px] text-slate-500";
            shift.textContent = `Shift: ${this.employee.shift || ""}`;
            badges.appendChild(status);
            badges.appendChild(shift);

            const btn = document.createElement("button");
            btn.className = "w-full py-1.5 bg-[#1152d4] rounded-lg text-white text-[10px] font-bold";
            btn.textContent = "View Profile";
            btn.addEventListener("click", (e) => {
              e.stopPropagation();
              try { openPanel(this.employee); } catch (err) {}
            });

            glass.appendChild(titleRow);
            glass.appendChild(badges);
            glass.appendChild(btn);

            tooltip.appendChild(glass);

            this.div.appendChild(container);
            this.div.appendChild(tooltip);

            this.tooltipEl = tooltip;

            // click forward to openPanel
            this.div.addEventListener("click", (e) => {
              e.stopPropagation();
              try {
                openPanel(this.employee);
              } catch (err) {
                // ignore
              }
            });

            this.pingEl = ping;
            this.avatarWrap = avatarWrap;

            // show/hide tooltip on hover
            this.div.addEventListener("mouseenter", () => {
              if (this.tooltipEl) {
                this.tooltipEl.style.opacity = "1";
                this.tooltipEl.style.transform = "translate(-50%,0)";
                this.tooltipEl.style.pointerEvents = "auto";
              }
            });
            this.div.addEventListener("mouseleave", () => {
              if (this.tooltipEl) {
                this.tooltipEl.style.opacity = "0";
                this.tooltipEl.style.transform = "translate(-50%,8px)";
                this.tooltipEl.style.pointerEvents = "none";
              }
            });

            this.getPanes().overlayMouseTarget.appendChild(this.div);
          }
          draw() {
            if (!this.div) return;
            const projection = this.getProjection();
            if (!projection) return;
            const point = projection.fromLatLngToDivPixel(new maps.LatLng(this.position.lat, this.position.lng));
            if (point) {
              this.div.style.left = `${point.x}px`;
              this.div.style.top = `${point.y}px`;
            }
          }
          onRemove() {
            if (this.div && this.div.parentNode) this.div.parentNode.removeChild(this.div);
            this.div = null;
            this.pingEl = null;
            this.avatarWrap = null;
          }
          setPosition(p) {
            this.position = p;
            try {
              this.draw();
            } catch (e) { }
          }
          setMoving(flag) {
            if (!this.pingEl || !this.avatarWrap) return;
            if (flag) {
              this.pingEl.style.background = "rgba(17,82,212,0.4)";
              this.pingEl.style.animation = "ping 1.5s cubic-bezier(0,0,0.2,1) infinite";
              this.avatarWrap.style.borderColor = "#1fb5ff";
            } else {
              this.pingEl.style.animation = "";
              this.pingEl.style.background = "transparent";
              this.avatarWrap.style.borderColor = "#1152d4";
            }
          }
        }

        const overlay = new AvatarOverlay(emp);
        overlay.setMap(mapRef.current);
        // attach a convenience setter to apply B/W mode (safe if onAdd hasn't run yet)
        overlay.setBW = (flag) => {
          try {
            const av = overlay.avatarWrap;
            const ping = overlay.pingEl;
            const tip = overlay.tooltipEl;
            if (av) {
              const imgEl = av.querySelector && av.querySelector("img");
              if (imgEl) imgEl.style.filter = flag ? "grayscale(1) contrast(0.9)" : "";
              av.style.borderColor = flag ? "#ffffff" : "#1152d4";
            }
            if (ping) {
              // if ping currently active, prefer a light ping on B/W mode
              if (flag) ping.style.background = ping.style.background || "rgba(255,255,255,0.25)";
            }
            if (tip) {
              const glass = tip.querySelector && tip.querySelector(".glass-panel");
              if (glass) {
                glass.style.background = flag ? "#0b0b0b" : "";
                glass.style.color = flag ? "#e6e6e6" : "";
              }
            }
          } catch (e) {
            /* ignore */
          }
        };
        // immediately set moving state if needed
        if (typeof overlay.setMoving === "function") overlay.setMoving(Boolean(movingMap[emp.id]));
        // apply current bwMode
        if (typeof overlay.setBW === "function") overlay.setBW(Boolean(bwMode));
        markersRef.current[emp.id] = overlay;
      }
    });

    // remove overlays that are no longer present
    Object.keys(markersRef.current).forEach((id) => {
      const numId = Number(id);
      if (!currentIds.has(numId)) {
        try {
          markersRef.current[numId].setMap(null);
        } catch (e) { }
        delete markersRef.current[numId];
      }
    });
  }, [employeesData, movingMap]);

  // Apply B/W mode to existing overlays when toggled
  useEffect(() => {
    Object.values(markersRef.current).forEach((ov) => {
      if (ov && typeof ov.setBW === "function") {
        try { ov.setBW(Boolean(bwMode)); } catch (e) { }
      }
    });
  }, [bwMode]);

  // apply map style when bwMode toggles
  useEffect(() => {
    const maps = window.google?.maps;
    if (!maps || !mapRef.current) return;
    try {
      mapRef.current.setOptions({ styles: bwMode ? darkMapStyleRef.current : null });
    } catch (e) {
      console.warn('Failed to apply map style', e);
    }
  }, [bwMode]);

  const openPanel = (employee) => {
    setSelectedEmployee(employee);
    setSidePanelOpen(true);
  };

  const closePanel = () => {
    setSidePanelOpen(false);
    setTimeout(() => setSelectedEmployee(null), 500);
  };


  return (
    <div
      className="relative flex h-screen w-full flex-col overflow-hidden bg-[#101622] text-slate-100"
      style={{ fontFamily: "Inter, sans-serif" }}
    >
      <style>{`
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        @import url('https://fonts.googleapis.com/icon?family=Material+Icons');
        
        .map-gradient-overlay {
          background: linear-gradient(180deg, rgba(16,22,34,0.8) 0%, rgba(16,22,34,0) 15%, rgba(16,22,34,0) 85%, rgba(16,22,34,0.9) 100%);
        }
        .glass-panel {
          background: rgba(28,31,39,0.85);
          backdrop-filter: blur(12px);
          border: 1px solid rgba(255,255,255,0.1);
        }
        .custom-scrollbar::-webkit-scrollbar { height: 4px; width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #282e39; border-radius: 10px; }
        
        @keyframes ping {
          75%, 100% { transform: scale(1.5); opacity: 0; }
        }
        .animate-ping { animation: ping 1.5s cubic-bezier(0,0,0.2,1) infinite; }
        .bw-mode .avatar-wrap img { filter: grayscale(1) contrast(0.95); }
        .bw-mode .avatar-wrap { border-color: #ffffff !important; }
        .bw-mode .glass-panel { background: #0b0b0b !important; color: #e6e6e6 !important; }
        
        .pin-tooltip {
          opacity: 0;
          transform: translateY(8px);
          pointer-events: none;
          transition: opacity 0.3s, transform 0.3s;
        }
        .pin-wrapper:hover .pin-tooltip {
          opacity: 1;
          transform: translateY(0);
          pointer-events: auto;
        }
        .side-panel-enter { transform: translateX(100%); }
        .side-panel-open { transform: translateX(0); }
        .side-panel-closed { transform: translateX(100%); }
      `}</style>

      {/* Main Map Area */}
      <main className="relative flex-1 overflow-hidden">
        {/* Background Map (Google Maps will mount here) */}
        <div className="absolute inset-0 bg-[#0a0c10]">
          <div ref={mapContainerRef} className="w-full h-full" />
          <div className="absolute inset-0 map-gradient-overlay pointer-events-none" />
        </div>

        {/* Map Controls */}
        <div className="absolute top-6 right-6 flex flex-col gap-3 z-20">
          <div className="flex flex-col bg-slate-900 rounded-xl shadow-2xl overflow-hidden border border-slate-800">
            <button className="p-3 hover:bg-slate-800 text-slate-300 transition-colors">
              <span style={{ fontFamily: "Material Icons", fontSize: 24 }}>+</span>
            </button>
            <div className="h-px bg-slate-800" />
            <button className="p-3 hover:bg-slate-800 text-slate-300 transition-colors">
              <span style={{ fontFamily: "Material Icons", fontSize: 24 }}>−</span>
            </button>
          </div>
          <button className="flex w-12 h-12 items-center justify-center rounded-xl bg-[#1152d4] text-white shadow-lg hover:bg-blue-600 transition-all">
            <svg viewBox="0 0 24 24" fill="currentColor" className="w-5 h-5">
              <path d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm8.94 3A8.994 8.994 0 0013 3.06V1h-2v2.06A8.994 8.994 0 003.06 11H1v2h2.06A8.994 8.994 0 0011 20.94V23h2v-2.06A8.994 8.994 0 0020.94 13H23v-2h-2.06zM12 19c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z" />
            </svg>
          </button>
          <button className="flex w-12 h-12 items-center justify-center rounded-xl bg-slate-900 text-slate-300 shadow-xl border border-slate-800 hover:bg-slate-800 transition-all">
            <svg viewBox="0 0 24 24" fill="currentColor" className="w-5 h-5">
              <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zm4.24 16L12 15.45 7.77 18l1.12-4.81-3.73-3.23 4.92-.42L12 5l1.92 4.53 4.92.42-3.73 3.23L16.23 18z" />
            </svg>
          </button>
          <button
            onClick={() => setBwMode((s) => !s)}
            title="Toggle B/W mode"
            className={`flex w-12 h-12 items-center justify-center rounded-xl shadow-lg transition-all ${bwMode ? 'bg-white text-black' : 'bg-slate-900 text-slate-300'}`}>
            <svg viewBox="0 0 24 24" fill="currentColor" className="w-5 h-5">
              <path d="M12 3v18a9 9 0 100-18z" />
            </svg>
          </button>
        </div>

        {/* Avatars are rendered as Google Maps overlays; DOM pins removed to avoid duplication. */}

        {/* Live Feed Panel */}
        <LiveTrackerBottomFeed employees={employeesData} openPanel={openPanel} />
      </main>

      {/* Side Panel Overlay */}
      {sidePanelOpen && (
        <div
          className="absolute inset-0 z-50 bg-black/20"
          onClick={closePanel}
        />
      )}

      {/* Side Panel */}
      <SidePanel selectedEmployee={selectedEmployee} sidePanelOpen={sidePanelOpen} closePanel={closePanel} />
    </div>
  );
}