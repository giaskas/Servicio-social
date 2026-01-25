<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/../PHP/conexion.php';

if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$accion = isset($_GET['accion']) ? strtolower(trim((string)$_GET['accion'])) : 'ver';
$esDescarga = ($accion === 'descargar');

if (!$id || $id <= 0) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=UTF-8');
    exit("Solicitud inválida: parámetro 'id'.");
}

$sql = "SELECT nombre, Archivo FROM archivos WHERE IdArchivo = ? LIMIT 1";
$stmt = $conexion->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    exit("Error prepare.");
}

$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$row) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    exit("Archivo no encontrado en BD.");
}

$nombre = (string)($row['nombre'] ?? '');
$pathRelativo = (string)($row['Archivo'] ?? '');

if ($pathRelativo === '') {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    exit("Ruta vacía en BD.");
}

$baseDir = realpath(__DIR__ . '/../');              
$pdfRoot = realpath($baseDir . '/Files/pdf');        
$fullPath = realpath($baseDir . '/' . ltrim($pathRelativo, '/\\'));

if ($baseDir === false || $pdfRoot === false || $fullPath === false) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    exit("Error resolviendo rutas. baseDir/pdfRoot/fullPath.");
}


$pdfRootNorm = rtrim($pdfRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
if (strpos($fullPath, $pdfRootNorm) !== 0) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=UTF-8');
    exit("Ruta no permitida. fullPath=$fullPath");
}

if (!is_file($fullPath) || !is_readable($fullPath)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    exit("Archivo no encontrado en disco: $fullPath");
}

$size = filesize($fullPath);
if ($size === false || $size <= 0) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    exit("El PDF está vacío (0 bytes): $fullPath");
}

// nombre seguro con .pdf
$nombre = $nombre ?: ("archivo_{$id}.pdf");
if (strtolower(pathinfo($nombre, PATHINFO_EXTENSION)) !== 'pdf') {
    $nombre .= '.pdf';
}

while (ob_get_level() > 0) { ob_end_clean(); }

header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=0, no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Content-Type: application/pdf');
header('Content-Length: ' . (string)$size);

$disposition = $esDescarga ? 'attachment' : 'inline';
$filenameSafe = str_replace(['"', "\r", "\n"], ["'", '', ''], $nombre);
$filenameStar = rawurlencode($nombre);
header("Content-Disposition: {$disposition}; filename=\"{$filenameSafe}\"; filename*=UTF-8''{$filenameStar}");

// enviar archivo
readfile($fullPath);
exit;
