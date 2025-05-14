import { AxiosRequestConfig, AxiosResponse } from 'axios';
import { AuthManager } from './auth';
import { DigiventuresConfig } from './types';
export declare class HttpClient {
    private client;
    private authManager;
    private baseUrl;
    constructor(config: DigiventuresConfig, authManager: AuthManager);
    private getBaseUrl;
    get<T = any>(url: string, config?: AxiosRequestConfig): Promise<AxiosResponse<T>>;
    post<T = any>(url: string, data?: any, config?: AxiosRequestConfig): Promise<AxiosResponse<T>>;
    put<T = any>(url: string, data?: any, config?: AxiosRequestConfig): Promise<AxiosResponse<T>>;
}
