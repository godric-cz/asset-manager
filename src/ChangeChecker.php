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
