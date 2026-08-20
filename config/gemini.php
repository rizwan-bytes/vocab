<?php

return [
    'api_key' => env('GEMINI_API_KEY'),
    'model' => env('GEMINI_MODEL', 'gemini-flash-lite-latest'),
    'timeout' => env('GEMINI_TIMEOUT', 25),
    'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
];
