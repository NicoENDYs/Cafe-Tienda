// Función reutilizable para mostrar notificaciones
function mostrarNotificacion({ estado, mensaje, titulo, confirmButtonText }) {
    const configuraciones = {
        exito: {
            icon: 'success',
            title: titulo || '¡Éxito!',
            text: mensaje || 'Operación realizada con éxito',
            confirmButtonText: confirmButtonText || 'Aceptar'
        },
        correo_existente: {
            icon: 'error',
            title: titulo || '¡Érror!',
            text: mensaje || 'El correo electrónico ya está registrado',
            confirmButtonText: confirmButtonText || 'Aceptar'
        },
        error: { 
            icon: 'error',
            title: titulo || 'Error',
            text: mensaje || 'Ocurrió un error inesperado',
            confirmButtonText: confirmButtonText || 'Entendido'
        },
        error_password: {
            icon: 'error',
            title: titulo || 'Error de contraseña',
            text: mensaje || 'La contraseña ingresada es incorrecta',
            confirmButtonText: confirmButtonText || 'Intentar de nuevo'
        },
        // ... otras configuraciones ...
    };

    if (configuraciones[estado]) {
        Swal.fire(configuraciones[estado]);
        limpiarURL();
    }
}

function limpiarURL() {
    if (window.history.replaceState) {
        const url = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.history.replaceState({ path: url }, "", url);
    }
}

// Auto-ejecución si hay parámetros GET
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('estado')) {
        mostrarNotificacion({
            estado: urlParams.get('estado'),
            mensaje: urlParams.get('mensaje') || '',
            titulo: urlParams.get('titulo') || '',
            confirmButtonText: urlParams.get('confirmButtonText') || ''
        });
    }
});