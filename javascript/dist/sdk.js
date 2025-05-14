"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.DigiventuresSDK = void 0;
const auth_1 = require("./auth");
const client_1 = require("./client");
const legajo_1 = require("./legajo");
class DigiventuresSDK {
    constructor(config) {
        this.authManager = new auth_1.AuthManager(config);
        this.client = new client_1.HttpClient(config, this.authManager);
        this.legajo = new legajo_1.LegajoService(this.client, this.authManager);
    }
    /**
     * Gets a file from a URL
     * @param fileUrl The URL of the file
     * @returns The file content as base64
     */
    async getFile(fileUrl) {
        // Extract just the path portion if a full URL is provided
        const url = fileUrl.startsWith('http')
            ? new URL(fileUrl).pathname
            : fileUrl;
        const response = await this.client.get(url);
        return response.data;
    }
}
exports.DigiventuresSDK = DigiventuresSDK;
