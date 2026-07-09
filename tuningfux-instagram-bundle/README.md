# tuningfux/instagram-bundle

Wiederverwendbares Symfony-Bundle für die Instagram Platform API.

## Features

- OAuth / Instagram Login
- Speicherung verbundener Accounts via Doctrine
- Profil abrufen
- Medien abrufen
- Bild veröffentlichen
- Webhook-Endpoint
- Command zum Token-Refresh

## Installation im Host-Projekt

### 1. Path-Repository oder VCS-Repository eintragen

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../tuningfux-instagram-bundle"
    }
  ]
}
```

### 2. Bundle installieren

```bash
composer require tuningfux/instagram-bundle
```

### 3. Bundle aktivieren

```php
// config/bundles.php
return [
    Tuningfux\InstagramBundle\TuningfuxInstagramBundle::class => ['all' => true],
];
```

### 4. Konfiguration

```yaml
# config/packages/tuningfux_instagram.yaml
tuningfux_instagram:
    app_id: '%env(INSTAGRAM_APP_ID)%'
    app_secret: '%env(INSTAGRAM_APP_SECRET)%'
    redirect_uri: '%env(INSTAGRAM_REDIRECT_URI)%'
    graph_version: 'v25.0'
    webhook_verify_token: '%env(INSTAGRAM_WEBHOOK_VERIFY_TOKEN)%'
    route_prefix: '/instagram'
    enable_webhook: true
    enable_publish: true
    enable_insights: true
    # store_service: App\Instagram\MyCustomInstagramStore
```

```env
INSTAGRAM_APP_ID=123456789
INSTAGRAM_APP_SECRET=supersecret
INSTAGRAM_REDIRECT_URI=https://example.com/instagram/callback
INSTAGRAM_WEBHOOK_VERIFY_TOKEN=my_verify_token
```

### 5. Migration erzeugen

```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

## Endpunkte

- `GET /instagram/connect`
- `GET /instagram/callback`
- `GET /instagram/accounts`
- `GET /instagram/{id}/profile`
- `GET /instagram/{id}/media`
- `POST /instagram/{id}/publish-image`
- `GET /instagram/{id}/insights`
- `GET /instagram/webhook`
- `POST /instagram/webhook`

## Token-Refresh

```bash
php bin/console tuningfux:instagram:refresh-tokens
```
