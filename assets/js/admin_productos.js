document.getElementById('fileInput').addEventListener('change', function(e) {
    const fileName = e.target.files[0] ? e.target.files[0].name : 'Sin archivos seleccionados';
    document.getElementById('fileSelectedName').textContent = fileName;
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