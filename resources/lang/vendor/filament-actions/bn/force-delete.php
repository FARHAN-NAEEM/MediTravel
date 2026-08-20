<?php

return [
    'single' => [
        'label' => 'ডিলিট',
        'modal' => [
            'heading' => ':label ডিলিট',
            'actions' => [
                'delete' => [
                    'label' => 'ডিলিট',
                ],
            ],
        ],
        'notifications' => [
            'deleted' => [
                'title' => 'ডিলিট হয়েছে',
            ],
        ],
    ],
    'multiple' => [
        'label' => 'নির্বাচিতগুলো ডিলিট',
        'modal' => [
            'heading' => 'নির্বাচিত :label ডিলিট',
            'actions' => [
                'delete' => [
                    'label' => 'ডিলিট',
                ],
            ],
        ],
        'notifications' => [
            'deleted' => [
                'title' => 'ডিলিট হয়েছে',
            ],
        ],
    ],
];
