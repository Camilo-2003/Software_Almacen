<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conexion = new mysqli("localhost", "root", "", "almacen");

if ($conexion->connect_errno) {
    die("❌ Error de conexión: " . $conexion->connect_error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="Img/logo_sena.png" type="image/x-icon">
    <title>Historial de Inventario</title>
    <link rel="stylesheet" href="../Css/historialphp.css">
</head>
<body>

    <header>
        <h1>📋 Historial de Inventario</h1>
    </header>

    

    <div class="container">
        <h2>📦 Materiales</h2>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Cantidad</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sqlM = "SELECT * FROM materiales ORDER BY id_material DESC";
                $resultM = $conexion->query($sqlM);

                if ($resultM->num_rows > 0) {
                    while ($row = $resultM->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['nombre']}</td>
                                <td>{$row['tipo']}</td>
                                <td>{$row['stock']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No hay materiales registrados.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <h2>💻 Equipos</h2>
        <table>
            <thead>
                <tr>
                    <th>Marca</th>
                    <th>Serial</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sqlE = "SELECT * FROM equipos ORDER BY id_equipo DESC";
                $resultE = $conexion->query($sqlE);

                if ($resultE->num_rows > 0) {
                    while ($row = $resultE->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['marca']}</td>
                                <td>{$row['serial']}</td>
                                <td>{$row['estado']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No hay equipos registrados.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>

<?php
$conexion->close();
?>
