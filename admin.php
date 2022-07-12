<?php
	
	$fields = json_decode( get_option('wp-pl-extrafield', '[]' ), true );

	if( isset($_POST['wp-pl-extrafield-name']) && isset($_POST['wp-pl-extrafield-type']) ){
		if( strlen($_POST['wp-pl-extrafield-name']) + strlen($_POST['wp-pl-extrafield-type']) > 1 ){
			$fields[] = array( 
				'name' => $_POST['wp-pl-extrafield-name'], 
				'type' => $_POST['wp-pl-extrafield-type'], 
				'type-attr' => 'text'
			);

			set_option('wp-pl-extrafield', json_encode($fields));
			$fields = json_decode( get_option('wp-pl-extrafield', '[]' ), true );
		}
	}

	/*$fields = array(
		array( 'name' => 'Profesor', 'type' => 'input', 'type-attr' => 'text'),
		array( 'name' => 'Descripcion', 'type' => 'textarea'),
		array( 'name' => 'Correo', 'type' => 'input', 'type-attr' => 'email')
	);*/

?>

<script src="https://use.fontawesome.com/99d9817277.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">

<div class="container">
	<div class="row justify-content-center">
		<div class="col-4 text-center">
			<h2>Settings Playlist</h2>
		</div>
	</div>
	<div class="row">
		<form class="col-4" action="admin.php?page=Wp-PlayList/admin.php">

			<?php foreach ($fields as $index => $field) { ?>
				<input 
						type="text" 
						id="wp-pl-extrafield-name-<?php echo $index; ?>"
						name="wp-pl-extrafield-name-<?php echo $index; ?>"
						value="<?php echo $field['name']; ?>"
						style="width: 100%;">
				<select
					id="wp-pl-extrafield-type-<?php echo $index; ?>"
					name="wp-pl-extrafield-type-<?php echo $index; ?>"
					style="width: 100%;">
						<option 
							<?php echo ($field['type']=='input')?'selected':'';?>>input</option>
						<option 
							<?php echo ($field['type']=='textarea')?'selected':'';?>>textarea</option>

				</select>
				<br><br>
			<?php } ?>
				<input 
						type="text" 
						id="wp-pl-extrafield-name"
						name="wp-pl-extrafield-name"
						value=""
						style="width: 100%;">
				<select
					id="wp-pl-extrafield-type"
					name="wp-pl-extrafield-type"
					style="width: 100%;">
						<option>input</option>
						<option>textarea</option>
				</select>
				<br><br>
				<input type="submit" value="Guardar">
		</form>
	</div>
</div>