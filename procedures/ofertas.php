<!--Este script nos servira para mostrar las imagenes en el fileInput cuando el usuario las suba en la creacion de anuncios -->
<script>
        function previewImage(inputId, boxId) {
            var input = document.getElementById(inputId);
            var box = document.getElementById(boxId);

            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    box.style.backgroundImage = "url('" + e.target.result + "')";
                };

                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>


<?php

/**
 * visualizar_oferta: Muestra los anuncios en el tablon, con sus respectivos botones
 *
 * @param  int $opcion Nos servira para diferenciar en que opcion del menu horizontal del index estamos
 * @param  array $oferta La oferta con todos sus datos a mostrar
 * @return void
 */
function visualizar_oferta($opcion, $oferta)
{
	$ofertante = $oferta['IdUser'];
	$foto = "fotos_anuncio/" . $oferta['FOTO1'];
	$id = $oferta['IdAnuncio'];
	$nombre = $oferta['Nombre'];
	$descripcion = $oferta['Descripcion'];
	$precio = $oferta['Precio'];
	$fecha = $oferta['SYS_INSERTED'];

	$editarURL = "index.php?go=ofertas/editar/" . $opcion . "/" . $id;
	$detalleURL = "index.php?go=ofertas/detalle/" . $opcion . "/" . $id;
	$cancelarURL = "index.php?go=ofertas/cancelar/" . $opcion . "/" . $id;

	//BOTONES
	$botones = "<button onclick=\"goURL('$detalleURL')\" type=\"button\" class=\"btn btn-sm btn-outline-secondary\">Ver</button>";
	if (!empty($_SESSION['usuario'])) {
		if ($opcion == 2 && $_SESSION['usuario'] == $ofertante && ($oferta['Visibilidad'] == 1 || $oferta['Visibilidad'] == 2)) {
			$botones .= "<button onclick=\"goURL('$editarURL')\" type=\"button\" class=\"btn btn-sm btn-outline-secondary\">Editar</button>";
			$botones .= "<button onclick=\"goURL('$cancelarURL')\" type=\"button\" class=\"btn btn-sm btn-outline-secondary\">Cancelar</button>";
		}
	}

		echo "<div class=\"col\">
		          <div class=\"card shadow-sm\">
		            <img src=\"$foto\" class=\"bd-placeholder-img card-img-top object-fit-scale\" width=\"100%\" height=\"225\"  alt=\"$nombre\">
		            <div class=\"card-body\">
		              <p class=\"card-text\">$nombre<br>$descripcion<br>$precio €</p>
		              <div class=\"d-flex justify-content-between align-items-center\">
		                <div class=\"btn-group\">$botones
		              </div>
		                <small class=\"text-body-secondary\">$fecha</small>
		              </div>
		            </div>
		          </div>
		        </div>";
}

/**
 * listado_ofertas: Hace la consulta a la base de datos para extraer los anuncios que van en cada tablon
 *
 * @param  int $opcion Nos servira para diferenciar en que opcion del menu horizontal del index estamos
 * @return void
 */
function listado_ofertas($opcion = 1)
{

    switch ($opcion) {
		case 1: //Escaparate
			$where[] = "visibilidad between 2 and 4";
			break;
		case 2: //Mis ofertas
			$where[] = "visibilidad != 5 and IdUser = '" . $_SESSION["usuario"] . "'";
            break;
		case 3: //Supervisor
			$where[] = "visibilidad = 1";
			break;
		}
	

	$titulo = '';
	if ($opcion == 2) {
		$titulo = 'MIS OFERTAS';
		$texto = "Consulta las ofertas de materiales que has publicado.";
	} elseif ($opcion == 3) {
		$titulo = 'SUPERVISIÓN DE OFERTAS';
		$texto = "Supervisión de las ofertas publicadas por los usuarios para aprobar, denegar o cancelar.";
	} else {
		$titulo = 'MATERIALES OFERTADOS';
		$texto = "En este portal puedes consultar las existencias de materiales nuevos que dejan de tener utilidad para ciertas unidades pero que pueden tener aún uso por otras.";
	}



	echo "<main><section class=\"text-center container\">";
	echo div_cabecera($titulo, $opcion, $texto);

	try {

		$ofertas = consulta("select * from vistaanuncioconfoto where " . implode(' and ', $where));
		

		
		if (!is_array($ofertas))
			throw new Exception($ofertas);

	
		echo "<div class=\"album  bg-body-tertiary\">
	  		<div class=\"container\">
	  			<div class=\"row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3\">";

		foreach ($ofertas as $arrDatos) {
			visualizar_oferta($opcion, $arrDatos);
		}
	} catch (Exception $error) {
		alerta('Consulta de Peticiones', $error->getMessage());
	}

	echo "</div></div></div></section></main>";
	echo footer();

}


/**
 * inserta_fotografia: Inserta las fotografias en la base de datos
 *
 * @param  int $i IdAnuncio. Es el id del anuncio al que pertenece la fotografía 
 * @param  int $num NumeroFoto. Es el orden en el que va la fotografía dentro del anuncio. Va desde el 1 hasta el 3 
 * @param  string $titulo Fichero. Nombre del fichero que tendrá la foto
 * @return mixed
 */
function inserta_fotografia($i, $num, $titulo)
{
    $conn = conexion_oracle("segundamoda");
    if ($conn) {
        try {
            $sqlinsert = "INSERT INTO foto (IdAnuncio, NumeroFoto, Fichero) VALUES (:i, :num, :titulo)";
            $stmt = $conn->prepare($sqlinsert);
            $stmt->bindParam(':i', $i);
            $stmt->bindParam(':num', $num);
            $stmt->bindParam(':titulo', $titulo);

            $stmt->execute();

        } catch (PDOException $e) {
            return $e->getMessage();
        } finally {
            $stmt = null;
            $conn = null;
        }
    }
}

/**
 * editar_oferta: Este metodo será usado para la edicion de los anuncios. Primero se lanza una consulta a la base de datos para
 * 				  recuperar los datos ya existentes y se crea un formulario con los datos escritos por defecto. El usuario los
 * 				  podrá editar y enviar el formulario
 *
 * @param  int $id_oferta Es el id de la oferta que queremos editar. 
 * @return void
 */
function editar_oferta($id_oferta)
{
	$conn = conexion_oracle("segundamoda");
	if ($conn){
    try {
       
		$sql = "SELECT * from vistaanuncioconfoto WHERE IdAnuncio = :id_oferta";
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':id_oferta', $id_oferta);
		$stmt->execute();
		$resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $nombre = $resultado['Nombre'];
		$descripcion = $resultado['Descripcion'];
		$ubicacion = $resultado['Ubicacion'];
		$precio = $resultado['Precio'];
		$id = $resultado['IdAnuncio'];

	echo "<main>";
	echo "<section class=\"text-center container\">";
	echo div_cabecera('Editar Porducto');
	if (isset($_SESSION["usuario"])) {
		
		echo"
		<form method=\"post\" action=\"index.php?go=procedures/procesar.php\" enctype=\"multipart/form-data\">
			<input type=\"hidden\" name=\"idAnuncio2\" id=\"idAnuncio2\" value='$id'/>
			<div class=\"form-group\">
				<label for=\"nombre\">Nombre del producto:</label>
				<input type=\"text\" name=\"nombre2\" id=\"nombre2\" value='$nombre'/>
			</div>
			<br>
			<div class=\"form-group\">
				<label for=\"descripcion\">Descripcion del producto:</label>
				<input type=\"text\" name=\"descripcion2\" id=\"descripcion2\" value='$descripcion'/>
			</div>
			<br>
			<div class=\"form-group\">
				<label for=\"localizacion\">Localizacion del producto:</label>
				<input type=\"text\" name=\"localizacion2\" id=\"localizacion2\" value='$ubicacion'/>
			</div>
			<br>
			<div class=\"form-group\">
				<label for=\"precio\">Precio:</label>
				<input type=\"text\" name=\"precio2\" id=\"precio2\" value='$precio'/>
			</div>
			<br>
			<!-- CARGAR LISTA DE ESTADOS DESDE BBDD -->
			<div class=\"form-group\">
				<label for=\"estado2\">Estado:</label>
				<select id=\"estado2\" name=\"estado2\">";
					echo lista_estados();
				echo "</select>
			</div>
			<br>
			<!-- CARGAR LISTA DE CATEGORIAS DESDE BBDD -->
			<div class=\"form-group\">
				<label for=\"categoria2\">Categoría:</label>
				<select id=\"categoria2\" name=\"categoria2\">";
					echo lista_categorias();
				echo "</select>
			</div>
			<div class=\"bt-submit\">
				<input type=\"submit\" value=\"Editar\" name=\"enviar2\" />
			</div>
		</form>";
		
		
	}
	echo "</section></main>";


		

    } catch (Exception $e) {
       
        alerta('', $e->getMessage(), 'error');
        detalle_oferta(3, $id_oferta);
    }
	finally {
		$stmt = null;
		$conn = null;
	}
}
}


/**
 * aprobar_oferta: Funcion utilizada para que el supervisor pueda aprobar las ofertas creadas por los usuarios. Cuando se
 * 				   utiliza la oferta pasara a ser visible para todos los usuarios
 *
 * @param  int $id_oferta Es el id de la oferta que queremos aprobar. 
 * @return void
 */
function aprobar_oferta($id_oferta)
{
	$conn = conexion_oracle("segundamoda");
	if ($conn){
    try {
		$sqlinsert = "UPDATE anuncio SET Visibilidad = 2 WHERE IdAnuncio = :id_oferta";
		$stmt = $conn->prepare($sqlinsert);
		$stmt->bindParam(':id_oferta', $id_oferta);

		$stmt->execute();
        alerta('', 'La oferta ha sido aprobada y comunicada al usuario', 'ok');
        listado_ofertas(3);

		reg_historial($id_oferta, 'VIS', $_SESSION['usuario']);

    } catch (Exception $e) {
        alerta('', $e->getMessage(), 'error');
        detalle_oferta(3, $id_oferta);
    }
	finally {
		$stmt = null;
		$conn = null;
	}
}
}

/**
 * rechazar_oferta: Funcion utilizada para que el supervisor pueda rechazar las ofertas creadas por los usuarios. La oferta 
 * 					seguirá siendo visible solo para el ofertante, el cual deberá de editarla para que el supervisor pueda
 * 					volver a evaluarla
 *
 * @param  int $id_oferta Es el id de la oferta que queremos cancelar. 
 * @param  string $motivo Motivo por el cual se ha rechazado la oferta.
 * @return void
 */
function rechazar_oferta($id_oferta, $motivo)
{
	$conn = conexion_oracle("segundamoda");
	if ($conn){
    try {
        // $sql2 = "SELECT * FROM Usuario WHERE Usuario = (Select IdUser from Anuncio where IdAnuncio = :id_oferta);";
        //     $stmt2 = $conn->prepare($sql2);
        //     $stmt2->bindParam(':id_oferta', $id_oferta);
        //     $stmt2->execute();

        //     $resultado = $stmt2->fetch(PDO::FETCH_ASSOC);
			
			// $para = $resultado["CorreoElectronico"];
			// $asunto = "Su anuncio ha sido rechazado";
			// $mensaje = "Su anuncio ha sido rechazado por el supervisor. Debera de editarlo para que el supervisor lo pueda volver a evaluar. Motivo ". $motivo;
			// $cabeceras = "From: cc2mafla@uco.es\r\n";
			// $cabeceras .= "MIME-Version: 1.0\r\n";
			// $cabeceras .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
			// mail($para, $asunto, $mensaje, $cabeceras);

			$conn = null;

			reg_historial($id_oferta, 'NOV', $_SESSION['usuario']);
		
        alerta('', 'La oferta ha sido cancelada y comunicada al usuario', 'ok');
        listado_ofertas(3);

    } catch (Exception $e) {
        alerta('', $e->getMessage(), 'error');
        detalle_oferta(3, $id_oferta);
    }
	finally {
		$stmt = null;
		$conn = null;
	}
}
}

/**
 * cancelar_oferta_supervisor: Funcion utilizada para que el supervisor pueda cancelar las ofertas creadas por los usuarios. Cuando
 * 							   se cancela una oferta no podrá ser editada ni publicada
 *
 * @param  int $id_oferta Es el id de la oferta que queremos cancelar.
 * @param  string $motivo Motivo por el cual se ha rechazado la oferta.
 * @return void
 */
function cancelar_oferta_supervisor($id_oferta, $motivo)
{
	$conn = conexion_oracle("segundamoda");
	if ($conn){
    try {
		$sqlinsert = "UPDATE anuncio SET Visibilidad = 6 WHERE IdAnuncio = :id_oferta";
		$stmt = $conn->prepare($sqlinsert);
		$stmt->bindParam(':id_oferta', $id_oferta);

		$stmt->execute();
		$sql2 = "SELECT * FROM Usuario WHERE Usuario = (Select IdUser from Anuncio where IdAnuncio = :id_oferta);";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bindParam(':id_oferta', $id_oferta);
            $stmt2->execute();

            $resultado = $stmt2->fetch(PDO::FETCH_ASSOC);
			
			$para = $resultado["CorreoElectronico"];
			$asunto = "Su anuncio ha sido cancelado";
			$mensaje = "Su anuncio ha sido cancelado por el supervisor. Motivo ". $motivo;
			$cabeceras = "From: cc2mafla@uco.es\r\n";
			$cabeceras .= "MIME-Version: 1.0\r\n";
			$cabeceras .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
			mail($para, $asunto, $mensaje, $cabeceras);

			$conn = null;

			reg_historial($id_oferta, 'COS', $_SESSION['usuario']);

        alerta('', 'La oferta ha sido cancelada y comunicada al usuario', 'ok');
        listado_ofertas(3);

    } catch (Exception $e) {
        alerta('', $e->getMessage(), 'error');
        detalle_oferta(3, $id_oferta);
    }
	finally {
		$stmt = null;
		$conn = null;
	}
}
}

/**
 * cancelar_oferta: Funcion utilizada para que el ofertante pueda cancelar una de sus ofertas. 
 *
 * @param  int $id_oferta Es el id de la oferta que queremos cancelar. 
 * @return void
 */
function cancelar_oferta($id_oferta)
{
	$conn = conexion_oracle("segundamoda");
	if ($conn){
    try {
		$sqlinsert = "UPDATE anuncio SET Visibilidad = 6 WHERE IdAnuncio = :id_oferta";
		$stmt = $conn->prepare($sqlinsert);
		$stmt->bindParam(':id_oferta', $id_oferta);

		$stmt->execute();

        alerta('', 'La oferta ha sido cancelada', 'ok');

    } catch (Exception $e) {
        alerta('', $e->getMessage(), 'error');
    }
	finally {
		$stmt = null;
		$conn = null;
	}
}
}

/**
 * recuperar_categoria: Recupera el id de las categorias que tenemos en la base de datos
 *
 * @param  string $nombre Nombre de la categoria.
 * @return string Retorna el id de la categoria. Si no existe ninguna categoria con el nombre seleccionado retorna 
 * 			   un mensaje de error
 */
function recuperar_categoria($nombre) {

    $conn = conexion_oracle("segundamoda");

    if ($conn) {
        try {
            $sql = "SELECT IdCategoria FROM categoria WHERE Nombre = :Nombre";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':Nombre', $nombre);
            $stmt->execute();

            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultado) {
                return $resultado['IdCategoria'];
            } else {
                return "Categoría no encontrada";
            }
        } catch (PDOException $e) {
            return "Error: " . $e->getMessage();
        } finally {
            $conn = null;
        }
    } else {
        return "Error de conexión";
    }
}

/**
 * recuperar_estado: Recupera el id de los estados que tenemos en la base de datos
 *
 * @param  string $estado Nombre del estado
 * @return string Retorna el id del estado. Si no existe ningun estado con el nombre seleccionado retorna 
 * 			   un mensaje de error
 */
function recuperar_estado($estado) {

    $conn = conexion_oracle("segundamoda");

    if ($conn) {
        try {
            $sql = "SELECT IdEstado FROM estadomaterial WHERE Estado = :Estado";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':Estado', $estado);
            $stmt->execute();

            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultado) {
                return $resultado['IdEstado'];
            } else {
                return "Estado no encontrado";
            }
        } catch (PDOException $e) {
            return "Error: " . $e->getMessage();
        } finally {
            $conn = null;
        }
    } else {
        return "Error de conexión";
    }
}

/**
 * subirImagenes: Funcion utilizada para subir las fotografías de los anuncios al fichero para su almacenamiento
 *
 * @param  string $directorioDestino Ruta del directorio donde se subiran las fotografías. 
 * @param  int $oferta Id de la oferta al que pertenecen las fotos.
 * @return string Retorna un mensaje diciendo si se han subido los archivos al directorio de forma correcta o no.
 */
function subirImagenes($directorioDestino, $oferta)
{
    $archivosSubidos = [];
    $errores = [];

    // Array de tipos MIME de imágenes permitidos
    $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif'];

    // Loop a través de los tres posibles campos de carga
    for ($i = 1; $i <= 3; $i++) {
        $fileInputName = "fileInput" . $i;
        $tArchivo = basename($_FILES[$fileInputName]["name"]);

        if (!empty($tArchivo)) {
            $extArchivo = pathinfo($tArchivo, PATHINFO_EXTENSION);

            if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
                $numerodeimagen = $i;
                $nombreArchivo = $oferta . "_" . $numerodeimagen . "." . $extArchivo;
                $rutaArchivoDestino = $directorioDestino . '/' . $nombreArchivo;

                // Verificar si el tipo MIME del archivo es una imagen permitida
                if (in_array($_FILES[$fileInputName]['type'], $tiposPermitidos)) {
                    // Mover el archivo al directorio de destino
                    if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $rutaArchivoDestino)) {
                        $archivosSubidos[] = $tArchivo;
                        //Grabar en la base de datos
                        inserta_fotografia($oferta, $numerodeimagen, $nombreArchivo);
                    } else {
                        $errores[] = "Error al mover el archivo $nombreArchivo";
                    }
                } else {
                    $errores[] = "El archivo $tArchivo no es una imagen válida.";
                }
            }
        }
    }

    if (count($archivosSubidos) > 0) {
        return "Archivos subidos con éxito: " . implode(", ", $archivosSubidos);
    } else {
        $errores[] = "No se subieron archivos válidos.";
        return implode("<br>", $errores);
    }
}


/**
 * procesa_oferta: Recoge los datos introducidos por el usuario en el formulario de nueva oferta y llama a
 * 				   los metodos correspondientes para la subida del anuncio y de las imagenes
 *
 * @return void
 */
function procesa_oferta()
{
	if (isset($_SESSION["usuario"])) {
		$oferta = null;
		if (isset($_POST["nombre"], $_POST["descripcion"], $_POST["localizacion"], $_POST["precio"]) && !empty($_POST["nombre"]) && !empty($_POST["descripcion"]) && !empty($_POST["localizacion"]) && !empty($_POST["precio"]) && $_POST["precio"]>0) {

			
			$oferta = inserta_oferta(recuperar_categoria($_POST["categoria"]), $_POST["nombre"], recuperar_estado($_POST["estado"]), $_POST["localizacion"], $_POST["descripcion"], $_POST["precio"]);

			if (is_numeric($oferta) && $oferta > 0) {
				//echo "Se ha insertado datos $oferta";
				// Grabar e insertar las imagenes
				$directorioDestino = "./fotos_anuncio/";
				$resultado = subirImagenes($directorioDestino, $oferta);

				//Ha ido bien : redirige a la pagina de mis anuncios
				modal("Nuevo material", "Su oferta se han grabado correctamente<br>$resultado", htmlentities("index.php"));
				reg_historial($oferta, 'NEW', $_SESSION['usuario']);

			} else {
				//Vuelve a insertar oferta
				modal("Nuevo material", "error $oferta", htmlentities("index.php"));
			}
		} else {
			//Vuelve a insertar oferta
			modal("Nuevo material", "No has introducido todos los campos o alguno esta incorrecto", htmlentities("index.php"));

		}
	}
}

/**
 * inserta_oferta: Inserta el anuncio en la base de datos
 *
 * @param  int $categoria Categoria del anuncio
 * @param  string $nombre Nombre del anuncio
 * @param  int $estado Estado del anuncio
 * @param  string $ubicacion Ubicacion del anuncio
 * @param  string $descripcion Descripcion del anuncio
 * @param  double $precio Precio del anuncio
 * @return mixed Retorna el id de la ultima oferta subida 
 */
function inserta_oferta($categoria, $nombre, $estado, $ubicacion, $descripcion, $precio)
{
    $nOferta = null;
    $usuario = $_SESSION['usuario'];
    $fechaActual = date('Y-m-d');

    $conn = conexion_oracle("segundamoda");
    
    if ($conn) {
        try {
			//INSERT INTO anuncio (`IdAnuncio`, `Categoria`, `Nombre`, `Precio`, `Descripcion`, `IdUser`, `Ubicacion`, `EstadoDelAnuncio`, `Visibilidad`, `SYS_INSERTED`) VALUES (NULL, '3', 'Zapatillas Gucci', '75', 'Zapatillas Gucci casi nuevas', 'Maria', 'Malaga', '2', 1, '2023-12-23')
			$sqlinsert = "INSERT INTO anuncio (`IdAnuncio`, `Categoria`, `Nombre`, `Precio`, `Descripcion`, `IdUser`, `Ubicacion`, `EstadoDelAnuncio`, `Visibilidad`, `SYS_INSERTED`) VALUES (NULL, :categoria, :nombre, :precio, :descripcion, :usuario, :ubicacion, :estado, 1, :fechaActual)";

            $stmt = $conn->prepare($sqlinsert);
            $stmt->bindParam(':categoria', $categoria);
			$stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':ubicacion', $ubicacion);
            $stmt->bindParam(':descripcion', $descripcion);
			$stmt->bindParam(':precio', $precio);
			$stmt->bindParam(':usuario', $usuario);
			$stmt->bindParam(':fechaActual', $fechaActual);

            $stmt->execute();

            // Obtener el último ID insertado
            $nOferta = $conn->lastInsertId();

            return $nOferta;
        } catch (PDOException $e) {
            return $e->getMessage();
        } finally {
            // Cerrar la conexión
            $conn = null;
        }
    } else {
        return "Error de conexión";
    }
}



/**
 * lista_estados: Realiza una consulta a la base de datos para recuperar todos los estados para poder
 * 				  mostrarlos en un <option>
 *
 * @return string Retorna todos los <option> en una cadena de texto
 */
function lista_estados()
{
	$nResult = '';
    $conn = conexion_oracle("segundamoda");
	if ($conn) {
		$query = "SELECT Estado FROM estadomaterial";
		$result = $conn->query($query);
    	while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        	$nResult .= "<option value='" . $row['Estado'] . "'>" . $row['Estado'] . "</option>";
    	}
		$result = null;
		$conn = null;
	}
	return ($nResult);
}


/**
 * lista_categorias: Realiza una consulta a la base de datos para recuperar todas las categorias para poder
 * 				     mostrarlas en un <option>
 *
 * @return string Retorna todos los <option> en una cadena de texto
 */
function lista_categorias()
{
	$nResult = '';
    $conn = conexion_oracle("segundamoda");
	if ($conn) {
		$query = "SELECT Nombre FROM categoria";
		$result = $conn->query($query);
    	while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        	$nResult .= "<option value='" . $row['Nombre'] . "'>" . $row['Nombre'] . "</option>";
    	}
		$result = null;
		$conn = null;
	}
	return ($nResult);
}

/**
 * nueva_oferta: Crea una formulario para que el usuario introduzca los datos de un nuevo anuncio
 *
 * @return void
 */
function nueva_oferta()
{
	echo "<main>";
	echo "<section class=\"text-center container\">";
	echo div_cabecera('Nuevo Porducto');
	if (isset($_SESSION["usuario"])) {
		?>
		<form method="post" action="index.php?go=ofertas/nueva" enctype="multipart/form-data">
			<div class="form-group">
				<label for="nombre">Nombre del producto:</label>
				<input type="text" name="nombre" id="nombre" />
			</div>
			<br>
			<div class="form-group">
				<label for="descripcion">Descripcion del producto:</label>
				<input type="text" name="descripcion" id="descripcion" />
			</div>
			<br>
			<div class="form-group">
				<label for="localizacion">Localizacion del producto:</label>
				<input type="text" name="localizacion" id="localizacion" />
			</div>
			<br>
			<div class="form-group">
				<label for="precio">Precio:</label>
				<input type="text" name="precio" id="precio" />
			</div>
			<br>
			<!-- CARGAR LISTA DE ESTADOS DESDE BBDD -->
			<div class="form-group">
				<label for="estado">Estado:</label>
				<select id="estado" name="estado">
					<?php echo lista_estados(); ?>
				</select>
			</div>
			<br>
			<!-- CARGAR LISTA DE CATEGORIAS DESDE BBDD -->
			<div class="form-group">
				<label for="categoria">Categoría:</label>
				<select id="categoria" name="categoria">
					<?php echo lista_categorias(); ?>
				</select>
			</div>
			<br>
			<div class="row">
				<div class="item col-lg-4 col-md-12 col-sm-12 col-xs-12">
					<div class="box" id="box1">
						<!-- Primer cuadro -->
					</div>
					<input type="file" class="file-button" id="fileInput1" name="fileInput1" onchange="previewImage('fileInput1', 'box1')" accept=".png, .gif, .jpg, .jpeg">
				</div>
				<div class="item col-lg-4 col-md-12 col-sm-12 col-xs-12">
					<div class="box" id="box2">
						<!-- Segundo cuadro -->
					</div>
					<input type="file" class="file-button" id="fileInput2" name="fileInput2" onchange="previewImage('fileInput2', 'box2')" accept=".png, .gif, .jpg, .jpeg">
				</div>
				<div class="item col-lg-4 col-md-12 col-sm-12 col-xs-12">
					<div class="box" id="box3">
						<!-- Tercer cuadro -->
					</div>
					<input type="file" class="file-button" id="fileInput3" name="fileInput3" onchange="previewImage('fileInput3', 'box3')" accept=".png, .gif, .jpg, .jpeg">
				</div>
			</div>
			<div class="bt-submit">
				<input type="submit" value="Enviar" name="enviar" />
			</div>
		</form>
		<?php
	}
	echo "</section></main>";

}

/**
 * reg_historial: Inserta un registro en el historial
 *
 * @param  int $idAnuncio Id del anuncio sobre el que se hace una accion
 * @param  string $accion Tipo de accion que se ha realizado
 * @param  int $IdUser Usuario que ha realizado la accion
 * @return void
 */
function reg_historial($idAnuncio, $accion, $IdUser){
	$fechaActual = date('Y-m-d H:i:s');
	try {
		$conn = conexion_oracle("segundamoda");
		if ($conn) {
			$sql = "INSERT INTO historial (IdHistorial, Accion, IdAnuncio, Fecha, IdUser) VALUES (NULL, :accion, :idAnuncio, :fecha, :idUser);";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bindParam(':idAnuncio', $idAnuncio);
                $stmt->bindParam(':accion', $accion);
				$stmt->bindParam(':fecha', $fechaActual);
				$stmt->bindParam(':idUser', $IdUser);
                if (!$stmt->execute()) {
                    $errorInfo = $stmt->errorInfo();
                    throw new Exception($errorInfo[2]);
                }
            }
			$conn = null;
		}
	
	} catch (Exception $e) {
        // Cerrar la conexión
        $conn = null;

        alerta('', $e->getMessage(),'error');
    }
	
}



/**
 * detalle_oferta: Nos muestra todos los datos y detalles de una oferta.
 *
 * @param  int $opcion Opcion que nos servira para mostrar unos botones u otros. 
 * @param  int $id_oferta Id de la oferta.
 * @return void
 */
function detalle_oferta($opcion, $id_oferta)
{
	$user = $_SESSION['usuario'];
	try {
		$conn = conexion_oracle("segundamoda");
		if ($conn) {
			$sql = "select count(*) as npet from peticion p where p.IdAnuncio = :id_oferta and p.EstadoSolicitud=1;";
            $stmt = $conn->prepare($sql);
			$stmt->bindParam(':id_oferta', $id_oferta);
			$stmt->execute();
			$resultado = $stmt->fetch(PDO::FETCH_ASSOC);

			$sql2 = "select IdPeticion from peticion where IdAnuncio = :id_oferta and IdUsuario=:user and EstadoSolicitud=1;";
            $stmt2 = $conn->prepare($sql2);
			$stmt2->bindParam(':id_oferta', $id_oferta);
			$stmt2->bindParam(':user', $user);
			$stmt2->execute();
			$resultado2 = $stmt2->fetch(PDO::FETCH_ASSOC);
		}
	} catch (Exception $e) {
        $conn = null;

        alerta('', $e->getMessage(),'error');
    }
	
	echo "<main>";
	echo "<section class=\"text-center container\">";
	echo div_cabecera("Detalle de la Oferta");
	$a = 0;

	$result = consulta("SELECT 
    o.*,
    p.IdUsuario AS USUARIO_RESERVA,
    p.IdPeticion,
    p.EstadoSolicitud,
    p.SYS_INSERTED AS FRESERVA 
	FROM vistaanuncioconfoto o
	LEFT JOIN peticion p ON o.IdAnuncio = p.IdAnuncio WHERE o.IdAnuncio = '$id_oferta' ORDER BY EstadoSolicitud ASC;");
	
	$result2 = consulta("SELECT IdPeticion, IdUsuario as USUARIO_RESERVA, IdAnuncio, EstadoSolicitud, SYS_INSERTED as FRESERVA FROM peticion  WHERE EstadoSolicitud = 3 AND IdAnuncio = '$id_oferta';");

	$result3 = consulta("SELECT Accion AS last_act from historial where IdAnuncio = '$id_oferta' ORDER BY Fecha DESC LIMIT 1;");


	$oferta = $result[0];
	$oferta2 = $result2[0];
	$oferta3 = $result3[0];
	

	if (!empty($resultado2)) {
		$a = 1;
	}
	

	$botones = opciones_detalle($id_oferta, $oferta['IdUser'], $oferta['Visibilidad'], $oferta3['last_act'], $resultado['npet'], $a);
	


	switch ($oferta['EstadoSolicitud']) {
		case 1:
			$e = 'Solicitado';
			$bg = 'bg-warning-subtle';
			break;
		case 2:
			$bg = 'bg-secondary-subtle';
			break;
		case 3:
			$e = 'Reservado';
			$bg = 'bg-danger-subtle';
			break;
		default:
			$bg = 'bg-secondary';
			break;
	}
	echo "<div class=\"card w-75 m-auto text-start\">";
	echo "<div class=\"row card-header align-items-baseline $bg\">";
	echo "<p class=\"col-6 fs-4 text-start\">" . $oferta['NOMBRE'] . "</p>";
	if ($oferta['EstadoSolicitud'] != 2)
		echo "<p class=\"col-6 text-end\">" . $e . "</p>";
	echo "</div>";

	mostrar_fotos(array($oferta['FOTO1'], $oferta['FOTO2'], $oferta['FOTO3']));
	

	echo "<div class=\"card-body\">";
	echo "<ul class=\"list-group list-group-flush\">";
	//Datos de la oferta
	echo "<li class=\"list-group-item\"><p class=\"$bg text-center\">Datos de la Oferta</p>";
	echo "<div class=\"float-start\">";
	echo "<p class=\"card-text\">Nombre: " . $oferta['Nombre'] . "</p>";
	echo "<p class=\"card-text\">Descripción: " . $oferta['Descripcion'] . "</p>";
	echo "<p class=\"card-text\">Categoría: " . $oferta['NombreCategoria'] . "</p>";
	echo "<p class=\"card-text\">Estado: " . $oferta['Estado'] . "</p>";
	echo "<p class=\"card-text\">Ubicacion: " . $oferta['Ubicacion'] . "</p>";
	echo "<p class=\"card-text\">Precio: " . $oferta['Precio'] . "</p>";
	echo "<p class=\"card-text\">Fecha de alta: " . $oferta['SYS_INSERTED']. "</p>";
	echo "</div>";
	echo "<div class=\"float-end\">";
	echo "</div></li>";

	//Datos del Ofertante 
	$ofertanteA = datos_usuario($oferta["IdUser"]);
	echo "<li class=\"list-group-item\"><p class=\"$bg text-center\">Usuario que ofrece el material</p>";
	echo "<div class=\"float-start\">";
	echo "<p class=\"card-text\">" . $ofertanteA['Usuario'] . "</p>";
	echo "<p class=\"card-text\">" . $ofertanteA['Nombre'] . "</p>";
	echo "</div>";
	echo "<div class=\"float-end\">";
	echo "<p class=\"card-text\"><img src=\"./img/bg/phone.png\" width=\"32px\">&nbsp;" . $ofertanteA['Telefono'] . "</p>";
	echo "<p class=\"card-text float-end user-select-none\"><img src=\"./img/bg/email.png\" width=\"32px\">&nbsp;" . $ofertanteA["CorreoElectronico"] . "</p>";
	echo "</div>";
	echo "</li>";
	//Aqui se muestran los botones relativos a la oferta
	if (is_array($botones)) {
		if ($opcion == 3 && isset($botones['S'])) {
			echo "<div class=\"d-flex justify-content-around align-items-center pt-3\">";
			foreach ($botones['S'] as $boton) {
				if ($boton['TIPO'] == 'SUBMIT')
					echo "<button onclick=\"goURL('index.php?go=ofertas/" . $boton['ACT'] . "/" . $id_oferta . "')\" type=\"submit\" ";
				elseif ($boton['TIPO'] == 'MODAL')
					echo "<button type=\"button\" data-bs-toggle=\"modal\" data-bs-target=\"#" . $boton['ACT'] . "\" ";
				echo "name=\"" . $boton['ACT'] . "\" class=\"btn btn-sm btn-primary " . $boton['ENABLE'] . "\">" . $boton['BT'] . "</button>";
			}
			echo "</div>";
		} elseif (isset($botones['O'])) {
			echo "<div class=\"d-flex justify-content-around align-items-center pt-3\">";
			foreach ($botones['O'] as $boton) {
				echo "<button onclick=\"goURL('index.php?go=ofertas/" . $boton['ACT'] . "/" . $id_oferta . "')\" type=\"submit\" name=\"" . $boton['ACT'] . "\" class=\"btn btn-sm btn-primary\">" . $boton['BT'] . "</button>";
			}
			echo "</div>";
		}
	}
	/*
	 * Los datos de peticiones y reservas solo se muestran para usuarios autorizados y si la oferta no está en estado 1
	 * (tiene que haber sido aprobada por el supervisor)
	 */
	if ($opcion != 3 && isset($_SESSION['usuario']) && $oferta['Visibilidad'] > 1 && $oferta['Visibilidad'] < 6) {
		//Datos de peticiones si hay
		if ($resultado['npet'] == 0 && empty($oferta['FRESERVA'])) {
			/**
			 * No hay peticiones pendientes ni reservas
			 * No se realizan acciones
			 **/
			echo "<li class=\"list-group-item\"><p class=\"$bg text-center\">Peticiones</p>";
			echo "No se han realizado peticiones de este producto";
			echo "</li>";
		} else {
			// NPETICIONES puede ser 0 si hay una reserva en este caso se mostrara solo la reserva
			if ($resultado['npet'] > 0 && empty($oferta2["USUARIO_RESERVA"])) {
				echo "<li class=\"list-group-item\"><p class=\"$bg text-center\">Peticiones</p>";
				if ($_SESSION['usuario'] == $oferta['IdUser']) {
					echo "<form method=\"post\" action=\"index.php?go=reservas/RES\">";

					$peticiones = consulta(" select * from peticion where IdAnuncio = " . $id_oferta . " and EstadoSolicitud = 1");
					$n = 0;
			

					foreach ($peticiones as $peticion) {
						$n = $n + 1;
						echo "<input type=\"radio\" name=\"peticion_seleccionada\" value=" . $peticion["IdPeticion"] ."> $n. ";
						$fecha = $peticion['SYS_INSERTED'];
						echo $peticion['IdUsuario'] . " " . $fecha . "<br>";
					}

				} else {
					echo "Se han realizado " . $resultado['npet'] . " peticiones a este producto";
				}
				echo "</li>";
			} elseif (!empty($oferta2["USUARIO_RESERVA"])) {
				if($_SESSION['usuario'] == $oferta['IdUser']){

				$reserva = consulta(" select * from peticion where IdAnuncio = " . $oferta["IdAnuncio"] . " and EstadoSolicitud = 3");

				echo "<li class=\"list-group-item\"><p class=\"$bg text-center\">Reserva</p>";
				foreach ($reserva as $reservas) {
					$fecha = $reservas['SYS_INSERTED'];
					echo $reservas['IdUsuario'] . " " . $fecha;
				}
				echo "</li>";
			}
			else{
				echo "<li class=\"list-group-item\"><p class=\"$bg text-center\">Reserva</p>";
				echo "Este producto ya esta reservado";
				echo "</li>";
			}
			}
		}
		//Botones relativos a las peticiones o reservas
		if (isset($botones['P'])) {
			echo "<div class=\"d-flex justify-content-around align-items-center pt-3\">";
			foreach ($botones['P'] as $boton) {
				
				echo "<button onclick=\"goURL('index.php?go=ofertas/" . $boton['ACT'] . "/" . $id_oferta . "')\" type=\"submit\" name=\"" . $boton['ACT'] . "\" class=\"btn btn-sm btn-primary\">" . $boton['BT'] . "</button>";
				if ($boton["ACT"] == 'RES') {
					echo "</form>";
				}
			}
			echo "</div>";
		}




	}
	
	echo "</ul></div</div>";
	echo "</section></main>";
	echo footer();


}



?>
