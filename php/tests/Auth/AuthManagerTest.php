<?php

namespace Digiventures\SDK\Tests\Auth;

use Digiventures\SDK\Auth\AuthManager;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;

class AuthManagerTest extends TestCase
{
    protected $config = [
        'applicationId' => 'test-app-id',
        'secret' => 'test-secret',
        'environment' => 'qa',
        'timeout' => 5
    ];

    protected function setUp(): void
    {
        // Make sure we reset any PHPUnit mocking
        parent::setUp();
    }

    public function testFetchNewToken()
    {
        // Create a mock response
        $mockResponse = [
            'token' => 'test-token',
            'expiration' => (new \DateTime('+1 hour'))->format('c'),
            'api' => [
                'version' => '1.0'
            ]
        ];
        
        // Create a mock handler
        $mock = new MockHandler([
            new Response(200, [], json_encode($mockResponse))
        ]);
        
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        
        // Create auth manager with our mocked client
        $authManager = $this->getMockBuilder(AuthManager::class)
            ->setConstructorArgs([$this->config])
            ->onlyMethods(['createHttpClient'])
            ->getMock();
        
        $authManager->method('createHttpClient')
            ->willReturn($client);
        
        // Test fetching a token
        $token = $authManager->fetchNewToken();
        
        // Verify the token was fetched
        $this->assertEquals('test-token', $token);
        
        // Verify API version was stored
        $this->assertEquals('1.0', $authManager->getApiVersion());
    }

    public function testGetTokenUsesCache()
    {
        // Create mock responses
        $mockResponse = [
            'token' => 'test-token',
            'expiration' => (new \DateTime('+1 hour'))->format('c'),
            'api' => [
                'version' => '1.0'
            ]
        ];
        
        // Create a mock handler that will throw an exception on second call
        // to verify we don't make a second request
        $mock = new MockHandler([
            new Response(200, [], json_encode($mockResponse)),
            new RequestException('Should not reach this', new Request('GET', 'test'))
        ]);
        
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        
        // Create auth manager with our mocked client
        $authManager = $this->getMockBuilder(AuthManager::class)
            ->setConstructorArgs([$this->config])
            ->onlyMethods(['createHttpClient'])
            ->getMock();
        
        $authManager->method('createHttpClient')
            ->willReturn($client);
        
        // Get token first time (should fetch)
        $token1 = $authManager->getToken();
        
        // Get token second time (should use cache)
        $token2 = $authManager->getToken();
        
        // Verify tokens match
        $this->assertEquals($token1, $token2);
        $this->assertEquals('test-token', $token1);
    }

    public function testGetTokenRefreshesExpiredToken()
    {
        // Create mock responses
        $expiredResponse = [
            'token' => 'expired-token',
            'expiration' => (new \DateTime('-1 hour'))->format('c'),
            'api' => [
                'version' => '1.0'
            ]
        ];
        
        $newResponse = [
            'token' => 'new-token',
            'expiration' => (new \DateTime('+1 hour'))->format('c'),
            'api' => [
                'version' => '1.0'
            ]
        ];
        
        // Create a mock handler with both responses
        $mock = new MockHandler([
            new Response(200, [], json_encode($expiredResponse)),
            new Response(200, [], json_encode($newResponse))
        ]);
        
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        
        // Create auth manager with our mocked client
        $authManager = $this->getMockBuilder(AuthManager::class)
            ->setConstructorArgs([$this->config])
            ->onlyMethods(['createHttpClient'])
            ->getMock();
        
        $authManager->method('createHttpClient')
            ->willReturn($client);
        
        // Get token first time (should get expired one)
        $token1 = $authManager->getToken();
        $this->assertEquals('expired-token', $token1);
        
        // Get token second time (should refresh)
        $token2 = $authManager->getToken();
        $this->assertEquals('new-token', $token2);
    }

    public function testAuthenticationError()
    {
        // Create a mock handler that throws an exception
        $mock = new MockHandler([
            new RequestException('Network error', new Request('GET', 'test'))
        ]);
        
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        
        // Create auth manager with our mocked client
        $authManager = $this->getMockBuilder(AuthManager::class)
            ->setConstructorArgs([$this->config])
            ->onlyMethods(['createHttpClient'])
            ->getMock();
        
        $authManager->method('createHttpClient')
            ->willReturn($client);
        
        // Expect exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error de autenticación');
        
        // Try to get a token
        $authManager->getToken();
    }

    public function testRetryFlagManagement()
    {
        $authManager = new AuthManager($this->config);
        
        // Initially should not have retried
        $this->assertFalse($authManager->hasRetried());
        
        // Mark as retried
        $authManager->markRetry();
        $this->assertTrue($authManager->hasRetried());
        
        // Reset retry status
        $authManager->resetAuthRetry();
        $this->assertFalse($authManager->hasRetried());
    }
}

// Add a testing helper method to the AuthManager class
// This is needed because we want to test the private methods
namespace Digiventures\SDK\Auth;

trait TestingHelpers
{
    protected function createHttpClient(array $config = [])
    {
        // This method will be mocked in tests
        return new \GuzzleHttp\Client([
            'base_uri' => $this->baseUrl,
            'timeout' => $this->config['timeout']
        ]);
    }
    
    public function fetchNewToken(): string
    {
        try {
            $client = $this->createHttpClient([
                'base_uri' => $this->baseUrl,
                'timeout' => $this->config['timeout']
            ]);

            $response = $client->get("/authorization/{$this->config['applicationId']}/{$this->config['secret']}");
            $data = json_decode($response->getBody()->getContents(), true);

            if (!isset($data['token']) || !isset($data['expiration'])) {
                throw new \Exception('Respuesta de autenticación inválida');
            }

            $this->token = $data['token'];
            $this->expirationTime = new \DateTime($data['expiration']);
            $this->apiVersion = $data['api']['version'] ?? null;
            $this->authRetry = false;

            return $this->token;
        } catch (\GuzzleHttp\Exception\GuzzleException | \Exception $e) {
            throw new \Exception("Error de autenticación: " . $e->getMessage());
        }
    }
} 