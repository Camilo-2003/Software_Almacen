<?php include("ProhibirAcceso.php"); 

if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    header("Location: /Software_Almacen/App/Error.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="Img/logo_sena.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Préstamos | SENA</title>
    <link rel="stylesheet" href="Css/Préstamos.css">
    <link rel="stylesheet" href="vendor/fontawesome/css/all.min.css">
</head>
<body>
<header>
    <div class="container-rgs">
    <a href="<?php echo $pagina_regresar; ?>" class="rgs" title="Haz clic para volver"><i class="fas fa-reply"></i> Regresar</a>
    </div> 
    <div class="header-content">
        <img src="Img/logo_sena.png" alt="Logo Sena" class="logo">
        <h1>Gestión de Préstamos</h1>
    </div>
</header>

<main class="container">
    <div class="card-grid">
        <div class="option-card">
            <div class="icon-container">
                <span class="emojii"><i class="fas fa-laptop"></i></span>
            </div>
            <h2>Equipos</h2>
            <p class="description">Gestiona los préstamos y devoluciones de equipos informáticos y tecnológicos.</p>
            <div class="card-actions">
                <a href="Equipos.php" class="btn primary-btn">Préstamo de Equipos</a>
            </div>
            <img src="Img/images.png" alt="Equipos" class="card-img">
        </div>

        <div class="option-card">
            <div class="icon-container">
                <span class="emoji"><i class="fa-solid fa-boxes-packing"></i></span>
            </div>
            <h2>Materiales</h2>
            <p class="description">Administra los préstamos y devoluciones de diversos materiales de uso en el almacén.</p>
            <div class="card-actions">
                <a href="Materiales.php" class="btn primary-btn">Préstamo de Materiales</a>
            </div>
            <img src="Img/sena3.jpg" alt="Materiales" class="card-img">
        </div>
    </div>
</main>

</body>
</html>