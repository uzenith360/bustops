<?php

if (!function_exists('mime_content_type')) {
    function mime_content_type($file) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mtype = finfo_file($finfo, $file);
        finfo_close($finfo);
        return $mtype;
    }
}