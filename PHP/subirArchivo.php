<?php


/*Para instalar las dependencias de FPDI Y FPDF es necesario modificar el PHP.INI
    y quitar el punto y coma de las siguientes dependencias 
    panelel de control de XAMPP -> PHP -> php.ini
    extension=zip
    extension=gd */

function pdf_text($s) {
    $s = (string)$s;

    // Limpieza típica de CSV/Excel (Â y NBSP)
    $s = str_replace(["\xC2\xA0", "Â"], " ", $s);  // NBSP + símbolo raro
    $s = preg_replace('/\s+/u', ' ', $s);
    $s = trim($s);

    // Si viene en UTF-8, conviértelo a ISO-8859-1 para FPDF.
    // Si ya viene en ISO-8859-1/Win-1252, no lo rompas.
    $enc = mb_detect_encoding($s, ['UTF-8', 'Windows-1252', 'ISO-8859-1'], true);

    if ($enc === 'UTF-8') {
        // IGNORE evita reventar por caracteres que no existen en Latin-1
        $s = iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $s);
    }

    return $s;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['archivo']['tmp_name'];
        $fileName = $_FILES['archivo']['name'];
        $fileSize = $_FILES['archivo']['size'];
        $fileType = $_FILES['archivo']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        $baseName = pathinfo($fileName, PATHINFO_FILENAME);

        $allowedfileExtensions = ['xlsx', 'xls', 'csv'];

        if (in_array($fileExtension, $allowedfileExtensions)) {

            //vendor/autoload.php es el archivo que carga todas las librerías instaladas con Composer
            require __DIR__ . '/../vendor/autoload.php';
            require __DIR__ . '/pdf_mc_table.php'; //


            //CREAR CARPETA TEMPORAL            
            $tempDir = '../Files/temp/';
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            //NOMBRE TEMPORAL
            $stampName = date('md');
            $tempfileName = $baseName . '_' . $stampName . '.' . $fileExtension;
            $tempFullPath = $tempDir . $tempfileName;

            if (!move_uploaded_file($fileTmpPath, $tempFullPath)) {
                $error = urlencode("No se pudo mover el archivo a la carpeta temporal.");
                header("Location: ../HTML/paginaPrincipal.php?error=" . $error);
                exit();
            }

            //LEER EL ARCHIVO SUBIDO
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tempFullPath);
                $sheet = $spreadsheet->getActiveSheet();
                $data = $sheet->toArray();
            } catch (Exception $e) {
                if (file_exists($tempFullPath)) unlink($tempFullPath);
                $error = urlencode("No se pudo leer el archivo: " . $e->getMessage());
                header("Location: ../HTML/paginaPrincipal.php?error=" . $error);
                exit();
            }

            // BORRAR ARCHIVO TEMPORAL
            if (file_exists($fileTmpPath)) {
                unlink($fileTmpPath);
            }

            //CREAR RUTA PARA PDF
            $pdfDir = '../Files/pdf/';
            if (!is_dir($pdfDir)) {
                mkdir($pdfDir, 0755, true);
            }

            $pdfFileName = 'NORM_' . $baseName . '_' . $stampName . '.pdf';
            $pdfFullPath = $pdfDir . $pdfFileName;

            if (file_exists($pdfFullPath)) {
                $error = urlencode("El archivo PDF ya existe: " . $pdfFileName);
                header("Location: ../HTML/paginaPrincipal.php?error=" . $error);
                exit();
            }

            //NORMALIZAR FECHA
            $timestamp = time();

            $meses = [
                1 => 'enero', 2 => 'febrero', 3 => 'marzo',
                4 => 'abril', 5 => 'mayo', 6 => 'junio',
                7 => 'julio', 8 => 'agosto', 9 => 'septiembre',
                10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
            ];

            $dia  = date('d', $timestamp);
            $mes  = $meses[(int)date('m', $timestamp)];
            $anio = date('Y', $timestamp);

            //CREAR UN PDF
            $pdf = new PDF_MC_Table();
            $pdf->AddPage();

            $indent = 20;
            $wText  = 170;
            $hLine  = 6;

            $pdf->SetFont('Arial','',11);
            $pdf->Cell(180, $hLine, "Campus Juriquilla, $dia de $mes de $anio", 0, 1, 'R');
            $pdf->Cell(180, $hLine, "REF: SADFI/oficio No. 292/$anio", 0, 1, 'R');
            $pdf->Ln(4);

            // Destinatario
            $pdf->SetFont('Arial','B',11);
            $pdf->SetX($indent);
            $pdf->Cell($wText, $hLine, "MTRO. ARTEMIO SOTOMAYOR OLMEDO", 0, 1, 'L');
            $pdf->SetX($indent);
            $pdf->Cell($wText, $hLine, "DIRECTOR DE RECURSOS HUMANOS", 0, 1, 'L');
            $pdf->SetX($indent);
            $pdf->Cell($wText, $hLine, "P R E S E N T E", 0, 1, 'L');
            $pdf->Ln(4);

            $texto = "Por medio de la presente me permito adjuntar las solicitudes originales de gasto. "
                . "Lo anterior para los efectos económicos y administrativos a que haya lugar.";

            $pdf->SetFont('Arial','',11);
            $pdf->SetX($indent);
            $pdf->MultiCell($wText, $hLine, utf8_decode($texto), 0, 'J');
            $pdf->Ln(3);

            // ENCABEZADO TABLA DE DATOS
            $pdf->SetWidths(Array(8, 35, 15, 35, 20, 25, 35, 17));
            $pdf->SetLineHeight(5);
            $pdf->SetAligns(array('C','C','C','C','C','C','C','C'));

            $pdf->SetFont('Arial','B',9);
            $pdf -> Row(Array(
                    "NO.",
                    "NOMBRE DEL BENEFICIARIO",
                    "CLAVE",
                    "PERIODO CORRESPONDIENTE A PAGAR",
                    "FECHA DE PAGO",
                    "TIPO DE NÓMINA",
                    "SOLICITUD DE GASTO",
                    "IMPORTE",
                ));

            // DATOS DE LA TABLA
            $no = 1;
            $nombre = '';
            $clave = '';
            foreach ($data as $i => $row) {
                if ($i % 2 === 0) {
                    $nombreCompleto = trim((string)($row[12] ?? ''));
                    $nombre = trim((string)($row[5] ?? ''));
                    $clave = trim((string)($row[4] ?? ''));
                    $fecha = trim((string)($row[2] ?? ''));
                    $partes = explode("/", $fecha);
                    if (count($partes) === 3) {
                        $mes  = str_pad($partes[0], 2, '0', STR_PAD_LEFT);
                        $dia  = str_pad($partes[1], 2, '0', STR_PAD_LEFT);
                        $anio = $partes[2];

                        $fecha = $dia . '/' . $mes . '/' . $anio;
                    }

                    
                    $importe = trim((string)($row[7] ?? ''));
                    $solicitud = trim((string)($row[6] ?? ''));
                    continue;
                }

                //PERIODO Y TIPO DE NOMINA
                $texto = trim((string)($row[6] ?? ''));

                $texto = str_replace(["Â", "\xC2\xA0"], " ", $texto);
                $texto = preg_replace('/\s+/', ' ', $texto);
                $texto = trim($texto);

                $periodo = '';
                $tipoNomina = '';

                if (preg_match('/^(.*)\s+(NOM\.\s*QUINCENAL)\s*$/i', $texto, $m)) {
                    $periodo    = trim($m[1]);
                    $tipoNomina = trim($m[2]);
                } 

                $pdf->SetFont('Arial','',9);
                $pdf->SetAligns(array('C','L','C','L','C','L','L','R'));
                $pdf->Row([
                    pdf_text($no),
                    pdf_text($nombre),
                    pdf_text($clave),
                    pdf_text($periodo),
                    pdf_text($fecha),
                    pdf_text($tipoNomina),
                    pdf_text($solicitud),
                    pdf_text($importe),
                ]);

                $no++;
            }

            $pdf->Ln(6);
            $pdf->SetFont('Arial','',11);
            $pdf->SetX($indent);
            $pdf->MultiCell($wText, $hLine, utf8_decode("Agradezco sus atenciones y quedo a sus órdenes para cualquier duda al respecto."), 0, 'J');

            $pdf->Ln(2);
            $pdf->SetFont('Arial','B',11);
            $pdf->Cell(0, $hLine, "ATENTAMENTE", 0, 1, 'C');
            $pdf->Cell(0, $hLine, utf8_decode('"RAZONAMIENTO Y TECNOLOGÍA PARA INNOVAR Y TRASCENDER"'), 0, 1, 'C');
            $pdf->Ln(18); 

            // Firma
            
            $nombreCompleto = preg_replace('/\s+/', ' ',$nombreCompleto);
            $partes = explode(' ', $nombreCompleto);
            if (count($partes) < 3){
                $secretaria = $nombreCompleto;
            }else {
                $apellidos = array_slice($partes, 0, 2);
                $nombres   = array_slice($partes, 2);
                $secretaria = trim(implode(' ', $nombres) . ' ' . implode(' ', $apellidos));
            }
            
            $pdf->SetFont('Arial','B',11);
            $pdf->Cell(0, $hLine, utf8_decode("M.P.S. $secretaria"), 0, 1, 'C');
            $pdf->SetFont('Arial','',11);
            $pdf->Cell(0, $hLine, utf8_decode("SECRETARIA ADMINISTRATIVA"), 0, 1, 'C');

            $pdf->Ln(6);

            // Pie
            $pdf->SetFont('Arial','',7);
            $pdf->Cell(0, 4, "ccp. Archivo", 0, 1, 'L');
            $pdf->Cell(0, 4, "M.P.S FGMZP/mpc", 0, 1, 'L');
            $pdf->Cell(0, 4, "monica.perezc@uaq.mx", 0, 1, 'L');

            //GUARDAR EL PDF EN LA RUTA DEFINIDA
            $pdf->Output('F', $pdfFullPath);

            //GUARDAR RUTA EN BASE DE DATOS
            $pdfPathDB = 'Files/pdf/' . $pdfFileName;

            include '../PHP/conexion.php';
            
            $sql = "INSERT INTO Archivos (Nombre, Archivo, Fecha_Creacion)
                        VALUES (?, ?, NOW())";

            $stmt = $conexion->prepare($sql);
                if (!$stmt) {
                    die("Error prepare: " . $conexion->error);
                }

            session_start();
            $idUsuario = $_SESSION['idusuario'];

            $stmt->bind_param("ss", $pdfFileName, $pdfPathDB);

            $stmt->execute();

            $stmt->close();

            $conexion->close();

            header("Location: ../HTML/paginaPrincipal.php?ok=1");
            exit();


        } else {
            $error = urlencode("Tipo de archivo no permitido. Solo se permiten archivos XLSX, XLS y CSV.");
            header("Location: ../HTML/paginaPrincipal.php?error=" . $error);
            exit();
        }
    } else {
        $error = urlencode("Error al subir el archivo. Código de error: " . $_FILES['archivo']['error']);
        header("Location: ../HTML/paginaPrincipal.php?error=" . $error);
        exit();
    }
} else {
    $error = urlencode("Método de solicitud no permitido.");
    header("Location: ../HTML/paginaPrincipal.php?error=" . $error);
    exit();
}

?>