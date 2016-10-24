<?php
return [
    'settings' => [

        // Site configuration
        'site' => [
            '+site_name' => 'Sepia River Photography',
            '+site_tagline' => 'Vancouver photographer: architectural, interiors, editorial and people photography.',
            '+site_name_styled' => 'Sepia River <span class="grey">Photography</span>',
            '+site_url' => SITE_URL,
            '+site_css' => [
                'sepia' => '#896536',
                'blue' => '#264358',
                'transitions' => '
                    -webkit-transition: all 300ms ease;
                    -moz-transition: all 300ms ease;
                    -ms-transition: all 300ms ease;
                    -o-transition: all 300ms ease;
                    transition: all 300ms ease;
                ',
            ],
            '+assets_path' => PUBLIC_BASE_PATH . 'assets/',
            '+assets_url' => 'assets/',
            '+social' => [
                'twitter' => 'https://twitter.com/sepiariver',
                'linkedin' => 'https://www.linkedin.com/in/sepiariver',
                'gplus' => 'https://plus.google.com/+SepiariverCa',
            ],
        ],

        // Renderer settings
        'renderer' => [
            'service_name' => 'modxrenderer',
            'template_path' => APP_CORE_PATH . 'templates/',
            'chunk_path' => APP_CORE_PATH . 'chunks/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => APP_CORE_PATH . 'logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        // Cache
        'cache' => [
            'path' => APP_CORE_PATH . 'cache/',
            'expires' => -1,
        ],
    ],
];
