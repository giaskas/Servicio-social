<?php
// visualizacionPDF.php
declare(strict_types=1);

// *** Asegúrate de NO tener BOM ni espacios antes de esta línea ***
ini_set('display_errors', '0'); // evita romper el PDF con notices en producción
error_reporting(E_ALL);

require __DIR__ . '/conexion.php';

// Si tu app usa sesiones en otras partes, libérala para no bloquear mientras servimos bytes
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

// 1) Parámetros
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$accion = isset($_GET['accion']) ? strtolower(trim((string)$_GET['accion'])) : 'ver'; // 'ver' | 'descargar'
$esDescarga = ($accion === 'descargar');

if (!$id || $id <= 0) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "Solicitud inválida: parámetro 'id'.";
    exit;
}

// 2) Consulta segura: traemos nombre y BLOB
$sql = "SELECT nombre, Archivo
        FROM archivos
        WHERE IdArchivo = ?
        LIMIT 1";

$stmt = $conexion->prepare($sql);
if (!$stmt) {
    error_log("visualizacionPDF: prepare failed: " . $conexion->error);
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "Error interno.";
    exit;
}

$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "Archivo no encontrado.";
    exit;
}

$stmt->bind_result($nombre, $blob);
$stmt->fetch();
$stmt->close();

// 3) Validaciones del BLOB
if ($blob === null || $blob === '') {
    error_log("visualizacionPDF: BLOB vacío para id=$id");
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "El archivo está vacío o corrupto.";
    exit;
}

// si accidentalmente guardaste base64, decodifica (heurística ligera)
$first4 = substr($blob, 0, 4);
if ($first4 !== '%PDF' && preg_match('/^[A-Za-z0-9+\/=\r\n]+$/', $blob)) {
    $decoded = base64_decode($blob, true);
    if ($decoded !== false && substr($decoded, 0, 4) === '%PDF') {
        $blob = $decoded;
    }
}

// tamaño real
$size = strlen($blob);

// nombre seguro y con .pdf
$nombre = $nombre ?: ("archivo_{$id}.pdf");
if (strtolower(pathinfo($nombre, PATHINFO_EXTENSION)) !== 'pdf') {
    $nombre .= '.pdf';
}

// 4) Limpia cualquier buffer antes de headers
while (ob_get_level() > 0) { ob_end_clean(); }

// 5) Cabeceras base
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=0, no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Content-Type: application/pdf');
header('Accept-Ranges: bytes');

$disposition = $esDescarga ? 'attachment' : 'inline';
$filenameSafe = str_replace(['"', "\r", "\n"], ['\'', '', ''], $nombre);
$filenameStar = rawurlencode($nombre);
header("Content-Disposition: {$disposition}; filename=\"{$filenameSafe}\"; filename*=UTF-8''{$filenameStar}");

// 6) HTTP Range (parcial) para que el visor de Chrome funcione bien
$range = $_SERVER['HTTP_RANGE'] ?? null;
$start = 0;
$end   = $size - 1;

if ($range && preg_match('/bytes=(\d*)-(\d*)/i', $range, $m)) {
    $start = ($m[1] === '') ? 0 : (int)$m[1];
    $end   = ($m[2] === '') ? ($size - 1) : (int)$m[2];

    if ($start > $end || $start >= $size) {
        header('Content-Range: bytes */' . $size);
        http_response_code(416);
        exit;
    }
    $length = ($end - $start) + 1;

    header("Content-Range: bytes {$start}-{$end}/{$size}");
    header("Content-Length: " . (string)$length);
    http_response_code(206);
    echo substr($blob, $start, $length);
    exit;
}

// 7) Respuesta completa
header('Content-Length: ' . (string)$size);
http_response_code(200);
echo $blob;
exit;
