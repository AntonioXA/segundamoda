<?php
//Recogemos los datos que el usuario ha escrito en el formulario de edicion y lanzamos la consulta a la base de datos
if (isset($_POST['enviar2'])) {
    $idAnuncio = $_POST['idAnuncio2'];
    $nombre = $_POST['nombre2'];
    $descripcion = $_POST['descripcion2'];
    $ubicacion = $_POST['localizacion2'];
    $precio = $_POST['precio2'];
    $estado = recuperar_estado($_POST['estado2']);
    $categoria = recuperar_categoria($_POST['categoria2']);

    echo 'aaa  '.$idAnuncio. ' ' .$nombre.'  '.$descripcion.'  '.$ubicacion.'  '. $precio.' '.$estado.' '.$categoria.' aaa';

    $conn = conexion_oracle("segundamoda");

    if ($conn) {
        try {
            
            $sqlUpdate = "UPDATE anuncio SET 
            Nombre = :nombre,
            Descripcion = :descripcion,
            Ubicacion = :ubicacion,
            Precio = :precio,
            EstadoDelAnuncio = :estado,
            Visibilidad = 1,
            Categoria = :categoria
            WHERE anuncio.IdAnuncio = :id";
            $stmtUpdate = $conn->prepare($sqlUpdate);
            $stmtUpdate->bindParam(':nombre', $nombre);
            $stmtUpdate->bindParam(':descripcion', $descripcion);
            $stmtUpdate->bindParam(':ubicacion', $ubicacion);
            $stmtUpdate->bindParam(':precio', $precio);
            $stmtUpdate->bindParam(':estado', $estado);
            $stmtUpdate->bindParam(':categoria', $categoria);
            $stmtUpdate->bindParam(':id', $idAnuncio);
            $stmtUpdate->execute();

        } catch (Exception $e) {
            alerta('', $e->getMessage(), 'error');
            
        } finally {
            $stmtUpdate = null;
            $conn = null;
        }
    }
}

?>