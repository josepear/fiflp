#!/usr/bin/env php
<?php
/**
 * Importa hitos desde un .rtf a un CPT de cronología (ACF repeater "hitos").
 *
 * Uso:
 * php scripts/importar-cronologia-rtf.php \
 *   --wp="/Users/josemendoza/Local Sites/fiflp/app/public" \
 *   --rtf="/Users/josemendoza/Desktop/cronologia 2.rtf" \
 *   --cronologia="1_Cronología 1926-2026" \
 *   --db-host="127.0.0.1:10004" \
 *   --dry-run
 *
 * Quita --dry-run para guardar en DB.
 */

declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

/**
 * Lee argumentos estilo --clave=valor y flags tipo --dry-run.
 */
function parse_args(array $argv): array
{
    $out = [];
    foreach ($argv as $arg) {
        if (strpos($arg, '--') !== 0) {
            continue;
        }
        $arg = substr($arg, 2);
        if (false === strpos($arg, '=')) {
            $out[$arg] = true;
            continue;
        }
        [$k, $v] = explode('=', $arg, 2);
        $out[$k] = $v;
    }
    return $out;
}

/**
 * Limpia un texto para comparar duplicados sin que espacios raros molesten.
 */
function norm(string $value): string
{
    $value = trim($value);
    $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
    return mb_strtolower($value, 'UTF-8');
}

/**
 * Comprueba si una línea parece fecha/título de hito.
 * Acepta:
 * - "1951"
 * - "8 de julio de 1951"
 * - "Octubre de 1953"
 * - "DE 1936 A 1939"
 */
function looks_like_date_title(string $line): bool
{
    $line = trim($line);
    if ($line === '') {
        return false;
    }

    if (preg_match('/^\d{4}$/u', $line)) {
        return true;
    }

    if (preg_match('/^\d{1,2}\s+de\s+[[:alpha:]áéíóúñ]+(?:\s+de)?\s+\d{4}$/iu', $line)) {
        return true;
    }

    if (preg_match('/^[[:alpha:]áéíóúñ]+\s+de\s+\d{4}$/iu', $line)) {
        return true;
    }

    if (preg_match('/^de\s+\d{4}\s+a\s+\d{4}$/iu', $line)) {
        return true;
    }

    return false;
}

/**
 * Convierte RTF a texto plano usando textutil (macOS).
 */
function rtf_to_text(string $rtfPath): string
{
    $cmd = 'textutil -convert txt -stdout ' . escapeshellarg($rtfPath);
    $txt = shell_exec($cmd);
    return is_string($txt) ? $txt : '';
}

/**
 * Parsea el txt en pares [fecha_titulo, texto].
 */
function parse_hitos_from_text(string $txt): array
{
    $lines = preg_split('/\R/u', $txt) ?: [];
    $clean = [];
    foreach ($lines as $line) {
        $line = trim((string)$line);
        if ($line === '') {
            continue;
        }
        $clean[] = $line;
    }

    $hitos = [];
    $currentDate = null;
    $buffer = [];

    foreach ($clean as $line) {
        if (looks_like_date_title($line)) {
            if ($currentDate !== null && !empty($buffer)) {
                $hitos[] = [
                    'fecha_titulo' => $currentDate,
                    'texto' => trim(implode("\n\n", $buffer)),
                ];
            }
            $currentDate = $line;
            $buffer = [];
            continue;
        }

        // Si no hay fecha aún, este texto se ignora para no crear hitos mal.
        if ($currentDate === null) {
            continue;
        }

        $buffer[] = $line;
    }

    if ($currentDate !== null && !empty($buffer)) {
        $hitos[] = [
            'fecha_titulo' => $currentDate,
            'texto' => trim(implode("\n\n", $buffer)),
        ];
    }

    return $hitos;
}

$args = parse_args($argv);

$wpPath = $args['wp'] ?? '/Users/josemendoza/Local Sites/fiflp/app/public';
$rtfPath = $args['rtf'] ?? '/Users/josemendoza/Desktop/cronologia 2.rtf';
$title = $args['cronologia'] ?? '1_Cronología 1926-2026';
$dryRun = isset($args['dry-run']);
$dbHost = $args['db-host'] ?? '';

if (!is_file($rtfPath)) {
    fwrite(STDERR, "No existe el RTF: {$rtfPath}\n");
    exit(1);
}

$wpLoad = rtrim($wpPath, '/').'/wp-load.php';
if (!is_file($wpLoad)) {
    fwrite(STDERR, "No existe wp-load.php en: {$wpLoad}\n");
    exit(1);
}

if ($dbHost !== '') {
    // Permite forzar host/puerto de MySQL cuando Local usa puerto dedicado.
    if (!defined('DB_HOST')) {
        define('DB_HOST', (string)$dbHost);
    }
}

require $wpLoad;

if (!function_exists('get_field') || !function_exists('update_field')) {
    fwrite(STDERR, "ACF no está disponible en este WordPress.\n");
    exit(1);
}

$post = get_page_by_title($title, OBJECT, 'fiflp_cronologia');
if (!$post || empty($post->ID)) {
    fwrite(STDERR, "No encontré la cronología: {$title}\n");
    exit(1);
}

$postId = (int)$post->ID;
$existing = get_field('hitos', $postId);
if (!is_array($existing)) {
    $existing = [];
}

$txt = rtf_to_text($rtfPath);
if ($txt === '') {
    fwrite(STDERR, "No pude convertir el RTF a texto.\n");
    exit(1);
}

$parsed = parse_hitos_from_text($txt);
if (empty($parsed)) {
    fwrite(STDERR, "No se detectaron hitos nuevos en el archivo.\n");
    exit(1);
}

$fingerprints = [];
foreach ($existing as $row) {
    $f = norm((string)($row['fecha_titulo'] ?? '')) . '||' . norm((string)($row['texto'] ?? ''));
    $fingerprints[$f] = true;
}

$toAdd = [];
foreach ($parsed as $row) {
    $f = norm($row['fecha_titulo']) . '||' . norm($row['texto']);
    if (isset($fingerprints[$f])) {
        continue;
    }
    $fingerprints[$f] = true;
    $toAdd[] = [
        'fecha_titulo' => $row['fecha_titulo'],
        'texto' => $row['texto'],
        'texto_posicion' => 'derecha',
        'caption' => '',
        'caption_2' => '',
        'imagen_posicion' => 'izquierda',
        'imagen_sangre' => 'none',
    ];
}

$final = array_merge($existing, $toAdd);

echo "Cronología: {$title} (ID {$postId})\n";
echo "Hitos actuales: " . count($existing) . "\n";
echo "Hitos detectados en RTF: " . count($parsed) . "\n";
echo "Hitos nuevos a añadir: " . count($toAdd) . "\n";
echo "Total final esperado: " . count($final) . "\n";

if ($dryRun) {
    echo "DRY RUN: no se guardó nada.\n";
    exit(0);
}

$ok = update_field('hitos', $final, $postId);
if (!$ok) {
    fwrite(STDERR, "ACF no devolvió OK al guardar. Revisa permisos/estado DB.\n");
    exit(1);
}

echo "OK: hitos guardados correctamente.\n";
