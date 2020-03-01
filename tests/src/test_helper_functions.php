<?php
/**
 * Verzeichnis & Inhalt rekursiv löschen
 */
function rrmdir(string $dir) : bool { 
    if (!is_dir($dir)) {
        return false;
    }
    array_map(
        function($item) use ($dir) {
            if (in_array($item, ['.', '..'])) {
                return;
            }
            if(!rrmdir($dir . '/' . $item)) {
                unlink($dir . '/' . $item);
            }
        },
        scandir($dir)
    );
    return rmdir($dir);
}

/**
 * Verzeichnisinhalt rekursiv löschen, Verzeichnis behalten
 */
function cleandir(string $dir, bool $keep = true) : bool { 
    if (!is_dir($dir)) {
        return false;
    }
    array_map(
        function($item) use ($dir) {
            if (in_array($item, ['.', '..'])) {
                return;
            }
            if(!cleandir($dir . '/' . $item, false)) {
                unlink($dir . '/' . $item);
            }
        },
        scandir($dir)
    );
    if ($keep) {
        return true;
    }
    return rmdir($dir);
}