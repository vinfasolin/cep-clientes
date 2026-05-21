const fallbackApiBaseUrl = "http://localhost:8081/api";

export const API_BASE_URL = (import.meta.env.VITE_API_BASE_URL || fallbackApiBaseUrl).replace(/\/$/, "");
