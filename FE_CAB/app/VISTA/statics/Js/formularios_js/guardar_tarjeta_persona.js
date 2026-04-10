function guardarAvance(fase) {
  fetch('guardar_avance', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'fase=' + fase
  })
  .then(response => response.text())
  .then(data => {
    console.log('Respuesta:', data);
    if (data.includes('OK')) {
      window.location.href = '../../dashboard';
    } else {
      alert('Error al guardar avance: ' + data);
    }
  });
}