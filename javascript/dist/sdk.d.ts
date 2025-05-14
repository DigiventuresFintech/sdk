import { LegajoService } from './legajo';
import { DigiventuresConfig, FileResponse } from './types';
export declare class DigiventuresSDK {
    private authManager;
    private client;
    legajo: LegajoService;
    constructor(config: DigiventuresConfig);
    /**
     * Gets a file from a URL
     * @param fileUrl The URL of the file
     * @returns The file content as base64
     */
    getFile(fileUrl: string): Promise<FileResponse>;
}
