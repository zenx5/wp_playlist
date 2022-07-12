<?php
/**
 * The template for displaying all single posts.
 *
 *
 * @package PlayList
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
	
	$post = get_post();

	$values = get_post_custom( $post->ID );

	$details = isset( $values['wp-pl-details'] )?json_decode( $values['wp-pl-details'][0],true ):self::$fieldsDefault;


	get_header();

	if( class_exists('Woocommerce_Pay_Per_Post_Helper') ){
		class Woo_Handler_Content extends Woocommerce_Pay_Per_Post_Helper{

		}
	}else{
		class Woo_Handler_Content{
			public static function has_access(){
				return true;
			}
			public static function get_no_access_content(){
				return "<center>Not Access</center>";
			}
		}
	}
?>
	
	<?php if ( Woo_Handler_Content::has_access() && PlayList::licenseValidate()): ?>

	<script src="https://use.fontawesome.com/99d9817277.js"></script>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
	<div class="container">
		<div class="row">
			<div class="col-12 text-center">
				<h1><?php echo $post->post_title; ?></h1>
				<?php
					if(isset($details['Autor'])){
						if( $details['Autor']['check'] == 'checked' ) {
							echo "<small class='text-muted'>Por ".$details['Autor']['value']."</small>";
						}						
					}
				?>
			</div>
		</div>
		<br>
		<div class="row justify-content-around">
			<div class="col-8 text-center  bg-dark ">
				<!-- 336812660 -->
				<?php Playlist::embed_player( $post->ID );?>

				
			</div>
			<div class="col-4 text-center">
				<?php Playlist::list_player( $post->ID );?>
			</div>
		</div>
		<br>
		<div class="row justify-content-center">
			<div class="col-8">
				<?php echo $details['Descripcion']['value']; ?>
			</div>
			<?php if($details['Correo']['check'] == 'checked' ){ ?>
			<div class="col-4">
				<center>
					<h3>Contacto</h3>					
				</center>
				<table class="table text-center">
					<tr>
						<th>Correo:</th>
						<td><?php echo $details['Correo']['value']; ?></td>
					</tr>
				</table>
			</div>
		<?php } ?>
		</div>


	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>
	<?php else: ?>
    <?php echo Woo_Handler_Content::get_no_access_content(); ?>
	<?php endif; ?>
	<?php get_footer();?>
		
	
