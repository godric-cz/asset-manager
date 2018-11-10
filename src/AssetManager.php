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
        $autobuild = true,
        $config,
        $dir,
        $tags = '',
        $url;

    function __construct($assetsDirectory, $assetsUrl) {
        $this->dir = $assetsDirectory;
        $this->url = $assetsUrl;
        // TODO validations
        // TODO requires leafo/scss
        @mkdir($this->dir); // TODO, maybe not on production
    }

    function addScss($globExpressions) {
        $this->addAsset($globExpressions, ScssAsset::class, 'scss');
    }

    /**
     * @return string html tags for html header -- TODO elaborate on description
     */
    function getTags() {
        return $this->tags;
    }

    /**
     * Sets configuration file with list of allowed compilable files.
     *
     * This is to allow interactive development with automatic build and batch
     * build of all possible files during deployment. To know all possible
     * files during deployment, it must be specified somwhere (if you don't
     * want to parse php source files) => jsonFile with list.
     */
    function setConfig($jsonFile) {
        $this->config = new Config($jsonFile);
    }

    /////////////
    // private //
    /////////////

    private function addAsset($globExpressions, $class, $typeInConfig) {
        $canonizedGlobExpressions = array_map(function($ge) {
            return naive_realpath($ge);
        }, $globExpressions);

        // check allowed assets (for development)
        if ($this->config && $this->autobuild)
            $this->config->checkAllowedBuild($typeInConfig, $canonizedGlobExpressions);

        $asset = new $class($canonizedGlobExpressions, $this->dir);

        // build if requested (for development)
        if ($this->autobuild)
            $asset->build();

        $this->tags .= $asset->getTag($this->url) . "\n";
    }

}
