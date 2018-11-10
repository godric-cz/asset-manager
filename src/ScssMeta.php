<?php

namespace Godric\AssetManager;

class ScssMeta {

    private
        $file;

    function __construct($jsonFile) {
        $this->file = $jsonFile;
    }

    function getDependencies() {
        $jsonString = @file_get_contents($this->file);
        if ($jsonString === false)
            return [];

        $json = json_decode($jsonString);
        if ($json === false)
            throw new \Exception("Not valid json in '{$this->file}'.");

        return $json->dependencies;
    }

    function setDependencies($dependencies) {
        $jsonString = json_encode(
            ['dependencies' => $dependencies],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );

        @unlink($this->file);
        file_put_contents($this->file, $jsonString);
    }

}
