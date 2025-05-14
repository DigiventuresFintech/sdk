"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.HttpClient = void 0;
const axios_1 = __importDefault(require("axios"));
const axios_retry_1 = __importDefault(require("axios-retry"));
class HttpClient {
    constructor(config, authManager) {
        this.authManager = authManager;
        this.baseUrl = this.getBaseUrl(config.environment);
        // Create Axios instance
        this.client = axios_1.default.create({
            baseURL: this.baseUrl,
            timeout: config.timeout || 10000,
        });
        // Configure retry logic
        (0, axios_retry_1.default)(this.client, {
            retries: config.maxRetries || 3,
            retryDelay: axios_retry_1.default.exponentialDelay,
            retryCondition: (error) => {
                var _a;
                // Only retry on network errors or 5xx errors (except 401 which will be handled separately)
                if (axios_retry_1.default.isNetworkOrIdempotentRequestError(error)) {
                    return true;
                }
                if (((_a = error.response) === null || _a === void 0 ? void 0 : _a.status) && error.response.status >= 500) {
                    return true;
                }
                // Explicitly return false for all other cases
                return false;
            }
        });
        // Add auth token to requests
        this.client.interceptors.request.use(async (config) => {
            var _a;
            // If the URL isn't already handling auth (like the auth endpoint itself)
            if (!((_a = config.url) === null || _a === void 0 ? void 0 : _a.includes('/authorization/'))) {
                const token = await this.authManager.getToken();
                // Add token to query params
                if (!config.params) {
                    config.params = {};
                }
                config.params.authorization = token;
            }
            return config;
        });
        // Handle 401 responses
        this.client.interceptors.response.use((response) => {
            // Reset retry flag on success
            this.authManager.resetAuthRetry();
            return response;
        }, async (error) => {
            var _a, _b;
            // If we get a 401 or 500 and haven't retried yet
            if (((_a = error.response) === null || _a === void 0 ? void 0 : _a.status) === 401 ||
                ((_b = error.response) === null || _b === void 0 ? void 0 : _b.status) === 500) {
                if (!this.authManager.hasRetried()) {
                    this.authManager.markRetry();
                    // Get a fresh token
                    await this.authManager.fetchNewToken();
                    // Retry the original request
                    const config = error.config;
                    config.params = config.params || {};
                    config.params.authorization = await this.authManager.getToken();
                    return this.client(config);
                }
            }
            return Promise.reject(error);
        });
    }
    getBaseUrl(environment) {
        switch (environment) {
            case 'qa':
                return 'https://api.qa.digiventures.com.ar';
            case 'staging':
                return 'https://api.staging.digiventures.la';
            case 'production':
                return 'https://api.production.digiventures.la';
            default:
                throw new Error(`Invalid environment: ${environment}`);
        }
    }
    async get(url, config) {
        return this.client.get(url, config);
    }
    async post(url, data, config) {
        return this.client.post(url, data, config);
    }
    async put(url, data, config) {
        return this.client.put(url, data, config);
    }
}
exports.HttpClient = HttpClient;
