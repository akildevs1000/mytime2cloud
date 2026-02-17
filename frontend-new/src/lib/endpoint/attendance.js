import { api, buildQueryParams } from "@/lib/api-client";

export const getAttendanceTabs = async () => {
    const params = await buildQueryParams();
    const { data } = await api.get("/get_attendance_tabs", { params });
    return data;
};

export const startReportGeneration = async (params = {}) => {
    const { data } = await api.get("/start-report-generation", { params: await buildQueryParams(params) });
    return data;
};

export const checkProgress = async () => {
    const { data } = await api.get("/progress", { params: await buildQueryParams() });
    return data;
};