<?php

namespace MyFolio\Controllers;

use MyFolio\Core\Controller;

final class CommentController extends Controller
{
    public function store(): void
    {
        http_response_code(501);
        echo 'Comment storage is ready for database integration.';
    }
}