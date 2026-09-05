<?php

namespace MyFolio\Controllers;

use MyFolio\Core\Controller;

final class MenuController extends Controller
{
    public function index(): void
    {
        $this->view('admin/menu-manage', ['title' => 'จัดการเมนู']);
    }
}