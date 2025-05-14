import { AuthManager } from '../src/auth';
import nock from 'nock';

describe('AuthManager', () => {
  const mockConfig = {
    applicationId: 'test-app-id',
    secret: 'test-secret',
    environment: 'qa' as const,
    timeout: 5000,
  };

  const mockAuthResponse = {
    token: 'test-token',
    expiration: new Date(Date.now() + 3600000).toISOString(), // 1 hour from now
    api: {
      version: '1.0'
    }
  };

  beforeEach(() => {
    // Clear all mocks before each test
    nock.cleanAll();
  });

  describe('getToken', () => {
    it('should fetch a new token when no token exists', async () => {
      // Mock the authentication API
      nock('https://api.qa.digiventures.com.ar')
        .get(`/authorization/${mockConfig.applicationId}/${mockConfig.secret}`)
        .reply(200, mockAuthResponse);

      const authManager = new AuthManager(mockConfig);
      const token = await authManager.getToken();

      expect(token).toBe(mockAuthResponse.token);
    });

    it('should use cached token if it exists and is not expired', async () => {
      // Mock the authentication API (should only be called once)
      const authScope = nock('https://api.qa.digiventures.com.ar')
        .get(`/authorization/${mockConfig.applicationId}/${mockConfig.secret}`)
        .reply(200, mockAuthResponse);

      const authManager = new AuthManager(mockConfig);
      
      // First call - should fetch from API
      const token1 = await authManager.getToken();
      expect(token1).toBe(mockAuthResponse.token);
      
      // Second call - should use cached token
      const token2 = await authManager.getToken();
      expect(token2).toBe(mockAuthResponse.token);
      
      // Verify the API was only called once
      authScope.done();
    });

    it('should fetch a new token if the current one is expired', async () => {
      // Create an expired token response
      const expiredTokenResponse = {
        ...mockAuthResponse,
        expiration: new Date(Date.now() - 1000).toISOString() // 1 second ago
      };
      
      // Create a new token response
      const newTokenResponse = {
        token: 'new-test-token',
        expiration: new Date(Date.now() + 3600000).toISOString(),
        api: {
          version: '1.0'
        }
      };

      // Mock the authentication API calls
      nock('https://api.qa.digiventures.com.ar')
        .get(`/authorization/${mockConfig.applicationId}/${mockConfig.secret}`)
        .reply(200, expiredTokenResponse);
      
      nock('https://api.qa.digiventures.com.ar')
        .get(`/authorization/${mockConfig.applicationId}/${mockConfig.secret}`)
        .reply(200, newTokenResponse);

      const authManager = new AuthManager(mockConfig);
      
      // First call - gets expired token
      await authManager.getToken();
      
      // Second call - should fetch new token because the first one is expired
      const token = await authManager.getToken();
      expect(token).toBe(newTokenResponse.token);
    });
  });

  describe('getApiVersion', () => {
    it('should return the API version from auth response', async () => {
      // Mock the authentication API
      nock('https://api.qa.digiventures.com.ar')
        .get(`/authorization/${mockConfig.applicationId}/${mockConfig.secret}`)
        .reply(200, mockAuthResponse);

      const authManager = new AuthManager(mockConfig);
      await authManager.getToken(); // Get token first
      
      const apiVersion = authManager.getApiVersion();
      expect(apiVersion).toBe('1.0');
    });

    it('should return null if no auth response received yet', () => {
      const authManager = new AuthManager(mockConfig);
      const apiVersion = authManager.getApiVersion();
      expect(apiVersion).toBeNull();
    });
  });

  describe('error handling', () => {
    it('should throw an error if authentication fails', async () => {
      // Mock failed authentication
      nock('https://api.qa.digiventures.com.ar')
        .get(`/authorization/${mockConfig.applicationId}/${mockConfig.secret}`)
        .replyWithError('Network error');

      const authManager = new AuthManager(mockConfig);
      
      await expect(authManager.getToken()).rejects.toThrow(/Authentication failed/);
    });
  });
}); 