<?php

namespace MyFolio\Controllers;

use MyFolio\Core\Controller;

final class ChallengeController extends Controller
{
    public function index(): void
    {
        $this->view('portfolio/show', ['title' => 'ประเด็นท้าทาย', 'categories' => []]);
    }
}