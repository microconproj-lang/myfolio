<?php

namespace MyFolio\Controllers;

use MyFolio\Core\Auth;
use MyFolio\Core\Controller;
use MyFolio\Core\RBAC;
use MyFolio\Models\PaCategory;
use MyFolio\Models\PaItem;

final class AdminController extends Controller
{
    public function dashboard(): void
    {
        RBAC::require('admin', 'committee', 'public');
        $items = PaItem::all();
        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'user' => Auth::user(),
            'items' => $items,
            'categories' => PaCategory::ensureDefaults(),
            'stats' => [
                'items' => count($items),
                'published' => count(array_filter($items, static fn (array $item): bool => $item['status'] === 'published')),
                'categories' => count(PaCategory::all()),
            ],
        ]);
    }
}