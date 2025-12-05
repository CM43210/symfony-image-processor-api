# Async image processing API: upload -> resize to multiple formats -> download ZIP

## Built with: PHP 8.4, Symfony 8.0, Symfony Messenger (async processing), Redis (message broker + progress tracking), MySQL 8.4

<br>

# How to run

```bash
make up
make composer-install
make migrations-migrate
```
### Or: without make
```bash
docker compose up -d --build
docker compose exec -e COMPOSER_ALLOW_SUPERUSER=1 php composer install --no-interaction --prefer-dist
docker compose exec php php bin/console doctrine:migrations:migrate
```

## URLs

* **API**: `http://localhost:8080/api/images`
* **phpMyAdmin (dev)**: `http://localhost:8081`<br>
  (`user: root`, `pass: root`, `host: db`, `db: app`)

## Configuration

Storage paths (`config/services.yaml`):
```yaml
parameters:
    image.storage_dir: '%kernel.project_dir%/var/storage/images'
    archive.storage_dir: '%kernel.project_dir%/var/storage/archives'
```

`.env`:
```env
DATABASE_URL="mysql://symfony:symfony@db:3306/app?serverVersion=8.4&charset=utf8mb4"
MESSENGER_TRANSPORT_DSN=redis://redis:6379/messages
```

## API Reference

### Upload Image

```http
POST /api/images
Content-Type: multipart/form-data
```

**Form data:**
- `image` (file): Image file (JPEG, PNG)

**Validation:**
- Max size: 20MB
- Allowed MIME types: `image/jpeg`, `image/png`

**Response** `202 Accepted`
```json
{
  "imageId": "01947c3e-1234-7890-abcd-ef1234567890",
  "message": "Image upload accepted for processing"
}
```

**Error** `400 Bad Request`
```json
{
  "type": "about:blank",
  "title": "Validation Failed",
  "status": 400,
  "errors": {
    "image": ["This value should not be blank."]
  }
}
```

### Get Image Status

```http
GET /api/images/{id}/status
```

**Response** `200 OK`
```json
{
  "imageId": "01947c3e-1234-7890-abcd-ef1234567890",
  "status": "processing",
  "progress": 50,
  "message": "Generating medium variant",
  "startedAt": "2025-12-05T10:00:00+00:00",
  "finishedAt": null
}
```

**Error** `404 Not Found`
```json
{
  "type": "about:blank",
  "title": "Image processing status not found",
  "status": 404
}
```

### Download Processed Image

```http
GET /api/images/{id}/download
```

**Response** `200 OK`
- Content-Type: `application/zip`
- Content-Disposition: `attachment; filename="{imageId}.zip"`

**ZIP contents:**
- `original.jpg` - Original image without metadata
- `thumbnail.webp` - 300x300px 
- `medium.webp` - 800x800px
- `large.webp` - 1920x1920px

**Error** `404 Not Found` (image not found or not processed yet)
```json
{
  "type": "about:blank",
  "title": "Image not found or processing not completed yet",
  "status": 404
}
```


