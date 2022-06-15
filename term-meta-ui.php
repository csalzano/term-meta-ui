<?php
defined( 'ABSPATH' ) or exit;

/**
 * Plugin Name: Term Meta UI
 * Description: Provides a user interface for reading and writing term meta data.
 * Author: Corey Salzano
 * Author URI: https://breakfastco.xyz
 * Version: 0.1.0
 * Text-domain: term-meta-ui
 * License: GPLv2
 */

class Term_Meta_UI
{
	public function add_hooks()
	{
		add_action( 'admin_init', array( $this, 'add_term_meta_box' ) );
	}

	public function add_meta_box( $term ) {
		if ( ! $term instanceof WP_Term ) {
			return;
		}

		?><h2><?php esc_html_e( 'Term Meta UI', 'term-meta-ui' ); ?></h2><?php
			?><div id="poststuff"><div class="term_meta_ui"><table class="form-table" role="presentation"><tbody><?php

		global $wpdb;
		$term_meta = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->termmeta WHERE term_id = %d ORDER BY meta_key", $term->term_id ) );

		foreach ( (array) $term_meta as $row ) :
			$disabled = false;

			if ( '' === $row->meta_key ) {
				continue;
			}

			if ( is_serialized( $row->meta_value ) ) {
				if ( is_serialized_string( $row->meta_value ) ) {
					// This is a serialized string, so we should display it.
					$value               = maybe_unserialize( $row->meta_value );
					$term_meta_to_update[] = $row->meta_key;
					$class               = 'all-options';
				} else {
					$value    = 'SERIALIZED DATA';
					$disabled = true;
					$class    = 'all-options disabled';
				}
			} else {
				$value               = $row->meta_value;
				$term_meta_to_update[] = $row->meta_key;
				$class               = 'all-options';
			}

			$name = esc_attr( $row->meta_key );

		?><tr>
			<th scope="row"><label for="<?php echo $name; ?>"><?php echo esc_html( $row->meta_key ); ?></label></th>
		<td>
			<?php if ( strpos( $value, "\n" ) !== false ) : ?>
				<textarea class="<?php echo $class; ?>" name="<?php echo $name; ?>" id="<?php echo $name; ?>" cols="30" rows="5"><?php echo esc_textarea( $value ); ?></textarea>
			<?php else : ?>
				<input class="regular-text <?php echo $class; ?>" type="text" name="<?php echo $name; ?>" id="<?php echo $name; ?>" value="<?php echo esc_attr( $value ); ?>"<?php disabled( $disabled, true ); ?> />
			<?php endif; ?></td>
		</tr><?php endforeach; ?></tbody></table></div></div><?php
	}

	public function add_term_meta_box()
	{
		$current_taxonomy = filter_input( INPUT_GET, 'taxonomy' );

		$ignored_taxonomies = apply_filters( 'term_meta_ui_ignored_taxonomies', array() );

		if ( is_array( $ignored_taxonomies ) && in_array( $current_taxonomy, $ignored_taxonomies, true ) ) {
			return;
		}

		add_action( "{$current_taxonomy}_edit_form", array( $this, 'add_meta_box' ) );
	}
}
$term_meta_ui_plugin_2038745092374 = new Term_Meta_UI();
$term_meta_ui_plugin_2038745092374->add_hooks();
