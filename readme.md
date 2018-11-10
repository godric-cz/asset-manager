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

## TODOs

- [ ] find `file_put_contents` and `copy` (see unlinks around) and find sustainable solution of file / directory permissions
    - note: if asset directory is writable for current user, she may delete (and recreate) contents but cannot write to existing contents
    - if she creates file unreadable for others, it still may be a problem (it will be unreadable for apache)
- [ ] allow both (one) string and array of strings as arguments to add* methods
- [ ] document `assets.json` config file
