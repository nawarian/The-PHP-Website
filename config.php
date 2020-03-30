<?php

use Illuminate\Support\Str;

return [
    'production' => false,
    'baseUrl' => '',
    'github' => 'https://github.com/nawarian/The-PHP-Website',
    'collections' => [
        'posts_en' => [
            'extends' => '_layouts.article',
            'isArticle' => true,
            'section' => 'content',
            'path' => function ($page) {
                return 'en/issue/' . Str::slug($page->get('slug'));
            },
            'sort' => '-createdAt',
        ],
        'posts_pt_br' => [
            'extends' => '_layouts.article',
            'isArticle' => true,
            'section' => 'content',
            'path' => function ($page) {
                return 'br/edicao/' . Str::slug($page->get('slug'));
            },
            'sort' => '-createdAt',
        ],
        'jobs_pt_br' => [
            'extends' => '_layouts.master',
            'section' => 'body',
            'path' => function ($page) {
                return 'br/vagas/' . Str::slug($page->get('slug'));
            },
            'sort' => '-createdAt',
        ],
    ],
];
