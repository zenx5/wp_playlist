<?php


	/**
	*	 @package WPPlaylist
	 	 
	
	Instalación, Desinstalación y Configuración
		public static function activation
		public static function deactivation
		public static function init
		public static function add_meta_boxes_playlist
		public static function register_type_post_playlist
	FRONTEND, RENDERIZADO, TEMPLATES
		public static function add_template
			$template 
		public static function embed_player
			$post_id , 
			$id_video = null
		public static function  render_meta_box
			$post
	MANEJO DE DATOS
		public static function save_data_playlist
			$post_id
		

	 */

include 'config.php';
include 'classJWT.php';

class PlayList {		
	
	/*
		Instalación, Desinstalación y Configuración
	*/
	private static $ExpRegUrl = "^https?:\/\/(w{3}.)?youtube.com\/watch\?v=[a-zA-Z0-9_$#]{1,}|^https?:\/\/(w{3}.)?vimeo.com\/[0-9]{1,}";
	private static $ExpRegName = "[a-zA-Z0-9 ]{5,50}";

	private static $fields = array(
		array( 'name' => 'Profesor', 'type' => 'input', 'type-attr' => 'text'),
		array( 'name' => 'Descripcion', 'type' => 'textarea'),
		array( 'name' => 'Correo', 'type' => 'input', 'type-attr' => 'email')
	);

	private static $fieldsDefault = array(
		'Profesor' => array( 'value' => '', 'check' => '' ),
		'Descripcion' => array( 'value' => '', 'check' => '' ),
		'Correo' => array( 'value' => '', 'check' => '' )
	);

	public static function activation() {
		$license = get_option('wp-pl-license');
		if( ! $license ){
			update_option('wp-pl-license',WP_PL_LICENSE);
		}
	}

	public static function deactivation() {
		unregister_post_type( 'playlist' );
	}
		
	public static function init() {
		self::register_type_post_playlist();
	}

	public static function add_meta_boxes_playlist() {
		add_meta_box(
			'playlist-lista', //ID
			'Lista de Reproduccion', //TITLE
			array('PlayList','render_meta_box_list'),//'wp_pl_meta_box_lista_content', //CALLBACK
			'playlist'
		); 
		add_meta_box(
			'playlist-details', //ID
			'Detalles', //TITLE
			array('PlayList','render_meta_box_details'),//'wp_pl_meta_box_lista_content', //CALLBACK
			'playlist'
		); 
	}

	public static function register_type_post_playlist() {
		$arguments_post_type = array(
			'labels'             => array(
				'name'               => _x( 'PlayList', 'post type general name', 'text-domain' ),
				'singular_name'      => _x( 'PlayList', 'post type singular name', 'text-domain' ),
				'menu_name'          => _x( 'PlayList', 'admin menu', 'text-domain' ),
				'add_new'            => _x( 'Añadir Nueva', 'playlist', 'text-domain' ),
				'add_new_item'       => __( 'Añadir nueva PlayList', 'text-domain' ),
				'new_item'           => __( 'Nueva PlayList', 'text-domain' ),
				'edit_item'          => __( 'Editar PlayList', 'text-domain' ),
				'view_item'          => __( 'Ver PlayList', 'text-domain' ),
				'all_items'          => __( 'Todas las PlayList', 'text-domain' ),
				'search_items'       => __( 'Buscar PlayList', 'text-domain' ),
				'not_found'          => __( 'No hay PlayList.', 'text-domain' ),
				'not_found_in_trash' => __( 'No hay PlayList en la papelera.', 'text-domain' )
			),
			'description'        => __( 'Listas de Reproduccion para administrar contenido multimedia', 'text-domain' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'playlist' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title'),
			'menu_icon'			 =>	'dashicons-playlist-video'
		);
		register_post_type( 'playlist', $arguments_post_type );
	}

	public static function licenseValidate(){
		$license = get_option('wp-pl-license',WP_PL_LICENSE);
		$jwt = new JWT($license);
		$hoy = date('d-m-Y');		
		return self::beutydate($hoy,'-') < self::beutydate($jwt->getData()->dateend, '-') || $jwt->getData()->nl;
	}

	private static function beutydate($f,$d){
		return explode($d, $f)[2].explode($d, $f)[1].explode($d, $f)[0];
	}
	/*
		FRONTEND, RENDERIZADO, TEMPLATES
	*/

	public static function add_template( $template ) {
		global $post;
		if ( isset($post->post_type) && $post->post_type == "playlist" ) {
			return dirname(__FILE__).'/single-playlist.php';
		}
		return $template;
	}	

	public static function list_player( $post_id ) {
		$play = 0;
		if( isset($_GET['play']) ){
			$play = $_GET['play'];
		}
		$values = get_post_custom( $post_id );
		$videos = isset( $values['wp-pl-playlist'] )?json_decode( $values['wp-pl-playlist'][0],true ):[];
		$rest = 10;
		?>
			<table class="table table-hover table-dark">
				<?php foreach($videos as $index => $video){?>
				<tr>
					<td>
					<a class="text-light" href="?play=<?php echo $index;?>">
						<?php echo $video['name'];?>
					</a>
					</td>
					<td>
						<?php
							if( $play == $index ){
								echo "<i class='fa fa-play'></i>";
							}
						?>
						</a>
					</td>
				</tr>
				<?php $rest--; }?>
				<?php for($i = 0; $i < $rest ; $i++){?>
					<tr>
						<td><small>...</small></td>
						<td></td>
					</tr>
				<?php }?>

			</table>

		<?php
	}

	public static function embed_player( $post_id ) {
		$play = 0;
		if( isset($_GET['play']) ){
			$play = $_GET['play'];
		}
		
		$values = get_post_custom( $post_id );
		$videos = isset( $values['wp-pl-playlist'] )?json_decode( $values['wp-pl-playlist'][0],true ):[];
		$video = self::dataVideo( $videos[$play] );
	?>
		<div>
			<iframe 
				title="<?php echo $video['title-attr'];?>" 
				src="<?php echo $video['urlEmbed'];?>"
				width="640" 
				height="360"
				allow="<?php echo $video['extra_allow'];?>"
				frameborder="0" 
				allowfullscreen>
				
			</iframe>
		</div>
	<?php
	}

	public static function  render_meta_box_details( $post ){
		$values = get_post_custom( $post->ID );
		$details = isset( $values['wp-pl-details'] )?json_decode( $values['wp-pl-details'][0],true ):self::$fieldsDefault;

		?>	
			<table style="padding: 5px; width: 100%;">
				<?php foreach (self::$fields as $field) { ?>
				<tr>
					<th>
						<label for="wp-pl-details-<?php echo $field['name']; ?>">
							<?php echo $field['name']; ?>:
						</label>
					</th>
					<td>
						<?php
							if( $field['type'] == 'input' ){
								?>
								<input 
									type="<?php echo $field['type-attr'];?>" 
									id="wp-pl-details-<?php echo $field['name'];?>"
									name="wp-pl-details-<?php echo $field['name'];?>"
									value="<?php echo $details[$field['name']]['value'];?>"
									style="width: 100%;"/>
								<?php
							}
							elseif(  $field['type'] == 'textarea' ){
								?>
								<textarea
									id="wp-pl-details-<?php echo $field['name'];?>"
									name="wp-pl-details-<?php echo $field['name'];?>"
									style="width: 100%;"
									rows="5"><?php echo $details[$field['name']]['value'];?></textarea>
								<?php
							}

						?>
					</td>
					<td>
						<label for="wp-pl-details-check-<?php echo $field['name'];?>">Publico: </label>
						<input 
							type="checkbox" 
							id="wp-pl-details-check-<?php echo $field['name'];?>"
							name="wp-pl-details-check-<?php echo $field['name'];?>"
							<?php echo $details[$field['name']]['check'];?>>
					</td>
				</tr>
			<?php } ?>
			</table>
		<?php
	}

	public static function  render_meta_box_list( $post ){
		$values = get_post_custom( $post->ID );
		$videos = isset( $values['wp-pl-playlist'] )?json_decode( $values['wp-pl-playlist'][0],true ):[];
		$count = count($videos);
		$events = isset( $values['wp-pl-events'] )? json_decode($values['wp-pl-events'][0],true):array(
				'msj' => '',
				'name' => '',
				'code' => ''
			);
		echo "<div class='wp-pl-meta-box-container'>";
		if( ! PlayList::licenseValidate() ){
			?>	

				<p>Parece que su licensia caduco, pongase en contacto con el administrador de su web p con el fabricante del plugin para obtener una nueva</p>
				<center>	
					<label for="wp-pl-license-key">Licensia</label>
					<input 
						type="text" 
						id="wp-pl-license-key"
						name="wp-pl-license-key"/><br>
					<input 
						type="submit" 
						id="wp-pl-btn-submit"
						name="wp-pl-btn-submit"
						value="Activar"/>
				</center>
			<?php
			echo "</div>";
			return;
		}
		if($events['code'] == 'EMPTY_FIELD'){	?>
			<span>
				<label><?php echo "<b>".$events['name']."</b> : ".$events['msj']?></label>
			</span></br>
		<?php } ?>
			<input type="hidden" name="wp-pl-current-count" value="<?php echo $count;?>">
			<style type="text/css">
				.wp-pl-border-bottom th,.wp-pl-border-bottom td {
					border-bottom: 1px solid lightgray;
				}
				
			</style>
			<table 
				class="wp-pl-container-data-video" 
				style="padding: 5px; width: 100%">
				<?php foreach($videos as $index => $video) { ?>
				<tr>
					<th>
						<label 
							for="wp-pl-name-<?php echo $index;?>">
							Nombre: </label>
					</th>
					<td>
						<input 
							type="text" 
							name="wp-pl-name-<?php echo $index;?>"
							id="wp-pl-name-<?php echo $index;?>" 
							value="<?php echo $video['name'];?>"
							style="width: 100%"
							pattern="<?php echo self::$ExpRegName;?>"
							disabled
							required/><br/>
						<small>Solo se permite letras y numeros</small>
					</td>
					<th>
						<center>Acciones</center>
					</th>
				</tr>
				<tr  class="wp-pl-border-bottom">
					<th>
						<label for="wp-pl-url-<?php echo $index;?>">
						Url: </label>
					</th>
					<td>
						<input 
							type="text" 
							name="wp-pl-url-<?php echo $index;?>"
							id="wp-pl-url-<?php echo $index;?>"
							value="<?php echo $video['url'];?>"
							style="width: 100%"
							pattern="<?php echo self::$ExpRegUrl;?>" 
							required
							disabled
							/><br>		
						<small>Debe ingresar una URL valida (http o https)</small>
					</td>
					<td>
						<center>							
							<input 
								type="submit" 
								name="wp-pl-btn-action-<?php echo $index; ?>"
								value="Borrar">
							<input 
								type="submit" 
								name="wp-pl-btn-action-<?php echo $index; ?>"
								value="🔽">
							<input 
								type="submit" 
								name="wp-pl-btn-action-<?php echo $index; ?>"
								value="🔼">
						</center>
					</td>
				</tr>
				<?php } ?>
				<tr>
					<th>
						<label 
							for="wp-pl-name-<?php echo $count;?>">
							Nombre: </label>
					</th>
					<td>
						<input 
							type="text" 
							name="wp-pl-name-<?php echo $count;?>"
							id="wp-pl-name-<?php echo $count;?>" 
							pattern="<?php echo self::$ExpRegName;?>"
							style="width: 100%"
							value=""/><br/>		
						<small>Solo se permite letras y numeros</small>
					</td>
				</tr>
				<tr>
					<th>
						<label for="wp-pl-url-<?php echo $count;?>">
						Url: </label>
					</th>
					<td>
						<input 
							type="text" 
							name="wp-pl-url-<?php echo $count;?>"
							id="wp-pl-url-<?php echo $count;?>"
							pattern="<?php echo self::$ExpRegUrl;?>"
							style="width: 100%"
							value="" /><br>
						<small>Debe ingresar una URL valida (http o https)</small>
					</td>
				</tr>
			</table>
		</div>
		<div class="wp-pl-meta-box-action">
			<center>
				<input 
					type="submit" 
					id="wp-pl-btn-agregar"
					name="wp-pl-btn-submit"
					value="Agregar" />
				<input 
					type="submit" 
					id="wp-pl-btn-borrar"
					name="wp-pl-btn-submit"
					value="Borrar Todo" />
			</center>
		</div>
		
		<?php
	}
	/*
		MANEJO DE DATOS
	*/
	public static function save_data_playlist( $post_id ) {
		if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ){
			return;
		}
		//falta verificar el nonce
		if( ! current_user_can('edit_post') ){
			return;
		}
		$action_index = -1;
		$action_band = false;
		$values = get_post_custom( $post_id );
		$videos = isset( $values['wp-pl-playlist'] )?json_decode( $values['wp-pl-playlist'][0],true ):[];
		$details = isset( $values['wp-pl-details'] )?json_decode( $values['wp-pl-details'][0],true ):self::$fieldsDefault;
		$count = count($videos);
		foreach (self::$fields as $field) {
			$details[$field['name']]['value'] = isset($_POST['wp-pl-details-'.$field['name']])?$_POST['wp-pl-details-'.$field['name']]:'';
			$details[$field['name']]['check'] = isset($_POST['wp-pl-details-check-'.$field['name']])?'checked':'';
		}
		update_post_meta( $post_id, 'wp-pl-details',  json_encode( $details )  );
		if( ! isset($_POST['wp-pl-btn-submit']) ) {
			for($i = 0; $i < $count; $i++){
				$action_band = $action_band || isset($_POST["wp-pl-btn-action-$i"]);
				$action_index = isset($_POST["wp-pl-btn-action-$i"])?$i:$action_index;
			}
			if(!$action_band){
				return;
			}
		}
		$events = array(
			'msj' => '',
			'name' => '',
			'code' => ''
		);
		if( $_POST['wp-pl-btn-submit'] == "Agregar"  ){
			if( !isset($_POST["wp-pl-name-$count"]) && !isset($_POST["wp-pl-url-$count"]) ){
				$events['name'] = 'Error';
				$events['code'] = 'EMPTY_FIELD';
				$events['msj'] = 'Rellene los campos antes de agregar un nuevo elemento a la lista';
			}
			elseif( ($_POST["wp-pl-name-$count"] == '') || ($_POST["wp-pl-url-$count"] == '') ){
				$events['name'] = 'Error';
				$events['code'] = 'EMPTY_FIELD';
				$events['msj'] = 'Rellene los campos antes de agregar un nuevo elemento a la lista';	
			}
			else{
				for ($i=0; $i <= $count; $i++) { 
					if( isset($_POST["wp-pl-name-$i"]) && isset($_POST["wp-pl-url-$i"]) ){
						$name = $_POST["wp-pl-name-$i"];
						$url = $_POST["wp-pl-url-$i"];
						if( self::validateExpReg($name, "/".self.$ExpRegName."/") && self::validateExpReg($url, "/".self.$ExpRegUrl."/") ){
							$videos[$i] = array(
								'name' => $name,
								'url' => $url
							);
						}
						else{
							$events['name'] = 'Error';
							$events['code'] = 'DATA_INVALID';
							$events['msj'] = 'Algunos de los datos Ingresados no cumplen con las condiciones esperadas, por favor verifique e intente nuevamente';	
						}
					}
				}
				update_post_meta( $post_id, 'wp-pl-playlist',  json_encode( $videos )  );
			}
		}
		elseif( $_POST['wp-pl-btn-submit'] == "Borrar Todo" ){
			update_post_meta( $post_id, 'wp-pl-playlist',  json_encode( array() )  );
		}
		elseif( $_POST['wp-pl-btn-submit'] == "Activar" ){
			if( isset( $_POST['wp-pl-license-key'] ) ){
				if( self::licenseValidate($_POST['wp-pl-license-key']) ){
					update_option('wp-pl-license', $_POST['wp-pl-license-key']);
				}
			}
		}
		else{
			$new_videos = array();
			if( $_POST['wp-pl-btn-action-'.$action_index] == "Borrar"){
				for ($i=0; $i < $count ; $i++) { 
					if( $i != $action_index ){
						$new_videos[] = $videos[$i];
					}
				}
			}
			elseif( $_POST['wp-pl-btn-action-'.$action_index] == "🔽"){
				if( $action_index < $count - 1 ){
					$aux =  $videos[$action_index];
					$videos[$action_index] = $videos[$action_index + 1];
					$videos[$action_index + 1] = $aux;
				}
				$new_videos = $videos;
			}
			elseif( $_POST['wp-pl-btn-action-'.$action_index] == "🔼"){
				if( $action_index > 0 ){
					$aux =  $videos[$action_index];
					$videos[$action_index] = $videos[$action_index - 1];
					$videos[$action_index - 1] = $aux;
				}
				$new_videos = $videos;	
			}
			update_post_meta( $post_id, 'wp-pl-playlist',  json_encode( $new_videos )  );				
		}
		update_post_meta( $post_id, 'wp-pl-events', json_encode( $events ) );
	}

	public static function validateExpReg( $data, $ExpReg ){
		return preg_match($ExpReg, $data);
	}
	
	public static function dataVideo( $video ){
		$result = array(
			'title' => $video['name'],
			'url' => $video['url'],
			'extra_allow' => ''
		);
		$aux = explode( ".", explode( "/", explode( "//", $result['url'])[1])[0]);
		foreach ($aux as $aux2) {
			if( ( strtolower( $aux2 ) == 'youtube' ) || ( strtolower( $aux2 ) == 'vimeo' ) ){
				$result['host'] = strtolower( $aux2 );
			}
		}
		if( $result['host'] == 'youtube' ) {
			$result['title-attr'] = "youtube-player";
			$result['code'] = explode('?', explode('watch?v=', $result['url'] )[1])[0];
			$result['urlEmbed'] = "https://www.youtube.com/embed/" . $result['code'];
			$result['extra_allow'] = "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture";
		}
		elseif( $result['host'] == 'vimeo' ){
			$result['title-attr'] = "vimeo-player";
			$result['code'] = explode('?', explode('vimeo.com/', $result['url'] )[1])[0];
			$result['urlEmbed'] = $result['url'];
		}
		return $result;
	}
}