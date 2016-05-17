<?php

return [
    /**
     * Whether all translations are stored in single PO file
     */
    'single_file' => false,

    /**
     * Name of single file in case of single_file set to true
     */
    'single_file_name' => 'messages',

    /**
     * Name of default group in case of no group can be resolved from
     * translation key (used only if single_file set to false)
     */
    'default_group_name' => 'messages',

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
        'ignored_files' => [],

        /**
         * Path where generated POT files should be saved
         */
        'output_directory' => storage_path('translations'),

        /**
         * Poedit base path (leave null if you don't want to see files content
         * directly from Poedit)
         *
         * Example: /projects/my-project/
         * In above example real project was located at D:\projects\my-project
         * In theory this path should be relative to your translation file
         */
        'base_path' => env('TRANSLATOR_POT_BASE_PATH', null),

        /**
         * Comments in POT file
         */
        'comments' => [
            /**
             * Whether comment should be added in some cases
             */
            'add' => true,

            /**
             * Info text about using plural form
             */
            'plural_text' => 'Uses plural form. ',

            /**
             * Info text about placeholders
             */
            'placeholders_text' => 'Available placeholders: ',
        ],
    ],
];
