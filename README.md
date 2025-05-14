# Digiventures SDK

Este repositorio contiene SDKs oficiales para integrar con la API de Digiventures.

## Características

- Manejo automático de tokens de autenticación
- Renovación automática de tokens expirados
- Reintentos automáticos para solicitudes fallidas (timeout de 10 segundos)
- Soporte para múltiples entornos (QA, Staging, Producción)
- Gestión automática de versiones de API

## SDKs Disponibles

- [JavaScript](./javascript/README.md)
- [PHP](./php/README.md)

## Documentación de la API

La documentación completa de la API está disponible en el archivo [api.yaml](./api.yaml) en formato OpenAPI 3.0.

## Versionado de la API

El SDK obtiene automáticamente la versión de la API a utilizar desde la respuesta de autenticación.
Esta versión se utiliza en todas las rutas de la API, por lo que no es necesario especificarla manualmente.

## Licencia

MIT 