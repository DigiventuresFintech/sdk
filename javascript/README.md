# Digiventures JavaScript SDK

SDK oficial para integrar con la API de Digiventures.

## Instalación

```bash
# Configura el registry de GitHub Packages
echo "@digiventures:registry=https://npm.pkg.github.com" >> .npmrc

# Instala el paquete
npm install @digiventures/sdk
```

## Uso

```javascript
// ES Modules
import { DigiventuresSDK } from '@digiventures/sdk';

// CommonJS
const { DigiventuresSDK } = require('@digiventures/sdk');

// Inicializa el SDK
const sdk = new DigiventuresSDK({
  applicationId: 'TU_APPLICATION_ID',
  secret: 'TU_SECRET',
  environment: 'qa' // 'qa', 'staging', 'production'
});

// Crea un legajo
async function createLegajo() {
  try {
    const legajo = await sdk.legajo.create({
      firstname: 'Juan',
      lastname: 'Pérez',
      email: 'juan.perez@example.com',
      idNumber: '12345678'
    });
    console.log('Legajo creado:', legajo);
  } catch (error) {
    console.error('Error al crear legajo:', error);
  }
}

// Obtiene un legajo por ID
async function getLegajo(legajoId) {
  try {
    const legajo = await sdk.legajo.get(legajoId);
    console.log('Legajo:', legajo);
  } catch (error) {
    console.error('Error al obtener legajo:', error);
  }
}

// Actualiza un legajo
async function updateLegajo(legajoId, data) {
  try {
    const legajo = await sdk.legajo.update(legajoId, data);
    console.log('Legajo actualizado:', legajo);
  } catch (error) {
    console.error('Error al actualizar legajo:', error);
  }
}

// Obtiene un archivo
async function getFile(fileUrl) {
  try {
    const file = await sdk.getFile(fileUrl);
    console.log('Archivo:', file);
  } catch (error) {
    console.error('Error al obtener archivo:', error);
  }
}
```

## Características

- Manejo automático de tokens de autenticación
- Renovación automática de tokens expirados
- Reintentos automáticos para solicitudes fallidas (timeout de 10 segundos)
- Soporte para múltiples entornos (QA, Staging, Producción)

## Documentación

Para más información sobre los métodos disponibles y sus parámetros, consulta la [documentación de la API](../api.yaml). 