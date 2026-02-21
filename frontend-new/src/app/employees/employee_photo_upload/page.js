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
import { addPerson, getBranches, getDepartments, getDepartmentsByBranchIds, getDeviceList, getDeviceListNew, getScheduledEmployeeList } from '@/lib/api';
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
        dept: emp?.department_name || "N/A",
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

      const formattedData = result.map((emp) => ({
        itemId: emp.id.toString(),

        id: emp.device_id, // Use real ID from DB
        name: emp?.name || "Unknown",
        dept: emp?.branch?.branch_name || "N/A",
        profile_picture_raw: null,
        profile_picture: null,
      }));

      console.log(formattedData);

      setDevices(formattedData);

      // setScheduledEmployees(data);
    } catch (error) {
      console.log(error);

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
      let list = personSync.selected.map(emp => ({
        name: emp.name,
        userCode: emp.id,
        profile_picture_raw: emp.profile_picture_raw,
        faceImage: emp.profile_picture
      }));

      let payload = {
        "personList": list,
        "snList": deviceSync.selected.map(e => e.id),
        "branch_id": null,
        company_id: user?.company_id || 0
      }

      console.log(devices);
      console.log(payload);
      let data = await addPerson(payload);
      console.log(data);


      // FIX: Check if status is explicitly false
      //   if (data?.status === false) {
      //     // Check if data.errors actually exists and has keys
      //     if (data.errors && Object.keys(data.errors).length > 0) {
      //       const firstKey = Object.keys(data.errors)[0];
      //       console.log(data.errors);
      //       notify("Error", data.errors[firstKey][0], "error");
      //     } else {
      //       // Fallback if status is false but no specific error object exists
      //       notify("Error", data?.message, "error");
      //     }
      //     return;
      //   }

      //   notify("Success", "Device Saved", "success")
      // } catch (error) {
      //   console.log(error);

      //   notify("Error", parseApiError(error), "error");

    } finally {
      setLoading(false);
    }
  };

  return (
    <div className='p-6 pb-32 space-y-12 bg-slate-50 dark:bg-slate-950 min-h-screen overflow-y-auto'>

      {/* HEADER SECTION */}
      <header className="flex flex-col lg:flex-row justify-between items-center gap-6 bg-surface p-4 px-6 rounded-2xl shadow-glass border border-glass-border">
        {/* Left Side: Title & Subtitle */}
        <div className="flex flex-col whitespace-nowrap">
          <h1 className="text-2xl font-bold text-gray-800 dark:text-gray-200 leading-tight">
            Elite Sync Studio
          </h1>
          <p className="text-[11px] font-medium text-slate-500 uppercase tracking-wider">
            Personnel & Hardware Bridge
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


