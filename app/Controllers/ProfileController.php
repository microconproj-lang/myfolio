<?php

namespace MyFolio\Controllers;

use MyFolio\Core\Controller;

final class ProfileController extends Controller
{
    public function show(): void
    {
        $this->view('profile/show', ['title' => 'โปรไฟล์']);
    }
}