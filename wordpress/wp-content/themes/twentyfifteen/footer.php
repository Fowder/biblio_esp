<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the "site-content" div and all content after.
 *
 * @package WordPress
 * @subpackage Twenty_Fifteen
 * @since Twenty Fifteen 1.0
 */
?>

	</div><!-- .site-content -->

	<footer id="colophon" class="site-footer" role="contentinfo">
		<div class="site-info">
			<?php
				/**
				 * Fires before the Twenty Fifteen footer text for footer customization.
				 *
				 * @since Twenty Fifteen 1.0
				 */
				do_action( 'twentyfifteen_credits' );
			?>
			<div class="coordonnees">
			<p><b>Adresse e-mail :</b> bibli.espere@orange.fr</p>
			<p><b>Téléphone bibliothèque :</b> 05.65.20.06.89</p>
			<p><b>Téléphone mairie :</b> 05.65.20.07.06</p>
			</div>
			<div class="liens">
			<a href="http://www.mediatheque.grandcahors.fr/index" target="_blank"><?php printf( __( 'Médiathèque Grand Cahors', 'twentyfifteen' ), 'WordPress' ); ?></a>
			<span><?php printf( __( '&nbsp;.&nbsp;', 'twentyfifteen' ), 'WordPress' ); ?></span>
			<a href="" target="_blank"><?php printf( __( 'Mentions Légales', 'twentyfifteen' ), 'WordPress' ); ?></a>
			</div>
			<p id="pfooter">Site réalisé par les élèves de la formation &nbsp;<a href="https://simplon.co/" target="_blank">Simplon</a>&nbsp; Cahors</p>
		</div><!-- .site-info -->
	</footer><!-- .site-footer -->

</div><!-- .site -->

<?php wp_footer(); ?>

</body>
</html>
