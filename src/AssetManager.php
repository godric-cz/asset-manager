<?php

namespace Godric\AssetManager;

require __DIR__ . '/_functions.php';

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
     * Cleans build directory and builds all assets.
     */
    function buildClean() {
        $this->clean();

        foreach ($this->config->getAllowedBuilds() as [$type, $canonizedGlobExpressions]) {
            // TODO use $type to select Asset class, see addAsset
            $asset = new ScssAsset($canonizedGlobExpressions, $this->dir);
            $asset->build();
        }
    }

    /**
     * @return string html tags for html header -- TODO elaborate on description
     */
    function getTags() {
        return $this->tags;
    }

    /**
     * Enable / disable automatic file changes checking & build.
     */
    function setAutobuild($value) {
        $this->autobuild = (bool) $value;
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

    /**
     * Deletes all files from $this->dir.
     */
    private function clean() {
        foreach (glob($this->dir . '/*') as $file) {
            $name = basename($file);
            if (!preg_match('/^\d{10}/', $name))
                throw new \Exception('Unexpected filename. Make sure "' . $this->dir . '" contains only build artefacts (and is thus safe to clean) and try again.');
            $deleted = unlink($file);
            if (!$deleted)
                throw new \Exception('Failed to delete "' . $file . '". Make sure target directory is writable to allow delete of other users files.');
        }
    }

}
