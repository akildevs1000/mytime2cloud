import ProfilePicture from "@/components/ProfilePicture";
import { Contact, Edit3, Fingerprint, Hash, Monitor, RefreshCw, ScanFace, Smartphone } from "lucide-react";

// 1. Define the base icon mapping
const baseIcons = {
  Card: <Contact size={16} title="Card" />,
  Fing: <Fingerprint size={16} title="Fingerprint" />,
  Face: <ScanFace size={16} title="Face" />,
  Pin: <Hash size={16} title="PIN" />,
  Manual: <Edit3 size={16} title="Manual" />,
  Repeated: <RefreshCw size={16} title="Repeated" />,
  Mobile: <Smartphone size={16} title="Mobile" />,
  Device: <Monitor size={16} title="Monitor" />,
};

// 2. Define how each mode maps to those icons
const iconGroups = {
  Card: [baseIcons.Card],
  Fing: [baseIcons.Fing],
  Face: [baseIcons.Face],
  "Fing + Card": [baseIcons.Fing, baseIcons.Card],
  "Face + Fing": [baseIcons.Face, baseIcons.Fing],
  "Face + Card": [baseIcons.Face, baseIcons.Card],
  "Card + Pin": [baseIcons.Card, baseIcons.Pin],
  "Face + Pin": [baseIcons.Face, baseIcons.Pin],
  "Fing + Pin": [baseIcons.Fing, baseIcons.Pin],
  "Fing + Card + Pin": [baseIcons.Fing, baseIcons.Card, baseIcons.Pin],
  "Face + Card + Pin": [baseIcons.Face, baseIcons.Card, baseIcons.Pin],
  "Face + Fing + Pin": [baseIcons.Face, baseIcons.Fing, baseIcons.Pin],
  "Face + Fing + Card": [baseIcons.Face, baseIcons.Fing, baseIcons.Card],
  Manual: [baseIcons.Manual],
  Repeated: [baseIcons.Repeated],
};

export default [
  {
    key: "employee",
    header: "Personnel",
    render: ({ employee }) => (
      <div className="flex items-center space-x-3">

        <ProfilePicture src={employee.profile_picture} />

        <div>
          <p className="font-medium text-sm text-slate-600 dark:text-slate-300 hidden xl:table-cell">{employee?.first_name}</p>
          <p className="text-sm text-gray-500">
            ID: {employee.employee_id}
          </p>
        </div>
      </div>
    ),
  },

  {
    key: "branch",
    header: "Branch / Department",
    render: ({ employee }) => (
      <p className="font-medium text-sm text-slate-600 dark:text-slate-300 hidden xl:table-cell">{employee?.branch?.branch_name || "N/A"} / {employee?.department?.name || "N/A"}</p>
    ),
  },
  {
    key: "datetime",
    header: "Date Time",
    render: (log) => (
      <p className="font-medium text-sm text-slate-600 dark:text-slate-300 hidden xl:table-cell">{log?.date} {log?.time} </p>
    ),
  },
  {
    key: "log_type",
    header: "Log Type",
    render: (log) => (
      <p className="font-medium text-sm text-slate-600 dark:text-slate-300 hidden xl:table-cell">{log?.log_type}</p>
    ),
  },
  {
    key: "Mode",
    header: "mode",
    render: (e) => {
      // Mode logic
      let modes = [];
      if (e.DeviceID?.includes("Mobile")) {
        modes = [baseIcons.Mobile];
      } else if (iconGroups[e.mode]) {
        modes = iconGroups[e.mode];
      } else {
        modes = [baseIcons.Device];
      }
      return (
        <div className="col-span-1 flex items-center text-slate-600 dark:text-slate-300">
          {modes?.map((icon, idx) => (
            <span key={idx}>{icon}</span>
          ))}
        </div>
      )
    }
  },
  {
    key: "device",
    header: "Device",
    render: (log) => (
      <p className="font-medium text-sm text-slate-600 dark:text-slate-300 hidden xl:table-cell">{log?.device?.name || "—"}</p>
    ),
  },

  {
    key: "location",
    header: "Location",
    render: (log) => (
      <p className="font-medium text-sm text-slate-600 dark:text-slate-300 hidden xl:table-cell">{log?.device?.location || "—"}</p>
    ),
  },
];
