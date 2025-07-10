  function redirigir(accion,id) {
    window.location.href = '../../controllers/Mesero.php?accion=' + accion + '&id=' + id;
  }

  function redirigir2(accion, id, id_usuario, total) {
    window.location.href = '../../controllers/Mesero.php?accion=' + accion + '&id=' + id + '&id_usuario=' + id_usuario + '&total=' + total;
  }
