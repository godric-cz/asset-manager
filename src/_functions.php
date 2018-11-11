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
                throw new \Exception("Glob expression '$globExpression' matched no files.");
            $files = array_merge($files, $currentFiles);
        } else {
            // just basic file
            $files[] = $globExpression;
        }
    }
    return $files;
}

/**
 * Creates nice hash from array of glob expressions.
 *
 * This variant requires canonic glob expressions and treats them relative to
 * to reference root path. This is useful for identifying same file on
 * on different machines.
 *
 * @param string $root reference directory, globs are internally turned to
 *  relative to this path
 * @param string[] $canonicGlobs canonic glob expressions
 * @return string nice hash
 */
function fs_relative_nice_hash($root, $canonicGlobs) {
    $relativeGlobs = array_map(function($glob)use($root) {
        return get_relative_path($root, $glob);
    }, $canonicGlobs);

    $hash = nice_hash($relativeGlobs);

    return $hash;
}

/**
 * Helper function for finding relative path from one path to another.
 * @see https://stackoverflow.com/a/2638272
 */
function get_relative_path($from, $to) {
    // some compatibility fixes for Windows paths
    // commented to avoid filesystem access
    //$from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
    //$to   = is_dir($to)   ? rtrim($to, '\/') . '/'   : $to;
    $from = str_replace('\\', '/', $from);
    $to   = str_replace('\\', '/', $to);
    $from = normalize_path($from);
    $to   = normalize_path($to);

    $from     = explode('/', $from);
    $to       = explode('/', $to);
    $relPath  = $to;

    foreach($from as $depth => $dir) {
        // find first non-matching dir
        if($dir === $to[$depth]) {
            // ignore this directory
            array_shift($relPath);
        } else {
            // get number of remaining dirs to $from
            $remaining = count($from) - $depth;
            if($remaining > 1) {
                // add traversals up to first matching dir
                $padLength = (count($relPath) + $remaining - 1) * -1;
                $relPath = array_pad($relPath, $padLength, '..');
                break;
            } else {
                $relPath[0] = './' . $relPath[0];
            }
        }
    }
    return implode('/', $relPath);
}

/**
 * Function for resolving path to it's canonic absolute form without accessing
 * the filesystem.
 *
 * Since it operates naively on input string, it doesn't reslove symlinks and
 * other filesystem specific features.
 */
function naive_realpath($path) {
    // add absolute prefix if needed
    if ($path[0] != '/') {
        // TODO check getcwd performance
        $path = getcwd() . '/' . $path;
    }

    $path = normalize_path($path);

    return $path;
}

/**
 * @param string[] $strings array of strings to hash together
 * @return string nice hash of given strings
 */
function nice_hash($strings) {
    $string = implode('|', $strings);
    $hash   = md5($string);
    // TODO improve to 63bits, check max value for 63bit int
    $num    = hexdec(substr($hash, 0, 15)); // process only first 60bits, hexdec does not work with negative numbers
    $fhash  = sprintf('%020d', $num);

    // compute wannabe random first digit 1-9
    $fhash[0] = ($fhash % 9) + 1;
    return $fhash;
}

/**
 * Resolves /../ in path.
 */
function normalize_path($path) {
    // regex for "/something/.."
    static $regex = '#' . '/[^/]+/\.\.' . '#';

    do {
        $path = preg_replace($regex, '', $path, 1, $numChanges);
    } while ($numChanges > 0);

    return $path;
}
