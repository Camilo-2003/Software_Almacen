<!--<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/semantic.css">
        <script src="js/jquery.js"></script>
        <script src="js/semantic.js"></script>
        <link rel="stylesheet" href="css/estilo.css">
        <title>Consultar usuarios</title>
    </head>
    <body>
        <div class="ui container">
            <br>
            <h1 class="texto">Registro de usuarios</h1>
            <table class="ui green table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Edad</th>
                        <th>Dirección</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th class="cols" colspan="2">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <    ?php
                    include "conexion2.php";
                    $sql = "SELECT * FROM usuarios"; 
                    $query = $connect -> prepare($sql); 
                    $query -> execute(); 
                    $resultado = $query -> fetchAll(PDO::FETCH_OBJ); 
                    if($query -> rowCount() > 0){ 
                        foreach($resultado as $result){?>
                        <tr>
                            <td><    ?php echo $result->nombre;?></td>
                            <td><     ?php echo $result->apellido;?></td>
                            <td><     ?php echo $result->edad?></td>
                            <td><     ?php echo $result->direccion;?></td>
                            <td><     ?php echo $result->telefono;?></td>
                            <td><    ?php echo $result->correo;?></td>
                            <td class="ui left icon input ttd"><a href="actualizar.php?id=<  ?php echo $result->idusuario; ?>"><i class="edit icon"></i>ACTUALIZAR</a></td>
                            <td class="ui left icon input ttd"><a onclick="eliminar(<    ?php echo $result->idusuario; ?>)" href="#"><i class="trash alternate icon"></i>BORRAR</a></td>
                        </tr>
                    <     ?php 
                        }
                     }
                    // mysqli_close($conexion);
                    ?>
                </tbody>
            </table>
            <br>
            <a href="insertar.php"><input type="submit" class="ui inverted green button boton" value="INSERTAR OTRO USUARIO"></a>
            <br>
        </div>
        <script>
            function eliminar(id){
            respuesta=confirm("Está seguro de que desea eliminar este usuario?");
            if (respuesta){
                window.location="eliminar.php?id="+id;
            }
            }
        </script>
    </body>
    </html>