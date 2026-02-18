import { api, buildQueryParams } from "@/lib/api-client";

export const getLeavesRequest = async (params = {}) => {
    const { data } = await api.get("/employee_leaves", { params: await buildQueryParams(params) });
    return data;
};