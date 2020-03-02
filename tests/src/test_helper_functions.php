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


function generateTestData(string $index_name, array $test_data) : void
{
    mkdir(STORAGE_PATH . '/' . $index_name);
    foreach($test_data as $ngram => $lines) {
        $filepath = STORAGE_PATH . '/' . $index_name . '/' . $ngram;
        $fh = fopen($filepath, 'w');
        foreach($lines as $line) {
            fputs($fh, $line . "\n");
        }
        fclose($fh);
    }
}