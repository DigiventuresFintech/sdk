import { AuthManager } from './auth';
import { HttpClient } from './client';
import { LegajoService } from './legajo';
import { DigiventuresConfig, FileResponse } from './types';

export class DigiventuresSDK {
  private authManager: AuthManager;
  private client: HttpClient;
  public legajo: LegajoService;

  constructor(config: DigiventuresConfig) {
    this.authManager = new AuthManager(config);
    this.client = new HttpClient(config, this.authManager);
    this.legajo = new LegajoService(this.client, this.authManager);
  }

  /**
   * Gets a file from a URL
   * @param fileUrl The URL of the file
   * @returns The file content as base64
   */
  async getFile(fileUrl: string): Promise<FileResponse> {
    // Extract just the path portion if a full URL is provided
    const url = fileUrl.startsWith('http')
      ? new URL(fileUrl).pathname
      : fileUrl;
    
    const response = await this.client.get<FileResponse>(url);
    return response.data;
  }
} 