<?php

namespace Godric\AssetManager;

/**
 * JSON file based configuration for AssetManager.
 */
class Config {

    private
        $allowedBuilds,
        $data,
        $file;

    function __construct($file) {
        $this->file = $file;
        // TODO possible shared abstraction with ScssMeta
    }

    function checkAllowedBuild($type, $canonizedGlobExpressions) {
        $isAllowed = in_array(
            [$type, $canonizedGlobExpressions],
            $this->getAllowedBuilds()
        );

        if (!$isAllowed) {
            // TODO better error message
            throw new \Exception('Build not allowed, add it to "possibleBuilds" in config file.');
        }
    }

    function getAllowedBuilds() {
        if (!isset($this->allowedBuilds)) {
            $builds = [];
            $buildsInJson = $this->getData()['possibleBuilds'];
            foreach ($buildsInJson as $b) {
                $builds[] = [
                    $b['type'],
                    array_map(function($f) {
                        return naive_realpath(dirname($this->file) . '/' . $f);
                    }, $b['files']),
                ];
            }
            $this->allowedBuilds = $builds;
        }
        return $this->allowedBuilds;
    }

    /////////////
    // private //
    /////////////

    private function getData() {
        if (!isset($this->data)) {
            $jsonString = @file_get_contents($this->file);
            if ($jsonString === false)
                throw new \Exception("Unable to read '{$this->file}'.");

            $json = json_decode($jsonString, true);
            if ($json === false)
                throw new \Exception("Not valid json in '{$this->file}'.");

            $this->data = $json;
        }
        return $this->data;
    }

}
