<?php
// app/Controllers/AdminController.php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\AdminLayout;
use App\Auth\LoginService;
use App\Core\Config;

/**
 * Controller pro hlavn� administra�n� rozhran�
 *
 * Zpracov�v� zobrazen� administra�n�ho dashboardu s p�ehledem
 * rychl�ch akc� a z�kladn�ch informac� o syst�mu.
 * Slou�� jako vstupn� bod do administra�n� ��sti aplikace.
 *
 * @package App\Controllers
 * @author KRS3
 * @version 3.0
 */
class AdminController
{
    /**
     * @param LoginService $authService Slu�ba pro ov��en� p�ihl�en�
     * @param string $baseUrl Z�kladn� URL aplikace
     * @param AdminLayout $adminLayout Layout administra�n�ho rozhran�
     */
    public function __construct(
        private LoginService $authService,
        private string $baseUrl,
        private \App\Core\AdminLayout $adminLayout
    ) {}

    /**
     * Zobraz� administra�n� dashboard s p�ehledem a rychl�mi akcemi
     *
     * Vytvo�� p�ehledovou str�nku administrace s uv�tac� zpr�vou
     * a odkazy na hlavn� spr�vcovsk� sekce syst�mu.
     *
     * @return string HTML obsah administra�n�ho dashboardu
     *
     * @example
     * $dashboard = $adminController->dashboard();
     * echo $dashboard; // zobraz� administra�n� p�ehled
     */
    public function dashboard(): string
    {
        $content = <<<HTML
<h1>{$this->translate('admin.titles.administration')}</h1>
<p>{$this->translate('admin.messages.welcome_admin')}</p>

<h2>{$this->translate('admin.titles.quick_actions')}:</h2>
<ul>
    <li><a href='{$this->baseUrl}admin/articles'>{$this->translate('admin.titles.manage_articles')}</a></li>
    <li><a href='{$this->baseUrl}admin/gallery'>{$this->translate('admin.titles.manage_gallery')}</a></li>
    <li><a href='{$this->baseUrl}admin/users'>{$this->translate('admin.titles.manage_users')}</a></li>
</ul>
HTML;

        return $this->adminLayout->wrap($content, $this->translate('admin.navigation.dashboard'));
    }

    /**
     * P�elo�� textov� kl�� pomoc� konfigura�n�ho syst�mu
     *
     * @param string $key Kl�� pro p�eklad
     * @return string P�elo�en� text
     */
    private function translate(string $key): string
    {
        return Config::text($key);
    }
}