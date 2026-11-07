import type { ApiResponse } from '~/types';

/**
 * Composable HTTP client untuk komunikasi ke Backend Laravel REST API.
 */
export const useApi = () => {
  const config = useRuntimeConfig();

  /**
   * Mengirim request HTTP menggunakan $fetch dengan header terstandarisasi.
   */
  const request = async <T>(endpoint: string, options: Parameters<typeof $fetch>[1] = {}): Promise<ApiResponse<T>> => {
    const headers: Record<string, string> = {
      Accept: 'application/json',
      ...(options.headers as Record<string, string> || {}),
    };

    try {
      const url = `${config.public.apiBaseUrl}${endpoint.startsWith('/') ? endpoint : `/${endpoint}`}`;
      return await $fetch<ApiResponse<T>>(url, {
        ...options,
        headers,
      });
    } catch (err: any) {
      throw err.response?._data || err;
    }
  };

  return {
    get: <T>(url: string, params?: Record<string, any>) => request<T>(url, { method: 'GET', params }),
    post: <T>(url: string, body?: any) => request<T>(url, { method: 'POST', body }),
    put: <T>(url: string, body?: any) => request<T>(url, { method: 'PUT', body }),
    delete: <T>(url: string) => request<T>(url, { method: 'DELETE' }),
  };
};
