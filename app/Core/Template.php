<?php
// app/Core/Template.php
declare(strict_types=1);

namespace App\Core;

class Template
{
    private string $templatesDir;
    private array $data = [];

    public function __construct(string $templatesDir = null)
    {
        $this->templatesDir = $templatesDir ?? __DIR__ . '/../../templates';
    }

    public function assign(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    public function assignMultiple(array $data): void
    {
        $this->data = array_merge($this->data, $data);
    }

    public function render(string $template, array $data = []): string
    {
        $fullPath = $this->templatesDir . '/' . ltrim($template, '/');

        if (!file_exists($fullPath)) {
            throw new \RuntimeException("Template not found: {$fullPath}");
        }

        // Extrahuj data do proměnných
        extract(array_merge($this->data, $data), EXTR_SKIP);

        // Zachyť výstup
        ob_start();
        include $fullPath;
        return ob_get_clean();
    }

    public function exists(string $template): bool
    {
        return file_exists($this->templatesDir . '/' . ltrim($template, '/'));
    }
}