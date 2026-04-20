# Contao Socialized

Contao-Bundle zur automatischen Veröffentlichung von News-Artikeln auf Facebook und Instagram via Meta Graph API.

## Tech-Stack

- **PHP >= 8.2**, Contao >= 5.3, Symfony 6.4/7.0
- **Bundle-Typ**: `contao-bundle` (Contao Manager Plugin)
- **Testing**: PHPUnit 10.5/11.0 (Tests noch nicht implementiert)
- **HTTP**: Symfony HttpClient für Meta Graph API v19.0

## Architektur

Hexagonale Architektur (Ports & Adapters) mit klarer Schichtentrennung:

```
src/
  Domain/
    Model/          # Value Objects (readonly final): SocialMediaPost, SocialMediaCredentials, PlatformResult, PublishResult
    Port/           # Interfaces: SocialMediaPlatformInterface, CredentialsProviderInterface, NewsContentResolverInterface
    Service/        # SocialMediaPublishService (Orchestrierung)
  Adapter/
    Contao/         # ContaoCredentialsProvider, ContaoNewsContentResolver
    Meta/           # FacebookPageAdapter, InstagramAdapter, MetaGraphApiClient, MetaApiException
  EventListener/
    DataContainer/  # NewsPublishListener (tl_news onsubmit Callback)
  ContaoManager/    # Plugin.php (Bundle-Registrierung)
config/
  services.yaml     # DI-Konfiguration mit Autowiring, Interface-Aliase
contao/
  dca/              # tl_news.php, tl_page.php (Feldkonfigurationen)
  languages/        # de/, en/ (Übersetzungen für tl_news, tl_page)
```

## Konventionen

- **Value Objects** sind `final readonly class` mit typed Properties
- **Plattform-Adapter** implementieren `SocialMediaPlatformInterface` und werden via `#[AutoconfigureTag('contao_socialized.platform')]` automatisch registriert
- **Port-Interfaces** werden in `services.yaml` per Alias auf Contao-Adapter gemappt
- **Sprache**: Deutsch als primäre Sprache, Englisch als Fallback

## Wichtige Patterns

- **TaggedIterator** für dynamische Plattform-Registry im `SocialMediaPublishService`
- **Idempotenz**: `socialMediaPublished`-Flag in `tl_news` verhindert Doppelposts
- **Token-Sicherheit**: `metaAccessToken` in `tl_page` ist verschlüsselt (`encrypt: true`)
- **Graceful Degradation**: Fehler einer Plattform blockieren nicht die anderen

## Build & Test

```bash
# Tests ausführen (noch keine Tests vorhanden)
vendor/bin/phpunit

# Contao-Migration nach Schema-Änderungen
vendor/bin/contao-console contao:migrate
```

## Neue Plattform hinzufügen

1. Neue Adapter-Klasse unter `src/Adapter/` erstellen
2. `SocialMediaPlatformInterface` implementieren
3. Wird automatisch via `#[AutoconfigureTag]` registriert
4. Ggf. neue Felder in `SocialMediaCredentials` und DCA ergänzen