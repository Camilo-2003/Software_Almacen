<!--<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/semantic.css">
    <script src="js/jquery.js"></script>
    <script src="js/semantic.js"></script>
    <link rel="stylesheet" href="css/estilo.css">
    <title>INSERTAR USUARIOS</title>
</head>
<body>
    <div class="ui container">
        <br>
        <h1 class="hh1">Formulario de insertar usuarios</h1>
            <form action="insertaru.php" class="ui form" method="POST">
            <div class="two fields">
                <div class="field">
                    <div class="ui pointing below label">
                        <label for="">Nombre(s)</label>
                    </div>
                    <div class="ui left icon input">
                        <input type="text" name="nombre" id="" placeholder="Ingrese su nombre">
                        <i class="user icon"></i>
                    </div>
                </div>
                <div class="field">
                    <div class="ui pointing below label">
                        <label for="">Apellido</label>
                    </div>
                    <div class="ui left icon input">
                        <input type="text" name="apellido" id="" placeholder="Ingrese su apellido">
                        <i class="user icon"></i>
                    </div>
                </div>
            </div>
                <div class="two fields">
                    <div class="field">
                        <div class="ui pointing below label">
                            <label for="">Edad</label>
                        </div>
                        <div class="ui left icon input">
                            <input type="number" name="edad" id="" placeholder="Ingrese su edad">
                            <i class="user icon"></i>
                        </div>
                    </div>
                    <div class="field">
                        <div class="ui pointing below label">
                            <label for="">Dirección</label>
                        </div>
                        <div class="ui left icon input">
                            <input type="text" name="direccion" id="" placeholder="Ingrese su dirección">
                            <i class="home icon"></i>
                        </div>
                    </div>
                </div>
            <div class="two fields">
                <div class="field">
                    <div class="ui pointing below label">
                        <label for="">Teléfono</label>
                    </div>
                    <div class="ui left icon input">
                        <input type="number" name="telefono" id="" placeholder="Ingrese el celular">
                        <i class="phone icon"></i>
                    </div>
                </div>
                <div class="field">
                    <div class="ui pointing below label">
                        <label for="">Correo</label>
                    </div>
                    <div class="ui left icon input">
                        <input type="text" name="correo" id="" placeholder="Ingrese su correo" required>
                        <i class="at icon"></i>
                    </div>
                </div>
            </div>
            <br>
            <input type="submit" name="btn_insertar" class="ui inverted green button boton">
            </form>

           
    </div>
</body>
</html>