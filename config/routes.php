<?php
declare(strict_types=1);

/**
 * @file routes.php
 * Route configuration for KRS3 CMS
 * /config/routes.php
 */

namespace App\Config;

return [
    // FRONTEND ROUTES - GET
    'GET' => [
        '/' => [
            'controller' => 'HomeController',
            'method' => 'showHomepage',
            'name' => 'homepage',
            'csrf' => false,
            'requires_auth' => false
        ],
        '/article/{slug}' => [
            'controller' => 'HomeController',
            'method' => 'showArticleDetail', 
            'name' => 'article.detail',
            'csrf' => false,
            'requires_auth' => false
        ],
        '/category/{slug}' => [
            'controller' => 'HomeController',
            'method' => 'showCategoryArticles',
            'name' => 'category.articles',
            'csrf' => false,
            'requires_auth' => false
        ],
        '/login' => [
            'controller' => 'AuthController',
            'method' => 'showLoginForm',
            'name' => 'login.form',
            'csrf' => true,
            'requires_auth' => false
        ],
        '/logout' => [
            'controller' => 'AuthController',
            'method' => 'logout',
            'name' => 'logout',
            'csrf' => false,
            'requires_auth' => false
        ],
    ],

    // FRONTEND ROUTES - POST
    'POST' => [
        '/login' => [
            'controller' => 'AuthController', 
            'method' => 'processLogin',
            'name' => 'login.process',
            'csrf' => true,
            'requires_auth' => false
        ],
    ],

    // ADMIN ROUTES - GET (vyžadují autentizaci)
    'ADMIN_GET' => [
        '/admin' => [
            'controller' => 'AdminController',
            'method' => 'showDashboard',
            'name' => 'admin.dashboard',
            'requires_auth' => true,
            'csrf' => false
        ],
        
        // ARTICLES
        '/admin/articles' => [
            'controller' => 'ArticleController',
            'method' => 'showArticles', 
            'name' => 'admin.articles.list',
            'requires_auth' => true,
            'csrf' => false
        ],
        '/admin/articles/new' => [
            'controller' => 'ArticleController',
            'method' => 'showCreateForm',
            'name' => 'admin.articles.create.form',
            'requires_auth' => true,
            'csrf' => false
        ],
        '/admin/articles/edit/{id}' => [
            'controller' => 'ArticleController',
            'method' => 'showEditForm',
            'name' => 'admin.articles.edit.form',
            'requires_auth' => true,
            'csrf' => false,
            'params' => ['id' => '\d+']
        ],
        
        // CATEGORIES
        '/admin/categories' => [
            'controller' => 'CategoryController',
            'method' => 'index',
            'name' => 'admin.categories.list',
            'requires_auth' => true,
            'csrf' => false
        ],
        '/admin/categories/create' => [
            'controller' => 'CategoryController',
            'method' => 'create',
            'name' => 'admin.categories.create.form',
            'requires_auth' => true,
            'csrf' => false
        ],
        '/admin/categories/edit/{id}' => [
            'controller' => 'CategoryController',
            'method' => 'edit',
            'name' => 'admin.categories.edit.form',
            'requires_auth' => true,
            'csrf' => false,
            'params' => ['id' => '\d+']
        ],
        
        // GALLERY
        '/admin/gallery' => [
            'controller' => 'GalleryController',
            'method' => 'index',
            'name' => 'admin.gallery.list',
            'requires_auth' => true,
            'csrf' => false
        ],
        '/admin/gallery/create' => [
            'controller' => 'GalleryController',
            'method' => 'create',
            'name' => 'admin.gallery.create.form',
            'requires_auth' => true,
            'csrf' => false
        ],
        '/admin/gallery/edit/{id}' => [
            'controller' => 'GalleryController',
            'method' => 'edit',
            'name' => 'admin.gallery.edit.form',
            'requires_auth' => true,
            'csrf' => false,
            'params' => ['id' => '\d+']
        ],
        '/admin/gallery/view/{id}' => [
            'controller' => 'GalleryController',
            'method' => 'view',
            'name' => 'admin.gallery.view',
            'requires_auth' => true,
            'csrf' => false,
            'params' => ['id' => '\d+']
        ],
        '/admin/gallery/confirm-delete/{id}' => [
            'controller' => 'GalleryController',
            'method' => 'confirmDelete',
            'name' => 'admin.gallery.confirm.delete',
            'requires_auth' => true,
            'csrf' => false,
            'params' => ['id' => '\d+']
        ],
        '/admin/gallery/confirm-permanent-delete/{id}' => [
            'controller' => 'GalleryController',
            'method' => 'confirmPermanentDelete',
            'name' => 'admin.gallery.confirm.permanent.delete',
            'requires_auth' => true,
            'csrf' => false,
            'params' => ['id' => '\d+']
        ],
        
        // IMAGES
        '/admin/images' => [
            'controller' => 'ImagesController',
            'method' => 'manage',
            'name' => 'admin.images.manage',
            'requires_auth' => true,
            'csrf' => false
        ],
        '/admin/images/upload' => [
            'controller' => 'ImagesController',
            'method' => 'upload',
            'name' => 'admin.images.upload.form',
            'requires_auth' => true,
            'csrf' => false
        ],
        '/admin/images/edit/{id}' => [
            'controller' => 'ImagesController',
            'method' => 'edit',
            'name' => 'admin.images.edit.form',
            'requires_auth' => true,
            'csrf' => false,
            'params' => ['id' => '\d+']
        ],
        '/admin/images/confirm-permanent-delete/{id}' => [
            'controller' => 'ImagesController',
            'method' => 'confirmPermanentDeleteImage',
            'name' => 'admin.images.confirm.permanent.delete',
            'requires_auth' => true,
            'csrf' => false,
            'params' => ['id' => '\d+']
        ],
        '/admin/images/featured-image-modal' => [
            'controller' => 'ImagesController',
            'method' => 'featuredImageModal',
            'name' => 'admin.images.featured.modal',
            'requires_auth' => true,
            'csrf' => false
        ],
        '/admin/images/image-info/{id}' => [
            'controller' => 'ImagesController',
            'method' => 'getImageInfo',
            'name' => 'admin.images.info',
            'requires_auth' => true,
            'csrf' => false,
            'params' => ['id' => '\d+']
        ],
    ],

    // ADMIN ROUTES - POST (vyžadují autentizaci + CSRF)
    'ADMIN_POST' => [
        // ARTICLES
        '/admin/articles/create' => [
            'controller' => 'ArticleController',
            'method' => 'createArticle',
            'name' => 'admin.articles.create',
            'requires_auth' => true,
            'csrf' => true
        ],
        '/admin/articles/update/{id}' => [
            'controller' => 'ArticleController',
            'method' => 'updateArticle',
            'name' => 'admin.articles.update',
            'requires_auth' => true,
            'csrf' => true,
            'params' => ['id' => '\d+']
        ],
        '/admin/articles/delete/{id}' => [
            'controller' => 'ArticleController',
            'method' => 'deleteArticle',
            'name' => 'admin.articles.delete',
            'requires_auth' => true, 
            'csrf' => true,
            'params' => ['id' => '\d+']
        ],
        '/admin/articles/restore/{id}' => [
            'controller' => 'ArticleController',
            'method' => 'restoreArticle',
            'name' => 'admin.articles.restore',
            'requires_auth' => true,
            'csrf' => true,
            'params' => ['id' => '\d+']
        ],
        '/admin/articles/permanent-delete/{id}' => [
            'controller' => 'ArticleController',
            'method' => 'permanentDeleteArticle',
            'name' => 'admin.articles.permanent.delete',
            'requires_auth' => true,
            'csrf' => true,
            'params' => ['id' => '\d+']
        ],
        
        // CATEGORIES
        '/admin/categories/store' => [
            'controller' => 'CategoryController',
            'method' => 'store',
            'name' => 'admin.categories.store',
            'requires_auth' => true,
            'csrf' => true
        ],
        '/admin/categories/update/{id}' => [
            'controller' => 'CategoryController',
            'method' => 'update',
            'name' => 'admin.categories.update',
            'requires_auth' => true,
            'csrf' => true,
            'params' => ['id' => '\d+']
        ],
        '/admin/categories/delete/{id}' => [
            'controller' => 'CategoryController',
            'method' => 'delete',
            'name' => 'admin.categories.delete',
            'requires_auth' => true,
            'csrf' => true,
            'params' => ['id' => '\d+']
        ],
        '/admin/categories/restore/{id}' => [
            'controller' => 'CategoryController',
            'method' => 'restore',
            'name' => 'admin.categories.restore',
            'requires_auth' => true,
            'csrf' => true,
            'params' => ['id' => '\d+']
        ],
        '/admin/categories/permanent-delete/{id}' => [
            'controller' => 'CategoryController',
            'method' => 'permanentDelete',
            'name' => 'admin.categories.permanent.delete',
            'requires_auth' => true,
            'csrf' => true,
            'params' => ['id' => '\d+']
        ],
        
        // GALLERY
        '/admin/gallery/store' => [
            'controller' => 'GalleryController',
            'method' => 'store',
            'name' => 'admin.gallery.store',
            'requires_auth' => true,
            'csrf' => true
        ],
        '/admin/gallery/update/{id}' => [
            'controller' => 'GalleryController',
            'method' => 'update',
            'name' => 'admin.gallery.update',
            'requires_auth' => true,
            'csrf' => true,
            'params' => ['id' => '\d+']
        ],
        '/admin/gallery/delete/{id}' => [
            'controller' => 'GalleryController',
            'method' => 'delete',
            'name' => 'admin.gallery.delete',
            'requires_auth' => true,
            'csrf' => true,
            'params' => ['id' => '\d+']
        ],
        '/admin/gallery/restore/{id}' => [
            'controller' => 'GalleryController',
            'method' => 'restore',
            'name' => 'admin.gallery.restore',
            'requires_auth' => true,
            'csrf' => true,
            'params' => ['id' => '\d+']
        ],
        '/admin/gallery/permanent-delete/{id}' => [
            'controller' => 'GalleryController',
            'method' => 'permanentDelete',
            'name' => 'admin.gallery.permanent.delete',
            'requires_auth' => true,
            'csrf' => true,
            'params' => ['id' => '\d+']
        ],
        
        // IMAGES
        '/admin/images/upload-image' => [
            'controller' => 'ImagesController',
            'method' => 'uploadImage',
            'name' => 'admin.images.upload',
            'requires_auth' => true,
            'csrf' => true
        ],
        '/admin/images/update/{id}' => [
            'controller' => 'ImagesController',
            'method' => 'update',
            'name' => 'admin.images.update',
            'requires_auth' => true,
            'csrf' => true,
            'params' => ['id' => '\d+']
        ],
        '/admin/images/delete/{id}' => [
            'controller' => 'ImagesController',
            'method' => 'deleteImage',
            'name' => 'admin.images.delete',
            'requires_auth' => true,
            'csrf' => true,
            'params' => ['id' => '\d+']
        ],
        '/admin/images/restore/{id}' => [
            'controller' => 'ImagesController',
            'method' => 'restoreImage',
            'name' => 'admin.images.restore',
            'requires_auth' => true,
            'csrf' => true,
            'params' => ['id' => '\d+']
        ],
        '/admin/images/permanent-delete/{id}' => [
            'controller' => 'ImagesController',
            'method' => 'permanentDeleteImage',
            'name' => 'admin.images.permanent.delete',
            'requires_auth' => true,
            'csrf' => true,
            'params' => ['id' => '\d+']
        ],
        '/admin/images/select-featured-image/{id}' => [
            'controller' => 'ImagesController',
            'method' => 'selectFeaturedImage',
            'name' => 'admin.images.select.featured',
            'requires_auth' => true,
            'csrf' => true,
            'params' => ['id' => '\d+']
        ],
    ],
];