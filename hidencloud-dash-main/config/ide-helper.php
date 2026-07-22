<?php

return [
    'extra' => [
        'Eloquent' => [
            'defaultConnection' => null,
        ],
    ],

    'include_fluent' => false,
    'write_model_magic_where' => true,
    'write_model_external_builder_methods' => true,
    'write_eloquent_model_mixins' => true,
    'include_factory_builders' => true,
    'include_helpers' => true,
    'include_macros' => true,
    'include_constants' => true,
    'include_class_aliases' => true,

    'magic' => [
        'App\Facades\Theme' => \App\Facades\Theme::class,
        'App\Facades\AdminTheme' => \App\Facades\AdminTheme::class,
    ],
];
