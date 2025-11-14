<?php
// config/texts.php
return [
    'texts' => [
        'cs' => [
            'pages' => [
                'home' => 'Úvodní stránka',
                'login' => 'Přihlášení',
                'article_detail' => 'Článek: {title}',
                'article_not_found' => 'Článek nenalezen',
                'categories' => 'Kategorie'
            ],
            'messages' => [
                'welcome' => 'Vítejte v našem systému',
                'no_articles' => 'Žádné články k zobrazení',
                'article_not_found' => 'Článek nebyl nalezen',
                'invalid_csrf' => 'Neplatný CSRF token',
                'login_failed' => 'Neplatné přihlašovací údaje',
                'login_success' => 'Přihlášení proběhlo úspěšně',
                'logout_success' => 'Odhlášení proběhlo úspěšně',
                'category_not_found' => 'Kategorie nebyla nalezena'
            ],
            'navigation' => [
                'home' => 'Úvod',
                'articles' => 'Články',
                'categories' => 'Kategorie',
                'admin' => 'Administrace',
                'login' => 'Přihlásit',
                'logout' => 'Odhlásit',
                'welcome' => 'Vítejte, {username}!'
            ],
            'admin' => [
                'navigation' => [
                    'administration' => 'Administrace',
                    'dashboard' => 'Dashboard',
                    'articles' => 'Články',
                    'categories' => 'Kategorie',
                    'gallery' => 'Galerie',
                    'users' => 'Uživatelé',
                    'settings' => 'Nastavení'
                ],
                'titles' => [
                    'administration' => 'Administrace',
                    'quick_actions' => 'Rychlé akce',
                    'manage_articles' => 'Správa článků',
                    'manage_categories' => 'Správa kategorií',
                    'manage_gallery' => 'Správa galerie',
                    'manage_users' => 'Správa uživatelů'
                ],
                'actions' => [
                    'create' => 'Vytvořit',
                    'edit' => 'Upravit',
                    'delete' => 'Smazat',
                    'save' => 'Uložit',
                    'cancel' => 'Zrušit',
                    'back' => 'Zpět'
                ],
                'messages' => [
                    'created' => ' byl úspěšně vytvořen',
                    'updated' => ' byl úspěšně upraven',
                    'deleted' => ' byl smazán',
                    'welcome_admin' => 'Vítejte v administraci redakčního systému'
                ],
                'articles' => [
                    'create' => 'Vytvořit článek',
                    'edit' => 'Upravit článek',
                    'manage' => 'Správa článků',
                    'active' => 'Aktivní články',
                    'trash' => 'Koš',
                    'categories' => 'Kategorie',
                    'no_categories' => 'Žádné kategorie',
                    'form' => [
                        'title' => 'Název článku',
                        'excerpt' => 'Úvodní text',
                        'content' => 'Obsah článku',
                        'status' => 'Stav',
                        'categories' => 'Kategorie',
                        'create_button' => 'Vytvořit článek',
                        'save_button' => 'Uložit změny',
                        'cancel' => 'Zrušit',
                        'back' => 'Zpět na seznam článků'
                    ],
                    'status' => [
                        'draft' => 'Koncept',
                        'published' => 'Publikováno',
                        'archived' => 'Archivováno'
                    ],
                    'table' => [
                        'title' => 'Název',
                        'status' => 'Stav',
                        'author' => 'Autor',
                        'created' => 'Vytvořeno',
                        'deleted' => 'Smazáno',
                        'actions' => 'Akce',
                        'unknown_author' => 'Neznámý autor',
                    ],
                    'actions' => [
                        'edit' => 'Upravit',
                        'delete' => 'Smazat',
                        'restore' => 'Obnovit',
                        'permanent_delete' => 'Trvale smazat'
                    ],
                    'confirm' => [
                        'delete' => 'Opravdu chcete smazat článek',
                        'permanent_delete' => 'Opravdu chcete trvale smazat článek'
                    ],
                    'messages' => [
                        'restored' => 'Článek byl úspěšně obnoven',
                        'deleted' => 'Článek byl úspěšně smazán',
                        'updated' => 'Článek byl úspěšně aktualizován',
                        'error' => 'Došlo k chybě',
                        'empty_trash' => 'Koš je prázdný',
                        'empty_active' => 'Žádné články',
                        'empty_text_trash' => 'V koši nejsou žádné smazané články.',
                        'empty_text_active' => 'Zatím nemáte žádné články. Vytvořte první článek!',
                        'create_first' => 'Vytvořit první článek'
                    ]
                ],
                'categories' => [
                    'manage' => 'Správa kategorií',
                    'create' => 'Vytvořit kategorii',
                    'edit' => 'Upravit kategorii',
                    'unknown_parent' => 'Neznámý rodič',
                    'default_category_id' => 1,
                    'active' => 'Aktivní kategorie',
                    'trash' => 'Koš',
                    'form' => [
                        'name' => 'Název kategorie',
                        'description' => 'Popis',
                        'parent' => 'Nadřazená kategorie',
                        'no_parent' => 'Žádná (hlavní kategorie)',
                        'create_button' => 'Vytvořit kategorii',
                        'save_button' => 'Uložit změny',
                        'cancel' => 'Zrušit',
                        'parent_help' => 'Vyberte nadřazenou kategorii pro vytvoření hierarchie'
                    ],
                    'table' => [
                        'name' => 'Název',
                        'slug' => 'Slug',
                        'description' => 'Popis',
                        'parent' => 'Rodič',
                        'actions' => 'Akce',
                        'deleted' => 'Smazáno'
                    ],
                    'actions' => [
                        'edit' => 'Upravit',
                        'delete' => 'Smazat',
                        'restore' => 'Obnovit',
                        'permanent_delete' => 'Trvale smazat',
                    ],
                    'confirm' => [
                        'delete' => 'Opravdu chcete smazat kategorii',
                        'permanent_delete' => 'Opravdu chcete trvale smazat kategorii',
                    ],
                    'messages' => [
                        'created' => 'Kategorie byla úspěšně vytvořena',
                        'updated' => 'Kategorie byla úspěšně aktualizována',
                        'deleted' => 'Kategorie byla úspěšně smazána',
                        'error' => 'Došlo k chybě',
                        'empty' => 'Žádné kategorie',
                        'empty_text' => 'Zatím nemáte žádné kategorie.',
                        'create_first' => 'Vytvořit první kategorii',
                        'restored' => 'Kategorie byla úspěšně obnovena',
                        'deleted' => 'Kategorie byla úspěšně přesunuta do koše',
                        'permanent_deleted' => 'Kategorie byla trvale smazána',
                        'empty_trash' => 'Koš je prázdný',
                        'empty_active' => 'Žádné kategorie',
                        'empty_text_trash' => 'V koši nejsou žádné smazané kategorie.',
                        'empty_text_active' => 'Zatím nemáte žádné kategorie. Vytvořte první kategorii!',
                        'cannot_delete_default' => 'Nelze smazat výchozí kategorii'
                    ],
                ],
				'gallery' => [
				    'title' => 'Galerie',
				    'upload_button' => 'Nahrát obrázek',
				    'create_gallery_button' => 'Vytvořit galerii',
				    'manage_galleries' => 'Spravovat galerie',
				    'back_to_gallery' => 'Zpět na galerii',
				    'create_first' => 'Vytvořit první galerii',
				    'upload_first' => 'Nahrát první obrázek',

				    // TLAČÍTKA PRO AKCE - POUZE PRO TLAČÍTKA V SEZNAMU
				    'actions' => [
				        'view' => 'Zobrazit',
				        'edit' => 'Upravit',
				        'delete' => 'Smazat'
				    ],

				    // SPRÁVA GALERIÍ - TABULKOVÉ ZOBRAZENÍ
				    'manage' => [
				        'title' => 'Správa galerií',
				        'empty_text' => 'Zatím nemáte žádné galerie.',
				        'table' => [
				            'name' => 'Název',
				            'parent' => 'Nadřazená galerie',
				            'images_count' => 'Počet obrázků',
				            'actions' => 'Akce'
				        ]
				    ],

				    // VYTVOŘENÍ GALERIE
				    'create' => [
				        'title' => 'Vytvořit galerii',
				        'button' => 'Vytvořit galerii', // tlačítko v seznamu
				        'submit' => 'Vytvořit'          // tlačítko ve formuláři
				    ],

				    // ÚPRAVA GALERIE
				    'edit' => [
				        'title' => 'Upravit galerii',
				        'button' => 'Upravit',          // tlačítko v seznamu
				        'submit' => 'Uložit změny'      // tlačítko ve formuláři
				    ],

				    // ZOBRAZENÍ DETAILU GALERIE
				    'view' => [
				        'button' => 'Zobrazit',         // tlačítko v seznamu
				        'empty_images' => 'Žádné obrázky',
				        'empty_images_text' => 'Tato galerie neobsahuje žádné obrázky.'
				    ],

				    // SMAZÁNÍ GALERIE
				    'delete' => [
				        'button' => 'Smazat',           // tlačítko v seznamu
				        'confirm_title' => 'Smazat galerii',
				        'confirm_button' => 'Ano, smazat galerii',
				        'cancel_button' => 'Zrušit',
				        'confirm_message' => 'Chystáte se smazat galerii „{name}“. Tato akce je nevratná.',
				        'warning' => 'Varování',
				        'gallery_info' => 'Informace o galerii',
				        'images_count' => 'Počet obrázků',
				        'children_will_be_promoted' => 'Podgalerie, které budou přesunuty ({count})',
				        'children_promote_message' => 'Následující podgalerie budou přesunuty na nejvyšší úroveň:',
				        'confirm_checkbox' => 'Ano, chci smazat galerii „{name}“ a přesunout její podgalerie na nejvyšší úroveň.',
				        'success_message' => 'Galerie „{name}“ byla úspěšně smazána. {children_count, plural, =0 {Žádné podgalerie nebyly přesunuty.} one {# podgalerie byla přesunuta na nejvyšší úroveň.} few {# podgalerie byly přesunuty na nejvyšší úroveň.} other {# podgalerií bylo přesunuto na nejvyšší úroveň.}}',
				        'error_not_found' => 'Galerie nebyla nalezena.',
				        'error_not_confirmed' => 'Musíte potvrdit smazání galerie.'
				    ],

				    // FORMULÁŘE
				    'form' => [
				        'name' => 'Název galerie',
				        'description' => 'Popis',
				        'parent' => 'Nadřazená galerie',
				        'no_parent' => 'Žádná (hlavní galerie)',
				        'parent_help' => 'Vyberte nadřazenou galerii pro vytvoření hierarchie',
				        'create_button' => 'Vytvořit galerii',
				        'save_button' => 'Uložit změny',
				        'cancel' => 'Zrušit'
				    ],

				    // UPLOAD OBRÁZKŮ
				    'upload' => [
				        'title' => 'Nahrát obrázek',
				        'select_file' => 'Vybrat soubor',
				        'file_help' => 'Podporované formáty: JPG, PNG, GIF. Maximální velikost: 5MB.',
				        'title_label' => 'Název',
				        'title_placeholder' => 'Volitelný název obrázku',
				        'description' => 'Popis',
				        'description_placeholder' => 'Volitelný popis obrázku',
				        'submit' => 'Nahrát',
				        'cancel' => 'Zrušit',
				        'assign_to_galleries' => 'Přiřadit k galeriím'
				    ],

				    // PRÁZDNÝ STAV - POUZE PRO PRÁZDNÉ GALERIE
				    'empty' => [
				        'title' => 'Žádné galerie',
				        'description' => 'Zatím nemáte žádné galerie. Vytvořte první galerii!'
				    ],

				    // STATISTIKY
				    'stats' => [
				        'total' => 'Celkem obrázků: {count}'
				    ],

				    // OBRÁZKY
				    'image' => [
				        'size' => 'Velikost',
				        'dimensions' => 'Rozměry',
				        'delete' => 'Smazat',
				        'view' => 'Zobrazit',
				        'usage' => 'Využití obrázku',
				        'in_articles' => 'Články používající tento obrázek',
				        'in_galleries' => 'Galerie obsahující tento obrázek',
				        'no_usage' => 'Tento obrázek není použit v žádném článku ani galerii'
				    ]
				],
                'layout' => [
                    'administration' => 'Administrace',
                    'articles' => 'Články',
                    'categories' => 'Kategorie',
                    'gallery' => 'Galerie',
                    'users' => 'Uživatelé',
                    'logout' => 'Odhlásit se',
                    'all_rights_reserved' => 'Všechna práva vyhrazena.'
                ]
            ],
            'article' => [
                'author' => 'Autor',
                'published' => 'Publikováno',
                'read_more' => 'Číst více',
                'back_to_articles' => 'Zpět na seznam článků',
                'no_articles' => 'Žádné články k zobrazení',
                'error_loading' => 'Chyba při načítání článku',
                'categories' => 'Kategorie',
                'no_categories' => 'Bez kategorie'
            ],
            'ui' => [
                'read_more' => 'Číst více',
                'discover_articles' => 'Objevte naše nejnovější články',
                'author' => 'Autor',
                'back_to_home' => 'Zpět na úvodní stránku',
                'login' => 'Přihlášení',
                'username' => 'Uživatelské jméno',
                'password' => 'Heslo',
                'search' => 'Hledat',
                'filter' => 'Filtrovat',
                'all' => 'Vše'
            ],
            'errors' => [
                'login_failed' => 'Neplatné přihlašovací údaje',
                'csrf' => 'Neplatný CSRF token',
                'invalid_request' => 'Neplatný požadavek',
                'not_found' => 'Stránka nenalezena',
                'server_error' => 'Chyba serveru',
                'forbidden' => 'Přístup odepřen'
            ]
        ],
        'en' => [
            'pages' => [
                'home' => 'Home page',
                'login' => 'Login',
                'article_detail' => 'Article: {title}',
                'article_not_found' => 'Article not found',
                'categories' => 'Categories'
            ],
            'messages' => [
                'welcome' => 'Welcome to our system',
                'no_articles' => 'No articles to display',
                'article_not_found' => 'Article was not found',
                'invalid_csrf' => 'Invalid CSRF token',
                'login_failed' => 'Invalid login credentials',
                'login_success' => 'Login was successful',
                'logout_success' => 'Logout was successful',
                'category_not_found' => 'Category was not found'
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
                    'upload' => [
                        'title' => 'Upload image',
                        'select_file' => 'Select file',
                        'file_help' => 'Supported formats: JPG, PNG, GIF. Maximum size: 5MB.',
                        'title_label' => 'Title',
                        'title_placeholder' => 'Optional image title',
                        'description' => 'Description',
                        'description_placeholder' => 'Optional image description',
                        'submit' => 'Upload',
                        'cancel' => 'Cancel'
                    ],
                    'empty' => [
                        'title' => 'No images',
                        'description' => 'You don\'t have any images yet. Upload the first one!'
                    ],
                    'stats' => [
                        'total' => 'Total images: {count}'
                    ],
                    'image' => [
                        'size' => 'Size',
                        'dimensions' => 'Dimensions',
                        'delete' => 'Delete'
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
        ],
        'de' => [
            'pages' => [
                'home' => 'Startseite',
                'login' => 'Anmeldung',
                'article_detail' => 'Artikel: {title}',
                'article_not_found' => 'Artikel nicht gefunden',
                'categories' => 'Kategorien'
            ],
            'messages' => [
                'welcome' => 'Willkommen in unserem System',
                'no_articles' => 'Keine Artikel zum Anzeigen',
                'article_not_found' => 'Artikel wurde nicht gefunden',
                'invalid_csrf' => 'Ungültiges CSRF-Token',
                'login_failed' => 'Ungültige Anmeldedaten',
                'login_success' => 'Anmeldung war erfolgreich',
                'logout_success' => 'Abmeldung war erfolgreich',
                'category_not_found' => 'Kategorie wurde nicht gefunden'
            ],
            'navigation' => [
                'home' => 'Startseite',
                'articles' => 'Artikel',
                'categories' => 'Kategorien',
                'admin' => 'Verwaltung',
                'login' => 'Anmelden',
                'logout' => 'Abmelden',
                'welcome' => 'Willkommen, {username}!'
            ],
            'admin' => [
                'navigation' => [
                    'administration' => 'Verwaltung',
                    'dashboard' => 'Dashboard',
                    'articles' => 'Artikel',
                    'categories' => 'Kategorien',
                    'gallery' => 'Galerie',
                    'users' => 'Benutzer',
                    'settings' => 'Einstellungen'
                ],
                'titles' => [
                    'administration' => 'Verwaltung',
                    'quick_actions' => 'Schnellaktionen',
                    'manage_articles' => 'Artikel verwalten',
                    'manage_categories' => 'Kategorien verwalten',
                    'manage_gallery' => 'Galerie verwalten',
                    'manage_users' => 'Benutzer verwalten'
                ],
                'actions' => [
                    'create' => 'Erstellen',
                    'edit' => 'Bearbeiten',
                    'delete' => 'Löschen',
                    'save' => 'Speichern',
                    'cancel' => 'Abbrechen',
                    'back' => 'Zurück'
                ],
                'messages' => [
                    'created' => ' wurde erfolgreich erstellt',
                    'updated' => ' wurde erfolgreich aktualisiert',
                    'deleted' => ' wurde gelöscht',
                    'welcome_admin' => 'Willkommen in der Verwaltung des Redaktionssystems'
                ],
                'articles' => [
                    'create' => 'Artikel erstellen',
                    'edit' => 'Artikel bearbeiten',
                    'manage' => 'Artikel verwalten',
                    'active' => 'Aktive Artikel',
                    'trash' => 'Papierkorb',
                    'categories' => 'Kategorien',
                    'no_categories' => 'Keine Kategorien',
                    'form' => [
                        'title' => 'Artikel-Titel',
                        'excerpt' => 'Einleitungstext',
                        'content' => 'Artikelinhalt',
                        'status' => 'Status',
                        'categories' => 'Kategorien',
                        'create_button' => 'Artikel erstellen',
                        'save_button' => 'Änderungen speichern',
                        'cancel' => 'Abbrechen',
                        'back' => 'Zurück zur Artikelübersicht'
                    ],
                    'status' => [
                        'draft' => 'Entwurf',
                        'published' => 'Veröffentlicht',
                        'archived' => 'Archiviert'
                    ],
                    'table' => [
                        'title' => 'Titel',
                        'status' => 'Status',
                        'author' => 'Autor',
                        'created' => 'Erstellt',
                        'deleted' => 'Gelöscht',
                        'actions' => 'Aktionen',
                        'unknown_author' => 'Unbekannter Autor',
                    ],
                    'actions' => [
                        'edit' => 'Bearbeiten',
                        'delete' => 'Löschen',
                        'restore' => 'Wiederherstellen',
                        'permanent_delete' => 'Endgültig löschen'
                    ],
                    'confirm' => [
                        'delete' => 'Möchten Sie den Artikel wirklich löschen',
                        'permanent_delete' => 'Möchten Sie den Artikel wirklich endgültig löschen'
                    ],
                    'messages' => [
                        'restored' => 'Artikel wurde erfolgreich wiederhergestellt',
                        'deleted' => 'Artikel wurde erfolgreich gelöscht',
                        'updated' => 'Artikel wurde erfolgreich aktualisiert',
                        'error' => 'Ein Fehler ist aufgetreten',
                        'empty_trash' => 'Papierkorb ist leer',
                        'empty_active' => 'Keine Artikel',
                        'empty_text_trash' => 'Es befinden sich keine gelöschten Artikel im Papierkorb.',
                        'empty_text_active' => 'Sie haben noch keine Artikel. Erstellen Sie den ersten Artikel!',
                        'create_first' => 'Ersten Artikel erstellen'
                    ]
                ],
                'categories' => [
                    'manage' => 'Kategorien verwalten',
                    'create' => 'Kategorie erstellen',
                    'edit' => 'Kategorie bearbeiten',
                    'unknown_parent' => 'Unbekanntes Elternelement',
                    'default_category_id' => 1,
                    'active' => 'Aktive Kategorien',
                    'trash' => 'Papierkorb',
                    'form' => [
                        'name' => 'Kategoriename',
                        'description' => 'Beschreibung',
                        'parent' => 'Übergeordnete Kategorie',
                        'no_parent' => 'Keine (Hauptkategorie)',
                        'create_button' => 'Kategorie erstellen',
                        'save_button' => 'Änderungen speichern',
                        'cancel' => 'Abbrechen',
                        'parent_help' => 'Wählen Sie eine übergeordnete Kategorie, um eine Hierarchie zu erstellen'
                    ],
                    'table' => [
                        'name' => 'Name',
                        'slug' => 'Slug',
                        'description' => 'Beschreibung',
                        'parent' => 'Elternelement',
                        'actions' => 'Aktionen',
                        'deleted' => 'Gelöscht'
                    ],
                    'actions' => [
                        'edit' => 'Bearbeiten',
                        'delete' => 'Löschen',
                        'restore' => 'Wiederherstellen',
                        'permanent_delete' => 'Endgültig löschen',
                    ],
                    'confirm' => [
                        'delete' => 'Möchten Sie die Kategorie wirklich löschen',
                        'permanent_delete' => 'Möchten Sie die Kategorie wirklich endgültig löschen',
                    ],
                    'messages' => [
                        'created' => 'Kategorie wurde erfolgreich erstellt',
                        'updated' => 'Kategorie wurde erfolgreich aktualisiert',
                        'deleted' => 'Kategorie wurde erfolgreich gelöscht',
                        'error' => 'Ein Fehler ist aufgetreten',
                        'empty' => 'Keine Kategorien',
                        'empty_text' => 'Sie haben noch keine Kategorien.',
                        'create_first' => 'Erste Kategorie erstellen',
                        'restored' => 'Kategorie wurde erfolgreich wiederhergestellt',
                        'deleted' => 'Kategorie wurde erfolgreich in den Papierkorb verschoben',
                        'permanent_deleted' => 'Kategorie wurde endgültig gelöscht',
                        'empty_trash' => 'Papierkorb ist leer',
                        'empty_active' => 'Keine Kategorien',
                        'empty_text_trash' => 'Es befinden sich keine gelöschten Kategorien im Papierkorb.',
                        'empty_text_active' => 'Sie haben noch keine Kategorien. Erstellen Sie die erste Kategorie!',
                        'cannot_delete_default' => 'Standardkategorie kann nicht gelöscht werden'
                    ],
                ],
                'gallery' => [
                    'title' => 'Galerie',
                    'upload_button' => 'Bild hochladen',
                    'create_gallery_button' => 'Galerie erstellen',
                    'upload' => [
                        'title' => 'Bild hochladen',
                        'select_file' => 'Datei auswählen',
                        'file_help' => 'Unterstützte Formate: JPG, PNG, GIF. Maximale Größe: 5MB.',
                        'title_label' => 'Titel',
                        'title_placeholder' => 'Optionaler Bildtitel',
                        'description' => 'Beschreibung',
                        'description_placeholder' => 'Optionale Bildbeschreibung',
                        'submit' => 'Hochladen',
                        'cancel' => 'Abbrechen'
                    ],
                    'empty' => [
                        'title' => 'Keine Bilder',
                        'description' => 'Sie haben noch keine Bilder. Laden Sie das erste Bild hoch!'
                    ],
                    'stats' => [
                        'total' => 'Gesamtanzahl Bilder: {count}'
                    ],
                    'image' => [
                        'size' => 'Größe',
                        'dimensions' => 'Abmessungen',
                        'delete' => 'Löschen'
                    ]
                ],
                'layout' => [
                    'administration' => 'Verwaltung',
                    'articles' => 'Artikel',
                    'categories' => 'Kategorien',
                    'gallery' => 'Galerie',
                    'users' => 'Benutzer',
                    'logout' => 'Abmelden',
                    'all_rights_reserved' => 'Alle Rechte vorbehalten.'
                ]
            ],
            'article' => [
                'author' => 'Autor',
                'published' => 'Veröffentlicht',
                'read_more' => 'Weiterlesen',
                'back_to_articles' => 'Zurück zur Artikelübersicht',
                'no_articles' => 'Keine Artikel zum Anzeigen',
                'error_loading' => 'Fehler beim Laden des Artikels',
                'categories' => 'Kategorien',
                'no_categories' => 'Keine Kategorie'
            ],
            'ui' => [
                'read_more' => 'Weiterlesen',
                'discover_articles' => 'Entdecken Sie unsere neuesten Artikel',
                'author' => 'Autor',
                'back_to_home' => 'Zurück zur Startseite',
                'login' => 'Anmeldung',
                'username' => 'Benutzername',
                'password' => 'Passwort',
                'search' => 'Suchen',
                'filter' => 'Filtern',
                'all' => 'Alle'
            ],
            'errors' => [
                'login_failed' => 'Ungültige Anmeldedaten',
                'csrf' => 'Ungültiges CSRF-Token',
                'invalid_request' => 'Ungültige Anfrage',
                'not_found' => 'Seite nicht gefunden',
                'server_error' => 'Serverfehler',
                'forbidden' => 'Zugriff verweigert'
            ]
        ]
    ]
];