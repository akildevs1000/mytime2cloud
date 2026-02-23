"use client";

import React, { useState, useEffect } from 'react';
import {
  Users,
  Cpu,
  RefreshCw
} from 'lucide-react';

// UI Components
import DropDown from '@/components/ui/DropDown';
import SyncGrid from '@/components/Employees/UploadPhoto/SyncGrid';
import { useAttendanceSync } from './useAttendanceSync';
import { addPerson, getBranches, getDepartments, getDeviceListNew, getScheduledEmployeeList } from '@/lib/api';
import { notify, parseApiError } from '@/lib/utils';
import { MODEL_NUMBERS } from '@/lib/dropdowns';
import MultiDropDown from '@/components/ui/MultiDropDown';
import { getUser } from '@/config/index';

export default function AttendanceTable() {
  const [branches, setBranches] = useState([]);
  const [departments, setDepartments] = useState([]);
  const [selectedBranch, setSelectedBranch] = useState(null);
  const [selectedDepartment, setSelectedDepartment] = useState([]);
  const [selectedModel, setSelectedModel] = useState(null);
  const [scheduledEmployees, setScheduledEmployees] = useState([]);
  const [devices, setDevices] = useState([]);


  // 1. Personnel Logic
  const personSync = useAttendanceSync(scheduledEmployees);

  // 2. Device Logic (Reusing the same hook!)
  const deviceSync = useAttendanceSync(devices);

  useEffect(() => {
    getBranches().then(setBranches).catch(err => notify("Error", parseApiError(err), "error"));
  }, []);

  useEffect(() => {
    getDepartments(selectedBranch).then(setDepartments).catch(err => notify("Error", parseApiError(err), "error"));
  }, [selectedBranch]);


  const fetchScheduledEmployees = async () => {
    try {

      let result = await getScheduledEmployeeList(selectedDepartment);

      const formattedData = result.map((emp) => ({
        itemId: emp.id.toString(),

        id: emp.id, // Use real ID from DB
        name: emp?.full_name || "Unknown",
        dept: [emp?.branch?.branch_name, emp?.department?.name]
          .filter(Boolean)
          .join(" / ") || "N/A",
        profile_picture_raw: emp.profile_picture_raw,
        profile_picture: emp.profile_picture,
      }));

      setScheduledEmployees(formattedData);

      // setScheduledEmployees(data);
    } catch (error) {
      console.log(error);

    }
  };

  useEffect(() => {
    fetchScheduledEmployees()
  }, [selectedDepartment]);


  const fetchDevices = async () => {
    try {
      let result = await getDeviceListNew({ branch_id: selectedBranch, module_number: selectedModel });

      // Use a Map to keep track of unique device_ids
      const seen = new Map();
      const uniqueResults = result.filter(emp => {
        if (!seen.has(emp.device_id)) {
          seen.set(emp.device_id, true);
          return true;
        }
        return false;
      });

      const formattedData = uniqueResults.map((emp) => ({
        itemId: emp.id.toString(), // The DB primary key
        id: emp.device_id,         // Now guaranteed unique in this array
        name: emp?.name || "Unknown",
        dept: emp?.branch?.branch_name || "N/A",
        profile_picture_raw: null,
        profile_picture: null,
      }));

      setDevices(formattedData);
    } catch (error) {
      console.error("Failed to fetch devices:", error);
    }
  };

  useEffect(() => {
    fetchDevices()
  }, [selectedBranch, selectedModel]);


  const [loading, setLoading] = useState(false);


  const onSubmit = async () => {
    setLoading(true);
    try {
      const user = await getUser();
      const companyId = user?.company_id || 0;

      const selectedEmployees = personSync.selected;
      const selectedDevices = deviceSync.selected;

      // Loop through each device
      for (const device of selectedDevices) {

        // Loop through each employee for that specific device
        for (const emp of selectedEmployees) {

          // Prepare payload for ONE employee and ONE device
          const payload = {
            personList: [{
              name: emp.name,
              userCode: emp.id,
              profile_picture_raw: emp.profile_picture_raw,
              faceImage: emp.profile_picture
            }],
            snList: [device.id], // Single device ID in the list
            branch_id: null,
            company_id: companyId
          };

          try {
            console.log(`Sending ${emp.name} to device ${device.id}...`);

            // Wait for the backend response
            const data = await addPerson(payload);

            // Show response one by one
            console.log("Response received:", data);

            // Optional: Update your UI state here to show progress
            // setSyncResults(prev => [...prev, data.deviceResponse[0]]);

          } catch (err) {
            console.error(`Failed to sync ${emp.name} to ${device.id}:`, err);
          }
        }
      }
    } finally {
      setLoading(false);
      console.log("All sync operations completed.");
    }
  };

  return (
    <div className='p-6 space-y-6 pb-32 overflow-y-auto max-h-[calc(100vh-100px)]'>

      {/* HEADER SECTION */}
      <header className="flex flex-col lg:flex-row justify-between items-center gap-6 bg-surface p-4 px-6 rounded-2xl shadow-glass border border-glass-border">
        {/* Left Side: Title & Subtitle */}
        <div className="flex flex-col whitespace-nowrap">
          <h1 className="text-2xl font-bold text-gray-800 dark:text-gray-200 leading-tight">
            Employee Upload
          </h1>
          <p className="text-[11px] font-medium text-slate-500 uppercase tracking-wider">
            upload emplpyee info to devices
          </p>
        </div>

        {/* Right Side: Dropdowns in one line */}
        <div className="flex flex-wrap lg:flex-nowrap items-center gap-3 w-full lg:w-auto">
          <div className="min-w-[200px]">
            <DropDown
              items={branches}
              value={selectedBranch}
              onChange={setSelectedBranch}
              placeholder="Select Branch"
              width="w-full lg:w-[220px]"
            />
          </div>
          <div className="min-w-[200px]">
            <MultiDropDown
              items={departments}
              value={selectedDepartment}
              onChange={setSelectedDepartment}
              placeholder="Select Department"
              width="w-full lg:w-[220px]"
            />
          </div>
          <div className="min-w-[200px]">
            <DropDown
              items={MODEL_NUMBERS}
              value={selectedModel}
              onChange={setSelectedModel}
              placeholder="Select Model"
              width="w-full lg:w-[220px]"
            />
          </div>
        </div>
      </header>

      {/* SECTION 1: PERSONNEL */}
      <div className="space-y-4">
        <SyncGrid sync={personSync} leftTitle="Available Employees" rightTitle="Selected Personnel" leftIcon={Users} />
      </div>

      {/* SECTION 2: DEVICES */}
      <div className="space-y-4">
        <SyncGrid sync={deviceSync} leftTitle="Available Devices" rightTitle="Target Hardware" leftIcon={Cpu} theme="indigo" />
      </div>


      <div className="fixed bottom-8 left-1/2 -translate-x-1/2 z-50">
        <button disabled={loading} onClick={onSubmit}
          className="group relative px-10 py-4 bg-slate-900 text-white text-sm font-bold uppercase tracking-widest rounded-2xl shadow-[0_10px_40px_-10px_rgba(79,70,229,0.5)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_20px_40px_-10px_rgba(79,70,229,0.6)] overflow-hidden">
          <div
            className="absolute inset-0 bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-600 opacity-80 group-hover:opacity-100 transition-opacity duration-300">
          </div>
          <div className="relative flex items-center gap-3">
            <RefreshCw className={`w-5 h-5 ${loading ? 'animate-spin' : ''}`} />
            {loading ? 'Synchronizing...' : 'Synchronize Data'}
          </div>
          <div
            className="absolute top-0 -inset-full h-full w-1/2 z-5 block transform -skew-x-12 bg-gradient-to-r from-transparent to-white opacity-20 group-hover:animate-shine">
          </div>
        </button>
      </div>
    </div>
  );
}


