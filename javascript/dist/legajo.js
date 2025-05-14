"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.LegajoService = void 0;
class LegajoService {
    constructor(client, authManager) {
        this.client = client;
        this.authManager = authManager;
    }
    /**
     * Gets the API version path prefix
     * @returns The API version path (e.g. "/1.0")
     */
    getApiVersionPath() {
        const version = this.authManager.getApiVersion() || '1.0';
        return `/${version}`;
    }
    /**
     * Creates a new legajo
     * @param data The data for the new legajo
     * @param strategy Creation strategy (IGNORE, COMPLETE, OVERRIDE)
     * @returns The created legajo
     */
    async create(data, strategy) {
        const headers = {};
        if (strategy) {
            headers['strategy'] = strategy;
        }
        const response = await this.client.post(`${this.getApiVersionPath()}/legajo`, data, {
            headers
        });
        return response.data;
    }
    /**
     * Gets a legajo by ID
     * @param legajoId The ID of the legajo
     * @returns The legajo
     */
    async get(legajoId) {
        const response = await this.client.get(`${this.getApiVersionPath()}/legajo/${legajoId}`);
        return response.data;
    }
    /**
     * Updates a legajo
     * @param legajoId The ID of the legajo to update
     * @param data The data to update
     * @returns The updated legajo
     */
    async update(legajoId, data) {
        const response = await this.client.put(`${this.getApiVersionPath()}/legajo/${legajoId}`, data);
        return response.data;
    }
}
exports.LegajoService = LegajoService;
