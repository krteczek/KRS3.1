<?php
// config/texts/en.php
declare(strict_types=1);

return [
    'pages' => [
        'home' => 'Home page',
        'login' => 'Login',
        'article_detail' => 'Article: {title}',
        'article_not_found' => 'Article not found',
        'categories' => 'Categories',
        '404' => 'Page not found - {site_name}'
    ],
    'messages' => [
        'welcome' => 'Welcome to our system',
        'no_articles' => 'No articles to display',
        'article_not_found' => 'Article was not found',
        'invalid_csrf' => 'Invalid CSRF token',
        'login_failed' => 'Invalid login credentials',
        'login_success' => 'Login was successful',
        'logout_success' => 'Logout was successful',
        'category_not_found' => 'Category was not found',
        '404' => 'Sorry, but the requested page was not found.'
    ],
    'navigation' => [
        'home' => 'Home',
        'articles' => 'Articles',
        'categories' => 'Categories',
        'admin' => 'Administration',
        'login' => 'Login',
        'logout' => 'Logout',
        'welcome' => 'Welcome, {username}!'
    ],
    'admin' => [
        'navigation' => [
            'administration' => 'Administration',
            'dashboard' => 'Dashboard',
            'articles' => 'Articles',
            'categories' => 'Categories',
            'gallery' => 'Gallery',
            'users' => 'Users',
            'settings' => 'Settings'
        ],
        'titles' => [
            'administration' => 'Administration',
            'quick_actions' => 'Quick Actions',
            'manage_articles' => 'Manage Articles',
            'manage_categories' => 'Manage Categories',
            'manage_gallery' => 'Manage Gallery',
            'manage_users' => 'Manage Users'
        ],
        'actions' => [
            'create' => 'Create',
            'edit' => 'Edit',
            'delete' => 'Delete',
            'save' => 'Save',
            'cancel' => 'Cancel',
            'back' => 'Back'
        ],
        'messages' => [
            'created' => ' was successfully created',
            'updated' => ' was successfully updated',
            'deleted' => ' was deleted',
            'welcome_admin' => 'Welcome to the editorial system administration'
        ],
        'articles' => [
            'create' => 'Create article',
            'edit' => 'Edit article',
            'manage' => 'Manage articles',
            'active' => 'Active articles',
            'trash' => 'Trash',
            'categories' => 'Categories',
            'no_categories' => 'No categories',
            'form' => [
                'title' => 'Article title',
                'excerpt' => 'Introduction text',
                'content' => 'Article content',
                'status' => 'Status',
                'categories' => 'Categories',
                'create_button' => 'Create article',
                'save_button' => 'Save changes',
                'cancel' => 'Cancel',
                'back' => 'Back to articles list'
            ],
            'status' => [
                'draft' => 'Draft',
                'published' => 'Published',
                'archived' => 'Archived'
            ],
            'table' => [
                'title' => 'Title',
                'status' => 'Status',
                'author' => 'Author',
                'created' => 'Created',
                'deleted' => 'Deleted',
                'actions' => 'Actions',
                'unknown_author' => 'Unknown author',
            ],
            'actions' => [
                'edit' => 'Edit',
                'delete' => 'Delete',
                'restore' => 'Restore',
                'permanent_delete' => 'Permanently delete'
            ],
            'confirm' => [
                'delete' => 'Do you really want to delete the article',
                'permanent_delete' => 'Do you really want to permanently delete the article'
            ],
            'messages' => [
                'restored' => 'Article was successfully restored',
                'deleted' => 'Article was successfully deleted',
                'updated' => 'Article was successfully updated',
                'error' => 'An error occurred',
                'empty_trash' => 'Trash is empty',
                'empty_active' => 'No articles',
                'empty_text_trash' => 'There are no deleted articles in the trash.',
                'empty_text_active' => 'You don\'t have any articles yet. Create the first one!',
                'create_first' => 'Create first article'
            ]
        ],
        'categories' => [
            'manage' => 'Manage categories',
            'create' => 'Create category',
            'edit' => 'Edit category',
            'unknown_parent' => 'Unknown parent',
            'default_category_id' => 1,
            'active' => 'Active categories',
            'trash' => 'Trash',
            'form' => [
                'name' => 'Category name',
                'description' => 'Description',
                'parent' => 'Parent category',
                'no_parent' => 'None (main category)',
                'create_button' => 'Create category',
                'save_button' => 'Save changes',
                'cancel' => 'Cancel',
                'parent_help' => 'Select a parent category to create a hierarchy'
            ],
            'table' => [
                'name' => 'Name',
                'slug' => 'Slug',
                'description' => 'Description',
                'parent' => 'Parent',
                'actions' => 'Actions',
                'deleted' => 'Deleted'
            ],
            'actions' => [
                'edit' => 'Edit',
                'delete' => 'Delete',
                'restore' => 'Restore',
                'permanent_delete' => 'Permanently delete',
            ],
            'confirm' => [
                'delete' => 'Do you really want to delete the category',
                'permanent_delete' => 'Do you really want to permanently delete the category',
            ],
            'messages' => [
                'created' => 'Category was successfully created',
                'updated' => 'Category was successfully updated',
                'deleted' => 'Category was successfully deleted',
                'error' => 'An error occurred',
                'empty' => 'No categories',
                'empty_text' => 'You don\'t have any categories yet.',
                'create_first' => 'Create first category',
                'restored' => 'Category was successfully restored',
                'deleted' => 'Category was successfully moved to trash',
                'permanent_deleted' => 'Category was permanently deleted',
                'empty_trash' => 'Trash is empty',
                'empty_active' => 'No categories',
                'empty_text_trash' => 'There are no deleted categories in the trash.',
                'empty_text_active' => 'You don\'t have any categories yet. Create the first category!',
                'cannot_delete_default' => 'Cannot delete the default category'
            ],
        ],
        'gallery' => [
            'title' => 'Gallery',
            'upload_button' => 'Upload image',
            'create_gallery_button' => 'Create gallery',
            'manage_galleries' => 'Manage galleries',
            'back_to_gallery' => 'Back to gallery',
            'create_first' => 'Create first gallery',
            'upload_first' => 'Upload first image',
            'unknown_parent' => 'Unknown parent',
            'actions' => [
                'view' => 'View',
                'edit' => 'Edit',
                'delete' => 'Delete'
            ],
            'manage' => [
                'title' => 'Manage galleries',
                'empty_text' => 'You don\'t have any galleries yet.',
                'table' => [
                    'name' => 'Name',
                    'parent' => 'Parent gallery',
                    'images_count' => 'Images count',
                    'actions' => 'Actions'
                ]
            ],
            'create' => [
                'title' => 'Create gallery',
                'button' => 'Create gallery',
                'submit' => 'Create'
            ],
            'edit' => [
                'title' => 'Edit gallery',
                'button' => 'Edit',
                'submit' => 'Save changes'
            ],
            'view' => [
                'button' => 'View',
                'empty_images' => 'No images',
                'empty_images_text' => 'This gallery doesn\'t contain any images.'
            ],
            'delete' => [
                'button' => 'Delete',
                'confirm_title' => 'Delete gallery',
                'confirm_button' => 'Yes, delete gallery',
                'cancel_button' => 'Cancel',
                'confirm_message' => 'You are about to delete the gallery "{name}". This action is irreversible.',
                'warning' => 'Warning',
                'gallery_info' => 'Gallery information',
                'images_count' => 'Images count',
                'children_will_be_promoted' => 'Sub-galleries that will be moved ({count})',
                'children_promote_message' => 'The following sub-galleries will be moved to the top level:',
                'confirm_checkbox' => 'Yes, I want to delete the gallery "{name}" and move its sub-galleries to the top level.',
                'success_message' => 'Gallery "{name}" was successfully deleted. {children_count, plural, =0 {No sub-galleries were moved.} one {# sub-gallery was moved to the top level.} other {# sub-galleries were moved to the top level.}}',
                'error_not_found' => 'Gallery was not found.',
                'error_not_confirmed' => 'You must confirm the deletion.'
            ],
            'form' => [
                'name' => 'Gallery name',
                'description' => 'Description',
                'parent' => 'Parent gallery',
                'no_parent' => 'None (main gallery)',
                'parent_help' => 'Select a parent gallery to create a hierarchy',
                'parent_restrictions' => 'Note: A gallery cannot be a parent to itself or to a gallery that is already its descendant.',
                'create_button' => 'Create gallery',
                'save_button' => 'Save changes',
                'cancel' => 'Cancel'
            ],
            'upload' => [
                'title' => 'Upload image',
                'select_file' => 'Select file',
                'file_help' => 'Supported formats: JPG, PNG, GIF. Maximum size: 5MB.',
                'title_label' => 'Title',
                'title_placeholder' => 'Optional image title',
                'description' => 'Description',
                'description_placeholder' => 'Optional image description',
                'submit' => 'Upload',
                'cancel' => 'Cancel',
                'assign_to_galleries' => 'Assign to galleries'
            ],
            'empty' => [
                'title' => 'No galleries',
                'description' => 'You don\'t have any galleries yet. Create the first gallery!'
            ],
            'stats' => [
                'total' => 'Total images: {count}'
            ],
            'image' => [
                'size' => 'Size',
                'dimensions' => 'Dimensions',
                'delete' => 'Delete',
                'view' => 'View',
                'usage' => 'Image usage',
                'in_articles' => 'Articles using this image',
                'in_galleries' => 'Galleries containing this image',
                'no_usage' => 'This image is not used in any article or gallery'
            ],
            'confirm' => [
                'delete' => 'Do you really want to delete the gallery?'
            ]
        ],
        'layout' => [
            'administration' => 'Administration',
            'articles' => 'Articles',
            'categories' => 'Categories',
            'gallery' => 'Gallery',
            'users' => 'Users',
            'logout' => 'Logout',
            'all_rights_reserved' => 'All rights reserved.'
        ]
    ],
    'article' => [
        'author' => 'Author',
        'published' => 'Published',
        'read_more' => 'Read more',
        'back_to_articles' => 'Back to articles list',
        'no_articles' => 'No articles to display',
        'error_loading' => 'Error loading article',
        'categories' => 'Categories',
        'no_categories' => 'No category'
    ],
    'ui' => [
        'read_more' => 'Read more',
        'discover_articles' => 'Discover our latest articles',
        'author' => 'Author',
        'back_to_home' => 'Back to home page',
        'login' => 'Login',
        'username' => 'Username',
        'password' => 'Password',
        'search' => 'Search',
        'filter' => 'Filter',
        'all' => 'All'
    ],
    'errors' => [
        'login_failed' => 'Invalid login credentials',
        'csrf' => 'Invalid CSRF token',
        'invalid_request' => 'Invalid request',
        'not_found' => 'Page not found',
        'server_error' => 'Server error',
        'forbidden' => 'Access denied'
    ]
];