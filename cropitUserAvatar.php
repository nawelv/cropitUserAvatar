<?php
/**
* Plugin Name: cropitUserAvatar
* Plugin URI: http://timple.it
* Description: Add local avatar function whit a simple upload form in the front end by a short-code.
* Version: 0.1
* Author: Timple.it
* Author URI: http://timple.it
* License: Opensource
*/


function frontEndForm() {
	
   if(get_current_user_id()){
	   
   if(!empty($_GET["txt"])){
	   $message = '<div class="message"><p>' . esc_html($_GET["txt"]) .'</p></div>';
   } else {
	   $message = '';
   }
	
  $return = '
  <style>
  
      .image-editor {
	text-align: center;
	width: 325px; 
	margin: auto;
      }
  
      .cropit-preview {
        background-color: #f8f8f8;
        background-size: cover;
        border: 1px solid #ccc;
        clip-path: circle(99px at center);
        margin-top: 7px;
        width: 250px;
        height: 250px;
	margin: auto;
      }
	  
      .image-editor input  {
	margin: 10px auto;
      }

      .cropit-preview-image-container {
        cursor: move;
      }

      .image-size-label {
        margin-top: 10px;
      }

      input, .export {
        display: block;
      }

      button {
        margin-top: 10px;
      }
      
      .showOnload {
	display: none;
      }
	  
  </style>
  '.$message.'
  
  <div class="image-editor">
	
      <div class="cropit-preview"></div>
      <input type="file" class="cropit-image-input">
      <div class="image-size-label">
      	'. __('Drag, rotate or zoom your image.').'
      </div>
	  
      <form action="" method="post" id="cropitUserAvatar" >
	<input type="range" class="cropit-image-zoom-input showOnload" style="">	    
      	<buttom type="buttom" href="javascript:;" class="rotate-preview showOnload btn">Rotar &#8634;</buttom>
	<input type="hidden" name="image-data-rotate" value="0" class="hidden-image-data-rotate" />
	<input type="hidden" name="image-data" class="hidden-image-data" />'.wp_nonce_field('nonce_upload','nonce_upload',true,false).'
        <input type="submit" class="export btn" value="Guardar">
      </form>
      
    </div>
	
    <script>
	var avatarURL = "'. get_avatar_url(get_current_user_id(),array('size' => 200)) .'";
    </script>
';
  
  } else {
	  
    $return = '
    <div class="image-editor">
	<p>' . __('Debes estar logeado para poder editar tu avatar') .'</p>
    </div>
	  
    <script>
	var avatarURL = "'. get_avatar_url(get_current_user_id()) .'";
    </script>';
	  
  }
  
  return $return;
  
}

add_shortcode('myAvatarForm', 'frontEndForm');


function cropitAvatarForm_enqueue_script() {   
    wp_enqueue_script( 'jquery.cropit', plugin_dir_url( __FILE__ ) . 'js/jquery.cropit.js', false, false, true);
	wp_enqueue_script( 'cropit-init', plugin_dir_url( __FILE__ ) . 'js/cropit-init.js', false, false, true );
}

add_action('wp_enqueue_scripts', 'cropitAvatarForm_enqueue_script');



function processAvatar(){
	
	if(isset($_POST["image-data"])){
	
	 if(is_user_logged_in()){

		foreach(array_keys($_REQUEST) as $key)

		{
		  $clean[$key] =  esc_sql($_REQUEST[$key]);
		}

		if ( !wp_verify_nonce( $_POST["nonce_upload"], "nonce_upload"  ) ) {
			wp_die("Se ha producido un error. (Codigo: 001)");
		}

		$data = $clean["image-data"];

		$upload_dir = wp_upload_dir();
		$user_ID = get_current_user_id();

		if($clean["image-data-rotate"]){
			$rotate = $clean["image-data-rotate"];
		} else {
			$rotate = 0;
		}

		$savedImage = base64ToJpg($data,$upload_dir['basedir']."/usersAvatar",$user_ID."_avatar".uniqid()."_",$rotate);

		if($savedImage){			
		
			$userAvatarMeta = update_user_meta( $user_ID, "localavatar", $savedImage);			

			if($userAvatarMeta) {			
				$mensajeError = "Se ha actualizado la imagen con exito.";			
			} else {
				$mensajeError = "No se pudo guardar la imagen de perfil. Intenta nuevamente.";
			}		
		} else {	

			$mensajeError = "Hubo un error subiendo la imagen. Intenta nuevamente.";
			exit;	
			
		}
		
		$url = $_SERVER["REQUEST_URI"];		
		$query = parse_url($_SERVER["REQUEST_URI"], PHP_URL_QUERY);
		
		if ($query) {
				$url .= '&txt='.$mensajeError;
		} else {
			$url .= '?txt='.$mensajeError;
		}
			
		wp_redirect($url);			

	 }

	}
	
	
}

add_action( 'wp', 'processAvatar' );


function base64ToJpg($image64,$folder,$preffix,$rotate=0){
	
	if (!file_exists($folder)) {
		mkdir($folder, 0777, true);
	}

	// Validan que lo que vamos a guardar sea una imagen.

	list($type, $image64) = explode(';', $image64);

	list(, $image64)      = explode(',', $image64);

	$data = base64_decode($image64);	

	$urlImage = $folder."/".uniqid($preffix,false).".jpg";	

	$img = imagecreatefromstring($data);	

	if($rotate > 0){

		$img = imagerotate($img, $rotate*-90, 0);

	}	

	imagejpeg($img, $urlImage);

	$upload_dir = wp_upload_dir();	

	$info = getimagesize($urlImage);	

	if ($info[0] > 0 && $info[1] > 0 && $info['mime']) {
		  // $result =  str_replace("/home/cpgamesful/public_html/unilever/dev/wp-content/uploads", $upload_dir["baseurl"], $urlImage);
	      $result =  str_replace($upload_dir["basedir"], $upload_dir["baseurl"], $urlImage);
	} else {
		$result = false;
	}	

	return $result;

}



// define the get_avatar_url callback 
function filter_get_avatar_url( $url, $id_or_email, $args ) { 
    
	if(filter_var($id_or_email, FILTER_VALIDATE_EMAIL)) {
       $user = get_user_by("email",$id_or_email);		
    }
	
	if(is_numeric($id_or_email)) {
       $user = get_user_by("ID",$id_or_email);		
    }
	
	if(!$user){		
	} else {
		
			$userAvatarMeta = get_user_meta( $user->ID, "localavatar", true);

			if($userAvatarMeta){				
				$url =$userAvatarMeta;				
			}
	}
	
	return $url; 
}; 
         
// add the filter 
add_filter( 'get_avatar_url', 'filter_get_avatar_url', 10, 3 );
?>
