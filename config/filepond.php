<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API middleware
    |--------------------------------------------------------------------------
    |
    | The middleware to append to the filepond API routes
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Local Temporary Path
    |--------------------------------------------------------------------------
    |
    | When initially uploading the files we store them in this path
    |
    */
    'temporary_files_path' => realpath(env('TMP', sys_get_temp_dir())),
    'input_name' => 'filepond',
];
