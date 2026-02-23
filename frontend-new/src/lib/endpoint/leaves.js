import { api, API_BASE, buildQueryParams } from "@/lib/api-client";

export const getLeavesRequest = async (params = {}) => {
    let baseURL = API_BASE;
    baseURL = `https://backend.mytime2cloud.com/api`;
    const { data } = await api.get(`${baseURL}/employee_leaves`, { params: await buildQueryParams(params) });
    return data;
};