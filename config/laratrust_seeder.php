<?php
return [
    'role_structure' => [
        'super_admin' => [
            'categories' => 'c,r,u,d',
            'products' => 'c,r,u,d',
            'clients' => 'c,r,u,d',
            'orders' => 'c,r,u,d',
            'users' => 'c,r,u,d',
            'suppliers' => 'c,r,u,d',
            'expenses' => 'c,r,u,d',
            'cash' => 'c,r,u,d',
            'reports' => 'c,r,u,d',
            'settings' => 'c,r,u,d',
            'stock' => 'c,r,u,d',
            'purchases' => 'c,r,u,d',
            'backup' => 'c,r,u,d',
            'trash' => 'c,r,u,d',
        ],
        'admin' => [
            'categories' => 'c,r,u,d',
            'products' => 'c,r,u,d',
            'clients' => 'c,r,u,d',
            'orders' => 'c,r,u,d',
            'suppliers' => 'c,r,u,d',
            'expenses' => 'c,r,u,d',
            'cash' => 'r',
            'reports' => 'r',
            'stock' => 'r',
            'purchases' => 'c,r,u,d',
        ],
    ],
    'permissions_map' => [
        'c' => 'create',
        'r' => 'read',
        'u' => 'update',
        'd' => 'delete',
    ],
];
