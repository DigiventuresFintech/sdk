import { HttpClient } from './client';
import { Legajo, LegajoCreateData, LegajoUpdateData } from './types';
import { AuthManager } from './auth';

export class LegajoService {
  private client: HttpClient;
  private authManager: AuthManager;

  constructor(client: HttpClient, authManager: AuthManager) {
    this.client = client;
    this.authManager = authManager;
  }

  /**
   * Gets the API version path prefix
   * @returns The API version path (e.g. "/1.0")
   */
  private getApiVersionPath(): string {
    const version = this.authManager.getApiVersion() || '1.0';
    return `/${version}`;
  }

  /**
   * Creates a new legajo
   * @param data The data for the new legajo
   * @param strategy Creation strategy (IGNORE, COMPLETE, OVERRIDE)
   * @returns The created legajo
   */
  async create(data: LegajoCreateData, strategy?: 'IGNORE' | 'COMPLETE' | 'OVERRIDE'): Promise<Legajo> {
    const headers: Record<string, string> = {};
    if (strategy) {
      headers['strategy'] = strategy;
    }

    const response = await this.client.post<Legajo>(`${this.getApiVersionPath()}/legajo`, data, {
      headers
    });
    
    return response.data;
  }

  /**
   * Gets a legajo by ID
   * @param legajoId The ID of the legajo
   * @returns The legajo
   */
  async get(legajoId: string): Promise<Legajo> {
    const response = await this.client.get<Legajo>(`${this.getApiVersionPath()}/legajo/${legajoId}`);
    return response.data;
  }

  /**
   * Updates a legajo
   * @param legajoId The ID of the legajo to update
   * @param data The data to update
   * @returns The updated legajo
   */
  async update(legajoId: string, data: LegajoUpdateData): Promise<Legajo> {
    const response = await this.client.put<Legajo>(`${this.getApiVersionPath()}/legajo/${legajoId}`, data);
    return response.data;
  }
} 