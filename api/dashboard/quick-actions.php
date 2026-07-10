<?php
/**
 * GET /api/dashboard/quick-actions.php
 * Returns the shortcut links shown in the "Quick Actions" panel.
 *
 * These are just navigation shortcuts to your admin pages — there's no
 * DB table for "actions", so this is a small static list. Edit the
 * array below to add/remove/reorder actions or change where they link.
 */

header('Content-Type: application/json');

require_once __DIR__ . "/../../config/bootstrap.php";
requireLogin();

try {

    $actions = [
        [
            'icon'  => 'fa-plus',
            'color' => 'var(--accent)',
            'bg'    => 'rgba(232,93,4,.1)',
            'title' => 'Add New Product',
            'desc'  => 'Create a product listing',
            'link'  => 'products/create.php'
        ],
        [
            'icon'  => 'fa-tags',
            'color' => 'var(--success)',
            'bg'    => 'rgba(5,150,105,.1)',
            'title' => 'Create Offer',
            'desc'  => 'Set up a new deal',
            'link'  => 'offers/create.php'
        ],
        [
            'icon'  => 'fa-pen-nib',
            'color' => 'var(--info)',
            'bg'    => 'rgba(8,145,178,.1)',
            'title' => 'Write Blog Post',
            'desc'  => 'Publish an article',
            'link'  => 'blogs/create.php'
        ],
        [
            'icon'  => 'fa-user-plus',
            'color' => 'var(--warn)',
            'bg'    => 'rgba(217,119,6,.1)',
            'title' => 'Invite User',
            'desc'  => 'Send an invitation',
            'link'  => 'users/create.php'
        ],
        [
            'icon'  => 'fa-store',
            'color' => 'var(--accent)',
            'bg'    => 'rgba(232,93,4,.1)',
            'title' => 'Add Brand',
            'desc'  => 'Onboard a new brand',
            'link'  => 'brands/create.php'
        ]
    ];

    echo json_encode($actions);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load quick actions', 'message' => $e->getMessage()]);
}