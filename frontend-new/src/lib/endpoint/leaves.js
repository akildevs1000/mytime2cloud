import { api, API_BASE, buildQueryParams } from "@/lib/api-client";

export const getLeavesRequest = async (params = {}) => {
    let baseURL = API_BASE;
    baseURL = `https://backend.mytime2cloud.com/api`;
    const { data } = await api.get(`${baseURL}/employee_leaves`, { params: await buildQueryParams(params) });
    return data;
};

export const getLeavesGroups = async (leaveGroupId, params = {}) => {
    let baseURL = API_BASE;
    baseURL = `https://backend.mytime2cloud.com/api`;
    const { data } = await api.get(`${baseURL}/leave_groups/${leaveGroupId}`, { params: await buildQueryParams(params) });
    return data;
};

// src/lib/endpoint/leaves.js additions
export const getUpcomingLeaves = async (params = {}) => {
    let baseURL = `https://backend.mytime2cloud.com/api`;
    const { data } = await api.get(`${baseURL}/employee_leaves_for_next_thirty_days_month`, { params: await buildQueryParams(params) });
    return data;
};