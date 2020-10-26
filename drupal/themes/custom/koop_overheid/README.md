# Data.overheid Theme

## Requirements
- NPM installed globally (https://www.npmjs.com/get-npm)
- Gulp installed globally (https://gulpjs.com)

## Install theme

Install NPM modules:

``npm install``

To compile once:

``gulp build``

### Gulp commands:

Run gulp default task:

``gulp``

Run without browsersync:

``gulp watch``

### Disable drupal caching:

Add the following to drupal/sites/default/services.yml

```yaml
services:
  cache.backend.null:
    class: Drupal\Core\Cache\NullBackendFactory
```

Add the following to drupal/sites/default/settings.php:

```php
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;
$settings['cache']['bins']['render'] = 'cache.backend.null';
```

Disables the page cache: has more impact, but could also be useful.

```php
$settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
$settings['cache']['bins']['page'] = 'cache.backend.null';
```
