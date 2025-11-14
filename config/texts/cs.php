<?php
// config/texts/cs.php
declare(strict_types=1);

return [
    'pages' => [
        'home' => 'Úvodní stránka',
        'login' => 'Přihlášení',
        'article_detail' => 'Článek: {title}',
        'article_not_found' => 'Článek nenalezen',
        'categories' => 'Kategorie',
        '404' => 'Stránka nenalezena - {site_name}'
    ],
    'messages' => [
        'welcome' => 'Vítejte v našem systému',
        'no_articles' => 'Žádné články k zobrazení',
        'article_not_found' => 'Článek nebyl nalezen',
        'invalid_csrf' => 'Neplatný CSRF token',
        'login_failed' => 'Neplatné přihlašovací údaje',
        'login_success' => 'Přihlášení proběhlo úspěšně',
        'logout_success' => 'Odhlášení proběhlo úspěšně',
        'category_not_found' => 'Kategorie nebyla nalezena',
        '404' => 'Omlouváme se, ale požadovaná stránka nebyla nalezena.'
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
            'settings' => 'Nastavení',
            'images' => 'Správa obrázků'
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
            'unknown_parent' => 'Neznámý rodič',
            'tabs' => [
                'active' => 'Aktivní galerie',
                'trash' => 'Koš',
            ],
            'trash' => [
                'empty_title' => 'Koš je prázdný',
                'empty_text' => 'Žádné galerie nebyly smazány.',
            ],
            'status' => [
                'active' => 'Aktivní',
                'deleted' => 'V koši',
            ],
            'restore' => [
                'button' => 'Obnovit'
            ],
            'actions' => [
                'view' => 'Zobrazit',
                'edit' => 'Upravit',
                'delete' => 'Smazat'
            ],
            'manage' => [
                'title' => 'Správa galerií',
                'empty_text' => 'Zatím nemáte žádné galerie.',
                'table' => [
                    'name' => 'Název',
                    'parent' => 'Nadřazená galerie',
                    'images_count' => 'Počet obrázků',
                    'actions' => 'Akce',
                    'deleted_at' => 'Smazáno',
                ]
            ],
            'create' => [
                'title' => 'Vytvořit galerii',
                'button' => 'Vytvořit galerii',
                'submit' => 'Vytvořit'
            ],
            'edit' => [
                'title' => 'Upravit galerii',
                'button' => 'Upravit',
                'submit' => 'Uložit změny'
            ],
            'view' => [
                'button' => 'Zobrazit',
                'empty_images' => 'Žádné obrázky',
                'empty_images_text' => 'Tato galerie neobsahuje žádné obrázky.'
            ],
            'delete' => [
                'button' => 'Smazat',
                'confirm_title' => 'Smazat galerii',
                'confirm_button' => 'Ano, smazat galerii',
                'cancel_button' => 'Zrušit',
                'confirm_message' => 'Chystáte se smazat galerii „{name}". Tato akce je nevratná.',
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
            'permanent_delete' => [
                'button' => 'Trvale smazat',
                'confirm_title' => 'Trvalé smazání galerie',
                'warning' => 'Varování: Trvalé smazání',
                'confirm_message' => 'Chystáte se trvale smazat galerii „{name}".
',
                'irreversible' => 'Tato akce je nevratná a nelze ji vrátit zpět!',
                'gallery_info' => 'Informace o galerii',
                'images_warning' => 'Pozor: Galerie obsahuje obrázky',
                'images_warning_text' => 'Všechny vazby na obrázky budou odstraněny, ale samotné obrázky zůstanou v systému.',
                'confirm_checkbox' => 'Ano, chci trvale smazat galerii „{name}“ a všechny její vazby na obrázky.',
                'confirm_button' => 'Trvale smazat',
                'cancel_button' => 'Zrušit'
            ],
            'form' => [
				'featured_image' => 'Tématický obrázek',
                'name' => 'Název galerie',
                'description' => 'Popis',
                'parent' => 'Nadřazená galerie',
                'no_parent' => 'Žádná (hlavní galerie)',
                'parent_help' => 'Vyberte nadřazenou galerii pro vytvoření hierarchie',
                'parent_restrictions' => 'Poznámka: Galerie nemůže být nadřazená sama sobě ani galerii, která je již jejím potomkem.',
                'create_button' => 'Vytvořit galerii',
                'save_button' => 'Uložit změny',
                'cancel' => 'Zrušit'
            ],
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
            'empty' => [
                'title' => 'Žádné galerie',
                'description' => 'Zatím nemáte žádné galerie. Vytvořte první galerii!'
            ],
            'stats' => [
                'total' => 'Celkem obrázků: {count}'
            ],
            'image' => [
                'size' => 'Velikost',
                'dimensions' => 'Rozměry',
                'delete' => 'Smazat',
                'view' => 'Zobrazit',
                'usage' => 'Využití obrázku',
                'in_articles' => 'Články používající tento obrázek',
                'in_galleries' => 'Galerie obsahující tento obrázek',
                'no_usage' => 'Tento obrázek není použit v žádném článku ani galerii',
                'confirm_delete' => 'Opravdu chcete smazat tento obrázek?'
            ],
	        'featured_image' => [
	            'button' => 'Vybrat tématický obrázek',
	            'modal_title' => 'Vyberte tématický obrázek',
	            'search_placeholder' => 'Hledat obrázky...',
	            'no_images' => 'Žádné obrázky nenalezeny',
	            'select' => 'Vybrat',
	            'remove' => 'Odstranit',
	            'current' => 'Aktuální obrázek',
	            'no_image_selected' => 'Není vybrán žádný obrázek',
	            'preview' => 'Náhled',
	            'dimensions' => 'Rozměry',
	            'size' => 'Velikost',
	            'uploaded' => 'Nahráno'
	        ],
            'confirm' => [
                'delete' => 'Opravdu chcete smazat galerii?',
                'permanent_delete' => 'Opravdu chcete trvale smazat tuto galerii? Tato akce je nevratná.',
            ],
            'back_to_trash' => 'Zpět do koše',
        ],
        'images' => [
            'manage' => [
                'title' => 'Správa obrázků',
                'empty_text' => 'Zatím nemáte žádné obrázky.',
                'table' => [
                    'image' => 'Obrázek',
                    'name' => 'Název',
                    'description' => 'Popis',
                    'size' => 'Velikost',
                    'dimensions' => 'Rozměry',
                    'status' => 'Status',
                    'deleted_at' => 'Smazáno',
                    'actions' => 'Akce'
                ]
            ],
            'upload_button' => 'Nahrát obrázek',
            'back_to_images' => 'Zpět na obrázky',
            'back_to_trash' => 'Zpět do koše',
            'tabs' => [
                'active' => 'Aktivní obrázky',
                'trash' => 'Koš'
            ],
            'trash' => [
                'empty_title' => 'Koš je prázdný',
                'empty_text' => 'Žádné obrázky nebyly smazány.'
            ],
            'empty' => [
                'title' => 'Žádné obrázky',
                'text' => 'Zatím nemáte žádné obrázky.'
            ],
            'status' => [
                'active' => 'Aktivní',
                'deleted' => 'V koši'
            ],
            'upload' => [
                'title' => 'Nahrát obrázek',
                'select_file' => 'Vybrat soubor',
                'file_help' => 'Podporované formáty: JPG, PNG, GIF, WebP. Maximální velikost: 10MB.',
                'title_label' => 'Název',
                'title_placeholder' => 'Volitelný název obrázku',
                'description' => 'Popis',
                'description_placeholder' => 'Volitelný popis obrázku',
                'assign_to_galleries' => 'Přiřadit k galeriím',
                'submit' => 'Nahrát',
                'cancel' => 'Zrušit',
                'csrf_error' => 'Neplatný CSRF token'
            ],
            'edit' => [
                'title' => 'Upravit obrázek',
                'image_preview' => 'Náhled obrázku',
                'view_original' => 'Zobrazit originál',
                'file_info' => 'Informace o souboru',
                'uploaded' => 'Nahráno',
                'assign_to_galleries' => 'Přiřadit k galeriím',
                'galleries_help' => 'Vyberte galerie, do kterých má být obrázek zařazen',
                'usage_info' => 'Využití obrázku',
                'used_in_articles' => 'Použito v článcích',
                'used_in_galleries' => 'Použito v galeriích',
                'submit' => 'Uložit změny',
                'cancel' => 'Zrušit',
                'success_message' => 'Obrázek byl úspěšně aktualizován',
                'error_message' => 'Chyba při aktualizaci obrázku',
                'csrf_error' => 'Neplatný CSRF token'
            ],
            'form' => [
                'name' => 'Název',
                'original_name' => 'Původní název',
                'description' => 'Popis',
                'size' => 'Velikost',
                'dimensions' => 'Rozměry',
                'format' => 'Formát'
            ],
            'actions' => [
                'view' => 'Zobrazit',
                'edit' => 'Upravit',
                'delete' => 'Smazat',
                'restore' => 'Obnovit',
                'permanent_delete' => 'Trvale smazat'
            ],
            'confirm' => [
                'delete' => 'Opravdu chcete smazat tento obrázek?'
            ],
            'permanent_delete' => [
                'confirm_title' => 'Trvalé smazání obrázku',
                'warning' => 'Varování: Trvalé smazání',
                'confirm_message' => 'Chystáte se trvale smazat obrázek „{name}".
',
                'irreversible' => 'Tato akce je nevratná a nelze ji vrátit zpět!',
                'image_info' => 'Informace o obrázku',
                'usage_warning' => 'Pozor: Obrázek je používán',
                'in_articles' => 'Články',
                'in_galleries' => 'Galerie',
                'confirm_checkbox' => 'Ano, chci trvale smazat obrázek „{name}“ a všechny jeho soubory.',
                'confirm_button' => 'Trvale smazat',
                'cancel_button' => 'Zrušit'
            ],
            'messages' => [
                'upload_success' => 'Obrázek byl úspěšně nahrán.',
                'upload_failed' => 'Nahrávání obrázku selhalo.',
                'delete_success' => 'Obrázek byl úspěšně smazán.',
                'restore_success' => 'Obrázek byl úspěšně obnoven z koše.',
                'permanent_delete_success' => 'Obrázek byl trvale smazán.',
                'error' => 'Došlo k chybě'
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
];