import { LegajoService } from '../src/legajo';
import { HttpClient } from '../src/client';
import { AuthManager } from '../src/auth';
import nock from 'nock';
import { AxiosResponse } from 'axios';

// Mock dependencies
jest.mock('../src/client');
jest.mock('../src/auth');

describe('LegajoService', () => {
  let legajoService: LegajoService;
  let mockClient: jest.Mocked<HttpClient>;
  let mockAuthManager: jest.Mocked<AuthManager>;

  const mockLegajo = {
    _id: 'test-id',
    name: 'John Doe',
    email: 'john.doe@example.com',
    idNumber: '12345678',
    referenceCode: 'REF123',
    linkLandingNext: 'https://example.com/landing',
    linkRecover: 'https://example.com/recover',
    createdAt: '2023-01-01T00:00:00.000Z',
    updatedAt: '2023-01-01T00:00:00.000Z'
  };

  beforeEach(() => {
    // Clear all mocks
    jest.clearAllMocks();

    // Setup mocks
    mockClient = new HttpClient({} as any, {} as any) as jest.Mocked<HttpClient>;
    mockAuthManager = new AuthManager({} as any) as jest.Mocked<AuthManager>;

    // Configure auth manager to return version
    mockAuthManager.getApiVersion.mockReturnValue('1.0');

    // Setup the legajo service with mocked dependencies
    legajoService = new LegajoService(mockClient, mockAuthManager);
  });

  describe('create', () => {
    it('should create a legajo successfully', async () => {
      // Mock response
      const mockResponse: Partial<AxiosResponse> = {
        data: mockLegajo
      };
      mockClient.post.mockResolvedValue(mockResponse as AxiosResponse);

      // Test data
      const legajoData = {
        firstname: 'John',
        lastname: 'Doe',
        email: 'john.doe@example.com',
        idNumber: '12345678'
      };

      // Call the method
      const result = await legajoService.create(legajoData);

      // Verify results
      expect(result).toEqual(mockLegajo);
      expect(mockClient.post).toHaveBeenCalledWith('/1.0/legajo', legajoData, { headers: {} });
    });

    it('should include strategy header when specified', async () => {
      // Mock response
      const mockResponse: Partial<AxiosResponse> = {
        data: mockLegajo
      };
      mockClient.post.mockResolvedValue(mockResponse as AxiosResponse);

      // Test data
      const legajoData = {
        firstname: 'John',
        lastname: 'Doe',
        email: 'john.doe@example.com',
        idNumber: '12345678'
      };
      const strategy = 'COMPLETE';

      // Call the method
      const result = await legajoService.create(legajoData, strategy);

      // Verify results
      expect(result).toEqual(mockLegajo);
      expect(mockClient.post).toHaveBeenCalledWith('/1.0/legajo', legajoData, { 
        headers: { strategy: 'COMPLETE' } 
      });
    });
  });

  describe('get', () => {
    it('should get a legajo by ID', async () => {
      // Mock response
      const mockResponse: Partial<AxiosResponse> = {
        data: mockLegajo
      };
      mockClient.get.mockResolvedValue(mockResponse as AxiosResponse);

      // Call the method
      const result = await legajoService.get('test-id');

      // Verify results
      expect(result).toEqual(mockLegajo);
      expect(mockClient.get).toHaveBeenCalledWith('/1.0/legajo/test-id');
    });
  });

  describe('update', () => {
    it('should update a legajo', async () => {
      // Mock response
      const mockResponse: Partial<AxiosResponse> = {
        data: {
          ...mockLegajo,
          updatedAt: '2023-01-02T00:00:00.000Z'
        }
      };
      mockClient.put.mockResolvedValue(mockResponse as AxiosResponse);

      // Test data
      const updateData = {
        vouchers: {
          type: 'test',
          value: 123
        }
      };

      // Call the method
      const result = await legajoService.update('test-id', updateData);

      // Verify results
      expect(result).toEqual(mockResponse.data);
      expect(mockClient.put).toHaveBeenCalledWith('/1.0/legajo/test-id', updateData);
    });
  });

  describe('API versioning', () => {
    it('should use API version from auth manager', async () => {
      // Setup a different API version
      mockAuthManager.getApiVersion.mockReturnValue('2.0');

      // Mock response
      const mockResponse: Partial<AxiosResponse> = {
        data: mockLegajo
      };
      mockClient.get.mockResolvedValue(mockResponse as AxiosResponse);

      // Call the method
      await legajoService.get('test-id');

      // Verify the correct API version was used
      expect(mockClient.get).toHaveBeenCalledWith('/2.0/legajo/test-id');
    });

    it('should default to 1.0 if no API version is available', async () => {
      // Setup no API version
      mockAuthManager.getApiVersion.mockReturnValue(null);

      // Mock response
      const mockResponse: Partial<AxiosResponse> = {
        data: mockLegajo
      };
      mockClient.get.mockResolvedValue(mockResponse as AxiosResponse);

      // Call the method
      await legajoService.get('test-id');

      // Verify it defaulted to 1.0
      expect(mockClient.get).toHaveBeenCalledWith('/1.0/legajo/test-id');
    });
  });
}); 