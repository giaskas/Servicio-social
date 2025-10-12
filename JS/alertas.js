const urlParams = new URLSearchParams(window.location.search);
const error = urlParams.get('error');
const url= window.location.href.split('?')[0];
const mensajeError = document.querySelector('.mensaje-error');
if (error) {
    const pElementoError = document.getElementById('texto-error');
    pElementoError.textContent = decodeURIComponent(error);
    mensajeError.classList.add('open');
}
                   
function cerrarVentana() {
    window.history.replaceState({}, document.title, url);
    mensajeError.classList.remove('open');
}
