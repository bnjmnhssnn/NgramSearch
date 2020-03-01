<?php
function log_last_error(Monolog\Logger $logger) : void {
    if(NULL !== $err = error_get_last()) {
        $err_type = array_search($err['type'], get_defined_constants());
        $logger->error($err_type . ' ' . $err['message'] . ' in ' . $err['file'] . ':' . $err['line']);
    }
}

/* JSON Hilfsfunktionen (https://www.php.net/manual/de/function.json-last-error.php) --> */
function get_post_payload() : stdClass {
    $json = file_get_contents('php://input');
    if (empty($json)) {
        throw new \Exception('POST Request Body ist leer');
    }
    $payload = @json_decode($json);
    if (json_last_error() === JSON_ERROR_NONE) {
        return $payload;
    }
    throw new \Exception('POST Request JSON konnte nicht decodiert werden: ' . json_last_error_readable());
}

function json_last_error_readable() : string {   
    switch(json_last_error()) {
        case JSON_ERROR_DEPTH:
            return 'Maximale Stacktiefe überschritten';
            break;
        case JSON_ERROR_STATE_MISMATCH:
            return 'Unterlauf oder Nichtübereinstimmung der Modi';
            break;
        case JSON_ERROR_CTRL_CHAR:
            return 'Unerwartetes Steuerzeichen gefunden';
            break;
        case JSON_ERROR_SYNTAX:
            return 'Syntaxfehler, ungültiges JSON';
            break;
        case JSON_ERROR_UTF8:
            return 'Missgestaltete UTF-8 Zeichen, möglicherweise fehlerhaft kodiert';
            break;
        default:
            return 'Unbekannter JSON Fehler';
    }
}
/* <-- JSON Hilfsfunktionen (https://www.php.net/manual/de/function.json-last-error.php) */


/**
 * Unittest-fähiger Wrapper für header()
 */
function set_header(string $header, bool $replace = TRUE, int $http_response_code = NULL) : void {
    if (php_sapi_name() === 'cli') {
        $key = 'phpunit_header_jar';
        if(!array_key_exists($key, $GLOBALS)) {
            $GLOBALS[$key] = []; 
        }
        $GLOBALS[$key][] = $header; 
    } else {
        header(...func_get_args());
    }
}

function get_storage_adapter() : NgramSearch\StorageAdapter\StorageAdapterInterface {
    switch(STORAGE_TYPE) {
        case 'Filesystem':
            return new NgramSearch\StorageAdapter\Filesystem(STORAGE_PATH);
            break;
        default:
            throw new Exception('No Storage Adapter defined for ' . STORAGE_TYPE);
    }
}
