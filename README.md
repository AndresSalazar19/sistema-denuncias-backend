# Backend - Sistema de Denuncias

## Requisitos
- PHP 8.1+
- Composer 2.5+
- MySQL 8.0+

## Instalación

```bash
# 1. Instalar dependencias
composer install

# 2. Copiar archivo de entorno
cp .env.example .env

# 3. Generar application key
php artisan key:generate

# 4. Generar JWT secret
php artisan jwt:secret

# 5. Configurar archivo .env con estos valores:
```

```env
APP_NAME=Laravel
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=34.9.123.229
DB_PORT=3306
DB_DATABASE=sistema-denuncias
DB_USERNAME=lp_user
DB_PASSWORD=123

SESSION_DRIVER=database
FILESYSTEM_DISK=gcs
CACHE_STORE=database
QUEUE_CONNECTION=database

JWT_SECRET=oZc3qn4JiPHd734XcVsghiW8cubGrXmjLg5vcqMHheV3kDlQVqt1Wd8uKXJ4hQh7

GOOGLE_CLOUD_PROJECT_ID=bionic-tracer-484716-a8
GOOGLE_CLOUD_KEY_FILE=bionic-tracer-847382-ab123.json
GOOGLE_CLOUD_STORAGE_BUCKET=denuncias-evidencias-2026-ec
```

```bash
# 6. Colocar archivo de credenciales de Google Cloud
# Descargar el archivo JSON de Google Cloud Console
# Renombrar a: bionic-tracer-847382-ab123.json
# Colocar en la raíz del proyecto Laravel

# 7. Solo verifica la conexión a la BD
php artisan tinker
>>> DB::connection()->getPdo();
# Si no da error, la conexión funciona

## Probar

```bash
# Verificar que el servidor esté corriendo
curl http://localhost:8000/api/denuncias

# Crear denuncia de prueba
curl -X POST http://localhost:8000/api/denuncias \
  -H "Content-Type: application/json" \
  -d '{
    "titulo": "Bache en Av. Principal",
    "descripcion": "Bache grande que necesita reparación",
    "categoria_id": 1,
    "ubicacion_lat": "-2.12345",
    "ubicacion_lng": "-79.12345",
    "ubicacion_direccion": "Av. Principal esq. Calle 5",
    "denunciante_nombre": "Juan Pérez",
    "denunciante_email": "juan@example.com",
    "denunciante_telefono": "0999999999"
  }'

# Consultar denuncia
curl http://localhost:8000/api/denuncias/consultar/DEN-2026-HXQ1CR
```

## Solución de Problemas

```bash
# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Recrear base de datos
php artisan migrate:fresh --seed
