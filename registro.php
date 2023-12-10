<?php
// Recoge todos los datos que el usuario ha introducido en el formulario de registro y los inserta en la 
// base de datos

$nombreusuario = $_POST['nombreusuario'];
$pass = hash('sha512', $_POST['password']);
$nombre = $_POST['nombre'];
$telefono = $_POST['telefono'];
$email = $_POST['email'];

// Validar que el teléfono contenga solo números
if (!preg_match('/^[0-9]+$/', $telefono)) {
   echo "Por favor, introduce solo números en el campo de teléfono.";
   exit(); 
}

$servername = "localhost";
$username = "root";
$password = "";
$database = "segundamoda";

$dsn = "mysql:host=$servername;dbname=$database;charset=UTF8";

try {
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT COUNT(*) FROM usuario WHERE Usuario = :nombreusuario");
    $stmt->bindParam(':nombreusuario', $nombreusuario);
    $stmt->execute();
    $result = $stmt->fetchColumn();

    if ($result > 0) {
        echo "El nombre de usuario ya está registrado.";
        header("refresh:3;url=index.php");
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM usuario WHERE CorreoElectronico = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $result = $stmt->fetchColumn();

        if ($result > 0) {
            echo "El correo electrónico ya está registrado.";
            header("refresh:3;url=index.php");
        } else {
            $cad = "CALL registro(:username, :pass, :nombre, :telefono, :email)";
            $sth = $conn->prepare($cad);

            $sth->bindParam(':username', $nombreusuario);
            $sth->bindParam(':pass', $pass);
            $sth->bindParam(':nombre', $nombre);
            $sth->bindParam(':telefono', $telefono);
            $sth->bindParam(':email', $email);
            $sth->execute();

            echo "Registro exitoso.";
            header("refresh:3;url=index.php");
        }
    }
} catch (PDOException $e) {
    echo $e->getCode();
} finally {
    if ($conn) {
        $conn = null;
    }
}
?>
