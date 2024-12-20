<?php
/**
 * Plugin Name: Term Meta UI
 * Description: Provides a user interface for reading and writing term meta data.
 * Author: Corey Salzano
 * Author URI: https://breakfastco.xyz
 * Version: 0.4.1
 * Text-domain: term-meta-ui
 * License: GPLv2
 * GitHub Plugin URI: csalzano/term-meta-ui
 * Primary Branch: main
 *
 * @author Corey Salzano <csalzano@duck.com>
 * @package term-meta-ui
 */

defined( 'ABSPATH' ) || exit;

/**
 * Term_Meta_UI
 */
class Term_Meta_UI {

	/**
	 * Adds hooks that power the feature.
	 *
	 * @return void
	 */
	public function add_hooks() {
		add_action( 'admin_init', array( $this, 'add_term_meta_box' ) );
		add_action( 'edit_term', array( $this, 'maybe_save_term_meta' ), 10 );
	}

	/**
	 * Outputs the UI HTML.
	 *
	 * @param  WP_Term $term The term being edited.
	 * @return void
	 */
	public function add_meta_box( $term ) {
		if ( ! $term instanceof WP_Term ) {
			return;
		}

		// Term Meta UI title.
		?><h2><?php esc_html_e( 'Term Meta UI', 'term-meta-ui' ); ?></h2><div id="poststuff"><div class="term_meta_ui"><table class="form-table" role="presentation"><tbody><tr>
			<?php

			// Add controls.
			?>
		<th scope="row"><input type="text" class="regular-text all-options" name="term_meta_ui_new_key" id="term_meta_ui_new_key" placeholder="<?php echo esc_attr__( 'new_key', 'term-meta-ui' ); ?>" /></th>
		<td><input type="text" class="regular-text all-options" name="term_meta_ui_new_value" id="term_meta_ui_new_value" placeholder="<?php echo esc_attr__( 'value', 'term-meta-ui' ); ?>" /></td>
		</tr>
		<?php

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
					$value                 = maybe_unserialize( $row->meta_value );
					$term_meta_to_update[] = $row->meta_key;
					$class                 = 'all-options';
				} else {
					$value    = 'SERIALIZED DATA';
					$disabled = true;
					$class    = 'all-options disabled';
				}
			} else {
				$value                 = $row->meta_value;
				$term_meta_to_update[] = $row->meta_key;
				$class                 = 'all-options';
			}

			$input_name = esc_attr( "meta[{$row->meta_key}][]" );

			?>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $input_name ); ?>"><?php echo esc_html( $row->meta_key ); ?></label></th>
		<td>
			<?php if ( strpos( $value, "\n" ) !== false ) : ?>
				<textarea class="<?php echo esc_attr( $class ); ?>" name="<?php echo esc_attr( $input_name ); ?>" id="<?php echo esc_attr( $input_name ); ?>" cols="30" rows="5"><?php echo esc_textarea( $value ); ?></textarea>
			<?php else : ?>
				<input class="regular-text <?php echo esc_attr( $class ); ?>" type="text" name="<?php echo esc_attr( $input_name ); ?>" id="<?php echo $input_name; ?>" value="<?php echo esc_attr( $value ); ?>"<?php disabled( $disabled, true ); ?> />
			<?php endif; ?>
		</td>
		</tr><?php endforeach; ?>
		</tbody></table></div></div>
		<?php
	}

	/**
	 * Decides whether or not the UI will be shown.
	 *
	 * @return void
	 */
	public function add_term_meta_box() {
		$current_taxonomy = filter_input( INPUT_GET, 'taxonomy' );

		$ignored_taxonomies = apply_filters( 'term_meta_ui_ignored_taxonomies', array() );

		if ( is_array( $ignored_taxonomies ) && in_array( $current_taxonomy, $ignored_taxonomies, true ) ) {
			return;
		}

		add_action( "{$current_taxonomy}_edit_form", array( $this, 'add_meta_box' ) );
	}

	/**
	 * Saves term meta values when the term is saved.
	 *
	 * @param  int $term_id Term ID.
	 * @return void
	 */
	public function maybe_save_term_meta( $term_id ) {
		// Is this edit-tags.php?
		if ( 'editedtag' !== filter_input( INPUT_POST, 'action' ) ) {
			return;
		}

		// Can this user edit terms?
		if ( ! current_user_can( 'edit_term', $term_id ) ) {
			// No.
			return;
		}

		if ( ! empty( $_POST['meta'] ) ) {
			foreach ( $_POST['meta'] as $key => $values ) {
				delete_term_meta( $term_id, $key );
				foreach ( $values as $value ) {
					add_term_meta( $term_id, $key, $value );
				}
			}
		}

		// Did the user enter values to add a new key value pair?
		if ( ! empty( $_POST['term_meta_ui_new_key'] ) ) {
			add_term_meta(
				$term_id,
				sanitize_text_field( $_POST['term_meta_ui_new_key'] ),
				sanitize_text_field( $_POST['term_meta_ui_new_value'] ?? '' )
			);
		}
	}
}
$term_meta_ui_plugin_2038745092374 = new Term_Meta_UI();
$term_meta_ui_plugin_2038745092374->add_hooks();
