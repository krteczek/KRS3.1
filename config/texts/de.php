<?php
// config/texts/de.php
declare(strict_types=1);

return [
    'pages' => [
        'home' => 'Startseite',
        'login' => 'Anmeldung',
        'article_detail' => 'Artikel: {title}',
        'article_not_found' => 'Artikel nicht gefunden',
        'categories' => 'Kategorien',
        '404' => 'Seite nicht gefunden - {site_name}'
    ],
    'messages' => [
        'welcome' => 'Willkommen in unserem System',
        'no_articles' => 'Keine Artikel zum Anzeigen',
        'article_not_found' => 'Artikel wurde nicht gefunden',
        'invalid_csrf' => 'Ungültiges CSRF-Token',
        'login_failed' => 'Ungültige Anmeldedaten',
        'login_success' => 'Anmeldung war erfolgreich',
        'logout_success' => 'Abmeldung war erfolgreich',
        'category_not_found' => 'Kategorie wurde nicht gefunden',
        '404' => 'Entschuldigung, aber die angeforderte Seite wurde nicht gefunden.'
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
            'manage_galleries' => 'Galerien verwalten',
            'back_to_gallery' => 'Zurück zur Galerie',
            'create_first' => 'Erste Galerie erstellen',
            'upload_first' => 'Erstes Bild hochladen',
            'unknown_parent' => 'Unbekanntes Elternelement',
            'actions' => [
                'view' => 'Anzeigen',
                'edit' => 'Bearbeiten',
                'delete' => 'Löschen'
            ],
            'manage' => [
                'title' => 'Galerien verwalten',
                'empty_text' => 'Sie haben noch keine Galerien.',
                'table' => [
                    'name' => 'Name',
                    'parent' => 'Übergeordnete Galerie',
                    'images_count' => 'Anzahl Bilder',
                    'actions' => 'Aktionen'
                ]
            ],
            'create' => [
                'title' => 'Galerie erstellen',
                'button' => 'Galerie erstellen',
                'submit' => 'Erstellen'
            ],
            'edit' => [
                'title' => 'Galerie bearbeiten',
                'button' => 'Bearbeiten',
                'submit' => 'Änderungen speichern'
            ],
            'view' => [
                'button' => 'Anzeigen',
                'empty_images' => 'Keine Bilder',
                'empty_images_text' => 'Diese Galerie enthält keine Bilder.'
            ],
            'delete' => [
                'button' => 'Löschen',
                'confirm_title' => 'Galerie löschen',
                'confirm_button' => 'Ja, Galerie löschen',
                'cancel_button' => 'Abbrechen',
                'confirm_message' => 'Sie sind dabei, die Galerie "{name}" zu löschen. Diese Aktion ist unwiderruflich.',
                'warning' => 'Warnung',
                'gallery_info' => 'Galerie-Informationen',
                'images_count' => 'Anzahl Bilder',
                'children_will_be_promoted' => 'Untergalerien, die verschoben werden ({count})',
                'children_promote_message' => 'Die folgenden Untergalerien werden auf die oberste Ebene verschoben:',
                'confirm_checkbox' => 'Ja, ich möchte die Galerie "{name}" löschen und ihre Untergalerien auf die oberste Ebene verschieben.',
                'success_message' => 'Galerie "{name}" wurde erfolgreich gelöscht. {children_count, plural, =0 {Keine Untergalerien wurden verschoben.} one {# Untergalerie wurde auf die oberste Ebene verschoben.} other {# Untergalerien wurden auf die oberste Ebene verschoben.}}',
                'error_not_found' => 'Galerie wurde nicht gefunden.',
                'error_not_confirmed' => 'Sie müssen die Löschung bestätigen.'
            ],
            'form' => [
                'name' => 'Galeriename',
                'description' => 'Beschreibung',
                'parent' => 'Übergeordnete Galerie',
                'no_parent' => 'Keine (Hauptgalerie)',
                'parent_help' => 'Wählen Sie eine übergeordnete Galerie, um eine Hierarchie zu erstellen',
                'parent_restrictions' => 'Hinweis: Eine Galerie kann nicht sich selbst oder eine Galerie, die bereits ihr Nachkomme ist, als übergeordnetes Element haben.',
                'create_button' => 'Galerie erstellen',
                'save_button' => 'Änderungen speichern',
                'cancel' => 'Abbrechen'
            ],
            'upload' => [
                'title' => 'Bild hochladen',
                'select_file' => 'Datei auswählen',
                'file_help' => 'Unterstützte Formate: JPG, PNG, GIF. Maximale Größe: 5MB.',
                'title_label' => 'Titel',
                'title_placeholder' => 'Optionaler Bildtitel',
                'description' => 'Beschreibung',
                'description_placeholder' => 'Optionale Bildbeschreibung',
                'submit' => 'Hochladen',
                'cancel' => 'Abbrechen',
                'assign_to_galleries' => 'Galerien zuweisen'
            ],
            'empty' => [
                'title' => 'Keine Galerien',
                'description' => 'Sie haben noch keine Galerien. Erstellen Sie die erste Galerie!'
            ],
            'stats' => [
                'total' => 'Gesamtanzahl Bilder: {count}'
            ],
            'image' => [
                'size' => 'Größe',
                'dimensions' => 'Abmessungen',
                'delete' => 'Löschen',
                'view' => 'Anzeigen',
                'usage' => 'Bildnutzung',
                'in_articles' => 'Artikel, die dieses Bild verwenden',
                'in_galleries' => 'Galerien, die dieses Bild enthalten',
                'no_usage' => 'Dieses Bild wird in keinem Artikel und keiner Galerie verwendet'
            ],
            'confirm' => [
                'delete' => 'Möchten Sie die Galerie wirklich löschen?'
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
];