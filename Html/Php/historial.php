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
    <br>
    <h2>📦 Materiales</h2><br>
    <div class="container">
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
                    echo "<tr><td colspan='3'>No hay materiales registrados en este momento.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div><br>
    <h2>💻 Equipos</h2><br>
      <div class="container2">
       
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
                    echo "<tr><td colspan='3'>No hay equipos registrados en este momento.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div><br>

<button class="btn-total" onclick="mostrarTotalesEquipos()">📊 Ver total de equipos por marca</button>

<h3 id="tituloTotales" style="display: none;">🔢 Equipos por Marca</h3>

<div class="container2" id="contenedorTabla" style="display: none; margin-top: 10px;">
  <table>
      <thead>
          <tr>
              <th>Marca</th>
              <th>Total</th>
          </tr>
      </thead>
      <tbody>
          <?php
          $sqlTotal = "SELECT marca, COUNT(*) AS total FROM equipos GROUP BY marca";
          $resultTotal = $conexion->query($sqlTotal);

          if ($resultTotal->num_rows > 0) {
              while ($row = $resultTotal->fetch_assoc()) {
                  echo "<tr>
                          <td>{$row['marca']}</td>
                          <td>{$row['total']}</td>
                        </tr>";
              }
          } else {
              echo "<tr><td colspan='2'>No hay datos disponibles en este momento.</td></tr>";
          }
          ?>
      </tbody>
  </table>
</div>

<button class="btn-total" onclick="mostrarTotalesMateriales()">📦 Ver total de materiales por tipo</button>

<h3 id="tituloTotalesMateriales" style="display: none;">📋 Total de Materiales por Tipo</h3>

<div class="container2" id="contenedorTablaMateriales" style="display: none; margin-top: 10px;">
  <table>
      <thead>
          <tr>
              <th>Tipo</th>
              <th>Total</th>
          </tr>
      </thead>
      <tbody>
          <?php
          $sqlMat = "SELECT tipo, COUNT(*) AS total FROM materiales GROUP BY tipo";
          $resultMat = $conexion->query($sqlMat);

          if ($resultMat->num_rows > 0) {
              while ($row = $resultMat->fetch_assoc()) {
                  echo "<tr>
                          <td>{$row['tipo']}</td>
                          <td>{$row['total']}</td>
                        </tr>";
              }
          } else {
              echo "<tr><td colspan='2'>No hay materiales registrados.</td></tr>";
          }
          ?>
      </tbody>
  </table>
</div>

<script src="../Js/historial.js"></script>

</body>
</html>

<?php
$conexion->close();
?>
