
<?php
/*Para instalar las dependencias de FPDI Y FPDF es necesario modificar el PHP.INI
    y quitar el punto y coma de las siguientes dependencias 
    panelel de control de XAMPP -> PHP -> php.ini
    extension=zip
    extension=gd */
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
            require('../vendor/autoload.php');
            //fileTmpPath es la ruta temporal del archivo subido
            
            $tempDir = '../Files/temp/';

            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            //NOMBRE TEMPORAL 

            $timestamp = date('md');

            $tempfileName = $baseName . '_' . $timestamp . '.' . $fileExtension;


            //MOVER EL ARCHVIO A LA CARPETA TEMPORAL
            move_uploaded_file($fileTmpPath, $tempDir . $tempfileName);
            $fileTmpPath = $tempDir . $tempfileName;
            
            
            //CREAR RUTA PARA PDF
            $pdfDir = '../Files/pdf/';

            if (!is_dir($pdfDir)) {
                mkdir($pdfDir, 0755, true);
            }

            //CREAR NOMBRE DE PDF
            $pdfFileName = 'NORM_' . $baseName . '_' . $timestamp . '.pdf';

            //DEFINIR LA RUTA DONDE SE GUARDARA EL PDF
            $pdfFullPath = $pdfDir . $pdfFileName;

            if (file_exists($pdfFullPath)) {
                $error = urlencode("El archivo PDF ya existe: " . $pdfFileName);
                header("Location: ../HTML/paginaPrincipal.php?error=" . $error);
                exit();
            }

            //OBTENER INFORMACION DEL CSV/EXCEL    
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fileTmpPath);

            // Borrar el archivo temporal
            if (file_exists($fileTmpPath)) {
                unlink($fileTmpPath);
            }


            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray();

            //CREAR UN PDF
            $pdf = new \setasign\Fpdi\Fpdi();
            $pdf->AddPage();
            $pdf->SetFont('Helvetica', '', 12);
            $texto = '';
            foreach ($data as $row) {
                $texto .= implode(' | ', $row) . "\n";
            }

            $pdf->SetXY(10, 10);
            $pdf->MultiCell(0, 6, $texto);

            
            

            

            //GUARDAR EL PDF EN LA RUTA DEFINIDA
            $pdf->Output('F', $pdfFullPath);

            //OBTENER RUTA RELATIVA PARA GUARDAR EN LA BASE DE DATOS
            $pdfPathDB = 'Files/pdf/' . $pdfFileName;


            //CONSULTA PARA GUARDAR EN LA BASE DE DATOS
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