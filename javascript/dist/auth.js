"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.AuthManager = void 0;
const axios_1 = __importDefault(require("axios"));
class AuthManager {
    constructor(config) {
        this.token = null;
        this.expirationTime = null;
        this.apiVersion = null;
        this.authRetry = false;
        this.config = config;
        this.baseUrl = this.getBaseUrl(config.environment);
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
    async getToken() {
        // If we have a token and it's not expired, return it
        if (this.token && this.expirationTime && new Date() < this.expirationTime) {
            return this.token;
        }
        // Otherwise, get a new token
        return this.fetchNewToken();
    }
    async fetchNewToken() {
        var _a;
        try {
            const { applicationId, secret } = this.config;
            const url = `${this.baseUrl}/authorization/${applicationId}/${secret}`;
            const response = await axios_1.default.get(url, {
                timeout: this.config.timeout || 10000
            });
            this.token = response.data.token;
            this.expirationTime = new Date(response.data.expiration);
            this.apiVersion = ((_a = response.data.api) === null || _a === void 0 ? void 0 : _a.version) || null;
            this.authRetry = false;
            if (this.token === null) {
                throw new Error('Authentication response missing token');
            }
            return this.token;
        }
        catch (error) {
            throw new Error(`Authentication failed: ${error instanceof Error ? error.message : String(error)}`);
        }
    }
    getApiVersion() {
        return this.apiVersion;
    }
    // Reset auth retry flag when a request succeeds
    resetAuthRetry() {
        this.authRetry = false;
    }
    // Check if we've already tried to refresh the token
    hasRetried() {
        return this.authRetry;
    }
    // Mark that we've tried to refresh the token
    markRetry() {
        this.authRetry = true;
    }
}
exports.AuthManager = AuthManager;
