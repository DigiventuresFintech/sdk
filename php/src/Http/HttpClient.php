<?php

namespace Digiventures\SDK\Http;

use Digiventures\SDK\Auth\AuthManager;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Cliente HTTP para comunicarse con la API de Digiventures
 */
class HttpClient
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var AuthManager
     */
    private $authManager;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var array
     */
    private $config;

    /**
     * Constructor del cliente HTTP
     *
     * @param array $config Configuración
     * @param AuthManager $authManager Manejador de autenticación
     */
    public function __construct(array $config, AuthManager $authManager)
    {
        $this->config = $config;
        $this->authManager = $authManager;
        $this->baseUrl = $this->getBaseUrl($config['environment']);

        // Configuramos el stack de handlers para middleware
        $stack = HandlerStack::create();
        
        // Middleware para reintentos
        $stack->push($this->createRetryMiddleware($config['maxRetries']));
        
        // Middleware para añadir el token de autenticación
        $stack->push($this->createAuthMiddleware());

        // Creamos el cliente
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => $config['timeout'],
            'handler' => $stack,
        ]);
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
     * Crea un middleware para reintentos
     *
     * @param int $maxRetries Número máximo de reintentos
     * 
     * @return callable Middleware
     */
    private function createRetryMiddleware(int $maxRetries): callable
    {
        return Middleware::retry(
            function (
                $retries,
                RequestInterface $request,
                ResponseInterface $response = null,
                \Exception $exception = null
            ) use ($maxRetries) {
                // No reintentamos si hemos alcanzado el máximo de reintentos
                if ($retries >= $maxRetries) {
                    return false;
                }

                // Reintentamos si hay un error de red
                if ($exception instanceof RequestException && $exception->getCode() === 0) {
                    return true;
                }

                // Reintentamos en errores 5xx
                if ($response && $response->getStatusCode() >= 500) {
                    // No reintentamos 401 aquí, ese caso se maneja en el middleware de auth
                    return true;
                }

                return false;
            },
            function ($retries) {
                // Exponential backoff
                return 1000 * pow(2, $retries);
            }
        );
    }

    /**
     * Crea un middleware para manejar la autenticación
     *
     * @return callable Middleware
     */
    private function createAuthMiddleware(): callable
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                // Si no es una petición de autenticación, añadimos el token
                if (strpos($request->getUri()->getPath(), '/authorization/') === false) {
                    // Obtenemos el token
                    $token = $this->authManager->getToken();
                    
                    // Modificamos la URI para añadir el token como query param
                    $uri = $request->getUri();
                    $query = $uri->getQuery();
                    
                    // Añadimos el token al query
                    parse_str($query, $queryParams);
                    $queryParams['authorization'] = $token;
                    $newQuery = http_build_query($queryParams);
                    
                    // Creamos la nueva URI con el token
                    $newUri = $uri->withQuery($newQuery);
                    
                    // Actualizamos la request con la nueva URI
                    $request = $request->withUri($newUri);
                }

                // Manejamos la respuesta
                return $handler($request, $options)->then(
                    function (ResponseInterface $response) use ($request, $options, $handler) {
                        // En caso de éxito, resetear el flag de reintento
                        $this->authManager->resetAuthRetry();
                        return $response;
                    },
                    function (\Exception $exception) use ($request, $options, $handler) {
                        // Verificamos si es un error 401 o 500
                        if ($exception instanceof RequestException && 
                            ($exception->getResponse() && 
                            ($exception->getResponse()->getStatusCode() === 401 || 
                             $exception->getResponse()->getStatusCode() === 500))) {
                            
                            // Si no hemos reintentado ya, refrescamos el token y reintentamos
                            if (!$this->authManager->hasRetried()) {
                                $this->authManager->markRetry();
                                
                                // Fetch a new token
                                $token = $this->authManager->fetchNewToken();
                                
                                // Modify the request URI to include the new token
                                $uri = $request->getUri();
                                parse_str($uri->getQuery(), $queryParams);
                                $queryParams['authorization'] = $token;
                                $newQuery = http_build_query($queryParams);
                                $newUri = $uri->withQuery($newQuery);
                                
                                // Create a new request with updated URI
                                $newRequest = $request->withUri($newUri);
                                
                                // Retry with the new request
                                return $handler($newRequest, $options);
                            }
                        }
                        
                        // If we can't handle it, rethrow the exception
                        throw $exception;
                    }
                );
            };
        };
    }

    /**
     * Realiza una petición GET
     *
     * @param string $url URL
     * @param array $options Opciones de la petición
     * 
     * @return mixed Respuesta de la API
     * 
     * @throws \Exception Si falla la petición
     */
    public function get(string $url, array $options = [])
    {
        try {
            $response = $this->client->get($url, $options);
            return $this->parseResponse($response);
        } catch (\Exception $e) {
            throw new \Exception("Error en la petición GET: " . $e->getMessage());
        }
    }

    /**
     * Realiza una petición POST
     *
     * @param string $url URL
     * @param array $data Datos a enviar
     * @param array $options Opciones de la petición
     * 
     * @return mixed Respuesta de la API
     * 
     * @throws \Exception Si falla la petición
     */
    public function post(string $url, array $data = [], array $options = [])
    {
        try {
            $options['json'] = $data;
            $response = $this->client->post($url, $options);
            return $this->parseResponse($response);
        } catch (\Exception $e) {
            throw new \Exception("Error en la petición POST: " . $e->getMessage());
        }
    }

    /**
     * Realiza una petición PUT
     *
     * @param string $url URL
     * @param array $data Datos a enviar
     * @param array $options Opciones de la petición
     * 
     * @return mixed Respuesta de la API
     * 
     * @throws \Exception Si falla la petición
     */
    public function put(string $url, array $data = [], array $options = [])
    {
        try {
            $options['json'] = $data;
            $response = $this->client->put($url, $options);
            return $this->parseResponse($response);
        } catch (\Exception $e) {
            throw new \Exception("Error en la petición PUT: " . $e->getMessage());
        }
    }

    /**
     * Procesa la respuesta de la API
     *
     * @param ResponseInterface $response Respuesta
     * 
     * @return mixed Respuesta procesada
     * 
     * @throws \Exception Si el formato de la respuesta es inválido
     */
    private function parseResponse(ResponseInterface $response)
    {
        $body = (string) $response->getBody();
        $data = json_decode($body);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Error al decodificar la respuesta: " . json_last_error_msg());
        }
        
        return $data;
    }
} 