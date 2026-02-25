// app/job-sites/geofencing/page.tsx
import React, { useEffect, useId, useState } from "react";
import Input from "../Theme/Input";
import DropDown from "../ui/DropDown";
import { getBranches, getBranchesForTable } from "@/lib/api";
import { notify, parseApiError } from "@/lib/utils";
import { RadiusSlider } from "./RadiusSlider";

export default function RightSection({ radius, setRadius, setCenter }) {

    const switchId = useId();
    const [hardLock, setHardLock] = useState(true);
    const [tab, setTab] = useState("existing");

    const toggle = () => setHardLock((v) => !v);

    const [branches, setBranches] = useState([]);
    const [dropdownItems, setDropdownItems] = useState([]);
    const [selectedBranchId, setSelectedBranchId] = useState(null);
    const [selectedLat, setSelectedLat] = useState(null);
    const [selectedLng, setSelectedLng] = useState(null);
    const [alertOnEntrance, setAlertOnEntrance] = useState(true);
    const [alertOnExit, setAlertOnExit] = useState(false);

    const fetchDropdowns = async () => {
        try {
            const { data } = await getBranchesForTable();

            console.debug("GeoFencing: fetched branches:", data);
            setBranches(data || []);

            // prepare items for DropDown (ensure `name` exists)
            const items = (data || []).map((b) => ({
                ...b,
                id: b.id,
                name: b.name ?? b.branch_name ?? b.title ?? b.name_en ?? `Branch ${b.id}`,
            }));
            setDropdownItems(items);
        } catch (error) {
            console.error("GeoFencing: failed to fetch branches", error);
            notify("Error", parseApiError(error), "error");
        }
    };

    useEffect(() => {
        fetchDropdowns();
    }, []);

    const selectedBranch = branches.find((b) => b.id == selectedBranchId);

    useEffect(() => {
        console.debug("GeoFencing: selectedBranchId", selectedBranchId);
        console.debug("GeoFencing: selectedBranch", selectedBranch);
    }, [selectedBranchId, selectedBranch]);

    useEffect(() => {
        if (!selectedBranch) {
            setSelectedLat(null);
            setSelectedLng(null);
            return;
        }

        const lat = Number(
            selectedBranch.lat ?? selectedBranch.latitude ?? selectedBranch.lat_dd
        );
        const lng = Number(
            selectedBranch.lon ?? selectedBranch.lng ?? selectedBranch.long ?? selectedBranch.longitude ?? selectedBranch.lon_dd
        );

        if (!isNaN(lat) && !isNaN(lng)) {
            setSelectedLat(lat);
            setSelectedLng(lng);

            // update parent map center immediately if provided
            if (typeof setCenter === "function") {
                setCenter({ lat, lng });
            }
        } else {
            // clear invalid values
            setSelectedLat(null);
            setSelectedLng(null);
        }
    }, [selectedBranch, setCenter]);

    const onsubmit = () => {
        if (!selectedBranch) {
            notify("Validation Error", "Please select a branch from the dropdown.", "warning");
            return;
        }
        if (selectedLat === null || selectedLng === null) {
            notify("Validation Error", "Selected branch does not have valid latitude and longitude.", "warning");
            return;
        }

        // console it
        console.log("Submitting Geo-fence with values:");
        console.log("Branch ID:", selectedBranchId);
        console.log("Latitude:", selectedLat);
        console.log("Longitude:", selectedLng);
        console.log("Radius (meters):", radius);
        console.log("Hard Lock Enabled:", hardLock);
        notify("Submitted", "Geo-fence configuration has been submitted. Check console for details.", "success");

    };

    return (
        <>
            {/* Header Actions */}
            <div className="p-6 border-b border-border ">
                {/* <div className="flex items-center gap-2 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-widest mb-2">
                            <span>Dashboard</span>
                            <span className="material-symbols-outlined text-[12px]">
                                chevron_right
                            </span>
                            <span className="text-primary">Job Sites</span>
                        </div> */}

                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-black tracking-tight text-slate-600 dark:text-white">
                        Geo-fencing
                    </h1>
                    <button
                        className="bg-slate-100 dark:bg-slate-900 p-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-200 transition-colors"
                        type="button"
                    >
                        <span className="material-symbols-outlined">sync</span>
                    </button>
                </div>
            </div>

            {/* Sidebar Tabs */}
            <div className="flex border-b border-border">
                <button
                    onClick={() => setTab("existing")}
                    type="button"
                    className={`flex-1 py-4 text-sm font-semibold transition-colors border-b-2
      ${tab === "existing"
                            ? "border-primary text-primary"
                            : "border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300"
                        }`}
                >
                    Active Branches
                </button>

                <button
                    onClick={() => setTab("new")}
                    type="button"
                    className={`flex-1 py-4 text-sm font-semibold transition-colors border-b-2
      ${tab === "new"
                            ? "border-primary text-primary"
                            : "border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300"
                        }`}
                >
                    New Branch
                </button>
            </div>

            {/* Scrollable Panel Content */}
            <div className="flex-1 overflow-y-auto p-6 space-y-6">
                {/* Branch List Section */}
                {
                    tab == "existing" &&
                    <div className="space-y-3">
                        <div className="flex items-center justify-between">
                            <h3 className="text-sm font-bold text-slate-500 uppercase tracking-wider">
                                Configured Areas
                            </h3>
                            <span className="bg-primary/10 text-primary text-[10px] font-bold px-1.5 py-0.5 rounded">
                                3 TOTAL
                            </span>
                        </div>

                        <div className="bg-slate-50 dark:bg-slate-900 border border-border rounded-xl p-4 shadow-sm relative overflow-hidden group">
                            <div className="absolute left-0 top-0 bottom-0 w-1 bg-accent-yellow" />
                            <div className="flex justify-between items-start">
                                <div>
                                    <h4 className="font-bold text-sm text-gray-600 dark:text-slate-500">Downtown HQ Central</h4>
                                    <p className="text-xs text-slate-500 mt-0.5">
                                        5th Ave, Manhattan, NY
                                    </p>
                                </div>
                                <div className="flex items-center gap-1">
                                    <span className="bg-green-500/10 text-green-500 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">
                                        Active
                                    </span>
                                </div>
                            </div>

                            <div className="grid grid-cols-2 mt-4 gap-4">
                                <div>
                                    <span className="text-[10px] text-slate-400 uppercase font-bold block">
                                        Radius
                                    </span>
                                    <span className="text-xs font-semibold text-gray-600 dark:text-slate-500">150 Meters</span>
                                </div>
                                <div>
                                    <span className="text-[10px] text-slate-400 uppercase font-bold block">
                                        Personnel
                                    </span>
                                    <span className="text-xs font-semibold text-gray-600 dark:text-slate-500">42 Staff</span>
                                </div>
                            </div>

                            <div className="mt-4 pt-3 border-t border-border  flex items-center justify-end gap-2">
                                {/* <button
                                        className="p-1.5 hover:bg-slate-200 dark:hover:bg-slate-900 rounded transition-colors"
                                        type="button"
                                    >
                                        <span className="material-symbols-outlined text-sm">
                                            edit
                                        </span>
                                    </button> */}
                                <button
                                    className="p-1.5 hover:bg-slate-200 dark:hover:bg-slate-900 rounded transition-colors text-red-500"
                                    type="button"
                                >
                                    <span className="material-symbols-outlined text-sm text-gray-600 dark:text-slate-500">
                                        delete
                                    </span>
                                </button>
                            </div>
                        </div>

                        <div className="bg-slate-50 dark:bg-slate-900 border border-border rounded-xl p-4 shadow-sm relative overflow-hidden group">
                            <div className="absolute left-0 top-0 bottom-0 w-1 bg-accent-yellow" />
                            <div className="flex justify-between items-start">
                                <div>
                                    <h4 className="font-bold text-sm text-gray-600 dark:text-slate-500">Downtown HQ Central</h4>
                                    <p className="text-xs text-slate-500 mt-0.5">
                                        5th Ave, Manhattan, NY
                                    </p>
                                </div>
                                <div className="flex items-center gap-1">
                                    <span className="bg-green-500/10 text-green-500 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">
                                        Active
                                    </span>
                                </div>
                            </div>

                            <div className="grid grid-cols-2 mt-4 gap-4">
                                <div>
                                    <span className="text-[10px] text-slate-400 uppercase font-bold block">
                                        Radius
                                    </span>
                                    <span className="text-xs font-semibold text-gray-600 dark:text-slate-500">150 Meters</span>
                                </div>
                                <div>
                                    <span className="text-[10px] text-slate-400 uppercase font-bold block">
                                        Personnel
                                    </span>
                                    <span className="text-xs font-semibold text-gray-600 dark:text-slate-500">42 Staff</span>
                                </div>
                            </div>

                            <div className="mt-4 pt-3 border-t border-border  flex items-center justify-end gap-2">
                                {/* <button
                                        className="p-1.5 hover:bg-slate-200 dark:hover:bg-slate-900 rounded transition-colors"
                                        type="button"
                                    >
                                        <span className="material-symbols-outlined text-sm">
                                            edit
                                        </span>
                                    </button> */}
                                <button
                                    className="p-1.5 hover:bg-slate-200 dark:hover:bg-slate-900 rounded transition-colors text-red-500"
                                    type="button"
                                >
                                    <span className="material-symbols-outlined text-sm text-gray-600 dark:text-slate-500">
                                        delete
                                    </span>
                                </button>
                            </div>
                        </div>

                    </div>
                }


                {/* Settings Form Area */}
                {
                    tab == "new" &&
                    <div className="space-y-5">
                        <h3 className="text-sm font-bold text-slate-500 uppercase tracking-wider">
                            New Branch Properties
                        </h3>

                        <div className="space-y-4">
                            <div>
                                <label className="text-xs font-bold text-slate-500 block mb-1.5 uppercase">
                                    Branch Name
                                </label>


                                <DropDown
                                    placeholder={"Select Branch"}
                                    items={dropdownItems}
                                    value={selectedBranchId}
                                    onChange={setSelectedBranchId}
                                />

                                {/* Display lat/lng for debugging / visibility */}
                                <div className="mt-3 grid grid-cols-2 gap-3">
                                    <Input
                                        label="Latitude"
                                        type="number"
                                        step="any"
                                        value={selectedLat ?? ""}
                                        onChange={(e) => {
                                            const raw = e.target.value;
                                            if (raw === "") {
                                                setSelectedLat(null);
                                                return;
                                            }
                                            const v = Number(raw);
                                            if (isNaN(v)) {
                                                // don't update center with invalid value, but keep input empty/null
                                                setSelectedLat(null);
                                                return;
                                            }
                                            setSelectedLat(v);
                                            if (typeof setCenter === "function" && selectedLng !== null) {
                                                setCenter({ lat: v, lng: selectedLng });
                                            }
                                        }}
                                    />
                                    <Input
                                        label="Longitude"
                                        type="number"
                                        step="any"
                                        value={selectedLng ?? ""}
                                        onChange={(e) => {
                                            const raw = e.target.value;
                                            if (raw === "") {
                                                setSelectedLng(null);
                                                return;
                                            }
                                            const v = Number(raw);
                                            if (isNaN(v)) {
                                                setSelectedLng(null);
                                                return;
                                            }
                                            setSelectedLng(v);
                                            if (typeof setCenter === "function" && selectedLat !== null) {
                                                setCenter({ lat: selectedLat, lng: v });
                                            }
                                        }}
                                    />
                                </div>
                            </div>
{/* 
                            <div>
                                <label htmlFor={switchId} className="text-xs font-bold text-slate-500 block mb-1.5 uppercase">
                                    Boundary Strictness
                                </label>
                                <div className="bg-slate-50 dark:bg-slate-900 p-3 rounded-lg flex items-center justify-between border border-border ">
                                    <div>
                                        <p className="text-sm font-bold">Hard Lock</p>
                                        <p className="text-[11px] text-slate-500">
                                            Block clock-in if outside area
                                        </p>
                                    </div>

                                    <button
                                        id={switchId}
                                        type="button"
                                        role="switch"
                                        aria-checked={hardLock}
                                        onClick={toggle}
                                        onKeyDown={(e) => {
                                            // Space/Enter toggles for keyboard users
                                            if (e.key === "Enter" || e.key === " ") {
                                                e.preventDefault();
                                                toggle();
                                            }
                                        }}
                                        className={[
                                            "relative inline-flex h-6 w-11 items-center rounded-full transition-colors",
                                            "focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-slate-900",
                                            hardLock ? "bg-primary" : "bg-slate-300 dark:bg-slate-700",
                                        ].join(" ")}
                                    >
                                        <span
                                            className={[
                                                "inline-block h-4 w-4 transform rounded-full bg-white transition-transform",
                                                hardLock ? "translate-x-6" : "translate-x-1",
                                            ].join(" ")}
                                        />
                                    </button>
                                </div>
                            </div> */}

                            <div>
                                <label className="text-xs font-bold text-slate-500 block mb-1.5 uppercase">
                                    Push Notifications
                                </label>
                                <div className="space-y-2">
                                    <label className="flex items-center gap-3 cursor-pointer">
                                        <input
                                            checked={alertOnEntrance}
                                            onChange={(e) => setAlertOnEntrance(e.target.checked)}
                                            className="rounded border border-border  text-primary focus:ring-primary bg-white dark:bg-slate-900"
                                            type="checkbox"
                                        />
                                        <span className="text-sm text-slate-600 dark:text-white">
                                            Alert manager on entrance
                                        </span>
                                    </label>

                                    <label className="flex items-center gap-3 cursor-pointer">
                                        <input
                                            checked={alertOnExit}
                                            onChange={(e) => setAlertOnExit(e.target.checked)}
                                            className="rounded border border-border  text-primary focus:ring-primary bg-white dark:bg-slate-900"
                                            type="checkbox"
                                        />
                                        <span className="text-sm text-slate-600 dark:text-white">
                                            Alert on unauthorized exit
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <RadiusSlider
                                min={50}
                                max={500}
                                defaultValue={radius}
                                value={radius}
                                step={5}
                                onChange={setRadius}
                            />
                        </div>
                    </div>
                }
            </div>

            {/* Bottom Sticky Actions */}
            {
                tab == "new" &&

                <div className="p-6 border-t border-border  bg-slate-50 dark:bg-slate-900/20 flex flex-col gap-3">
                    <button onClick={onsubmit}
                        className="w-full bg-primary text-white font-bold py-3 rounded-xl flex items-center justify-center gap-2 shadow-lg shadow-primary/20 hover:bg-primary/90 active:scale-[0.98] transition-all"
                        type="button"
                    >
                        <span className="material-symbols-outlined text-[20px]">
                            save
                        </span>
                        Submit
                    </button>

                    {/* <button
                            className="w-full bg-white dark:bg-slate-900 border border-border  text-slate-600 dark:text-slate-300 font-bold py-3 rounded-xl flex items-center justify-center gap-2 hover:bg-slate-50 dark:hover:bg-slate-900 transition-all"
                            type="button"
                        >
                            <span className="material-symbols-outlined text-[20px]">
                                devices
                            </span>
                            SYNC TO ALL DEVICES
                        </button> */}
                </div>

            }
        </>
    );
}