## Usecases

Default:

```php
$am = new Godric\AssetManager\AssetManager('./assets', 'https://mysite.com/assets');

$am->addScss(['style/style.scss', 'style/components/*.scss']);

... = $am->getTags(); // triggers build (added only)
```

Gamecon local development:

```php
$am = new Godric\AssetManager\AssetManager('./assets', 'https://mysite.com/assets');

$am->setConfig('assets.json');

$am->addScss(['style/style.scss', 'style/components/*.scss']); // is checked if allowed

... = $am->getTags(); // triggers build (added only)
```

Gamecon local build:

```php
$am = new Godric\AssetManager\AssetManager('./assets', 'https://mysite.com/assets');

$am->setConfig('assets.json');

$am->build(); // builds all possilbe assets from assets.json
```

Gamecon production:

```php
$am = new Godric\AssetManager\AssetManager('./assets', 'https://mysite.com/assets');

$am->setAutobuild(false);

$am->addScss(['style/style.scss', 'style/components/*.scss']);

... = $am->getTags(); // only urls of added are generated, no build
```
