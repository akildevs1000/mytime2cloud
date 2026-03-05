// menuData.js
import {
  Home,
  Users,
  Building,
  Clock,
  CalendarDays,
  FileText,
  History,
  Lock,
  Briefcase,
  Megaphone,
  ActivitySquare,
  DollarSign,
  Upload,
  Layers,
  Workflow,
  Shield,
  Group,
  Calendar,
  DoorClosedIcon,
  LogInIcon,
  Settings,
  Clipboard,
  File,
  Map,
  LocateFixed,
} from "lucide-react";

// 1️⃣ Reusable menu groups
const attendanceMenu = [
  { href: "/shift", icon: Clock, label: "Shift" },
  { href: "/schedule", icon: CalendarDays, label: "Schedule" },
  { href: "/attendance", icon: FileText, label: "Reports" },
  { href: "/logs", icon: History, label: "Device Logs" },
  { href: "/attendance/change_request", icon: File, label: "Change Request" },
  // { href: "/access_control_logs", icon: Lock, label: "Access Control Logs" },
];

const companyMenu = [
  { href: "/setup", icon: Settings, label: "Setup" },
  { href: "/geo-fencing", icon: Map, label: "Geo Fencing" },
  { href: "/live-tracker", icon: LocateFixed, label: "Live Tracker" },
  { href: "/company", icon: Building, label: "Company" },
  { href: "/branch", icon: Briefcase, label: "Branch" },
  { href: "/department-tabs", icon: Layers, label: "Department" },
  { href: "/login/manager-login", icon: LogInIcon, label: "Login" },
  { href: "/device", icon: DoorClosedIcon, label: "Device" },
  { href: "/automation", icon: Workflow, label: "Automation" },
  { href: "/roles", icon: Shield, label: "Roles" },
  { href: "/holiday", icon: CalendarDays, label: "Holidays" },
  { href: "/announcements", icon: Megaphone, label: "Announcements" },
  { href: "/activity", icon: ActivitySquare, label: "Activity" },
  { href: "/payslips", icon: DollarSign, label: "Payroll" },
];

const employeesMenu = [
  { href: "/employees", icon: Users, label: "Employee List" },
  { href: "/employees/employee_photo_upload", icon: Upload, label: "Employee Upload" },
  { href: "/leaves", icon: FileText, label: "Leaves Requests" },
  { href: "/document-expiry", icon: FileText, label: "Document Expiry" },
];

const visitorMenu = [
  { href: "/visitor", icon: Users, label: "Visitor" },
];

const dashboardMenu = [
  { href: "/", icon: Home, label: "Dashboard" },
  { href: "/employees", icon: Users, label: "Employees" },
  { href: "/shift", icon: FileText, label: "Attendance" },
  { href: "/attendance", icon: Calendar, label: "Reports" },
];

export const leftNavLinks = {
  "/": dashboardMenu,


  "/visitor": visitorMenu,
  "/employees": employeesMenu,
  "/employee_photo_upload": employeesMenu,
  "/leaves": employeesMenu,
  "/document-expiry": employeesMenu,

  "/shift": attendanceMenu,
  "/schedule": attendanceMenu,
  "/attendance": attendanceMenu,
  "/reports": attendanceMenu,
  "/logs": attendanceMenu,
  // "/access_control_logs": attendanceMenu,

  "/setup": companyMenu,
  "/company": companyMenu,
  "/branch": companyMenu,
  "/login/manager-login": companyMenu,
  "/department-tabs": companyMenu,
  "/device": companyMenu,
  "/payslips": companyMenu,
  "/geo-fencing": companyMenu,
  "/live-tracker": companyMenu,
};
