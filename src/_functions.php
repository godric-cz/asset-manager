<?php

namespace Godric\AssetManager;

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
 * Function for resolving /./ and /../ in paths without need to access the filesystem.
 */
function naive_realpath($path) {
    // regex for "/something/.."
    static $regex = '/[^/]+/\.\.';

    if ($path[0] != '/') {
        // TODO check getcwd performance
        $path = getcwd() . '/' . $path;
    }

    do {
        $path = preg_replace("#$regex#", '', $path, 1, $numChanges);
    } while($numChanges > 0);

    return $path;
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
