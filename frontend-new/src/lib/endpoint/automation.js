import { api, buildQueryParams } from "@/lib/api-client";

export const getReportNotifications = async (params = {}) => {
    const { data } = await api.get(`/report_notification`, { params: await buildQueryParams(params) });
    return data;
};

export const storeReportNotification = async (payload = {}) => {
    const { data } = await api.post(`/report_notification`, payload);
    return data;
};