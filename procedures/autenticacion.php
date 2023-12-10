<?php

/**
 * uf_acredita: Con este metodo comprobaremos que el usuario y la contraseña son correctos
 *
 * @param  string $cUser Usuario
 * @param  string $tPass Contraseña
 * @return mixed
 */
function uf_acredita($cUser, $tPass) {
   $nResult = null;
   try {
      $conn = conexion_oracle("segundamoda");

      $pass = hash('sha512', $tPass);
      $cad = "CALL VerificarContraseña(:cUser, :tPass, @nResult)";
      $sth = $conn->prepare($cad);

      $sth->bindParam(':cUser', $cUser);
      $sth->bindParam(':tPass', $pass);
      $sth->execute();

      $sqlSelect = "SELECT @nResult AS nResult";
      $result = $conn->query($sqlSelect);
      $row = $result->fetch(PDO::FETCH_ASSOC);
      $nResult = $row['nResult'];

   } catch (PDOException $e) {
      return $e->getCode(); 
   } finally {
      if ($conn) {
         $conn = null;
      }
   }

   return $nResult;
}



/**
 * datos_usuario: Recupera todos los datos del usuario
 *
 * @param  string $cUser Id del usuario del que queremos recuperar sus datos
 * @return mixed Retorna los datos del usuario si existe en una array. Si no existe retorna 0.
 */
function datos_usuario($cUser) {
   $usuarioA = array();
   $conn = conexion_oracle('segundamoda');
   if ($conn) {
      try {
         // Utilizando PDO para preparar y ejecutar la consulta
         $sth = $conn->prepare("SELECT * FROM Usuario WHERE Usuario = :cUser");
         $sth->bindParam(':cUser', $cUser);
         $sth->execute();

         // Fetching as an associative array
         $usuarioA = $sth->fetch(PDO::FETCH_ASSOC);

      } catch (PDOException $e) {
         // Manejo de excepciones PDO
         $usuarioA['usuario'] = $cUser;
         
      } finally {
         // Cerrar la conexión en el bloque finally garantiza que se cierre incluso si hay una excepción
         if ($conn) {
            $conn = null;
         }
      }

      return $usuarioA;
   } else {
      return 0;
   }
}


/**
 * logout: Metodo para cerrar sesion
 *
 * @return void
 */
function logout()
{
   if (isset($_SESSION["usuario"])) { 
      session_destroy();
   }
   redireccion();
   exit;
}


/**
 * login: Metodo para iniciar sesion. Crea un formulario para que el usuario introduzca sus datos. 
 *        Crea variables de sesion con los datos del usuario
 *
 * @param  string $cUser Usuario
 * @param  string $tPass Contraseña
 * @return void
 */
function login($cUser='',$tPass='') {
   try {
      if (isset($_SESSION["usuario"])) throw new Exception (" Usuario " . $_SESSION["usuario"] . " conectado");

      if (!empty($cUser) && !empty($tPass)) {
         if (uf_acredita($cUser, $tPass) == '1') {
            $_SESSION["ip_usuario"] = $_SERVER['REMOTE_ADDR'];
            $_SESSION["usuario"] = $cUser;
            $usuarioA = datos_usuario($_SESSION["usuario"]);
            if (is_array($usuarioA)) {
               $_SESSION["nombre_usuario"] = $usuarioA['Nombre'];
               $_SESSION["correo_usuario"] = $usuarioA['CorreoElectronico'];
               $_SESSION["tfno_usuario"] = $usuarioA['Telefono'];
               $_SESSION["es_supervisor"] = $usuarioA['Rol'];
            }
            redireccion();
         } 
         else throw new Exception ("error de acceso");
      }
      else {
         echo 
         "<div id=\"login\">
            <h1>Inicio de sesión</h1>
            <form name=\"AA\" action=\"index.php?go=login\" method='post' target='_top'>
               <div >
                  <label for=\"Usuario\">Usuario</label>
                  <input type=\"text\" name=\"cUser\" id=\"cUser\" />
                </div>
               <div >
                  <label for=\"Contraseña\">Contraseña</label>
                  <input type=\"password\" name=\"tPass\" id=\"tPass\" />
                </div>
               <div class=\"login-submit\">
                  <input type=\"submit\" value=\"Login\" name=\"submit\">
               </div>
            </form>
            <form action=\"registro.html\" method='get' target='_top'>
               <div class=\"register-link\">
                  <label>¿No tienes una cuenta? </label>
                  <input type=\"submit\" value=\"Regístrate\">
               </div>
            </form>
         </div>";
      }
   } catch (Exception $error) {
      echo $error->getMessage();
      //alerta("Inicio de sesión",$error->getMessage());
      //listado_ofertas();
      //modal ("Inicio de sesion",$error->getMessage());      
   }
}

?>