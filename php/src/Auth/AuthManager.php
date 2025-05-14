<?php

namespace Digiventures\SDK\Auth;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Manejador de autenticación para la API de Digiventures
 */
class AuthManager
{
    use TestingHelpers;
    
    /**
     * @var array
     */
    private $config;

    /**
     * @var string|null
     */
    private $token = null;

    /**
     * @var \DateTime|null
     */
    private $expirationTime = null;

    /**
     * @var string|null
     */
    private $apiVersion = null;

    /**
     * @var bool
     */
    private $authRetry = false;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * Constructor del manejador de autenticación
     *
     * @param array $config Configuración
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->baseUrl = $this->getBaseUrl($config['environment']);
    }

    /**
     * Obtiene la URL base según el entorno
     *
     * @param string $environment Entorno
     * 
     * @return string URL base
     */
    private function getBaseUrl(string $environment): string
    {
        switch ($environment) {
            case 'qa':
                return 'https://api.qa.digiventures.com.ar';
            case 'staging':
                return 'https://api.staging.digiventures.la';
            case 'production':
                return 'https://api.production.digiventures.la';
            default:
                throw new \InvalidArgumentException("Entorno inválido: {$environment}");
        }
    }

    /**
     * Obtiene un token de autenticación, renovándolo si es necesario
     *
     * @return string Token de autenticación
     * 
     * @throws \Exception Si falla la autenticación
     */
    public function getToken(): string
    {
        // Si tenemos un token y no ha expirado, lo devolvemos
        if ($this->token !== null && $this->expirationTime !== null && new \DateTime() < $this->expirationTime) {
            return $this->token;
        }

        // Si no, obtenemos uno nuevo
        return $this->fetchNewToken();
    }

    /**
     * Obtiene un nuevo token de autenticación
     *
     * @return string Token de autenticación
     * 
     * @throws \Exception Si falla la autenticación
     */
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
        } catch (GuzzleException | \Exception $e) {
            throw new \Exception("Error de autenticación: " . $e->getMessage());
        }
    }

    /**
     * Obtiene la versión de la API
     *
     * @return string|null Versión de la API
     */
    public function getApiVersion(): ?string
    {
        return $this->apiVersion;
    }

    /**
     * Reinicia el flag de reintento de autenticación
     */
    public function resetAuthRetry(): void
    {
        $this->authRetry = false;
    }

    /**
     * Verifica si ya se reintentó la autenticación
     *
     * @return bool
     */
    public function hasRetried(): bool
    {
        return $this->authRetry;
    }

    /**
     * Marca que se ha reintentado la autenticación
     */
    public function markRetry(): void
    {
        $this->authRetry = true;
    }
    
    /**
     * Crea un cliente HTTP
     * Método para ser sobreescrito en las pruebas
     * 
     * @param array $config Configuración del cliente
     * 
     * @return Client Cliente HTTP
     */
    protected function createHttpClient(array $config = []): Client
    {
        return new Client($config);
    }
} 