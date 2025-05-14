import { DigiventuresConfig } from './types';
export declare class AuthManager {
    private config;
    private token;
    private expirationTime;
    private apiVersion;
    private authRetry;
    private baseUrl;
    constructor(config: DigiventuresConfig);
    private getBaseUrl;
    getToken(): Promise<string>;
    fetchNewToken(): Promise<string>;
    getApiVersion(): string | null;
    resetAuthRetry(): void;
    hasRetried(): boolean;
    markRetry(): void;
}
