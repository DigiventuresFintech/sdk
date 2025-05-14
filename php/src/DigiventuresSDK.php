<?php

namespace Digiventures\SDK;

use Digiventures\SDK\Auth\AuthManager;
use Digiventures\SDK\Http\HttpClient;
use Digiventures\SDK\Services\LegajoService;

/**
 * DigiventuresSDK - Cliente principal para interactuar con la API de Digiventures
 */
class DigiventuresSDK
{
    use TestingHelpers;
    
    /**
     * @var AuthManager
     */
    private $authManager;

    /**
     * @var HttpClient
     */
    private $client;

    /**
     * @var LegajoService
     */
    public $legajo;

    /**
     * Constructor del SDK
     *
     * @param array $config Configuración del SDK
     *                      - applicationId: ID de aplicación
     *                      - secret: Secret de aplicación
     *                      - environment: Entorno ('qa', 'staging', 'production')
     *                      - timeout: Timeout en segundos (default: 10)
     *                      - maxRetries: Número máximo de reintentos (default: 3)
     */
    public function __construct(array $config)
    {
        // Validar configuración mínima
        if (empty($config['applicationId']) || empty($config['secret']) || empty($config['environment'])) {
            throw new \InvalidArgumentException('La configuración debe contener applicationId, secret y environment');
        }

        // Configurar valores por defecto
        $config['timeout'] = $config['timeout'] ?? 10;
        $config['maxRetries'] = $config['maxRetries'] ?? 3;

        $this->authManager = new AuthManager($config);
        $this->client = new HttpClient($config, $this->authManager);
        $this->legajo = new LegajoService($this->client, $this->authManager);
    }

    /**
     * Obtiene un archivo desde una URL
     *
     * @param string $fileUrl URL del archivo
     * 
     * @return object Respuesta con el archivo en base64
     */
    public function getFile(string $fileUrl)
    {
        // Extraer solo la parte del path si se proporciona una URL completa
        if (strpos($fileUrl, 'http') === 0) {
            $parsedUrl = parse_url($fileUrl);
            $fileUrl = $parsedUrl['path'];
        }
        
        return $this->client->get($fileUrl);
    }
} 