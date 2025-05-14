import { DigiventuresSDK } from '../src/sdk';
import { AuthManager } from '../src/auth';
import { HttpClient } from '../src/client';
import { LegajoService } from '../src/legajo';
import { FileResponse } from '../src/types';
import { AxiosResponse } from 'axios';

// Mock dependencies
jest.mock('../src/auth');
jest.mock('../src/client');
jest.mock('../src/legajo');

describe('DigiventuresSDK', () => {
  let sdk: DigiventuresSDK;
  let mockAuthManager: jest.Mocked<AuthManager>;
  let mockClient: jest.Mocked<HttpClient>;
  let mockLegajoService: jest.Mocked<LegajoService>;

  const mockConfig = {
    applicationId: 'test-app-id',
    secret: 'test-secret',
    environment: 'qa' as const,
  };

  // Setup mocks for constructor
  beforeEach(() => {
    // Clear previous mocks
    jest.clearAllMocks();

    // Setup constructor mocks
    mockAuthManager = new AuthManager({} as any) as jest.Mocked<AuthManager>;
    mockClient = new HttpClient({} as any, {} as any) as jest.Mocked<HttpClient>;
    mockLegajoService = new LegajoService({} as any, {} as any) as jest.Mocked<LegajoService>;

    // Mock constructors
    (AuthManager as jest.MockedClass<typeof AuthManager>).mockImplementation(() => mockAuthManager);
    (HttpClient as jest.MockedClass<typeof HttpClient>).mockImplementation(() => mockClient);
    (LegajoService as jest.MockedClass<typeof LegajoService>).mockImplementation(() => mockLegajoService);

    // Create SDK instance
    sdk = new DigiventuresSDK(mockConfig);
  });

  describe('constructor', () => {
    it('should initialize SDK with configuration', () => {
      expect(AuthManager).toHaveBeenCalledWith(mockConfig);
      expect(HttpClient).toHaveBeenCalledWith(mockConfig, mockAuthManager);
      expect(LegajoService).toHaveBeenCalledWith(mockClient, mockAuthManager);
    });
  });

  describe('getFile', () => {
    it('should get a file by URL', async () => {
      // Mock response
      const mockFile: FileResponse = {
        file: 'base64-encoded-content'
      };
      const mockResponse: Partial<AxiosResponse> = {
        data: mockFile
      };
      mockClient.get.mockResolvedValue(mockResponse as AxiosResponse);

      // Call the method with a URL
      const result = await sdk.getFile('https://example.com/file.pdf');

      // Verify results
      expect(result).toEqual(mockFile);
      expect(mockClient.get).toHaveBeenCalledWith('/file.pdf');
    });

    it('should handle path-only file URLs', async () => {
      // Mock response
      const mockFile: FileResponse = {
        file: 'base64-encoded-content'
      };
      const mockResponse: Partial<AxiosResponse> = {
        data: mockFile
      };
      mockClient.get.mockResolvedValue(mockResponse as AxiosResponse);

      // Call the method with just a path
      const result = await sdk.getFile('/path/to/file.pdf');

      // Verify results
      expect(result).toEqual(mockFile);
      expect(mockClient.get).toHaveBeenCalledWith('/path/to/file.pdf');
    });
  });

  describe('integration with services', () => {
    it('should expose legajo service', () => {
      expect(sdk.legajo).toBe(mockLegajoService);
    });
  });
}); 