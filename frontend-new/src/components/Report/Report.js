"use client";

import React, { useState, useEffect, useCallback } from 'react';
import { Eye, File, Printer, RefreshCw } from 'lucide-react';
import { Tabs, TabsList, TabsTrigger, TabsContent } from "@/components/ui/tabs";
import { getAttendanceReports, getBranches, getDepartmentsByBranchIds, getScheduledEmployeeList, getStatuses } from '@/lib/api';

import DropDown from '@/components/ui/DropDown';
import DateRangeSelect from "@/components/ui/DateRange";
import Pagination from '@/lib/Pagination';
import DataTable from '@/components/ui/DataTable';
import Columns from "./columns";
import MultiDropDown from '@/components/ui/MultiDropDown';
import { formatDateDubai, notify, parseApiError } from '@/lib/utils';
import RegenerateReport from '@/components/Report/Regenerate';
import { getAttendanceTabs, startReportGeneration } from '@/lib/endpoint/attendance';
import LoadingProgressDialog from './LoadingProgressDialog';
import { API_BASE_URL } from '@/config';
import { getUser } from "@/config/index";

const shiftTabMapping = {
  '0': { single: true, double: false, multi: false },
  '2': { single: false, double: false, multi: true },
  '5': { single: false, double: true, multi: false },
}

const reportTemplates = [
  { id: `Template1`, name: `Monthly Report Format A` },
  { id: `Template2`, name: `Monthly Report Format B` },
  { id: 'Template4', name: 'Monthly Report Format C' },
  { id: `Template3`, name: `Daily` },
];

export default function AttendanceTable() {

  const [isOpen, setIsOpen] = useState(false);


  // filters
  const [shiftTypeId, setShiftTypeId] = useState(`0`);
  const [selectedStatusIds, setSelectedStatusIds] = useState([]);
  const [selectedBranchIds, setSelectedBranchIds] = useState([]);
  const [selectedDepartmentIds, setSelectedDepartment] = useState([]);
  const [selectedEmployeeIds, setSelectedEmployeeIds] = useState([]);
  const [selectedReportTemplate, setSelectedReportTemplate] = useState("Template1");

  const [from, setFrom] = useState(null);
  const [to, setTo] = useState(null);

  const [records, setAttendance] = useState([]);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState(null);

  // Pagination State
  const [currentPage, setCurrentPage] = useState(1);
  const [perPage, setPerPage] = useState(25);
  const [total, setTotalAttendance] = useState(0);


  const [statusses, setStatusses] = useState([]);
  const [branches, setBranches] = useState([]);
  const [departments, setDepartments] = useState([]);
  const [scheduledEmployees, setScheduledEmployees] = useState([]);

  const [isButtonclicked, setIsButtonclicked] = useState(false);

  const [tabs, setTabs] = useState([]);
  const [orginalTabSet, setOriginalTabSet] = useState({});


  const fetchStatuses = async () => {
    try {
      setStatusses(await getStatuses());
    } catch (error) {
      setError(parseApiError(error));
    }
  };

  const fetchAttendanceTabs = async () => {
    try {
      const response = await getAttendanceTabs(); // e.g., { single: true, double: true, multi: false }

      // Define the mapping between JSON keys and your numeric IDs
      const shiftMapping = [
        { key: 'single', id: "0", name: 'Single Shift' },
        { key: 'double', id: "5", name: 'Double Shift' },
        { key: 'multi', id: "2", name: 'Multi Shift' }
      ];

      // Filter based on the API response
      const activeTabs = shiftMapping
        .filter(item => response[item.key] === true)
        .map(({ id, name }) => ({ id, name }));

      setTabs(activeTabs);

      setOriginalTabSet(response)

      // Optional: Auto-select the first available tab if current selection is empty
      if (activeTabs.length > 0) {
        setShiftTypeId(activeTabs[0].id);
      }
    } catch (error) {
      setError(parseApiError(error));
    }
  };

  const fetchBranches = async () => {
    try {
      setBranches(await getBranches());
    } catch (error) {
      setError(parseApiError(error));
    }
  };

  const fetchDepartments = async () => {
    try {
      setDepartments(await getDepartmentsByBranchIds(selectedBranchIds));
    } catch (error) {
      setError(parseApiError(error));
    }
  };

  const fetchScheduledEmployees = async () => {
    try {

      let result = await getScheduledEmployeeList(selectedDepartmentIds);

      let data = result.map(e => ({ ...e, name: e.full_name }))

      console.log(data);

      setScheduledEmployees(data);
    } catch (error) {
      setError(parseApiError(error));
    }
  };

  useEffect(() => {
    fetchAttendanceTabs();
    fetchStatuses();
    fetchBranches();
  }, []);


  useEffect(() => {
    fetchDepartments();
  }, [selectedBranchIds]);

  useEffect(() => {
    fetchScheduledEmployees();
  }, [selectedDepartmentIds]);

  useEffect(() => {
    console.log(selectedEmployeeIds);
  }, [selectedEmployeeIds]);

  const [params, setParams] = useState(null);


  const fetchRecords = async (shiftTypeId) => {

    if (!shiftTypeId || !isButtonclicked) return;

    if (
      !selectedEmployeeIds?.length
    ) {
      notify("Warning", "Employee not selected", "warning");
      return;
    }

    setIsLoading(true);
    setError(null);

    try {
      const params = {
        page: currentPage,
        per_page: perPage,
        shift_type_id: shiftTypeId,
        report_template: selectedReportTemplate,
        from_date: formatDateDubai(from),
        to_date: formatDateDubai(to),
        employee_id: selectedEmployeeIds,
        statuses: selectedStatusIds,
        department_ids: selectedDepartmentIds,
        showTabs: JSON.stringify({ single: true, double: true, multi: true }),
      };


      setParams(params);
      console.log(params);

      const result = await getAttendanceReports(params);

      if (result && Array.isArray(result.data)) {
        setAttendance(result.data);
        setCurrentPage(result.current_page || 1);
        setTotalAttendance(result.total || 0);
      } else {
        throw new Error("Invalid data structure received from API.");
      }

    } catch (error) {
      setError(parseApiError(error));
    } finally {
      setIsLoading(false); // Always turn off loading
    }
  };

  // --- NEW DIALOG STATE ---
  const [isProgressOpen, setIsProgressOpen] = useState(false);
  const [queryStringUrl, setQueryStringUrl] = useState("");

  const process_file_in_child_comp = async (type, actionType) => {
    if (selectedEmployeeIds.length === 0) {
      notify("Warning", "Employee not selected", "warning");
      return;
    }

    if (
      !selectedReportTemplate
    ) {
      notify("Warning", "Template not selected", "warning");
      return;
    }

    try {
      
      const isMultiShift = [2, 5].includes(Number(shiftTypeId));
      const endpointPrefix = isMultiShift ? "multi_in_out_" : "";
      const baseUrl = `${API_BASE_URL}/${endpointPrefix}${type}`;

      const user = await getUser();

      console.log(user);


      let company_id = user?.company_id ?? 0;

      // Common parameters used in most logic branches
      const commonParams = {
        report_template: selectedReportTemplate,
        main_shift_type: shiftTypeId,
        shift_type_id: shiftTypeId,
        company_id: company_id,
        report_type: 'Monthly',
        from_date: formatDateDubai(from),
        to_date: formatDateDubai(to),
        showTabs: JSON.stringify(shiftTabMapping[shiftTypeId]),
      };

      if (selectedReportTemplate == 'Template3' && actionType == 'PDF') {
        setIsOpen(true);
        return;
      }

      // 1. Handle Template4 Redirect (Special Case)
      if (selectedReportTemplate === "Template4" && actionType !== "EXCEL") {
        const t4Params = new URLSearchParams({
          employee_ids: selectedEmployeeIds.join(","),
          company_id: company_id,
          from_date: commonParams.from_date,
          to_date: commonParams.to_date,
          shift_type_id: shiftTypeId,
          company_name: "Hilal & Co",
        });

        window.open(`http://localhost:4173/attendance-report/?${t4Params.toString()}`, "_blank");
        return;
      }

      // 2. Prepare the Query String for other actions
      const queryObj = new URLSearchParams(commonParams);

      if (selectedDepartmentIds?.length > 0) {
        queryObj.append("department_ids", selectedDepartmentIds.join(","));
      }

      queryObj.append("employee_id", selectedEmployeeIds); // Note: passes as string/comma-sep
      queryObj.append("employee_ids", selectedEmployeeIds.join(","));

      const fullQsUrl = `${baseUrl}?${queryObj.toString()}`;


      // 3. Handle PDF/Async Generation
      if (actionType !== "EXCEL") {
        const payload = {
          ...commonParams,
          overtime: 0,
          employee_id: selectedEmployeeIds,
          'employee_id[]': selectedEmployeeIds,
          filterType: 'Monthly'
        };
        await startReportGeneration(payload);
        setQueryStringUrl(fullQsUrl);
        setIsProgressOpen(true);
        return;
      }

      // 4. Handle Excel Download
      const downloadAnchor = document.createElement("a");
      downloadAnchor.href = fullQsUrl;
      downloadAnchor.target = "_blank";
      downloadAnchor.click();

    } catch (error) {
      console.error("Report Generation Error:", error);
    }
  };

  const handleSubmit = () => {
    setIsButtonclicked(true);
    fetchRecords(shiftTypeId);
  }

  useEffect(() => {
    fetchRecords(shiftTypeId)
  }, [shiftTypeId])


  return (
    <div className='p-10'>
      <div className="flex flex-wrap items-center justify-between space-x-3 space-y-2 mb-6 sm:space-y-0">

        <div className='flex gap-5'>
          <h1 className="text-2xl font-extrabold text-gray-600 dark:text-slate-300 flex items-center">
            {/* <User className="w-7 h-7 mr-3 text-indigo-600" /> */}
            Attendance Report
          </h1>

          <div className="flex flex-col">
            <MultiDropDown
              placeholder={'Select Status'}
              items={statusses}
              value={selectedStatusIds}
              onChange={setSelectedStatusIds}
              badgesCount={1}
            />
          </div>

          <div className="flex flex-col">
            <MultiDropDown
              placeholder={'Select Branch'}
              items={branches}
              value={selectedBranchIds}
              onChange={setSelectedBranchIds}
              badgesCount={1}
            />
          </div>

          <div className="flex flex-col">
            <MultiDropDown
              placeholder={'Select Department'}
              items={departments}
              value={selectedDepartmentIds}
              onChange={setSelectedDepartment}
              badgesCount={1}
            />
          </div>

          <div className="flex flex-col">
            <MultiDropDown
              placeholder={'Select Employees'}
              items={scheduledEmployees}
              value={selectedEmployeeIds}
              onChange={setSelectedEmployeeIds}
              badgesCount={1}
            />
          </div>

          <div className="flex flex-col">
            <DropDown
              placeholder={'Select Report Template'}
              onChange={setSelectedReportTemplate}
              value={selectedReportTemplate}
              items={reportTemplates}
            />
          </div>

          <div className="flex flex-col">
            <DateRangeSelect
              value={{ from, to }}
              onChange={({ from, to }) => {
                setFrom(from);
                setTo(to);
              }
              } />
          </div>

          {/* Refresh Button */}
          <button onClick={handleSubmit} className="bg-primary text-white px-4 py-1 rounded-lg font-semibold shadow-md hover:bg-indigo-700 transition-all flex items-center space-x-2 whitespace-nowrap">
            <RefreshCw className={`w-4 h-4 mr-1 ${isLoading ? 'animate-spin' : ''}`} /> Submit
          </button>
        </div>



      </div>

      <div className='flex'>
        <Tabs
          value={shiftTypeId || '0'}
          onValueChange={(value) => setShiftTypeId(value)}
          className="w-full"
        >
          {/* --- Tabs Header --- */}
          <div className="flex justify-between mb-4">
            {
              tabs.length > 0 && <div className="flex justify-between p-2 bg-white dark:bg-slate-800 w-full rounded-lg shadow">
                <TabsList className="flex bg-white dark:bg-slate-700 p-1">
                  {tabs.map((tab) => (
                    <TabsTrigger
                      key={tab.id}
                      value={tab.id}
                      className="px-4 py-2 text-sm font-medium rounded-md data-[state=active]:bg-primary/10 data-[state=active]:text-primary data-[state=active]:shadow-sm transition-all duration-200"
                    >
                      {tab.name}
                    </TabsTrigger>
                  ))}
                </TabsList>

                <div className='flex gap-2'>

                  <RegenerateReport shift_type_id={shiftTypeId} />

                  <button onClick={() => process_file_in_child_comp('monthly_download_pdf', `PDF`)}
                    className="bg-primary hover:bg-blue-600 text-white text-sm font-semibold py-2 px-3 rounded-lg flex items-center gap-1 transition-all shadow-lg shadow-primary/20"
                  >
                    <Printer size={15} />
                  </button>

                  <button onClick={() => process_file_in_child_comp('monthly_download_csv', `EXCEL`)}
                    className="bg-primary hover:bg-blue-600 text-white text-sm font-semibold py-2 px-3 rounded-lg flex items-center gap-1 transition-all shadow-lg shadow-primary/20"
                  >
                    <File size={15} />
                  </button>
                </div>

              </div>
            }

          </div>

          {/* --- Tabs Content --- */}
          {tabs.map((tab) => (
            <TabsContent key={tab.id} value={tab.id} className="space-y-2 rounded-lg">
              <DataTable
                columns={Columns(tab.id)} // Pass the specific tab ID
                data={records}
                isLoading={isLoading}
                error={error}
                onRowClick={(item) => console.log("Clicked:", item)}
                pagination={
                  <Pagination
                    page={currentPage}
                    perPage={perPage}
                    total={total}
                    onPageChange={setCurrentPage}
                    onPerPageChange={(n) => {
                      setPerPage(n);
                      setCurrentPage(1);
                    }}
                    pageSizeOptions={[10, 25, 50]}
                  />
                }
              />
            </TabsContent>
          ))}
        </Tabs>
      </div>

      {/* --- ADD THE DIALOG COMPONENT HERE --- */}
      <LoadingProgressDialog
        isOpen={isProgressOpen}
        queryStringUrl={queryStringUrl}
        onClose={() => setIsProgressOpen(false)}
      />

      
    </div>
  );
}
