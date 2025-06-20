// Función principal de eliminación
function confirmarEliminarUsuario(id) {
    Swal.fire({
        title: '¿Eliminar usuario?',
        text: "¡Esta acción no se puede deshacer!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6d4c41',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        backdrop: `
            rgba(109, 76, 65, 0.4)
            url("../assets/image/coffee-bean-icon.png")
            left top
            no-repeat
        `
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `../controllers/EliminarUsuario.php?id=${id}`;
        }
    });
}

// Opcional: Notificación de resultados
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('estado') === 'exito') {
    Swal.fire({
        title: '¡Éxito!',
        text: urlParams.get('mensaje') || 'Operación realizada correctamente',
        icon: 'success',
        confirmButtonColor: '#6d4c41'
    });
} else if (urlParams.get('estado') === 'error') {
    Swal.fire({
        title: 'Error',
        text: urlParams.get('mensaje') || 'Ocurrió un error',
        icon: 'error',
        confirmButtonColor: '#d33'
    });
}