<?php
session_start();

/**
 * modal: Este metodo nos servira para crear un cuadro modal
 *
 * @param  string $titulo Titulo del cuadro modal
 * @param  string $txt_mensaje Mensaje del cuadro modal
 * @param  string $go Url a la que nos reedigiremos 
 * @return void
 */
function modal($titulo, $txt_mensaje, $go = "./index.php")
{
   echo "<dialog id='modal' open>";
   echo "<h2>$titulo</h2>";
   echo "<p>$txt_mensaje</p>";
   echo "<button onclick='window.modal.close();goURL(\"$go\");'>Cerrar</button>";
   echo "</dialog>";
}


/**
 * alerta: Funcion que mostrara un mensaje de alerta
 *
 * @param  string $titulo Titulo del mensaje
 * @param  string $txt_mensaje Cuerpo del mensaje
 * @param  string $tipo Tipo de mensaje: Alerta, error, informacion y ok
 * @return void
 */
function alerta($titulo, $txt_mensaje, $tipo = "warning")
{
   if ($tipo == "warning")
      $tipo_alerta = "alert-warning";
   elseif ($tipo == "error")
      $tipo_alerta = "alert-danger";
   elseif ($tipo == "info")
      $tipo_alerta = "alert-info";
   elseif ($tipo == "ok")
      $tipo_alerta = "alert-success";

   ?>
   <div class="alert <?php echo $tipo_alerta; ?> alert-dismissible fade show" role="alert">
      <strong>
         <?php echo $titulo; ?>
      </strong>
      <p>
         <?php echo $txt_mensaje; ?>
      </p>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
   </div>
   <?php

}


/**
 * redireccion: Metodo que nos redirecciona a una url especifica
 *
 * @param  string $pagina Url a la que nos dirigiremos
 * @return void
 */
function redireccion($pagina = "index.php")
{
   echo "<META HTTP-EQUIV=\"refresh\" CONTENT=\"0; URL=$pagina\">";
}

/**
 * array_envia: Este metodo recibe una array por parametro, la serializa y la devuelve
 *
 * @param  mixed $array
 * @return mixed
 */
function array_envia($array)
{

   $tmp = serialize($array);
   $tmp = urlencode($tmp);

   return $tmp;
}
/**
 * array_recibe: Este metodo recibe una array serializada por parametro, la deserializa y la devuelve
 *
 * @param  mixed $array
 * @return mixed
 */
function array_recibe($array)
{
   $tmp = stripslashes($array);
   $tmp = urldecode($tmp);
   $tmp = unserialize($tmp);

   return $tmp;
}

/**
 * div_cabecera: Esta funcion crea una cabecera 
 *
 * @param  string $titulo Titulo que tendra la cabecera
 * @param  int $opcion Opcion que nos ayudara a saber cuando debemos de mostrar un texto o cuando no
 * @param  string $texto Texto que se mostrara en la cabecera
 * @return string
 */
function div_cabecera($titulo, $opcion = null, $texto=null) {
   $cabecera = "<div class=\"row py-lg-4\">
               <div class=\"col-md-8 mx-auto\">
                 <h1 class=\"fw-light\">$titulo</h1>";
   if (!empty($texto)) $cabecera .= "<p text-align=\"center\" class=\"lead text-body-secondary\">$texto</p>";
   if ($opcion == 2 || ($opcion == 1 && isset($_SESSION['usuario'])))
      $cabecera .= "<p><a href=\"index.php?go=ofertas/nueva\" class=\"btn btn-primary my-2\">Nueva oferta</a></p>";
   $cabecera .= "</div></div>";
   return ($cabecera);
}

/**
 * footer: ESta funcion crea un pie de pagina
 *
 * @return string
 */
function footer() {
   return("<footer class=\"text-body-secondary py-5\">
        <div class=\"container\">
          <p class=\"float-end mb-1\">
            <a href=\"#\">Back to top</a>
          </p>
          <p class=\"mb-1\">Segunda Moda</p>
        </div>
      </footer>");
}

/**
 * opciones_detalle: Devuelve un array con los botones que se muestran en el detalle de ofertas
 *
 * @param  mixed $id IdAnuncio Id del anuncio
 * @param  mixed $ofertante IdUsuario propietario del anuncio
 * @param  mixed $estado_oferta Visibilidad del anuncio
 * @param  mixed $last_act Ultima accion registrada en la tabla historial sobre el anuncio
 * @param  mixed $npeticiones Numero de peticiones solicitadas sobre un anuncio
 * @param  mixed $linteres Indica si el anuncio tiene alguna solicitud de peticion
 * @return array Array con los botones
 */
function opciones_detalle($id, $ofertante, $estado_oferta, $last_act, $npeticiones = 0, $linteres = 0 )
{
   $opciones = array();
   if (isset($_SESSION['usuario'])) {
      $interesado = $_SESSION['usuario'];
      if ($_SESSION['es_supervisor'] && $estado_oferta == 1) { //supervisar la oferta
         //APROBAR: si la oferta es nueva o ha sido rechazada
         if (empty($last_act) || $last_act == 'NEW')
            $enable = '';
         else
            $enable = 'disabled';
         if($last_act == 'EDI')
            $enable = '';
         $opciones['S'][] = array("BT" => "Aprobar oferta", "ACT" => "VIS", "TIPO" => 'SUBMIT', "ENABLE" => $enable);
         //RECHAZAR: si la oferta es nueva o ha sido editada
         if ($last_act == 'NOV')
            $enable = 'disabled';
         else
            $enable = '';
         $opciones['S'][] = array("BT" => "Rechazar oferta", "ACT" => "NOV", "TIPO" => 'MODAL', "ENABLE" => $enable);
         //CANCELAR: si la oferta esta en estado 1 (siempre)
         $opciones['S'][] = array("BT" => "Cancelar oferta", "ACT" => "COS", "TIPO" => 'MODAL', "ENABLE" => '');

         ?>         
         <?php
      } //fin supervisor

      if ($ofertante == $interesado) { //opciones para el ofertante gestionar la oferta
         /** 
          * Estado 1 o 2 => Editar o cancelar la oferta
          * Estado 2 && hay peticiones => reservar
          * Estado 3 => Cancelar una reserva, marcar como entregado, contactar con interesado
          * Estado 4 => Archivar
          **/
         switch ($estado_oferta) {
            case 1:
            case 2:
               if ($npeticiones!=0)
                  $opciones['P'][] = array("BT" => "Reservar", "ACT" => "RES", "TIPO" => 'SUBMIT');
               
               break;
            case 3:
               //$opciones['P'][] = array("BT" => "Cancelar la reserva", "ACT" => "CRO", "TIPO" => 'MODAL');
               //$opciones['P'][] = array("BT" => "Contactar con el interesado", "ACT" => "MAIL", "TIPO" => 'MAIL');
               //$opciones['O'][] = array("BT" => "Material entregado", "ACT" => "ENT", "TIPO" => 'SUBMIT');
               break;
            case 4:
               $opciones['O'][] = array("BT" => "Archivar la oferta", "ACT" => "ARC", "TIPO" => 'SUBMIT');
               break;
         }
      } else { //opciones para el interesado
         if ($estado_oferta == 2 && $linteres == 0) {
            $opciones['O'][] = array("BT" => "Realizar una Peticion", "ACT" => "PET", "TIPO" => 'SUBMIT');
         }
         elseif ($estado_oferta==2 && $linteres == 1){
               $opciones['P'][] = array("BT" => "Cancelar Peticion", "ACT" => "CPI", "TIPO" => 'MODAL');
            }
         elseif ($estado_oferta == 3 && $linteres == 1) {
            
            //$opciones['P'][] = array("BT" => "Cancelar la reserva", "ACT" => "CRI", "TIPO" => 'MODAL');
            //$opciones['P'][] = array("BT" => "Contactar con el ofertante", "ACT" => "MAIL", "TIPO" => 'MAIL');
         }
      }
      
      foreach ($opciones as $key => $grupo) {
         if ($key == 'P') $go = 'peticiones';
         else $go = 'ofertas';
         foreach ($grupo as $op) {
            if ($op["TIPO"] == 'MODAL') { ?>

               <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="<?php echo $op['ACT'];?>" tabindex="-1"
                  aria-labelledby="NOVLabel" aria-hidden="true">
                  <div class="modal-dialog">
                     <div class="modal-content">
                        <div class="modal-header">
                           <h1 class="modal-title fs-5" id="NOVLabel"><?php echo $op['BT'];?></h1>
                           <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form class="container-fluid justify-content-start m-0" action="index.php?go=<?php echo $go.'/'.$op['ACT'].'/'.$id; ?>"
                           name="NOVform" method='post'>
                           <div class="modal-body">
                              <div class="mb-3">
                                 <label for="message-text" class="col-form-label">Motivo:</label>
                                 <textarea class="form-control" id="message-text" name="motivoRechazo"></textarea>
                              </div>
                           </div>
                           <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                              <button type="submit" class="btn btn-primary">Enviar</button>
                           </div>
                        </form>
                     </div>
                  </div>
               </div>

            <?php }
         }
      }

   }
   return ($opciones);

}


/**
 * mostrar_fotos: Muestra las fotos con un slider.
 *
 * @param  mixed $fotosA
 * @return void
 */
function mostrar_fotos($fotosA)
{
   $primera = true;
   echo "<div id=\"carouselFotos\" class=\"carousel slide\" style=\"height:400px\">
           <div class=\"carousel-inner\" style=\"height:100%\">";
   foreach ($fotosA as $foto) {
      echo "<div class=\"carousel-item " . ($primera ? "active" : "") . "\">";
      if (empty($foto)) { ?>
         <svg class="bd-placeholder-img bd-placeholder-img-lg d-block w-100" width="800" height="400" role="img"
            aria-label="Marcador de posición: Primera foto" focusable="false" preserveAspectRatio="xMidYMid slice"
            xmlns="http://www.w3.org/2000/svg">
            <title>Marcador de posición</title>
            <rect width="100%" height="100%" fill="#777"></rect><text x="50%" y="50%" fill="#555" dy=".3em">sin foto</text>
         </svg>
      <?php } else {
         echo "<div style=\"text-align: center\">";
         echo "<img src=\"fotos_anuncio/" . $foto . "\" class=\"d-block w-100 object-fit-scale mx-auto\" alt=\"...\" height=\"400px\">";
         echo "</div>";
      }
      echo "</div>";
      $primera = false;
   }
   echo "</div>
           <button class=\"carousel-control-prev\" type=\"button\" data-bs-target=\"#carouselFotos\" data-bs-slide=\"prev\">
           <img class=\"carousel-control-prev-icon\" src=\"./img/bg/logos/flechaanterior.png\" height=\"30px\">
             <span class=\"visually-hidden\">Previous</span>
           </button>
           <button class=\"carousel-control-next\" type=\"button\" data-bs-target=\"#carouselFotos\" data-bs-slide=\"next\">
           <img class=\"carousel-control-next-icon\" src=\"./img/bg/logos/flechasiguiente.png\" height=\"30px\">
         <span class=\"visually-hidden\">Next</span>
           </button>
         </div>";
}
?>
