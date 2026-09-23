<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Space storage disk
    |--------------------------------------------------------------------------
    |
    | Personal spaces and agent file get/put use this disk. It must be the
    | s3 disk configured in config/filesystems.php. Tests may fake it.
    |
    */

    'disk' => env('BINDRR_DISK', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Upload limit
    |--------------------------------------------------------------------------
    |
    | Maximum size of a browser upload or an agent put, in kilobytes.
    | PHP upload_max_filesize and post_max_size must be at least this large.
    |
    */

    'max_upload_kilobytes' => (int) env('BINDRR_MAX_UPLOAD_KB', 10240),

];
