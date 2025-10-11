<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../HTML/login.html');
    exit;
}

?>
<!DOCTYPE html>
<html lang="es-MX">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Sistema de Gestión de Archivos - UAQ</title>
    <link rel="stylesheet" href="../CSS/estilosPaginaPrincipal.css" />
    <script src="../JS/funciones.js" defer></script>
    <script src="../JS/alertas.js" defer></script>

  </head>
  <body>
    <header class="topbar" role="banner">
      <div class="topbar_left">
          <img src="../Icons/logoUaq.png" alt="Logo UAQ" width="32" height="32" />
          <div class="header_text">
            <h1 class="header_title">Sistema de Gestión de Archivos</h1>
            <p class="header_subtitle">Universidad Autónoma de Querétaro</p>
          </div> 
      </div>

      <div class="topbar_right">
        <div class="user-mini" aria-label="Usuario actual">
          <img class="user-mini_avatar" src="../Icons/user.p-ng" alt="Avatar" width="24" height="24" />
          <div class="user-mini_data">
            <span class="user-mini_name">Dr. María González</span>
            <span class="user-mini_role">Administrador</span>

          </div>
        </div>
        <form action="../PHP/cerrarSesion.php" method="POST">
          <button class="btn-logout" id="logout" type="submit">
            
            <img class="img-logout" id="logout" type="button" src="../Icons/logout.png" alt="Cerrar sesión" width="20"/>
          </button>
        </form>
      </div>
       
      </div>
      </div>
    </header>
    
    
    <section class="toolbar" aria-label="Acciones principales">
      <div class="toolbar_left">
            <button class="btn-primary" onclick="abrirVentanaUploader()" id="upload" type="button">
            <img src="../Icons/upload.png" id="icon-upload" alt="Subir archivo" width="20" height="20" />
            <span>Subir archivo</span>
        </button>
        </div>
      
      </div>
      <script>
        function abrirVentanaUploader() {
          const uploaderSection = document.querySelector('.busqueda-archivos');
          uploaderSection.classList.add('open');
        }
        function cerrarVentanaUploader() {
          const uploaderSection = document.querySelector('.busqueda-archivos');
          uploaderSection.classList.remove('open');
        }
      </script>
      <section class="busqueda-archivos" role="dialog" aria-labelledby="titulo-uploader">
          <div class="uploader">
              <h1 class="uploader__title" id="titulo-uploader">Abrir un archivo</h1>
              <button class="uploader__close" id="close-uploader" aria-label="Cerrar ventana de subida de archivos" onclick="cerrarVentanaUploader()">
                  <img src="../Icons/cross.png" alt="Cerrar" width="15" height="15"/>
              </button>
              
              <form action="../PHP/subirArchivo.php" method="POST" enctype="multipart/form-data">

                  <div id="dropzone" class="uploader__drop">
                      <input id="file-input" class="file-input" type="file" name="archivo" accept=".xlsx, .xls, .csv" required />
                      <div id="file-names" class="uploader__names">No se ha seleccionado ningún archivo</div>
                      <label for="file-input" class="btn-archivo" id="btn-examinar">Examinar</label>
                      <p class="uploader__hint">o arrastra aquí un archivo</p>
                  </div>
          
                  <div class="uploader__actions">
                      <button id="btn-subir" type="submit" class="boton btn-subir" disabled>Generar PDF</button>
                  </div>

              </form>
              </div>
      </section>

                <section class="mensaje-error">
                    <section class="mensaje">
                        <p id="texto-error"></p>


                        <button class="btn-cerrar" onclick="cerrarVentana()" type="button">
                            <img class="img-cerrar" src="../Icons/cross.png" alt="Cerrar" width="15"/>
                        </button>
                    </section>
                </section>
      <form class="toolbar_right" id="search-form" role="search">

        <label for="ordenar" >Ordenar por</label>
        <select id="ordenar" name="sort">
          <option value="fecha" selected>Fecha</option>
          <option value="usuario">Usuario</option>
        </select>
      </form>
    </section>
    
    <main id="main" role="main">
      <section class="card" aria-labelledby="historial-title">
        <div class="card_header">
          <span class="icon-folder" aria-hidden="true"></span>
          <h2 id="historial-title">Historial de Archivos</h2>
        </div>

        <div class="card_body">
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th scope="col">Archivo</th>
                  <th scope="col">Fecha de creación</th>
                  <th scope="col">Usuario</th>
                  <th scope="col" class="col--acciones">Acciones</th>
                </tr>
              </thead>

              <tbody id="files-tbody">
                <tr class="fila" data-id="1">
                  <td class="cell-file">
                    <img src="../Icons/file-pdf.png" alt="Icono de archivo PDF" width="20" height="20"/>
                    <div class="file-meta">
                      <span class="file-name">recibo_cfe.pdf</span>
                      <span class="file-size">0.3 MB</span>
                    </div>
                  </td>

                  <td class="cell-date">
                    <img src="../Icons/calendar.png" alt="Icono de calendario" width="20" height="20"/>
                    <time datetime="2025-08-24T14:06:32">24/8/2025, 2:06:32 p.m.</time>
                  </td>
                  <td class="cell-user">
                    <img src="../Icons/user.png" alt="Icono de usuario" width="15" height="15"/>
                    <span>Dr. María González</span>
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
              </tbody>
              <tbody id="files-tbody">
                <tr class="fila" data-id="1">
                  <td class="cell-file">
                    <img src="../Icons/file-pdf.png" id="icon-file" alt="Icono de archivo PDF" width="20" height="20"/>
                    <div class="file-meta">
                      <span class="file-name">recibo_cfe.pdf</span>
                      <span class="file-size">0.3 MB</span>
                    </div>
                  </td>

                  <td class="cell-date">
                    <img src="../Icons/calendar.png" id="icon-calendar" alt="Icono de calendario" width="20" height="20"/>
                    <time datetime="2025-08-24T14:06:32">24/8/2025, 2:06:32 p.m.</time>
                  </td>
                  <td class="cell-user">
                    <img src="../Icons/user.png" alt="Icono de usuario" width="15" height="15"/>
                    <span>Dr. María González</span>
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
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </main>
    <template id="file-row-template">
      <tr class="row-file">
        <td class="cell-file">
          <img src="../Icons/file-pdf.png" alt="Icono de archivo PDF" width="20" height="20"/>
            <span class="file-name"></span>
            <span class="file-size"></span>
          </div>
        </td>
        <td class="cell-date">
          <img src="../Icons/calendar.png" alt="Icono de calendario" width="20" height="20"/>
          <time></time>
        </td>
        <td class="cell-user">
          <img src="../Icons/user.png" alt="Icono de usuario" width="20" height="20"/>
          <span class="user-name"></span>
        </td>
        <td class="cell-actions">
          <div id="boton">
          <button class="btn-icon" data-action="preview" aria-label="Ver archivo"><span class="icon-eye" aria-hidden="true"></span></button>
          <button class="btn-icon" data-action="download" aria-label="Descargar archivo"><img src="../Icons/download.png" alt="Descargar"></button>
          </div>
        </td>
      </tr>
    </template>
  </body>
</html>
