<?php

return [
    /**
     * Settings for POT generation
     */
    'pot' => [
        /**
         * Paths to search for used translations
         */
        'paths' => [
            app_path(),
            resource_path(),
        ],
        /**
         * Paths to ignore to search for used translations
         */
        'excluded_paths' => [
            app_path('storage'),
        ],
        /**
         * Files that should be excluded (full path)
         */
        'excluded_files' => [
        ],
        /**
         * Path where generated POT files should be saved
         */
        'generation_directory' => storage_path('translations'),
    ],
];
