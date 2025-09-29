<!DOCTYPE html>
<html>
<head>
    <title>Tabla Desplegable</title>
    <script>
        function mostrarDetalles(id) {
            var detalles = document.getElementById('detalles_' + id);
            detalles.style.display = detalles.style.display === 'none' ? 'table-row' : 'none';
        }
    </script>
    <style>
        .detalles {
            display: none;
        }
    </style>
</head>
<body>

<table border="1">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Detalles</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>Usuario 1</td>
            <td><button onclick="mostrarDetalles(1)">Mostrar</button></td>
        </tr>
        <tr class="detalles" id="detalles_1">
            <td colspan="3">Detalles del usuario 1: Información adicional aquí...</td>
        </tr>
        <tr>
            <td>2</td>
            <td>Usuario 2</td>
            <td><button onclick="mostrarDetalles(2)">Mostrar</button></td>
        </tr>
        <tr class="detalles" id="detalles_2">
            <td colspan="3">Detalles del usuario 2: Información adicional aquí...</td>
        </tr>
        <!-- Puedes agregar más filas aquí -->
    </tbody>
</table>

</body>
</html>
