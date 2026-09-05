<?php

return [
    'name' => getenv('APP_NAME') ?: 'MyFolio',
    'url' => rtrim(getenv('APP_URL') ?: '/', '/'),
    'timezone' => getenv('APP_TIMEZONE') ?: 'Asia/Bangkok',
    'session_name' => getenv('SESSION_NAME') ?: 'myfolio_session',
];