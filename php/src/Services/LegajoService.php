<?php

namespace Digiventures\SDK\Services;

use Digiventures\SDK\Http\HttpClient;
use Digiventures\SDK\Auth\AuthManager;

/**
 * Servicio para operaciones con legajos
 */
class LegajoService
{
    /**
     * @var HttpClient
     */
    private $client;

    /**
     * @var AuthManager
     */
    private $authManager;

    /**
     * Constructor del servicio de legajos
     *
     * @param HttpClient $client Cliente HTTP
     * @param AuthManager $authManager Manejador de autenticación
     */
    public function __construct(HttpClient $client, AuthManager $authManager)
    {
        $this->client = $client;
        $this->authManager = $authManager;
    }

    /**
     * Obtiene el prefijo de ruta de la versión de API
     * 
     * @return string Prefijo de versión de la API (e.g. "/1.0")
     */
    private function getApiVersionPath(): string
    {
        $version = $this->authManager->getApiVersion() ?: '1.0';
        return "/{$version}";
    }

    /**
     * Crea un nuevo legajo
     *
     * @param array $data Datos del legajo
     * @param string|null $strategy Estrategia de creación (IGNORE, COMPLETE, OVERRIDE)
     * 
     * @return object Legajo creado
     */
    public function create(array $data, ?string $strategy = null)
    {
        $options = [];
        
        if ($strategy !== null) {
            $options['headers'] = [
                'strategy' => $strategy
            ];
        }
        
        return $this->client->post($this->getApiVersionPath() . '/legajo', $data, $options);
    }

    /**
     * Obtiene un legajo por ID
     *
     * @param string $legajoId ID del legajo
     * 
     * @return object Legajo
     */
    public function get(string $legajoId)
    {
        return $this->client->get($this->getApiVersionPath() . "/legajo/{$legajoId}");
    }

    /**
     * Actualiza un legajo
     *
     * @param string $legajoId ID del legajo
     * @param array $data Datos a actualizar
     * 
     * @return object Legajo actualizado
     */
    public function update(string $legajoId, array $data)
    {
        return $this->client->put($this->getApiVersionPath() . "/legajo/{$legajoId}", $data);
    }
} 