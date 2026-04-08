# Contao Socialized

A Contao CMS bundle that automatically publishes news articles to Facebook and Instagram when they are published in the backend. Uses the Meta Graph API to post content including teaser images.

## Requirements

- PHP >= 8.2
- Contao >= 5.3
- contao/news-bundle >= 5.3
- A Meta (Facebook) App with Graph API access

## Installation

### Via Composer (Git/VCS repository)

Add the repository to your Contao project's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/YOUR-USER/contao-socialized.git"
        }
    ]
}
```

Then install the package:

```bash
composer require moortech-grasberg/contao-socialized
```

### Via local path (for development)

If the package is stored locally next to your Contao project:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../contao-socialized"
        }
    ]
}
```

```bash
composer require moortech-grasberg/contao-socialized:@dev
```

### Database migration

After installation, run the Contao migration to create the required database columns:

```bash
vendor/bin/contao-console contao:migrate
```

## Configuration

### 1. Set up a Meta (Facebook) App

To use this bundle you need a **Long-Lived Page Access Token** from the Meta Graph API. Follow these steps:

1. Go to [Meta for Developers](https://developers.facebook.com/) and create an app (type: Business).
2. Add the **Facebook Login for Business** product to your app.
3. Request the following permissions:
   - `pages_manage_posts` (for Facebook Page posting)
   - `pages_read_engagement`
   - `instagram_basic` (for Instagram posting)
   - `instagram_content_publish`
4. Generate a **Page Access Token** via the [Graph API Explorer](https://developers.facebook.com/tools/explorer/).
5. Exchange it for a **Long-Lived Token** (valid for 60 days, or permanent for Page tokens obtained through a System User).

> Refer to the [Meta Graph API documentation](https://developers.facebook.com/docs/graph-api/) for detailed instructions.

### 2. Configure your Contao root page

1. Open the Contao backend and navigate to **Site Structure**.
2. Edit a **root page** (or root fallback page).
3. Scroll to the **Social media** section.
4. Check **Enable social media**.
5. Enter the following credentials:
   - **Meta Access Token** - Your Long-Lived Page Access Token
   - **Facebook Page ID** - The numeric ID of your Facebook Page (leave empty to skip Facebook)
   - **Instagram User ID** - Your Instagram Business Account ID (leave empty to skip Instagram)

> Each root page can have its own set of credentials, allowing different websites in a multi-site setup to post to different social media accounts.

### 3. Publish a news article

When you publish a news article (set it to "published" and save), the bundle will automatically:

1. Extract the teaser text (or headline as fallback) as the post caption.
2. Resolve the teaser image to a publicly accessible URL.
3. Post to all configured platforms (Facebook and/or Instagram).
4. Store the result in the news record.

#### Per-article controls

Each news article has two additional fields in the **Publish** section:

- **Skip social media** - Check this to prevent automatic posting for a specific article.
- **Published to social media** - Read-only indicator showing whether the article has been posted. Uncheck manually to allow re-posting.

## Important notes

### Instagram image requirement

Instagram's API requires images to be accessible via a **public HTTPS URL**. This means:

- Your Contao installation must be reachable from the internet.
- Local development environments (localhost, Docker) will not work for Instagram posting.
- The image URL is built from the root page's **DNS** and **SSL** settings.

### Token expiration

Long-Lived Page Access Tokens can expire after 60 days. If you obtain the token through a System User in Meta Business Manager, it will not expire. The bundle logs API errors, so check your Contao system log if posts stop appearing.

### Supported post types

| Platform  | Text only | Text + Image | Text + Link |
|-----------|-----------|--------------|-------------|
| Facebook  | Yes       | Yes          | Yes         |
| Instagram | No        | Yes          | Yes (in caption) |

Instagram requires an image for every post. Articles without a teaser image will be skipped on Instagram.

## Architecture

The bundle follows a hexagonal (ports & adapters) architecture:

```
EventListener (Trigger)
        |
  Domain Layer (Service + Value Objects + Port Interfaces)
        |
   +---------+----------+
   |                     |
Contao Adapters    Meta API Adapters
(content, creds)   (Facebook, Instagram)
```

### Adding a new platform

Implement `SocialMediaPlatformInterface` in your own bundle or app code. The interface is tagged with `contao_socialized.platform` via `#[AutoconfigureTag]`, so any implementing service will be picked up automatically.

```php
use MoortechGrasberg\ContaoSocialized\Domain\Port\SocialMediaPlatformInterface;

class MyPlatformAdapter implements SocialMediaPlatformInterface
{
    public function publish(SocialMediaPost $post, SocialMediaCredentials $credentials): PlatformResult { /* ... */ }
    public function supports(SocialMediaCredentials $credentials): bool { /* ... */ }
    public function getName(): string { return 'my-platform'; }
}
```

## License

MIT
