                <?php
                    include '../PHP/conexion.php';

                    $consulta = "SELECT nombre, DATE_FORMAT(fecha_creacion, '%Y-%m-%d %H:%i') as fecha_creacion FROM archivos";
                    $resultado = $conexion->query($consulta);

                    if ($resultado->num_rows > 0) {
                            
                        while ($fila = $resultado->fetch_assoc()) {
                            $nombreArchivo = $fila['nombre'];
                            $fechaCreacion = $fila['fecha_creacion'];

                ?>
                <tr class="fila">
                  <td class="cell-file">
                    <img src="../Icons/file-pdf.png" id="icon-file" alt="Icono de archivo PDF" width="20" height="20"/>
                    <div class="file-meta">
                      <span class="file-name"><?php echo htmlspecialchars($nombreArchivo); ?></span>
                      <span class="file-size">0.3 MB</span>
                    </div>
                  </td>

                  <td class="cell-date">
                    <img src="../Icons/calendar.png" id="icon-calendar" alt="Icono de calendario" width="20" height="20"/>
                    <time datetime="<?php echo htmlspecialchars($fechaCreacion); ?>"><?php echo htmlspecialchars($fechaCreacion); ?></time>
                  </td>
                  <td class="cell-user">
                    <img src="../Icons/user.png" alt="Icono de usuario" width="15" height="15"/>
                    <span class="user-name">Alberto</span>
                  </td>
                  <td class="cell-actions">
                    <button class="btn-icon" data-action="preview" aria-label="Ver archivo">
                      <img src="../Icons/eye.png" alt="Ver archivo" width="15" height="15"/>
                    </button>
                
                    <button class="btn-icon" data-action="download" aria-label="Descargar archivo">
                      <img src="../Icons/download.png" alt="Descargar archivo" width="15" height="15"/>
                    </button>
                  </td>   
                </tr>
                <?php
                        }
                       
                       
                    } else {
                        echo "<tr><td colspan='4'>No se encontraron archivos.</td></tr>";
                    }
                ?>