<?php

namespace Godric\AssetManager;

use Leafo\ScssPhp\Compiler as ScssCompiler;

class ScssAsset {

    private
        $dir,
        $globSources,
        $meta,
        $target;

    /**
     * @param string[] $globSources expressions for glob defining all sources
     * @param string $targetDirectory where to place all built files
     */
    function __construct($globSources, $targetDirectory) {
        // TODO target directory might be emptystring?
        $this->globSources = $globSources;

        $hash = nice_hash($globSources);
        $this->dir    = $targetDirectory;
        $this->target = "$targetDirectory/$hash.css";
        $this->meta   = new ScssMeta("$targetDirectory/$hash.json");
    }

    /**
     * Builds target.
     *
     * Does notihing, if none of sources or required files were modified since
     * last build.
     */
    function build() {
        $checker      = new ChangeChecker($this->target);
        $sources      = expand_globs($this->globSources);
        $dependencies = $this->meta->getDependencies();

        if (!$checker->areChanged($sources) && !$checker->areChanged($dependencies))
            return; // nothing to build

        // TODO usecase: scss depends on picture, picture gets removed => error should be triggered

        $this->doBuild($sources);
    }

    /**
     * @return string html tag to built asset
     */
    function getTag($baseUrl) {
        $target  = $this->target;
        $version = filemtime($target);

        if ($version === false)
            throw new \Exception('Asset must be built first.');

        $url = $baseUrl . '/' . basename($target) . '?v=' . $version;

        // TODO is media attribute necessary?
        // TODO it's not clear, if url and tag creation should be implemented in this class
        return '<link rel="stylesheet" type="text/css" href="' . $url . '" media="screen,projection">';
    }

    /////////////
    // private //
    /////////////

    private function doBuild($sources) {
        // collect scss string
        // file-aware function (includes, asset urls) must be prepared here
        $scssString  = '';
        $importPaths = []; // this is for @import directives
        foreach ($sources as $source) {
            $sourceDir = dirname($source);
            $sourceContents = file_get_contents($source);

            // convert asset-urls to relative to current file
            // TODO this is dummy solution
            $sourceContents = strtr($sourceContents, ["asset-url('" => "asset-url('$sourceDir/"]);

            $scssString .= $sourceContents;

            // TODO instead of import paths, use similar approach as with
            // assets and use then all imports as dependencies to allow auto
            // recompilation even for imported files
            $importPaths[$sourceDir] = true;
        }

        // configure compiler, bind custom functions (macros)
        $dependencies = [];
        $scss = new ScssCompiler;
        $scss->setImportPaths(array_keys($importPaths));
        $scss->registerFunction('asset-url', function($args)use(&$dependencies) { // this is for static assets
            $arg = $args[0][2][0];
            $dependencies[] = $arg;
            return 'url(\'' . $this->refreshDependency($arg) . '\')';
        });

        // run compiler
        $cssString = $scss->compile($scssString);       // TODO compilation density and sourcemaps
        $this->meta->setDependencies($dependencies);    // TODO FIXME dependencies are relative, so this is probably broken
        file_put_contents($this->target, $cssString);   // TODO Exception
                                                        // TODO file permissions
    }

    /**
     * @param string $file required file (ie. image) to refresh (=make current version accessible to css)
     * @return string url which will be put to target css
     */
    private function refreshDependency($file) {
        // TODO this should work in two modes
        // (A) COPY mode - file is copied to asset directory (maybe also compressed etc).
        // (B) DIRECT mode - file is kept as is, but base url for such files is then necessary. In that case, correct expiry headers in original directory must be set.
        // in both cases file version should be added as GET parameter - note that in both cases version is computed on compile time and when changed, browser recognizes the change and reloads file
        // Especially for possible problems with expiry headers in DIRECT mode, for now we choose as one and only the COPY mode.
        $suffix     = substr($file, strrpos($file, '.'));
        $modified   = filemtime($file);

        $targetName = nice_hash([$file]) . $suffix;
        $targetUrl  = $targetName . '?v=' . $modified;
        $targetFile = $this->dir . '/' . $targetName;

        if (!is_file($targetFile)) {
            copy($file, $targetFile);
            // TODO Exception
        }

        return $targetUrl;
    }

}
