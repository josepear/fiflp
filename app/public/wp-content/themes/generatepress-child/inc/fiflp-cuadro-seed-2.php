<?php
/**
 * Datos y creación/actualización del cuadro editorial titulado «2» (CPT fiflp_cuadro).
 *
 * @package GeneratePress_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'fiflp_get_cuadro_editorial_2_seed_filas' ) ) {
	/**
	 * Filas ACF (repeater «filas») para el cuadro de obra.
	 *
	 * @return array<int, array<string, string>>
	 */
	function fiflp_get_cuadro_editorial_2_seed_filas() {
		return array(
			array(
				'cifra_1' => '18.000',
				'texto_1' => 'aforo total',
				'cifra_2' => '557.157 M3',
				'texto_2' => 'hormigón en masa',
				'cifra_3' => '3.400 M3',
				'texto_3' => 'excavaciones de los cimientos',
			),
			array(
				'cifra_1' => '8.908 M3',
				'texto_1' => 'de terreno',
				'cifra_2' => '1.162 M3',
				'texto_2' => 'hormigón armado',
				'cifra_3' => '108 TON.',
				'texto_3' => 'de hierro',
			),
			array(
				'cifra_1' => '11.830 M3',
				'texto_1' => 'el desmonte',
				'cifra_2' => '3.486 M',
				'texto_2' => 'paredes y sillares',
				'cifra_3' => '18.000',
				'texto_3' => 'sacos de cemento',
			),
			array(
				'cifra_1' => '3.390 M3',
				'texto_1' => 'mampostería',
				'cifra_2' => '2.477 M',
				'texto_2' => 'muros de sillares',
				'cifra_3' => '5.000.000',
				'texto_3' => 'de pesetas de presupuesto',
			),
		);
	}
}

if ( ! function_exists( 'fiflp_cuadro_editorial_2_post_id' ) ) {
	/**
	 * ID del cuadro con título exacto «2», o 0.
	 *
	 * @return int
	 */
	function fiflp_cuadro_editorial_2_post_id() {
		global $wpdb;

		$id = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_title = %s AND post_status IN ('publish','draft','pending','private') ORDER BY ID ASC LIMIT 1",
				'fiflp_cuadro',
				'2'
			)
		);

		return $id > 0 ? $id : 0;
	}
}

if ( ! function_exists( 'fiflp_seed_cuadro_editorial_2' ) ) {
	/**
	 * Crea o actualiza el post «2» y campos ACF (3 columnas, ancho, 4 filas).
	 *
	 * @return int|WP_Error ID del post o error.
	 */
	function fiflp_seed_cuadro_editorial_2() {
		if ( ! function_exists( 'update_field' ) ) {
			return new WP_Error( 'fiflp_acf', 'ACF no está activo (update_field).' );
		}

		$existing_id = fiflp_cuadro_editorial_2_post_id();
		$postarr     = array(
			'post_type'    => 'fiflp_cuadro',
			'post_status'  => 'publish',
			'post_title'   => '2',
			'post_content' => '',
		);

		if ( $existing_id > 0 ) {
			$postarr['ID'] = $existing_id;
			$post_id       = wp_update_post( $postarr, true );
		} else {
			$post_id = wp_insert_post( $postarr, true );
		}

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}
		if ( $post_id <= 0 ) {
			return new WP_Error( 'fiflp_post', 'No se pudo guardar el cuadro.' );
		}

		$filas = fiflp_get_cuadro_editorial_2_seed_filas();

		update_field( 'field_cuadro_num_columnas', '3', $post_id );
		update_field( 'field_cuadro_ancho_grid', 'ancho', $post_id );
		update_field( 'field_cuadro_filas', $filas, $post_id );

		return (int) $post_id;
	}
}
