<?php
// config/texts.php
return [
    'texts' => [
        'cs' => [
            'pages' => [
                'home' => 'Úvodní stránka',
                'login' => 'Přihlášení',
                'article_detail' => 'Článek: {title}',
                'article_not_found' => 'Článek nenalezen'
            ],
            'messages' => [
                'welcome' => 'Vítejte v našem systému',
                'no_articles' => 'Žádné články k zobrazení',
                'article_not_found' => 'Článek nebyl nalezen',
				'invalid_csrf' => 'Neplatný CSRF token',
				'login_failed' => 'Neplatné přihlašovací údaje',
				'login_success' => 'Přihlášení proběhlo úspěšně',
				'logout_success' => 'Odhlášení proběhlo úspěšně'
            ],
            'navigation' => [
                'home' => 'Úvod',
                'articles' => 'Články',
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
                    'gallery' => 'Galerie',
                    'users' => 'Uživatelé',
                    'settings' => 'Nastavení'
                ],
                'titles' => [
                    'administration' => 'Administrace',
                    'quick_actions' => 'Rychlé akce',
                    'manage_articles' => 'Správa článků',
                    'manage_gallery' => 'Správa galerie',
                    'manage_users' => 'Správa uživatelů'
                ],
                'actions' => [
                    'create' => 'Vytvořit',
                    'edit' => 'Upravit',
                    'delete' => 'Smazat',
                    'save' => 'Uložit'
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

                    // Formulářové prvky
                    'form' => [
                        'title' => 'Název článku',
                        'excerpt' => 'Úvodní text',
                        'content' => 'Obsah článku',
                        'status' => 'Stav',
                        'create_button' => 'Vytvořit článek',
                        'save_button' => 'Uložit změny',
                        'cancel' => 'Zrušit',
                        'back' => 'Zpět na seznam článků'
                    ],

                    // Stavy článků
                    'status' => [
                        'draft' => 'Koncept',
                        'published' => 'Publikováno',
                        'archived' => 'Archivováno'
                    ],

                    // Tabulka
                    'table' => [
                        'title' => 'Název',
                        'status' => 'Stav',
                        'author' => 'Autor',
                        'created' => 'Vytvořeno',
                        'deleted' => 'Smazáno',
                        'actions' => 'Akce'
                    ],

                    // Akce
                    'actions' => [
                        'edit' => 'Upravit',
                        'delete' => 'Smazat',
                        'restore' => 'Obnovit',
                        'permanent_delete' => 'Trvale smazat'
                    ],

                    // Potvrzovací dialogy
                    'confirm' => [
                        'delete' => 'Opravdu chcete smazat článek',
                        'permanent_delete' => 'Opravdu chcete trvale smazat článek'
                    ],

                    // Zprávy specifické pro články
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
            ],
            'article' => [
                'author' => 'Autor',
                'published' => 'Publikováno',
                'read_more' => 'Číst více',
                'back_to_articles' => 'Zpět na seznam článků',
                'no_articles' => 'Žádné články k zobrazení',
                'error_loading' => 'Chyba při načítání článku'
            ],
            'ui' => [
                'read_more' => 'Číst více',
                'discover_articles' => 'Objevte naše nejnovější články',
                'author' => 'Autor',
                'back_to_home' => 'Zpět na úvodní stránku',
				'login' => 'Přihlášení',
				'username' => 'Uživatelské jméno',
				'password' => 'Heslo',
            ],
			'errors' => [
				'login_failed' => 'Neplatné přihlašovací údaje',
			    'csrf' => 'Neplatný CSRF token',
			    'invalid_request' => 'Neplatný požadavek',
			]
        ],
        'en' => [
            'pages' => [
                'home' => 'Homepage',
                'login' => 'Login',
                'article_detail' => 'Article: {title}',
                'article_not_found' => 'Article not found'
            ],
            'messages' => [
                'welcome' => 'Welcome to our system',
                'no_articles' => 'No articles to display',
                'article_not_found' => 'Article not found',
				'invalid_csrf' => 'Invalid CSRF token',
				'login_failed' => 'Invalid login credentials',
				'login_success' => 'Login successful',
				'logout_success' => 'Logout successful',
			],
            'navigation' => [
                'home' => 'Home',
                'articles' => 'Articles',
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
                    'gallery' => 'Gallery',
                    'users' => 'Users',
                    'settings' => 'Settings'
                ],
                'titles' => [
                    'administration' => 'Administration',
                    'quick_actions' => 'Quick Actions',
                    'manage_articles' => 'Manage Articles',
                    'manage_gallery' => 'Manage Gallery',
                    'manage_users' => 'Manage Users'
                ],
                'actions' => [
                    'create' => 'Create',
                    'edit' => 'Edit',
                    'delete' => 'Delete',
                    'save' => 'Save'
                ],
                'messages' => [
                    'created' => ' was successfully created',
                    'updated' => ' was successfully updated',
                    'deleted' => ' was deleted',
                    'welcome_admin' => 'Welcome to the editorial system administration'
                ],
                'articles' => [
                    'create' => 'Create Article',
                    'edit' => 'Edit Article',
                    'manage' => 'Manage Articles',
                    'active' => 'Active Articles',
                    'trash' => 'Trash',

                    // Form elements
                    'form' => [
                        'title' => 'Article Title',
                        'excerpt' => 'Excerpt',
                        'content' => 'Content',
                        'status' => 'Status',
                        'create_button' => 'Create Article',
                        'save_button' => 'Save Changes',
                        'cancel' => 'Cancel',
                        'back' => 'Back to Articles'
                    ],

                    // Article statuses
                    'status' => [
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived'
                    ],

                    // Table
                    'table' => [
                        'title' => 'Title',
                        'status' => 'Status',
                        'author' => 'Author',
                        'created' => 'Created',
                        'deleted' => 'Deleted',
                        'actions' => 'Actions'
                    ],

                    // Actions
                    'actions' => [
                        'edit' => 'Edit',
                        'delete' => 'Delete',
                        'restore' => 'Restore',
                        'permanent_delete' => 'Permanently Delete'
                    ],

                    // Confirmation dialogs
                    'confirm' => [
                        'delete' => 'Are you sure you want to delete the article',
                        'permanent_delete' => 'Are you sure you want to permanently delete the article'
                    ],

                    // Messages specific for articles
                    'messages' => [
                        'restored' => 'Article was successfully restored',
                        'deleted' => 'Article was successfully deleted',
                        'updated' => 'Article was successfully updated',
                        'error' => 'An error occurred',
                        'empty_trash' => 'Trash is empty',
                        'empty_active' => 'No articles',
                        'empty_text_trash' => 'There are no deleted articles in trash.',
                        'empty_text_active' => 'You don\'t have any articles yet. Create your first article!',
                        'create_first' => 'Create first article'
                    ]
                ],
            ],
            'article' => [
                'author' => 'Author',
                'published' => 'Published',
                'read_more' => 'Read more',
                'back_to_articles' => 'Back to articles',
                'no_articles' => 'No articles to display',
                'error_loading' => 'Error loading article'
            ],
            'ui' => [
				'read_more' => 'Read more',
				'discover_articles' => 'Discover our latest articles',
				'author' => 'Author',
				'back_to_home' => 'Back to homepage',
				'login' => 'Login',
				'username' => 'Username',
				'password' => 'Password',
            ],
			'errors' => [
			    'login_failed' => 'Invalid login credentials',
			    'csrf' => 'Invalid CSRF token',
			    'invalid_request' => 'Invalid request'
			],
        ],
        'de' => [
            'pages' => [
                'home' => 'Startseite',
                'login' => 'Anmeldung',
                'article_detail' => 'Artikel: {title}',
                'article_not_found' => 'Artikel nicht gefunden'
            ],
            'messages' => [
                'welcome' => 'Willkommen in unserem System',
                'no_articles' => 'Keine Artikel zum Anzeigen',
                'article_not_found' => 'Artikel nicht gefunden',
				'invalid_csrf' => 'Ungültiges CSRF-Token',
				'login_failed' => 'Ungültige Anmeldedaten',
				'login_success' => 'Anmeldung erfolgreich',
				'logout_success' => 'Abmeldung erfolgreich',
            ],
            'navigation' => [
                'home' => 'Startseite',
                'articles' => 'Artikel',
                'admin' => 'Administration',
                'login' => 'Anmelden',
                'logout' => 'Abmelden',
                'welcome' => 'Willkommen, {username}!'
            ],
            'admin' => [
                'navigation' => [
                    'administration' => 'Administration',
                    'dashboard' => 'Dashboard',
                    'articles' => 'Artikel',
                    'gallery' => 'Galerie',
                    'users' => 'Benutzer',
                    'settings' => 'Einstellungen'
                ],
                'titles' => [
                    'administration' => 'Administration',
                    'quick_actions' => 'Schnellaktionen',
                    'manage_articles' => 'Artikel verwalten',
                    'manage_gallery' => 'Galerie verwalten',
                    'manage_users' => 'Benutzer verwalten'
                ],
                'actions' => [
                    'create' => 'Erstellen',
                    'edit' => 'Bearbeiten',
                    'delete' => 'Löschen',
                    'save' => 'Speichern'
                ],
                'messages' => [
                    'created' => ' wurde erfolgreich erstellt',
                    'updated' => ' wurde erfolgreich aktualisiert',
                    'deleted' => ' wurde gelöscht',
                    'welcome_admin' => 'Willkommen in der Redaktionssystem-Verwaltung'
                ],
                'articles' => [
                    'create' => 'Artikel erstellen',
                    'edit' => 'Artikel bearbeiten',
                    'manage' => 'Artikel verwalten',
                    'active' => 'Aktive Artikel',
                    'trash' => 'Papierkorb',

                    // Formularelemente
                    'form' => [
                        'title' => 'Artikel-Titel',
                        'excerpt' => 'Auszug',
                        'content' => 'Inhalt',
                        'status' => 'Status',
                        'create_button' => 'Artikel erstellen',
                        'save_button' => 'Änderungen speichern',
                        'cancel' => 'Abbrechen',
                        'back' => 'Zurück zur Artikelübersicht'
                    ],

                    // Artikelstatus
                    'status' => [
                        'draft' => 'Entwurf',
                        'published' => 'Veröffentlicht',
                        'archived' => 'Archiviert'
                    ],

                    // Tabelle
                    'table' => [
                        'title' => 'Titel',
                        'status' => 'Status',
                        'author' => 'Autor',
                        'created' => 'Erstellt',
                        'deleted' => 'Gelöscht',
                        'actions' => 'Aktionen'
                    ],

                    // Aktionen
                    'actions' => [
                        'edit' => 'Bearbeiten',
                        'delete' => 'Löschen',
                        'restore' => 'Wiederherstellen',
                        'permanent_delete' => 'Endgültig löschen'
                    ],

                    // Bestätigungsdialoge
                    'confirm' => [
                        'delete' => 'Möchten Sie den Artikel wirklich löschen',
                        'permanent_delete' => 'Möchten Sie den Artikel wirklich endgültig löschen'
                    ],

                    // Nachrichten spezifisch für Artikel
                    'messages' => [
                        'restored' => 'Artikel wurde erfolgreich wiederhergestellt',
                        'deleted' => 'Artikel wurde erfolgreich gelöscht',
                        'updated' => 'Artikel wurde erfolgreich aktualisiert',
                        'error' => 'Ein Fehler ist aufgetreten',
                        'empty_trash' => 'Papierkorb ist leer',
                        'empty_active' => 'Keine Artikel',
                        'empty_text_trash' => 'Es befinden sich keine gelöschten Artikel im Papierkorb.',
                        'empty_text_active' => 'Sie haben noch keine Artikel. Erstellen Sie Ihren ersten Artikel!',
                        'create_first' => 'Ersten Artikel erstellen'
                    ]
                ],
            ],
            'article' => [
                'author' => 'Autor',
                'published' => 'Veröffentlicht',
                'read_more' => 'Weiterlesen',
                'back_to_articles' => 'Zurück zur Artikelübersicht',
                'no_articles' => 'Keine Artikel zum Anzeigen',
                'error_loading' => 'Fehler beim Laden des Artikels'
            ],
            'ui' => [
				'read_more' => 'Weiterlesen',
				'discover_articles' => 'Entdecken Sie unsere neuesten Artikel',
				'author' => 'Autor',
				'back_to_home' => 'Zurück zur Startseite',
				'login' => 'Anmeldung',
				'username' => 'Benutzername',
				'password' => 'Passwort',            ],
	        ],
			'errors' => [
			    'login_failed' => 'Ungültige Anmeldedaten',
			    'csrf' => 'Ungültiges CSRF-Token',
			    'invalid_request' => 'Ungültige Anfrage'
			],

    ],
];