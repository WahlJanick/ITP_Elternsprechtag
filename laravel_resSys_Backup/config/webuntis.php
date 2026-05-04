<?php

return [
    'enabled' => (bool) env('WEBUNTIS_ENABLED', false),
    'base_url' => rtrim((string) env('WEBUNTIS_BASE_URL', ''), '/'),
    'school_id' => env('WEBUNTIS_SCHOOL_ID'),
    'bearer_token' => env('WEBUNTIS_BEARER_TOKEN'),
    'timeout' => (int) env('WEBUNTIS_TIMEOUT', 15),
    'teacher_endpoint' => env('WEBUNTIS_TEACHER_ENDPOINT', '/WebUntis/api/rest/extern/v2/teachers'),
    'class_endpoint' => env('WEBUNTIS_CLASS_ENDPOINT', '/ims/oneroster/v1p1/classes'),
    'enrollment_endpoint' => env('WEBUNTIS_ENROLLMENT_ENDPOINT'),
];
