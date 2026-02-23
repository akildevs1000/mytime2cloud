"use client";

import React, { useState, useEffect, useMemo } from 'react';
import {
  Eye,
  X,
  FileText,
  Calendar,
  Users,
  PieChart,
  ClipboardList
} from 'lucide-react';
import { getLeavesGroups } from '@/lib/endpoint/leaves';

export default function LeaveViewDialog({ isOpen, setIsOpen, editedItem, auth, onResponse }) {
  const [activeTab, setActiveTab] = useState('info');
  const [leaveStats, setLeaveStats] = useState([]);
  const [approveRejectNotes, setApproveRejectNotes] = useState(editedItem?.approve_reject_notes || "");
  const [loading, setLoading] = useState(false);

  const toggleModal = () => setIsOpen(!open);

  // Computed: Day Difference logic from Vue
  const dayDifference = useMemo(() => {
    if (!editedItem?.start_date || !editedItem?.end_date) return 0;
    const from = new Date(editedItem.start_date);
    const to = new Date(editedItem.end_date);
    const diffTime = Math.abs(to - from);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
    return Math.max(1, diffDays);
  }, [editedItem]);

  const formatDate = (date) => {
    if (!date) return "--";
    return new Date(date).toLocaleDateString("en-US", {
      day: "2-digit",
      month: "short",
      year: "numeric",
    });
  };


  const verifyAvailableCount = async (leaveGroupId) => {

    if (leaveGroupId) {
      try {
        let data1 = await getLeavesGroups(leaveGroupId, {
          per_page: 1000,
          employee_id: editedItem.employee.id,
        });

        let leaveStats = data1[0].leave_count.map((e) => ({
          leave_group: e.leave_type.short_name,
          used: e.employee_used,
          total: e.leave_type_count,
          available: e.leave_type_count - e.employee_used,
        }));

        setLeaveStats(leaveStats)
      } catch (error) {
        console.log(error);
      }
    }


  }

  // Simulated API calls (Replace with your actual Axios calls)
  useEffect(() => {
    if (isOpen && editedItem?.id) {
      console.log(editedItem);
      verifyAvailableCount(editedItem.employee.leave_group_id)
    }
  }, [isOpen, editedItem]);


  const handleAction = async (type) => {
    if (!approveRejectNotes) {
      alert("Notes are required");
      return;
    }
    const actionText = type === 'approve' ? 'Approve' : 'Reject';
    if (!confirm(`Are you sure to ${actionText} Leave?`)) return;

    setLoading(true);
    try {
      // Example Axios call logic
      // await axios.post(`${endpoint}/${type}/${editedItem.id}`, { notes: approveRejectNotes, ... })
      onResponse?.(true);
      setIsOpen(false);
    } catch (e) {
      console.error(e);
    } finally {
      setLoading(false);
    }
  };

  if (!isOpen) return null;

  return (
    <>
      <button
        onClick={() => setOpen(true)}
        className="flex items-center gap-2 px-3 py-1.5 rounded-md hover:bg-slate-700 text-slate-400 hover:text-white transition-colors text-sm font-medium"
      >
        <Eye size={15} /> View
      </button>

      {open && (
        <div className="fixed inset-0 z-50 flex items-center justify-center px-4">
          {/* Backdrop */}
          <div
            className="absolute inset-0 bg-black/70 backdrop-blur-sm transition-opacity animate-in fade-in duration-300"
            onClick={toggleModal}
          ></div>

          {/* Modal Card */}
          <div className="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-white/10 w-full max-w-4xl overflow-hidden transform transition-all animate-in fade-in zoom-in duration-200 flex flex-col max-h-[90vh]">

            {/* Header */}
            <div className="px-6 py-5 border-b border-gray-200 dark:border-white/10 flex justify-between items-center bg-white dark:bg-slate-800">
              <div>
                <h3 className="text-lg font-bold text-gray-600 dark:text-gray-200">
                  Leave Details
                </h3>
                <p className="text-xs text-slate-400 mt-0.5">
                  Reviewing request for {editedItem?.employee?.full_name}
                </p>
              </div>
              <button onClick={toggleModal} className="text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors rounded-full p-1">
                <X size={20} />
              </button>
            </div>

            {/* Tabs Navigation */}
            <div className="flex justify-end px-6 bg-gray-50 dark:bg-slate-900/50 border-b border-gray-200 dark:border-white/10">
              {[
                { id: 'info', label: 'Information', icon: <ClipboardList size={14} /> },
                { id: 'quota', label: 'Leave Quota', icon: <PieChart size={14} /> }
              ].map((tab) => (
                <button
                  key={tab.id}
                  onClick={() => setActiveTab(tab.id)}
                  className={`flex items-center gap-2 px-4 py-3 text-sm font-medium transition-all border-b-2 ${activeTab === tab.id
                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                    : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'
                    }`}
                >
                  {tab.icon} {tab.label}
                </button>
              ))}
            </div>

            {/* Modal Body */}
            <div className="p-6 overflow-y-auto bg-white dark:bg-slate-800">
              {activeTab === 'info' && (
                <div className="grid grid-cols-12 gap-6">
                  {/* Left Column */}
                  <div className="col-span-12 lg:col-span-7 space-y-5">
                    <div className="flex gap-4">
                      <div className="flex-1 space-y-4">
                        <div className="space-y-1.5">
                          <label className="text-xs font-medium text-slate-400 uppercase tracking-wider">From Date</label>
                          <div className="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-900/50 border border-gray-200 dark:border-white/5 text-sm dark:text-gray-300">
                            {formatDate(editedItem?.start_date)}
                          </div>
                        </div>
                        <div className="space-y-1.5">
                          <label className="text-xs font-medium text-slate-400 uppercase tracking-wider">To Date</label>
                          <div className="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-900/50 border border-gray-200 dark:border-white/5 text-sm dark:text-gray-300">
                            {formatDate(editedItem?.end_date)}
                          </div>
                        </div>
                      </div>
                      <div className="w-28 flex flex-col items-center justify-center rounded-2xl bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20">
                        <span className="text-2xl font-black text-blue-600 dark:text-blue-400">{dayDifference}</span>
                        <span className="text-[10px] font-bold text-blue-600/60 dark:text-blue-400/60 uppercase">Days</span>
                      </div>
                    </div>

                    <div className="space-y-1.5">
                      <label className="text-xs font-medium text-slate-400 uppercase tracking-wider">Applied Date</label>
                      <div className="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-900/50 border border-gray-200 dark:border-white/5 text-sm dark:text-gray-300">
                        {formatDate(editedItem?.created_at)}
                      </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                      <div className="space-y-1.5">
                        <label className="text-xs font-medium text-slate-400 uppercase tracking-wider">Group</label>
                        <div className="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-900/50 border border-gray-200 dark:border-white/5 text-sm dark:text-gray-300">
                          {editedItem?.employee?.leave_group?.group_name || "--"}
                        </div>
                      </div>
                      <div className="space-y-1.5">
                        <label className="text-xs font-medium text-slate-400 uppercase tracking-wider">Leave Type</label>
                        <div className="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-900/50 border border-gray-200 dark:border-white/5 text-sm dark:text-gray-300">
                          {editedItem?.leave_type?.short_name || "--"}
                        </div>
                      </div>
                    </div>

                    <div className="space-y-1.5">
                      <label className="text-xs font-medium text-slate-400 uppercase tracking-wider">Reason</label>
                      <div className="p-3 rounded-lg bg-slate-50 dark:bg-slate-900/50 border border-gray-200 dark:border-white/5 text-sm dark:text-gray-400">
                        {editedItem?.reason || "No reason provided"}
                      </div>
                    </div>

                    <div className="space-y-1.5">
                      <label className="text-xs font-medium text-slate-400 uppercase tracking-wider">Approve/Reject Date</label>
                      <div className="p-3 rounded-lg bg-slate-50 dark:bg-slate-900/50 border border-gray-200 dark:border-white/5 text-sm dark:text-gray-400">
                        {formatDate(editedItem?.updated_at)}
                      </div>
                    </div>



                    {/* Action Area */}
                    <div className="pt-4 border-t border-gray-100 dark:border-white/5 space-y-4">
                      <div className="space-y-1.5">
                        <label className="text-sm font-bold text-blue-500 dark:text-blue-400">Manager Notes</label>
                        <textarea
                          value={approveRejectNotes}
                          onChange={(e) => setApproveRejectNotes(e.target.value)}
                          placeholder="Provide a reason for approval or rejection..."
                          className="w-full p-3 rounded-xl bg-white dark:bg-slate-900 border border-gray-200 dark:border-white/10 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all dark:text-gray-200"
                          rows="3"
                        />
                      </div>

                      <div className="flex gap-3">
                        <button
                          disabled={loading}
                          onClick={() => handleAction('reject')}
                          className="flex-1 px-4 py-2.5 rounded-xl border border-red-200 dark:border-red-500/30 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-all text-sm font-bold disabled:opacity-50"
                        >
                          Reject Request
                        </button>
                        <button
                          disabled={loading}
                          onClick={() => handleAction('approve')}
                          className="flex-1 px-4 py-2.5 rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition-all text-sm font-bold shadow-lg shadow-blue-500/20 disabled:opacity-50"
                        >
                          {loading ? "Processing..." : "Approve Leave"}
                        </button>
                      </div>
                    </div>
                  </div>

                  {/* Right Column (Widgets) */}
                  <div className="col-span-12 lg:col-span-5 space-y-4">
                    <div className="p-8 border-2 border-dashed border-gray-100 dark:border-white/5 rounded-2xl flex flex-col items-center justify-center text-slate-400">
                      <Calendar className="mb-2 opacity-20" size={32} />
                      <span className="text-xs font-medium">Calendar Widget</span>
                    </div>
                    <div className="p-8 border-2 border-dashed border-gray-100 dark:border-white/5 rounded-2xl flex flex-col items-center justify-center text-slate-400">
                      <Users className="mb-2 opacity-20" size={32} />
                      <span className="text-xs font-medium">Team Availability</span>
                    </div>
                  </div>
                </div>
              )}

              {activeTab === 'quota' && (
                <div className="rounded-xl border border-gray-100 dark:border-white/10 overflow-hidden">
                  <table className="w-full text-sm text-center">
                    <thead className="bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 font-bold">
                      <tr>
                        <th className="px-4 py-3 text-left">Type</th>
                        <th className="px-4 py-3">Total</th>
                        <th className="px-4 py-3">Used</th>
                        <th className="px-4 py-3">Balance</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100 dark:divide-white/5 text-slate-600 dark:text-slate-300">
                      {leaveStats.map((d, index) => (
                        <tr key={index} className="hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                          <td className="px-4 py-3 text-left font-medium"> {d.leave_group}</td>
                          <td className="px-4 py-3">{d.total}</td>
                          <td className="px-4 py-3 text-red-500">{d.used}</td>
                          <td className="px-4 py-3 text-green-500 font-bold">{d.available}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )}
            </div>

            {/* Footer */}
            <div className="px-6 py-4 border-t border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-slate-900/30 flex justify-end">
              <button
                onClick={toggleModal}
                className="px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 text-slate-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 transition-all text-sm font-medium"
              >
                Close View
              </button>
            </div>
          </div>
        </div>
      )}
    </>
  );
}