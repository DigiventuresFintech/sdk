import { HttpClient } from './client';
import { Legajo, LegajoCreateData, LegajoUpdateData } from './types';
import { AuthManager } from './auth';
export declare class LegajoService {
    private client;
    private authManager;
    constructor(client: HttpClient, authManager: AuthManager);
    /**
     * Gets the API version path prefix
     * @returns The API version path (e.g. "/1.0")
     */
    private getApiVersionPath;
    /**
     * Creates a new legajo
     * @param data The data for the new legajo
     * @param strategy Creation strategy (IGNORE, COMPLETE, OVERRIDE)
     * @returns The created legajo
     */
    create(data: LegajoCreateData, strategy?: 'IGNORE' | 'COMPLETE' | 'OVERRIDE'): Promise<Legajo>;
    /**
     * Gets a legajo by ID
     * @param legajoId The ID of the legajo
     * @returns The legajo
     */
    get(legajoId: string): Promise<Legajo>;
    /**
     * Updates a legajo
     * @param legajoId The ID of the legajo to update
     * @param data The data to update
     * @returns The updated legajo
     */
    update(legajoId: string, data: LegajoUpdateData): Promise<Legajo>;
}
