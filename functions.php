<?php

function get_config(): array
{
    static $config;
    if ($config === null) {
        $config = require __DIR__ . '/config.php';
    }
    return $config;
}

function start_session(): void
{
    $config = get_config();
    if (session_status() === PHP_SESSION_NONE) {
        session_name($config['session_name']);
        session_start();
    }
}

function load_links(): array
{
    $config = get_config();
    if (!file_exists($config['data_file'])) {
        return [];
    }
    $json = file_get_contents($config['data_file']);
    $data = json_decode($json, true);
    if (!is_array($data)) {
        return [];
    }
    foreach ($data as &$link) {
        if (!isset($link['id'])) {
            $link['id'] = generate_id();
        }
        if (!isset($link['display_order'])) {
            $link['display_order'] = 0;
        }
    }
    unset($link);
    usort($data, static function ($a, $b) {
        return ($b['display_order'] ?? 0) <=> ($a['display_order'] ?? 0);
    });
    return $data;
}

function save_links(array $links): void
{
    $config = get_config();
    $json = json_encode(array_values($links), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($config['data_file'], $json, LOCK_EX);
}

function ensure_data_file(): void
{
    $config = get_config();
    if (!file_exists($config['data_file'])) {
        if (!is_dir(dirname($config['data_file']))) {
            mkdir(dirname($config['data_file']), 0775, true);
        }
        file_put_contents($config['data_file'], json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}

function require_login(): void
{
    start_session();
    if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: admin.php');
        exit;
    }
}

function sanitize_text(?string $value): string
{
    return trim(filter_var($value ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
}

function sanitize_color(?string $value): string
{
    $value = trim((string) $value);
    if (preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $value)) {
        return $value;
    }
    return '#4b5fff';
}

function generate_id(): string
{
    return bin2hex(random_bytes(8));
}

function find_link_by_id(array $links, string $id): ?array
{
    foreach ($links as $link) {
        if (($link['id'] ?? '') === $id) {
            return $link;
        }
    }
    return null;
}

function update_link(array $links, string $id, array $newData): array
{
    foreach ($links as $index => $link) {
        if (($link['id'] ?? '') === $id) {
            $links[$index] = array_merge($link, $newData);
            return $links;
        }
    }
    return $links;
}

function delete_link(array $links, string $id): array
{
    return array_values(array_filter($links, static function ($link) use ($id) {
        return ($link['id'] ?? '') !== $id;
    }));
}
