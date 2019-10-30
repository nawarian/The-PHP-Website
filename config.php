<?php

use Illuminate\Support\Str;

return [
    'production' => false,
    'baseUrl' => '',
    'collections' => [
        'posts_en' => [
            'extends' => '_layouts.master',
            'section' => 'body',
            'path' => function ($page) {
                return 'en/issue/' . Str::slug($page->get('slug'));
            },
            'sort' => '-createdAt',
        ],
    ],
];
