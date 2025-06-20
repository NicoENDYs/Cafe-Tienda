document.getElementById("fileInput").addEventListener("change", function (e) {
  const fileName = e.target.files[0]
    ? e.target.files[0].name
    : "Sin archivos seleccionados";
  document.getElementById("fileSelectedName").textContent = fileName;
});

function updateFileName(id) {
  const input = document.getElementById(`edit_fileInput${id}`);
  const nameSpan = document.getElementById(`edit_fileSelectedName${id}`);

  if (input.files.length > 0) {
    nameSpan.textContent = input.files[0].name;
  } else {
    nameSpan.textContent = "Sin archivos seleccionados";
  }
}

function eliminarProducto(id) {
  Swal.fire({
    title: "¿Estás seguro?",
    text: "¿Deseas eliminar este producto?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
    reverseButtons: true,
  }).then((result) => {
    if (result.isConfirmed) {
      // Redirigir al controlador de eliminación
      window.location.href = `../controllers/EliminarProducto.php?id=${id}`;
    }
  });
}
