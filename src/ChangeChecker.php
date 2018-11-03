<?php

namespace Godric\AssetManager;

class ChangeChecker {

    private
        $targetModificationTime,
        $target;

    function __construct($target) {
        $this->target = $target;
    }

    /**
     * @param string[] $files files to check
     * @return bool if any file changed since last target build
     */
    function areChanged($files) {
        $targetModified = $this->getTargetModificationTime();
        foreach ($files as $file) {
            if (filemtime($file) > $targetModified) return true;
        }
        return false;
    }

    /**
     * @param string[] $files files to check
     * @return string[] files changed since last target build
     */
    function getChanged($files) {
        $targetModified = $this->getTargetModificationTime();
        return array_filter($files, function($f)use($targetModified) {
            return filemtime($f) > $targetModified;
        });
    }

    /////////////
    // private //
    /////////////

    private function getTargetModificationTime() {
        if (!isset($this->targetModificationTime)) {
            $this->targetModificationTime = (int) @filemtime($this->target);
        }
        return $this->targetModificationTime;
    }

}
