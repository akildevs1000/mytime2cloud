import { api, buildQueryParams } from "@/lib/api-client";

export const getAttendanceCount = async (params = {}) => {
    const queryParams = await buildQueryParams(params);
    const { data } = await api.get("/dashbaord_attendance_count", { params: queryParams });
    return data;
};

export const dashboardGetCountslast7DaysChart = async (params = {}) => {
    const queryParams = await buildQueryParams(params);
    const { data } = await api.get("/dashboard_counts_last_7_days_chart", { params: queryParams });
    return data;
};

export const getCompanyStats = async (params = {}) => {
    const queryParams = await buildQueryParams(params);
    const { data } = await api.get("/company_stats", { params: queryParams });
    return data;
};
