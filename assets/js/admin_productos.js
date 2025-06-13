document.getElementById('fileInput').addEventListener('change', function(e) {
    const fileName = e.target.files[0] ? e.target.files[0].name : 'Sin archivos seleccionados';
    document.getElementById('fileSelectedName').textContent = fileName;
});