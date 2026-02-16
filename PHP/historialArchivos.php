<?php
    include '../PHP/conexion.php';
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $nombreUsuarioSesion = $_SESSION['usuario'];

    $modo = $_GET['modo'] ?? 'propios';

    //hacemos la consulta
    if ($modo === 'todos') {

        $consulta = "SELECT 
                a.IdArchivo,
                a.Nombre AS NombreArchivo,
                DATE_FORMAT(r.Fecha, '%Y-%m-%d') AS FechaCreacion,
                u.Nombre AS NombreUsuario
            FROM Archivos a
            JOIN registro r ON a.IdArchivo = r.IdArchivo
            JOIN usuarios u ON r.IdUsuario = u.IdUsuario
            ORDER BY r.Fecha DESC";

        $stmt = $conexion->prepare($consulta);
        if (!$stmt) die('Error prepare: ' . $conexion->error);

        $stmt->execute();

    } else {

        $consulta = "SELECT 
                        a.IdArchivo,
                        a.Nombre AS NombreArchivo,
                        DATE_FORMAT(r.Fecha, '%Y-%m-%d') AS FechaCreacion,
                        u.Nombre AS NombreUsuario
                    FROM Archivos a
                    JOIN registro r ON a.IdArchivo = r.IdArchivo
                    JOIN usuarios u ON r.IdUsuario = u.IdUsuario
                    WHERE u.Nombre = ?
                    ORDER BY r.Fecha DESC";

        $stmt = $conexion->prepare($consulta);
        if (!$stmt) die('Error prepare: ' . $conexion->error);

        $stmt->bind_param("s", $nombreUsuarioSesion);
        $stmt->execute();
    }

    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        //asignamos las consultas en variables
        while ($fila = $resultado->fetch_assoc()) {
            $idArchivo = (int)$fila['IdArchivo'];
            $nombreArchivo = $fila['NombreArchivo'];
            $fechaCreacion = $fila['FechaCreacion'];
            $nombreUsuario = $fila['NombreUsuario'];
?>          
  
            <tr class="fila">
                <td class="cell-file">
                    <img src="../Icons/file-pdf.png" id="icon-file" alt="Icono de archivo PDF" width="20" height="20" />
                    <div class="file-meta">
                        <span class="file-name"><?php echo htmlspecialchars($nombreArchivo); ?></span>
                        <!--<span class="file-size">0.3 MB</span>-->
                    </div>
                </td>
                <td class="cell-date">
                    <img src="../Icons/calendar.png" id="icon-calendar" alt="Icono de calendario" width="20" height="20" />
                    <time datetime="<?php echo htmlspecialchars($fechaCreacion); ?>"><?php echo htmlspecialchars($fechaCreacion); ?></time>
                </td>
                <td class="cell-user">
                    <img src="../Icons/user.png" alt="Icono de usuario" width="15" height="15" />
                    <span class="user-name"><?php echo htmlspecialchars($nombreUsuario); ?></span>
                </td>
                <td class="cell-actions">
                    <button class="btn-icon btn-ver" 
                            data-id="<?php echo $idArchivo; ?>" 
                            data-nombre="<?php echo htmlspecialchars($nombreArchivo); ?>" 
                            aria-label="Ver archivo">
                      <img src="../Icons/eye.png" alt="Ver archivo" width="15" height="15" />
                    </button>

                    <a href="../PHP/visualizacionPDF.php?id=<?php echo $idArchivo; ?>&accion=descargar" class="btn-icon" aria-label="Descargar archivo">
                        <img src="../Icons/download.png" alt="Descargar archivo" width="15" height="15" />
                    </a>
                </td>
            </tr>
<?php
        } 
    } else {
        echo "<tr><td colspan='4'><center>
            <br><br><br><br>No se encontraron archivos.<br><br><br><br><br>
            </center></td></tr>";
    }
    $stmt->close();
    $conexion->close();
?>

<section class="visualizacion" role="dialog" id="modal-visualizador">
    <div class="ventanaPDF">
        <h1 class="nombrePDF" id="modal-titulo"></h1>
        <div class="PDF">
            <iframe id="modal-iframe" src="about:blank" style="width: 100%; height: 100%; border: none;"></iframe>
        </div>
        <button class="uploader__close" id="modal-cerrar" aria-label="Cerrar ventana de visualización">
            <img src="../Icons/cross.png" alt="Cerrar" width="15" height="15" />
        </button>
    </div>
</section>


<script>document.addEventListener('DOMContentLoaded', function() {
    //asignamos las clases a variables
    const modal = document.getElementById('modal-visualizador');
    const modalTitulo = document.getElementById('modal-titulo');
    const modalIframe = document.getElementById('modal-iframe');
    const btnCerrar = document.getElementById('modal-cerrar');
    
    const cuerpoTabla = document.querySelector('tbody');

    function abrirModal(id, nombre) {
        console.log(`Abriendo archivo ID: ${id}, Nombre: ${nombre}`); 
        
        const urlArchivo = `../PHP/visualizacionPDF.php?id=${id}`;
        
        // insertamos la visualizacion del archivo dentro del modal
        modalTitulo.textContent = nombre;
        modalIframe.src = urlArchivo;
        
        modal.classList.add('open');
    }

    function cerrarModal() {
        modal.classList.remove('open');
        modalIframe.src = 'about:blank';
    }

    //escuchar los clics en toda la tabla
    if (cuerpoTabla) {
        cuerpoTabla.addEventListener('click', function(event) {
            // Buscamos si el clic se hizo en el boton de ver
            const botonVer = event.target.closest('.btn-ver');
            
            if (botonVer) {
                //mostramos el modal
                const id = botonVer.dataset.id;
                const nombre = botonVer.dataset.nombre;
                abrirModal(id, nombre);
            }
        });
    }

    // asignar el evento de cierre al boton de x y fuera del cuadro
    btnCerrar.addEventListener('click', cerrarModal);

    modal.addEventListener('click', function(event) {
        // si el clic fue directamente en el fondo oscuro, también se cierra
        if (event.target === modal) {
            cerrarModal();
        }
    });

});
</script>