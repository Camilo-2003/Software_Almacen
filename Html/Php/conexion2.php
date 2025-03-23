<!--?php
  $servidor = "localhost";
  $usuario = "root";
  $password = "";
  try {
        $connect = new PDO("mysql:host=$servidor;dbname=almacen", $usuario, $password, 
        array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));      
        $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      }
  catch(PDOException $e)
      {
      echo "La conexión ha fallado: " . $e->getMessage();
      }
  $conexion = null; 
// define('DB_HOST','localhost');
// define('DB_USER','root');
// define('DB_PASS','');
// define('DB_NAME','php_insertarpdo');
 
// // Ahora, establecemos la conexión.
// try
// {
// // Ejecutamos las variables y aplicamos UTF8
// $connect = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME,DB_USER, DB_PASS,
// array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
// }
// catch (PDOException $e)
// {
// exit("Error: " . $e->getMessage());
// }

?>