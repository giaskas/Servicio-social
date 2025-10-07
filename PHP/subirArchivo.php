<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['archivo']['tmp_name'];
        $fileName = $_FILES['archivo']['name'];
        $fileSize = $_FILES['archivo']['size'];
        $fileType = $_FILES['archivo']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedfileExtensions = ['xlsx', 'xls', 'csv'];
        if (in_array($fileExtension, $allowedfileExtensions)) {
            //vendor/autoload.php es el archivo que carga todas las librerías instaladas con Composer
            //aqui viene como la de PhpSpreadsheet como FPDI
            //peroo ocupamos descargar unas cosas primero para que funcione y yo ya zzzzzzzz
            require 'vendor/autoload.php';

            //fileTmpPath es la ruta temporal del archivo subido
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fileTmpPath);
            
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray();
            print_r($data); 

            //aqui va la logica de empezar a generar el PDF 
            //se usa FPDI para generar una plantilla de PDF y luego se rellena con los datos extraidos del array del Excel

        } else {
            echo "Tipo de archivo no permitido. Solo se permiten archivos XLSX, XLS y CSV.";
        }
    } else {
        echo "Error en la subida del archivo. Código de error: " . $_FILES['archivo']['error'];
    }
} else {
    echo "Método de solicitud no válido.";
}
?>