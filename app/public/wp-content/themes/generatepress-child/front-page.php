<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$current_page_id    = (int) get_option( 'page_on_front' );
$home_hero_data     = fiflp_get_home_hero_data( $current_page_id );
$home_hero_has_data = ! empty( $home_hero_data['imagen'] )
	|| ! empty( $home_hero_data['logo_principal'] )
	|| ! empty( $home_hero_data['titulo'] )
	|| ! empty( $home_hero_data['texto'] )
	|| ! empty( $home_hero_data['boton_capitulos_texto'] )
	|| ! empty( $home_hero_data['boton_capitulos_url'] )
	|| ! empty( $home_hero_data['link_pdf'] )
	|| ! empty( $home_hero_data['link_epub'] )
	|| ! empty( $home_hero_data['logos'] );

if ( $home_hero_has_data ) {
	get_template_part( 'template-parts/bloques/home-hero', null, array( 'hero_data' => $home_hero_data ) );
	get_footer();
	return;
}

if ( $current_page_id > 0 ) {
	$front_page = get_post( $current_page_id );

	if ( $front_page instanceof WP_Post ) {
		$GLOBALS['post'] = $front_page;
		setup_postdata( $front_page );
		get_template_part( 'page' );
		wp_reset_postdata();
		get_footer();
		return;
	}
}

if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		the_content();
	}
}

get_footer();
