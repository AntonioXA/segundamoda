<?php

/**
 * conexion_oracle: Con esta funcion nos conectaremos a la base de datos utilizando PDO
 *
 * @param  string $DB Nombre de la base de datos
 * @return mixed
 */
function conexion_oracle($DB) {
   $servername = "localhost";
   $username = "root";
   $password = "";
   $database = $DB; 

   
   $dsn = "mysql:host=$servername;dbname=$database;charset=UTF8";

   try {
      $conn = new PDO($dsn, $username, $password);
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      return $conn;
   } catch (PDOException $e) {
      $error_message = "Error conexión a la bd $username: " . $e->getMessage();
      error_log($error_message);
      return null;
   }
}

/**
 * consulta: Realiza una consulta devolviendo los registros recuperados en un array
 *
 * @param  string $qry Consulta que realizaremos en la base de datos
 * @param  mixed $conn Conexion a la base de datos
 * @param  string $modo Forma en la que se nos devolveran los datos de la consulta
 * @return mixed Retorna el resultado de la consulta
 */
function consulta($qry, $conn = null, $modo = 'ROW') {
   try {
      $resultA = 0;

      if (!$conn) {
         $conn = conexion_oracle("segundamoda");
         if (!$conn) {
            throw new Exception("Error de conexión a la BD");
         }
         $cierraConn = true;
      } else {
         $cierraConn = false;
      }

      $sth = $conn->prepare($qry);

      if (!$sth) {
         $e = $conn->errorInfo();
         throw new Exception('Error1: ' . $e[2]);
      }

      $sth->execute();

      if ($modo === 'ROW') {
         $resultA = $sth->fetchAll(PDO::FETCH_ASSOC);
      } elseif ($modo === 'COLUMN') {
         $resultA = $sth->fetchAll(PDO::FETCH_COLUMN);
      }

      if (empty($resultA)) {
         throw new Exception('No hay datos');
      }

      if ($cierraConn) {
         $conn = null;
      }

      return $resultA;
   } catch (Exception $error) {
      if ($conn && $cierraConn) {
         $conn = null;
      }
      return $error->getMessage();
   }
}


?>






