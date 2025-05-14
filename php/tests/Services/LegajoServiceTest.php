<?php

namespace Digiventures\SDK\Tests\Services;

use Digiventures\SDK\Services\LegajoService;
use Digiventures\SDK\Http\HttpClient;
use Digiventures\SDK\Auth\AuthManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class LegajoServiceTest extends TestCase
{
    /**
     * @var HttpClient|MockObject
     */
    private $mockClient;

    /**
     * @var AuthManager|MockObject
     */
    private $mockAuthManager;

    /**
     * @var LegajoService
     */
    private $legajoService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mocks
        $this->mockClient = $this->createMock(HttpClient::class);
        $this->mockAuthManager = $this->createMock(AuthManager::class);

        // Set API version
        $this->mockAuthManager
            ->method('getApiVersion')
            ->willReturn('1.0');

        // Create service
        $this->legajoService = new LegajoService($this->mockClient, $this->mockAuthManager);
    }

    public function testCreate()
    {
        // Example legajo data
        $data = [
            'firstname' => 'Juan',
            'lastname' => 'Pérez',
            'email' => 'juan.perez@example.com',
            'idNumber' => '12345678'
        ];

        // Expected response
        $expectedResponse = (object) [
            '_id' => 'test-id',
            'name' => 'Juan Pérez',
            'email' => 'juan.perez@example.com',
            'idNumber' => '12345678',
            'referenceCode' => 'REF123',
            'linkLandingNext' => 'https://example.com/landing',
            'linkRecover' => 'https://example.com/recover',
            'createdAt' => '2023-01-01T00:00:00.000Z',
            'updatedAt' => '2023-01-01T00:00:00.000Z'
        ];

        // Configure mock
        $this->mockClient
            ->expects($this->once())
            ->method('post')
            ->with('/1.0/legajo', $data, [])
            ->willReturn($expectedResponse);

        // Call method
        $result = $this->legajoService->create($data);

        // Verify result
        $this->assertEquals($expectedResponse, $result);
    }

    public function testCreateWithStrategy()
    {
        // Example legajo data
        $data = [
            'firstname' => 'Juan',
            'lastname' => 'Pérez',
            'email' => 'juan.perez@example.com',
            'idNumber' => '12345678'
        ];

        // Strategy
        $strategy = 'COMPLETE';

        // Expected options
        $expectedOptions = [
            'headers' => [
                'strategy' => 'COMPLETE'
            ]
        ];

        // Expected response
        $expectedResponse = (object) [
            '_id' => 'test-id',
            'name' => 'Juan Pérez',
            'email' => 'juan.perez@example.com',
            'idNumber' => '12345678',
            'referenceCode' => 'REF123',
            'linkLandingNext' => 'https://example.com/landing',
            'linkRecover' => 'https://example.com/recover',
            'createdAt' => '2023-01-01T00:00:00.000Z',
            'updatedAt' => '2023-01-01T00:00:00.000Z'
        ];

        // Configure mock
        $this->mockClient
            ->expects($this->once())
            ->method('post')
            ->with('/1.0/legajo', $data, $expectedOptions)
            ->willReturn($expectedResponse);

        // Call method
        $result = $this->legajoService->create($data, $strategy);

        // Verify result
        $this->assertEquals($expectedResponse, $result);
    }

    public function testGet()
    {
        // Legajo ID
        $legajoId = 'test-id';

        // Expected response
        $expectedResponse = (object) [
            '_id' => 'test-id',
            'name' => 'Juan Pérez',
            'email' => 'juan.perez@example.com',
            'idNumber' => '12345678',
            'referenceCode' => 'REF123',
            'linkLandingNext' => 'https://example.com/landing',
            'linkRecover' => 'https://example.com/recover',
            'createdAt' => '2023-01-01T00:00:00.000Z',
            'updatedAt' => '2023-01-01T00:00:00.000Z'
        ];

        // Configure mock
        $this->mockClient
            ->expects($this->once())
            ->method('get')
            ->with('/1.0/legajo/test-id')
            ->willReturn($expectedResponse);

        // Call method
        $result = $this->legajoService->get($legajoId);

        // Verify result
        $this->assertEquals($expectedResponse, $result);
    }

    public function testUpdate()
    {
        // Legajo ID
        $legajoId = 'test-id';

        // Update data
        $data = [
            'vouchers' => [
                'type' => 'test',
                'value' => 123
            ]
        ];

        // Expected response
        $expectedResponse = (object) [
            '_id' => 'test-id',
            'name' => 'Juan Pérez',
            'email' => 'juan.perez@example.com',
            'idNumber' => '12345678',
            'referenceCode' => 'REF123',
            'linkLandingNext' => 'https://example.com/landing',
            'linkRecover' => 'https://example.com/recover',
            'createdAt' => '2023-01-01T00:00:00.000Z',
            'updatedAt' => '2023-01-02T00:00:00.000Z'
        ];

        // Configure mock
        $this->mockClient
            ->expects($this->once())
            ->method('put')
            ->with('/1.0/legajo/test-id', $data)
            ->willReturn($expectedResponse);

        // Call method
        $result = $this->legajoService->update($legajoId, $data);

        // Verify result
        $this->assertEquals($expectedResponse, $result);
    }

    public function testApiVersioning()
    {
        // Configure a different API version
        $this->mockAuthManager = $this->createMock(AuthManager::class);
        $this->mockAuthManager
            ->method('getApiVersion')
            ->willReturn('2.0');

        // Create a new service with the updated auth manager
        $legajoService = new LegajoService($this->mockClient, $this->mockAuthManager);

        // Legajo ID
        $legajoId = 'test-id';

        // Expected response
        $expectedResponse = (object) [
            '_id' => 'test-id',
            'name' => 'Juan Pérez',
            'email' => 'juan.perez@example.com',
            'idNumber' => '12345678'
        ];

        // Configure mock for API version 2.0
        $this->mockClient
            ->expects($this->once())
            ->method('get')
            ->with('/2.0/legajo/test-id')
            ->willReturn($expectedResponse);

        // Call method
        $result = $legajoService->get($legajoId);

        // Verify result
        $this->assertEquals($expectedResponse, $result);
    }

    public function testApiVersioningDefault()
    {
        // Configure no API version
        $this->mockAuthManager = $this->createMock(AuthManager::class);
        $this->mockAuthManager
            ->method('getApiVersion')
            ->willReturn(null);

        // Create a new service with the updated auth manager
        $legajoService = new LegajoService($this->mockClient, $this->mockAuthManager);

        // Legajo ID
        $legajoId = 'test-id';

        // Expected response
        $expectedResponse = (object) [
            '_id' => 'test-id',
            'name' => 'Juan Pérez',
            'email' => 'juan.perez@example.com',
            'idNumber' => '12345678'
        ];

        // Configure mock for default API version 1.0
        $this->mockClient
            ->expects($this->once())
            ->method('get')
            ->with('/1.0/legajo/test-id')
            ->willReturn($expectedResponse);

        // Call method
        $result = $legajoService->get($legajoId);

        // Verify result
        $this->assertEquals($expectedResponse, $result);
    }
} 