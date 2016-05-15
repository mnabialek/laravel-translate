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
         * Files in which translations should be searched
         */
        'files' => [
            '*.php',
        ],
        /**
         * Files that should be ignored (you should use here full file path)
         */
        'ignored_files' => [
        ],
        /**
         * Path where generated POT files should be saved
         */
        'output_directory' => storage_path('translations'),
    ],
];
