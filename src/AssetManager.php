<?php

namespace Godric\AssetManager;

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

}


//////////////////////
// helper functions //
//////////////////////

/**
 * @return bool if haystack contains needle
 */
function contains($haystack, $needle) {
    return strpos($haystack, $needle) !== false;
}

/**
 * @param string[] $globs array of glob expressions
 * @return string[] array of matching files
 */
function expand_globs($globs) {
    $files = [];
    foreach ($globs as $globExpression) {
        if (contains($globExpression, '*')) {
            // actual glob expression
            $currentFiles = glob($globExpression);
            if (!$currentFiles)
                throw new Exception("Glob expression '$globExpression' matched no files.");
            $files = array_merge($files, $currentFiles);
        } else {
            // just basic file
            $files[] = $globExpression;
        }
    }
    return $files;
}

/**
 * @param string[] $strings array of strings to hash together
 * @return string nice hash of given strings
 */
function nice_hash($strings) {
    $string = implode('|', $strings);
    $hash   = md5($string);
    $num    = hexdec(substr($hash, 0, 15)); // process only first 60bits, hexdec does not work with negative numbers
    $fhash  = sprintf('%020d', $num);

    // compute wannabe random first digit 1-9
    $fhash[0] = ($fhash % 9) + 1;
    return $fhash;
}
