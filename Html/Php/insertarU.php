<!--?php 
    include "conexio.php";
    // require
    if(isset($_POST['btn_insertar'])){
        $nombre=$_POST['nombre'];
        $apellido=$_POST['apellido'];
        $edad=$_POST['edad'];
        $direccion=$_POST['direccion'];
        $telefono=$_POST['telefono'];
        $correo=$_POST['correo'];
    
        // En este paso la siguiente instrucción prepara una sentencia para su ejecución y devuelve un objeto sentencia. 
        // Si observamos toma la variable $sql
        $sql="insert into usuarios(nombre,apellido,edad,direccion,telefono,correo) values(:nombre,:apellido,:edad,:direccion,:telefono,:correo)";
    
    $sql = $connect->prepare($sql);
        // Como podemos apreciar en este script, estamos vinculando un parámetro al nombre de variable especificado
        $sql->bindParam(':nombre',$nombre,PDO::PARAM_STR, 45);
        $sql->bindParam(':apellido',$apellido,PDO::PARAM_STR, 45);
        $sql->bindParam(':edad',$edad,PDO::PARAM_INT,3);
        $sql->bindParam(':direccion',$direccion,PDO::PARAM_STR,75);
        $sql->bindParam(':telefono',$telefono,PDO::PARAM_STR,15);
        $sql->bindParam(':correo',$correo,PDO::PARAM_STR,150);
        // Usando la instrucción "execute" podemos ejecutar la consulta preparada previamente
        $sql->execute();
        // Compruebe que la inserción realmente funcionó
        $lastInsertId = $connect->lastInsertId();
        if($lastInsertId>0){
            // echo "<script>window.location='cone/conexion.php';</script>";
            echo "<script>window.location='consulta.php';</script>";
        }else{
            print_r($sql->errorInfo()); 
        }
    }
?>