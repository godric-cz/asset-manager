<?php

namespace Godric\AssetManager;

require __DIR__ . '/_functions.php';

// TODO temporary autoloader
spl_autoload_register(function($class) {
    $realClass = strtr($class, ['Godric\\AssetManager\\' => '']);
    @include __DIR__ . '/' . $realClass . '.php';
});

/**
 * Temporary dummy implementation of asset manager.
 */
class AssetManager {

    private
        $builtCssUrls = [],
        $config,
        $dir,
        $url;

    function __construct($assetsDirectory, $assetsUrl) {
        $this->dir = $assetsDirectory;
        $this->url = $assetsUrl;
        // TODO validations
        // TODO requires leafo/scss
        @mkdir($this->dir); // TODO, maybe not on production
    }

    // TODO pro seznam souborů radši json soubor, aby bylo zřejmé, že ty "povolení" nesmí psát někam do kódu
    function addScss($globExpressions) {
        $asset = new ScssAsset($globExpressions, $this->dir);

        $this->config->checkAllowedBuild('scss', $globExpressions); // TODO what if config is not set?
        $asset->build(); // TODO do not do this on production

        $target  = $asset->getTarget();
        $version = filemtime($target); // TODO this is already known in changechecker, use it (but beware of rebuild. Take into account production vs local)
        $this->builtCssUrls[] = $this->url . '/' . basename($target) . '?v=' . $version;
    }

    function getCssTags() {
        $tags = array_map(function($url) {
            // TODO media=screen and other attributes necessary?
            return '<link rel="stylesheet" type="text/css" href="' . $url . '" media="screen,projection">';
        }, $this->builtCssUrls);
        return implode("\n", $tags);
    }

    function setConfig($jsonFile) {
        $this->config = new Config($jsonFile);
    }

}
