<?php

/**
 * listado_peticiones: Lista todas las peticiones que haya hecho el usuario conectado
 *
 * @return void
 */
function listado_peticiones() {
	try {
		echo "<main><section class=\"text-center container\">";
		echo div_cabecera('MIS PETICIONES');
		$peticiones = consulta("select o.*, p.IdPeticion, p.IdAnuncio, p.IdUsuario, p.EstadoSolicitud, p.SYS_INSERTED as FechaPeticion
						from vistaanuncioconfoto o join peticion p on o.IdAnuncio = p.IdAnuncio
						where p.IdUsuario = '".$_SESSION['usuario']."' and p.EstadoSolicitud = 1
						order by p.EstadoSolicitud, o.SYS_INSERTED ");
	

		if (!is_array($peticiones)) throw new Exception ($peticiones);
		else {
			echo "<div class=\"album  bg-body-tertiary\">
    				<div class=\"container\">";
    		echo "<form name=\"consultaPeticion\" action=\"index.php?go=peticiones/detalle\" method=\"post\">";
			echo "<div class=\"row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3\">";
			foreach($peticiones as $peticion) {
				visualizar_peticion($peticion);
			}
			echo "</div></div>";
			echo "</form></div>";
		}
		
	} catch (Exception $error) {
		alerta ('Consulta de Peticiones', $error->getMessage());
	}

	echo "</section></main>";
	echo footer();
	
}

/**
 * visualizar_peticion: Visualiza todos los datos de la oferta a la que se le ha hecho la peticion
 *
 * @param  array $peticion
 * @return void
 */
function visualizar_peticion($peticion)
{

	$foto = "fotos_anuncio/" . $peticion['FOTO1'];
	$nombre = $peticion['Nombre'];
	$descripcion = $peticion['Descripcion'];
	$fecha = $peticion['SYS_INSERTED'];
	$precio = $peticion['Precio'];
	echo "<div class=\"col\">
	        <div class=\"card shadow-sm\">
	        	<img src=\"$foto\" class=\"bd-placeholder-img card-img-top object-fit-scale\" width=\"100%\" height=\"225\" role=\"img\" preserveAspectRatio=\"xMidYMid slice\" alt=\"$nombre\">
	            <div class=\"card-body\">
	            	<p class=\"card-text\">$nombre<br>$descripcion<br>$precio €</p>
	              	<div class=\"d-flex justify-content-between align-items-center\">
	              		<input type=\"hidden\" name=\"peticion".$peticion['IdPeticion']."\" value=\"".array_envia($peticion)."\">
	              		</input>
	                	<div class=\"btn-group\">
	                		<button type=\"submit\" id=\"btVerPeticion\" name=\"btVerPeticion\" value=\"".$peticion['IdPeticion']."\" class=\"btn btn-sm btn-outline-secondary\">Ver</button>
	                	</div>
	              	</div>
	                <small class=\"text-body-secondary\">Solicitado: $fecha</small>
	            </div>
	        </div>
	        </div>";
			

}

/**
 * peticion_oferta: Inserta un registro en la tabla peticion
 *
 * @param  int $id_oferta Id de la oferta a la cual queremos hacer una peticion
 * @return void
 */
function peticion_oferta($id_oferta){
	$fechaActual = date('Y-m-d');
	try {
		$conn = conexion_oracle("segundamoda");
		if ($conn) {
			$sql = "INSERT INTO peticion (IdPeticion, IdAnuncio, IdUsuario, EstadoSolicitud, SYS_INSERTED) VALUES (NULL, :id_oferta, :usuario, 1, :fechaActual);";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bindParam(':id_oferta', $id_oferta);
                $stmt->bindParam(':usuario', $_SESSION['usuario']);
				$stmt->bindParam(':fechaActual', $fechaActual);

                if (!$stmt->execute()) {
                    $errorInfo = $stmt->errorInfo();
                    throw new Exception($errorInfo[2]);
                }
            }
			// $sql2 = "SELECT * FROM Usuario WHERE Usuario = (Select IdUser from Anuncio where IdAnuncio = :id_oferta);";
            // $stmt2 = $conn->prepare($sql2);
            // $stmt2->bindParam(':id_oferta', $id_oferta);
            // $stmt2->execute();

            // $resultado = $stmt2->fetch(PDO::FETCH_ASSOC);
			
			// $para = $resultado["CorreoElectronico"];
			// $asunto = "Ha recibido una peticion a uno de sus anuncios";
			// $mensaje = "Ha recibido una peticion a uno de sus anuncios, puede revisarla en la seccion mis anuncios";
			// $cabeceras = "From: cc2mafla@uco.es\r\n";
			// $cabeceras .= "MIME-Version: 1.0\r\n";
			// $cabeceras .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
			// mail($para, $asunto, $mensaje, $cabeceras);

			$conn = null;
			reg_historial($id_oferta, "PET", $_SESSION['usuario']);
		}

		alerta('', 'La peticion ha sido realizada y comunicado al usuario','ok');
		listado_ofertas(1);	
	
	} catch (Exception $e) {
        // Cerrar la conexión
        $conn = null;

        alerta('', $e->getMessage(),'error');
		detalle_oferta(1,$id_oferta);
    }
	
}

/**
 * cancelar_peticion: Esta funcion sirve para cancelar una peticion ya existente
 *
 * @param  int $idPet Id de la peticion que vamos a cancelar
 * @param  string $accion Accion 
 * @return void
 */
function cancelar_peticion($idPet, $accion) {
	try {
		$conn = conexion_oracle("segundamoda");
		if ($conn) {
			$sql = "UPDATE peticion SET EstadoSolicitud = 6 WHERE IdPeticion = :idPet;";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bindParam(':idPet', $idPet);
                

                if (!$stmt->execute()) {
                    $errorInfo = $stmt->errorInfo();
                    throw new Exception($errorInfo[2]);
                }
            }
			// if(!isset($_POST['motivoRechazo'])){
			// $sql2 = "SELECT U.* FROM Peticion P JOIN Usuario U ON P.IdUsuario = U.Usuario WHERE P.IdPeticion = :idPet;";
			// $stmt2 = $conn->prepare($sql2);
            // $stmt2->bindParam(':idPet', $idPet);
            // $stmt2->execute();

            // $resultado = $stmt2->fetch(PDO::FETCH_ASSOC);
			// $para = $resultado["CorreoElectronico"];
			// $asunto = "Su peticion ha sido rechazada";
			// $mensaje = "Una de las peticiones que ha realizado ha sido rechazada";
			// $cabeceras = "From: cc2mafla@uco.es\r\n";
			// $cabeceras .= "MIME-Version: 1.0\r\n";
			// $cabeceras .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
			// mail($para, $asunto, $mensaje, $cabeceras);
			// }

            $sql3 = "SELECT IdAnuncio FROM peticion WHERE IdPeticion = :idPet";
			$stmt3 = $conn->prepare($sql3);
            $stmt3->bindParam(':idPet', $idPet);
            $stmt3->execute();

            $resultado = $stmt3->fetch(PDO::FETCH_ASSOC);

			$conn = null;

            reg_historial($resultado['IdAnuncio'], $accion, $_SESSION['usuario']);

		}
	} catch (Exception $e) {
        // Cerrar la conexión
        $conn = null;

        alerta('', $e->getMessage(), 'error');
        detalle_oferta(1, $idPet);
    }


}

/**
 * reservar: Reserva una peticion y anula el resto de peticiones que tiene un anuncio
 *
 * @param  int $idPet Id de la peticion que queremos reservar
 * @return void
 */
function reservar($idPet) {
    try {
        $conn = conexion_oracle("segundamoda");

        if ($conn) {

			$sql = "UPDATE peticion SET EstadoSolicitud = 3 WHERE IdPeticion = :idPet;";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bindParam(':idPet', $idPet);
                

                if (!$stmt->execute()) {
                    $errorInfo = $stmt->errorInfo();
                    throw new Exception($errorInfo[2]);
                }
            }

			$sql2 = "select IdPeticion from peticion where IdAnuncio = (select IdAnuncio from peticion where IdPeticion = :idPet) and EstadoSolicitud = 1";
			$stmt2 = $conn->prepare($sql2);
			if ($stmt2) {
                $stmt2->bindParam(':idPet', $idPet);
                

                if (!$stmt2->execute()) {
                    $errorInfo = $stmt->errorInfo();
                    throw new Exception($errorInfo[2]);
                }
            }
			
			while ($peticiones = $stmt2->fetch(PDO::FETCH_ASSOC)) {
				cancelar_peticion($peticiones['IdPeticion'], "CPO");
			}

            // $sql3 = "SELECT * FROM Usuario WHERE Usuario = (Select IdUsuario from Peticion where IdPeticion = :idPet);";
            // $stmt3 = $conn->prepare($sql3);
            // $stmt3->bindParam(':idPet', $idPet);
            // $stmt3->execute();

            // $resultado = $stmt3->fetch(PDO::FETCH_ASSOC);
			
			// $para = $resultado["CorreoElectronico"];
			// $asunto = "Su peticion ha sido aceptada";
			// $mensaje = "Una de las peticiones que ha realizado ha sido aceptada";
			// $cabeceras = "From: cc2mafla@uco.es\r\n";
			// $cabeceras .= "MIME-Version: 1.0\r\n";
			// $cabeceras .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
			// mail($para, $asunto, $mensaje, $cabeceras);

            $sql4 = "SELECT IdAnuncio FROM peticion WHERE IdPeticion = :idPet";
			$stmt4 = $conn->prepare($sql4);
            $stmt4->bindParam(':idPet', $idPet);
            $stmt4->execute();

            $resultado2 = $stmt4->fetch(PDO::FETCH_ASSOC);

			$sql = "UPDATE anuncio SET Visibilidad = 3 WHERE IdAnuncio = :idAnuncio;";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bindParam(':idAnuncio', $resultado2['IdAnuncio']);
                

                if (!$stmt->execute()) {
                    $errorInfo = $stmt->errorInfo();
                    throw new Exception($errorInfo[2]);
                }
            }

            $conn = null;
            reg_historial($resultado2['IdAnuncio'], 'RES', $_SESSION["usuario"]);
        }

        alerta('', 'La peticion ha sido reservada', 'ok');

    } catch (Exception $e) {
        // Cerrar la conexión
        $conn = null;

        alerta('', $e->getMessage(), 'error');
        detalle_oferta(1, $idPet);
    }
}

/**
 * detalle_peticion: Muestra todos los detalles de una peticiom
 *
 * @param  int $idPeticion Id de la peticion de la cual queremos ver sus detalles 
 * @param  array $peticion Array que contiene todos los datos del anuncio al cual se le ha hecho la peticion
 * @return void
 */
function detalle_peticion($idPeticion, $peticion)
{
	echo "<main><section class=\"text-center container\">";
	echo div_cabecera("Detalle de la petición");
	

	switch ($peticion['EstadoSolicitud']) {
		case 1: 
			$bg='bg-warning-subtle';
			$estSol = 'Solicitado';
			break;
		case 3: 
			$bg='bg-secondary-subtle';
			$estSol = 'Reservado';
			break;
		case 6: 
			$bg='bg-danger-subtle';
			$estSol = 'Cancelada';
			break;
	}

	
	
	echo "<div class=\"card w-75 m-auto text-start\">";
	echo "<div class=\"row card-header align-items-baseline $bg\">";
	echo "<p class=\"col-6 fs-4 text-start\">".$peticion['Nombre']."</p>";
	echo "<p class=\"col-6 text-end\">".$estSol."</p>";
	echo "</div>";


	mostrar_fotos(array($peticion['FOTO1'],$peticion['FOTO2'],$peticion['FOTO3']));

    echo "<div class=\"card-body\">";
    echo "<ul class=\"list-group list-group-flush\">";
    //Datos de la oferta
	echo "<li class=\"list-group-item\"><p class=\"$bg text-center\">Datos de la Oferta</p>";
	echo "<div class=\"float-start\">";
	echo "<p class=\"card-text\">Descripción: ".$peticion['Descripcion']."</p>";
	echo "<p class=\"card-text\">Categoría: ".$peticion['NombreCategoria']."</p>";
	echo "<p class=\"card-text\">Precio: ".$peticion['Precio']."</p>";
	echo "<p class=\"card-text\">Estado: ".$peticion['Estado']."</p>";
	echo "<p class=\"card-text\">Ubicacion: ".$peticion['Ubicacion']."</p>";
	echo "<p class=\"card-text\">Fecha de alta: ".$peticion['FechaPeticion']."</p>";
	echo "</div>";
	//Datos del Ofertante 
    $ofertanteA = datos_usuario($peticion['IdUser']);
	echo "<li class=\"list-group-item\"><p class=\"$bg text-center\">Usuario que ofrece el material</p>";
	echo "<div class=\"float-start\">";
	echo "<p class=\"card-text\">" . $ofertanteA['Usuario'] . "</p>";
	echo "<p class=\"card-text\">" . $ofertanteA['Nombre'] . "</p>";
	echo "</div>";
	echo "<div class=\"float-end\">";
	echo "<p class=\"card-text\"><img src=\"./img/bg/phone.png\" width=\"32px\">&nbsp;" . $ofertanteA['Telefono']."</p>";
	echo "<p class=\"card-text float-end user-select-none\"><img src=\"./img/bg/email.png\" width=\"32px\">&nbsp;" . $ofertanteA['CorreoElectronico']."</p>";
	echo "</div>";
	echo "</li>";

    //Botones

	$botones = opciones_detalle($idPeticion, $peticion['IdUser'], $peticion['Visibilidad'], '', '', true);
    echo "<div class=\"d-flex justify-content-around align-items-center pt-3\">";
	foreach ($botones['P'] as $boton) {
		
		if ($boton['TIPO']=='SUBMIT')
			echo "<button onclick=\"goURL('index.php?go=peticiones/".$boton['ACT']."/".$peticion['IdAnuncio']."')\" type=\"submit\" ";
		elseif ($boton['TIPO']=='MODAL') 
			echo "<button type=\"button\" data-bs-toggle=\"modal\" data-bs-target=\"#".$boton['ACT']."\" ";
		
		echo "name=\"" . $boton['ACT'] . "\" class=\"btn btn-sm btn-primary \">" . $boton['BT'] . "</button>";

	}
    
    echo "</div>";
	echo "</ul></div</div>";


	

	echo "</section></main>";
	echo footer();

}
?>