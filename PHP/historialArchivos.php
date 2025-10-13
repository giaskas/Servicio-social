<?php








    //todo esto lo hice con chatgpt realmente esaba muy cabron y nadie decia bien como hacerlo lol
    //todo lo que tiene que ver con el modal, que es lo de visualizar el pdf, como ni si quiera lo podia imprimir por la variable esa de la base de datos
    //me desespere y cai en las tentasiones








    include '../PHP/conexion.php';
    // CAMBIO 1: Modificamos la consulta. NO necesitamos traer el contenido binario (`Archivo`).
    // Es muy pesado y lento. Solo necesitamos el 'id' para construir el enlace.
    $consulta = "SELECT IdArchivo, nombre, DATE_FORMAT(fecha_creacion, '%Y-%m-%d %H:%i') as fecha_creacion FROM archivos";
    $resultado = $conexion->query($consulta);

    if ($resultado->num_rows > 0) {

        while ($fila = $resultado->fetch_assoc()) {
            // CAMBIO 2: Obtenemos el ID del archivo.
            $idArchivo = $fila['IdArchivo'];
            $nombreArchivo = $fila['nombre'];
            $fechaCreacion = $fila['fecha_creacion'];
            // $archivoBinario = $fila['Archivo']; // <-- Ya no necesitamos esta línea aquí.
?>
            <tr class="fila">
                <td class="cell-file">
                    <img src="../Icons/file-pdf.png" id="icon-file" alt="Icono de archivo PDF" width="20" height="20" />
                    <div class="file-meta">
                        <span class="file-name"><?php echo htmlspecialchars($nombreArchivo); ?></span>
                        <span class="file-size">0.3 MB</span>
                    </div>
                </td>
                <td class="cell-date">
                    <img src="../Icons/calendar.png" id="icon-calendar" alt="Icono de calendario" width="20" height="20" />
                    <time datetime="<?php echo htmlspecialchars($fechaCreacion); ?>"><?php echo htmlspecialchars($fechaCreacion); ?></time>
                </td>
                <td class="cell-user">
                    <img src="../Icons/user.png" alt="Icono de usuario" width="15" height="15" />
                    <span class="user-name">Alberto</span>
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
        } // Fin del while
    } else {
        echo "<tr><td colspan='4'>No se encontraron archivos.</td></tr>";
    }
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
    
    // 1. Obtener referencias a los elementos del DOM
    const modal = document.getElementById('modal-visualizador');
    const modalTitulo = document.getElementById('modal-titulo');
    const modalIframe = document.getElementById('modal-iframe');
    const btnCerrar = document.getElementById('modal-cerrar');
    
    // La tabla donde están los botones
    const cuerpoTabla = document.querySelector('tbody');

    // 2. Función para abrir el modal
    function abrirModal(id, nombre) {
        console.log(`Abriendo archivo ID: ${id}, Nombre: ${nombre}`); // Para depurar
        const urlArchivo = `../PHP/visualizacionPDF.php?id=${id}`;
        
        // Actualizamos los contenidos del modal
        modalTitulo.textContent = nombre;
        modalIframe.src = urlArchivo;
        
        // ¡La magia para mostrarlo! Añadimos la clase .open
        modal.classList.add('open');
    }

    // 3. Función para cerrar el modal
    function cerrarModal() {
        // Le quitamos la clase .open para ocultarlo
        modal.classList.remove('open');
        // Limpiamos el iframe
        modalIframe.src = 'about:blank';
    }

    // 4. Escuchar los clics en TODA la tabla
    if (cuerpoTabla) {
        cuerpoTabla.addEventListener('click', function(event) {
            // Buscamos si el clic se hizo en un botón con la clase 'btn-ver'
            const botonVer = event.target.closest('.btn-ver');
            
            if (botonVer) {
                // Si se encontró el botón, obtenemos sus datos y abrimos el modal
                const id = botonVer.dataset.id;
                const nombre = botonVer.dataset.nombre;
                abrirModal(id, nombre);
            }
        });
    }

    // 5. Asignar el evento de cierre al botón X y al fondo
    btnCerrar.addEventListener('click', cerrarModal);

    modal.addEventListener('click', function(event) {
        // Si el clic fue directamente en el fondo oscuro, también se cierra
        if (event.target === modal) {
            cerrarModal();
        }
    });

});
</script>