<?php
session_name("sm_logon");
session_start();
error_reporting(E_ERROR);
require_once("procedures/conexion.php");
require_once("procedures/autenticacion.php");
require_once("procedures/ofertas.php");
require_once("procedures/peticiones.php");
require_once("procedures/utilidades.php");
require_once("procedures/procesar.php");
?>
<!DOCTYPE html>
<HTML>

<head>
    <title>Segunda Moda - Compra de ropa de segunda mano</title>
    <meta name="keywords" content="Segunda Moda">
    <meta name="description" content="Segunda Moda">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="css/SM.css" rel="stylesheet">
	<link href="css/estilo.css" rel="stylesheet">
    <script src="bootstrap/js/color-modes.js"></script>
	<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<link href="bootstrap/css/docs.min.css" rel="stylesheet">

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
	<script type="text/javascript" src="lib/jquery-3.7.1.min.js"></script>
	<script src="js/script.js"></script>
	<script src="js/general.js"></script>
</head>

<body>

    <div class="container">
		<header class="d-flex justify-content-between align-items-center border-bottom">
			<div class="col-lg-3 col-md-3 col-sm-3 col-xs-12 text-start">
				<img class="img-fluid" src="img/bg/logos/SM.png" alt="Segunda Moda Logo" title="Segunda Moda" height="100">
			</div>
			<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-break text-center">
				<p class="fs-2"><span class="titulo_responsive"
						data-desktoptext="Segunda Moda"
						data-tablettext="Segunda Moda" data-phonetext="SM"></span></p>
			</div>
		</header>

		<!-- Menu horizontal -->
        <nav class="navbar navbar-expand-lg bg-body-tertiary">
			<div class="container-fluid">
				<div class="collapse navbar-collapse" id="navbarNav">
					<ul class="navbar-nav nav-underline">
						<li class="nav-item">
							<a href="index.php" class="nav-link link-body-emphasis px-2 " aria-current="page">Inicio</a>
						</li>
						<?php if (isset($_SESSION['usuario'])) { ?>
							<li class="nav-item">
								<a href="index.php?go=ofertas/list/2" class="nav-link link-body-emphasis px-2">Mis
									Anuncios</a>
							</li>
							<li class="nav-item">
								<a href="index.php?go=peticiones/list" class="nav-link link-body-emphasis px-2">Mis
									Peticiones</a>
							</li>
							<?php if ($_SESSION["es_supervisor"] == 1) { ?>
								<li class="nav-item"><a href="index.php?go=ofertas/list/3"
										class="nav-link link-body-emphasis px-2">Supervisar anuncios</a>
								</li>
							<?php } ?>
						<?php } ?>
					</ul>
				</div>
				<div>
					<ul class="nav align-items-center">
						<?php if (!isset($_SESSION['usuario'])) { ?>
							<li class="nav-item"><a href="index.php?go=login" class="nav-link link-body-emphasis px-2"><img
										class='login-ico' src='img/bg/logos/login.png'>Iniciar sesion</a></li>
						<?php } else { ?>
							<li class="nav-item">
								<?php echo $_SESSION['nombre_usuario']; ?><a href="index.php?go=logout"
									class="nav-link link-body-emphasis px-2">Cerrar Sesión</a>
							</li>
						<?php } ?>
					</ul>
				</div>
			</div>
			
		</nav>

        <!-- Fin Cabecera -->

		<!-- Sección principal -->
		<section class="row">
			<?php
			
			//Divide la cadena que se encuentra en $_GET['go'] en un array, utilizando '/' como el delimitador.
			if (!isset($go) && isset($_GET['go'])) {
				$go = explode('/', $_GET['go']);
			}
			
			
			switch ($go[0]) {
				case 'login':
					login($_POST['cUser'], $_POST['tPass']);
					break;
				case 'logout':
					logout();
					break;
				case 'ofertas':
					switch ($go[1]) {

						case 'list':
							if (empty($go[2]))
								$opcion = 1;
							else
								$opcion = $go[2];

							if (($opcion != 1 && !isset($_SESSION['usuario'])) || ($opcion == 3 && $_SESSION['es_supervisor'] == 0))
								$opcion = 1;
							else {
								if ($opcion == 2)
									$param['IdUser'] = $_SESSION['usuario'];
							}
							listado_ofertas($opcion);
							break;
						case 'nueva':
							if (!isset($_POST['nombre']))
								nueva_oferta();
							else {
								procesa_oferta();
							}
							break;
						case 'detalle':
							detalle_oferta($go[2], $go[3]);
							break;
						case 'editar':
							editar_oferta($go[3]);
							break;
						case 'PET':
							peticion_oferta($go[2]);
							break;
						case 'VIS':
							aprobar_oferta($go[2]);
							break;
						case 'NOV':
							rechazar_oferta($go[2], $_POST['motivoRechazo']);
							break;
						case 'COS':
							cancelar_oferta_supervisor($go[2], $_POST['motivoRechazo']);
							break;
						case 'cancelar':
							cancelar_oferta($go[3]);
							listado_ofertas();
							break;
						default:
							listado_ofertas();
							break;
					}
					break;
				case 'peticiones':
					if (!isset($_SESSION['usuario'])) {
						alerta('Consulta de peticiones', 'No hay usuario conectado');
						listado_ofertas();
					} else {
						switch ($go[1]) {
							case 'list':
								if (isset($_SESSION['usuario']))
									listado_peticiones();
								else {
									alerta('Consulta de peticiones', 'No hay usuario conectado');
									listado_ofertas();
								}
								break;
							case 'CPO':
								$motivo = "";
							case 'CPI':
								if(isset($_POST['motivoRechazo'])){
									$motivo = $_POST['motivoRechazo'];
								}
								cancelar_peticion($go[2], $go[1]);
								break;
							case 'detalle':
								if (isset($_POST['btVerPeticion'])) {
									$idPeticion = $_POST['btVerPeticion'];
									detalle_peticion($idPeticion, array_recibe($_POST["peticion$idPeticion"]));
								}
								break;

						}
					}
					break;

					case 'reservas':
						if (!isset($_SESSION['usuario'])) {
							alerta('Consulta de peticiones', 'No hay usuario conectado');
							listado_ofertas();
						} else {
							switch ($go[1]) {							
								case 'RES':
									if (isset($_POST['peticion_seleccionada'])){
										$idPeticion = $_POST['peticion_seleccionada']; 
										reservar($idPeticion);
									} else {
										alerta('', 'Debes de seleccionar el usuario al que le quieras hacer la reserva','error');
									}

									break;
								case 'CRO': //Ofertante: cancelar una reserva
								case 'CRI': //Interesado: cancelar una reserva
									//cancelar_reserva($idPeticion, $motivo, $accion);
									break;
							}
	
						}
						break;

				case 'sm':
					require("./" . $_GET['go']);
					break;

				default:
					listado_ofertas(1);
					break;
			}
			
			?>
		</section> 
		<!-- Fin Sección principal -->

</body>



</html>


