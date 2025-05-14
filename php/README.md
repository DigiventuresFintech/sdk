# Digiventures PHP SDK

SDK oficial para integrar con la API de Digiventures.

## Instalación

```bash
composer require digiventures/sdk
```

## Uso

```php
<?php

require 'vendor/autoload.php';

use Digiventures\SDK\DigiventuresSDK;

// Inicializa el SDK
$sdk = new DigiventuresSDK([
    'applicationId' => 'TU_APPLICATION_ID',
    'secret' => 'TU_SECRET',
    'environment' => 'qa', // 'qa', 'staging', 'production'
]);

// Crea un legajo
try {
    $legajo = $sdk->legajo->create([
        'firstname' => 'Juan',
        'lastname' => 'Pérez',
        'email' => 'juan.perez@example.com',
        'idNumber' => '12345678'
    ]);
    echo "Legajo creado: " . json_encode($legajo, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "Error al crear legajo: " . $e->getMessage() . "\n";
}

// Obtiene un legajo por ID
try {
    $legajo = $sdk->legajo->get('ID_DEL_LEGAJO');
    echo "Legajo: " . json_encode($legajo, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "Error al obtener legajo: " . $e->getMessage() . "\n";
}

// Actualiza un legajo
try {
    $legajo = $sdk->legajo->update('ID_DEL_LEGAJO', [
        'vouchers' => [
            // Configuración de vouchers
        ]
    ]);
    echo "Legajo actualizado: " . json_encode($legajo, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "Error al actualizar legajo: " . $e->getMessage() . "\n";
}

// Obtiene un archivo
try {
    $file = $sdk->getFile('URL_DEL_ARCHIVO');
    echo "Archivo obtenido con éxito\n";
    // $file->file contiene el contenido del archivo en base64
} catch (Exception $e) {
    echo "Error al obtener archivo: " . $e->getMessage() . "\n";
}
```

## Características

- Manejo automático de tokens de autenticación
- Renovación automática de tokens expirados
- Reintentos automáticos para solicitudes fallidas (timeout de 10 segundos)
- Soporte para múltiples entornos (QA, Staging, Producción)

## Documentación

Para más información sobre los métodos disponibles y sus parámetros, consulta la [documentación de la API](../api.yaml). 