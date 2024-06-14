<?php
/**
 * Plugin Name:       Gatherpress onlineevent-or-venue block
 * Description:       An experiment to replace the  `gatherpress/online-event` block with a block-variation of the `core/post-terms` and/or the `core/button` block.
 * Version:           0.1.0-alpha
 * Requires at least: 6.5.3
 * Requires PHP:      8.1
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gatherpress-onlineevent-or-venue
 *
 * @package           create-block
 */

namespace GatherPressOnlineeventOrVenueBlock;

use GatherPress\Core\Event;
	
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Start the engines.
 *
 * @return void
 */
function bootstrap(): void {




	// Register and enqueue blocks only if $with_venue_archives = true;
	// $settings            = Settings::get_instance();
	// $with_venue_archives = $settings->get_value( 'general', 'general', 'with_venue_archives' ),
	
	add_action( 'init', __NAMESPACE__ . '\\register_assets', 1 );
	add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\\enqueue_assets' );
	
	// https://developer.wordpress.org/reference/hooks/render_block_this-name/
	add_filter( 'render_block_core/post-terms', __NAMESPACE__ . '\\render_post_terms_block', 10, 3 );

	// God damn!!!!
	//
	// https://core.trac.wordpress.org/ticket/59551
	// [...] This means anybody following the example usage of that filter could end up
	// with their return not respected unless they change priority to higher than 10,
	// which is not documented!
	//
	// Prevent block rendering if no 'online-event' term exists.
	add_filter( 'pre_render_block', __NAMESPACE__ . '\\pre_render_button_block', 11, 2 );
		add_filter( 'render_block_core/button', __NAMESPACE__ . '\\render_button_block', 10, 3 );
	
	// add_filter( 'hooked_block_types', __NAMESPACE__ . '\\hook_block_into_pattern', 10, 4 );
	
	// add_filter( 'hooked_block_core/post-terms', __NAMESPACE__ . '\\modify_hooked_block_in_pattern', 10, 5 );
	
	add_action( 'init', __NAMESPACE__ . '\\register_block_bindings_source' );
	/**
	 * ATTENTION
	 * 
	 * This includes BREAKING CHANGES, because permalinks may change and must be reset.
	 */
	\add_filter(
		'register_' . 'gatherpress_event' . '_post_type_args',
		function ( $args ) {
			
			// Allow for i18n post type archive urls.
			$args['rewrite']         = ( isset( $args['rewrite'] ) ) ? $args['rewrite'] : array();
			$args['rewrite']['slug'] = prepare_rewrite_argument( $args['labels']['singular_name'] );
			
			return $args;
		},
		11
	);
	\add_filter(
		'register_' . 'gatherpress_venue' . '_post_type_args',
		function ( $args ) {
			
			// Allow for i18n post type archive urls.
			$args['rewrite']         = ( isset( $args['rewrite'] ) ) ? $args['rewrite'] : array();
			$args['rewrite']['slug'] = prepare_rewrite_argument( $args['labels']['singular_name'] );
	
			return $args;
		},
		11
	);
	\add_filter(
		'register_' . '_gatherpress_venue' . '_taxonomy_args',
		function ( $args ) {

			/**
			 * Event-Archives per Venue, like archives for categories or tags.
			 * 
			 * Disable this option to:
			 * - avoid duplicated content issues,
			 * - keep your sitemaps clean,
			 * - avoid having a venue-tag-cloud (block),
			 * - and to keep the block-inserter free from unnecessary clutter.
			 *
			 * Depending on the used theme AND the Use-case, enabling archives maybe from interest:
			 *
			 * 1. Meetup Group
			 *    Has only one physical venue, maybe online-events
			 *    => Does not need Event-Archives per Venue.
			 *
			 * 2. Patricias Chire
			 *    Has multiple venues
			 *    => Could benefit from Event-Archives per Venue.
			 *
			 * 3. A theater (portal) website
			 *    Has multiple venues, maybe hybrid & maybe online-events
			 *    => Absolutely needs Event-Archives per Venue.
			 */
			/**
			 * QUESTION: Wasn't there a filter to add or remove settings?
			 */
			// $settings            = Settings::get_instance();
			// $with_venue_archives = $settings->get_value( 'general', 'general', 'with_venue_archives' ),
			$with_venue_archives = true;
			// $with_venue_archives = false;

			// $args['rewrite']            = false; // Results in https://gatherpress.test/?_gatherpress_venue=_new-york
			// $args['publicly_queryable'] = false; // Results in: nothing rendered in frontend & block says: "Term items not found".
			// $args['publicly_queryable'] = \is_admin(); // Results in: nothing rendered in frontend & block says: "Term items not found".

			// $args['public']             = $with_venue_archives;
			// $args['publicly_queryable'] = $with_venue_archives ?? true;

			$args['publicly_queryable'] = $with_venue_archives;
			$_args_rewrite              = ( isset( $args['rewrite'] ) ) ? $args['rewrite'] : [];
			$args['rewrite']            = get_rewrite_argument( $with_venue_archives, $_args_rewrite );

			return $args;
		}
	);
}
bootstrap();


function prepare_rewrite_argument( string $rewrite_suggest ): string {
	return strtolower( sanitize_title_with_dashes( $rewrite_suggest ) );
}

function get_rewrite_argument( bool $with_venue_archives, $rewrite_argument ): string|array|bool {
	if ( ! $with_venue_archives ) {
		return false;
	}

	// Nicest version, a plural of the registered post type.
	// Allow for i18n taxonomy archive urls.
	$event_post_type = get_post_type_object( 'gatherpress_event' );
	if ( $event_post_type instanceof \WP_Post_Type && ! empty( $event_post_type->labels->name ) ) {
		$slug = prepare_rewrite_argument( $event_post_type->labels->name );
		// Prevent errors with identical URLs.
		if ( $slug !== $event_post_type->rewrite['slug'] ) {
			$rewrite_argument['slug'] = $slug;
			return $rewrite_argument;
		}
	}
	// ok'ish version
	// same slug as post type with an underscore prefixed
	$venue_post_type = get_post_type_object( 'gatherpress_venue' );
	if ( isset( $venue_post_type->rewrite['slug'] ) ) {
		$slug                     = '_' . $venue_post_type->rewrite['slug'];
		$rewrite_argument['slug'] = $slug;
		return $rewrite_argument;
	}
	// fallback, can be trueish or false.
	return $venue_post_type->rewrite;
}


/**
 * Get backend-only editor assets.
 *
 * @return string[]
 */
function get_editor_assets(): array {
	return [
	// 'onlineevent-or-venue',
	];
}


/**
 * 
 *
 * @return void
 */
function register_assets(): void {
	\array_map(
		__NAMESPACE__ . '\\register_asset',
		\array_merge(
			get_editor_assets(),
			[
				'variations',
			]
		)
	);
}

/**
 * Enqueue all scripts.
 *
 * @return void
 */
function enqueue_assets(): void {
	\array_map(
		__NAMESPACE__ . '\\enqueue_asset',
		// get_editor_assets()
		[
			'variations',
		]
	);
}

/**
 * Enqueue a script.
 *
 * @param  string $asset Slug of the block to load the frontend scripts for.
 *
 * @return void
 */
function enqueue_asset( string $asset ): void {
	wp_enqueue_script( "gatherpress-onlineevent-or-venue--$asset" );
	// wp_enqueue_style( "gatherpress-onlineevent-or-venue--$asset" );
}


/**
 * Register a new script and sets translated strings for the script.
 *
 * @throws \Error If build-files doesn't exist errors out in local environments and writes to error_log otherwise.
 *
 * @param  string $asset Slug of the block to register scripts and translations for.
 *
 * @return void
 */
function register_asset( string $asset ): void {

	$dir = __DIR__;

	$script_asset_path = "$dir/build/$asset/$asset.asset.php";

	
	if ( ! \file_exists( $script_asset_path ) ) {
		$error_message = "You need to run `npm start` or `npm run build` for the '$asset' block-asset first.";
		if ( \in_array( wp_get_environment_type(), [ 'local', 'development' ], true ) ) {
			throw new \Error( esc_html( $error_message ) );
		} else {
			// Should write to the \error_log( $error_message ); if possible.
			return;
		}
	}

	$index_js     = "build/$asset/$asset.js";
	$script_asset = require $script_asset_path; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
	\wp_register_script(
		"gatherpress-onlineevent-or-venue--$asset",
		plugins_url( $index_js, __FILE__ ),
		$script_asset['dependencies'],
		$script_asset['version'],
		true
	);

	// $index_css = "build/$asset/$asset.css";
	// \wp_register_style(
	// "gatherpress-onlineevent-or-venue--$asset",
	// plugins_url( $index_css, __FILE__ ),
	// [ 'wp-block-post-date','global-styles' ],
	// time(),
	// 'screen'
	// );
	wp_set_script_translations(
		"gatherpress-onlineevent-or-venue--$asset",
		'gatherpress',
		"$dir/languages"
	);
}



/**
 * Filter the render_block to add the needed directives to the inner cover blocks.
 * 
 * @see https://developer.wordpress.org/reference/hooks/render_block_this-name/
 *
 * @param string $block_content The content being rendered by the block.
 */
function render_post_terms_block( $block_content, $block, $instance ) {
	if ( ! isset( $block['attrs']['className'] ) || false === \strpos( $block['attrs']['className'], 'gp-onlineevent-or-venue' ) ) {
		return $block_content;
	}

	// $block_content = '<pre>' . \var_export(
	// [
	// $block['attrs'],
	// $instance->context,
	// // get_post( $instance->context['postId'] ),
	// // \get_post_meta( 
	// // $instance->context['postId'],
	// // 'venue_information_website',
	// // true
	// // ),
	// ],
	// true 
	// ) . '</pre>' . $block_content;

	return $block_content;
}

/**
 * Prevent block rendering if no 'online-event' term exists.
 * 
 * Allows render_block() to be short-circuited, by returning a non-null value.
 */
function pre_render_button_block( $pre_render, $parsed_block ) {
	if ( isset( $parsed_block['attrs']['className'] ) && false !== \strpos( $parsed_block['attrs']['className'], 'gp-onlineevent-or-venue-button' ) ) {

		// error_log( \var_export(
		// $pre_render = '<pre>' . \var_export(
		// [
		// __FUNCTION__,
		// get_post()->ID,
		// $parsed_block['attrs'],
		// $parsed_block['attrs']['className'],
		// $instance->context,
		// get_post( $instance->context['postId'] ),
		// \get_post_meta( 
		// $instance->context['postId'],
		// 'venue_information_website',
		// true
		// ),
		// ],
		// true 
		// )
		// );
		// ) . '</pre>' . $pre_render;


		// Will short-circuit if no 'online-event' term exists.
		if ( ! \has_term( 'online-event', '_gatherpress_venue', get_post()->ID ) ) {
			
			return false; // And do not render the block at all.

			// Can still be a real venue.
			// Maybe show the website of the venue here ???
			// return '';
		}


		// // DEMO & DEBUG ONLY !!!
		// $on_off = ( 1 === rand( 1, 2 ) ) ? '__return_true' : '__return_false';
		// \add_filter( 'gatherpress_force_online_event_link', $on_off );
		// // \add_filter( 'gatherpress_force_online_event_link', '__return_true' );
	}

	return $pre_render;
}


/**
 * Filter the render_block to ...
 * 
 * At this point we already know, that the event has the 'online-event' term.
 * 
 * @see https://developer.wordpress.org/reference/hooks/render_block_this-name/
 *
 * @param string $block_content The content being rendered by the block.
 */
function render_button_block( $block_content, $block, $instance ) {
	if ( ! isset( $block['attrs']['className'] ) || false === \strpos( $block['attrs']['className'], 'gp-onlineevent-or-venue-button' ) ) {
		return $block_content;
	}
	// Change behaviour ...
	// ... or remove rendering.
	// return '';
	if ( empty( get_block_binding_values( [ 'key' => 'url' ], $instance ) ) ) {
		$button = new \WP_HTML_Tag_Processor( $block_content );
		if ( $button->next_tag( 'a' ) ) {
			// Inform the user with a spinning cursor and a waiting message.
			$button->set_attribute( 'style', 'cursor:wait;' . $button->get_attribute( 'style' ) );
			$button->set_attribute( 'title', 'Link is visible to attendees only. (CHANGES BY RANDOM FOR THE DEMO)' );
			// Allow for styling with CSS.
			$button->add_class( 'gp-onlineevent-or-venue-button__disabled' );
			// Prevent click & focus,
			// by removing the href, which works better than 'disabled'.
			$button->remove_attribute( 'href' );
			// Return modified HTML.
			$block_content = $button->get_updated_html();
		}   
	}
	// // // DEMO & DEBUG ONLY !!!
	\remove_all_filters( 'gatherpress_force_online_event_link' );
	
	// $block_content = '<pre>' . \var_export( 
	// [
	// // $block['attrs'],
	// // $instance->context,
	// // get_post( $instance->context['postId'] ),
	// // \get_post_meta( 
	// // \has_term( 'online-event', '_gatherpress_venue', $instance->context['postId'] ),
	// $instance->context['postId'],
	// // 'venue_information_website',
	// // true
	// // ),
	// ],
	// true 
	// ) . '</pre>' . $block_content;

	return $block_content;
}


function hook_block_into_pattern( $hooked_block_types, $relative_position, $anchor_block_type, $context ) {

	if (
		// Conditionally hook the block into the "gatherpress/venue-template" pattern.
		is_array( $context ) &&
		isset( $context['name'] ) &&
		'gatherpress/venue-template' === $context['name']
	) {

		// Conditionally hook the block after "the" paragraph block,
		// this <p> is the important one, like described in gatherpress/includes/core/classes/class-block.php.
		if ( 'after' === $relative_position && 'core/post-terms' === $anchor_block_type ) {
			$hooked_block_types[] = 'core/post-terms';
		}
	}

	if (
		// Conditionally hook the block into the "gatherpress/venue-details" pattern.
		is_array( $context ) &&
		isset( $context['name'] ) &&
		'gatherpress/venue-details' === $context['name']
	) {

		// Conditionally hook the block after "the" post-title block.
		if ( 'after' === $relative_position && 'core/post-title' === $anchor_block_type ) {
			$hooked_block_types[] = 'core/post-terms';
		}
	}
	return $hooked_block_types;
}


function modify_hooked_block_in_pattern( $parsed_hooked_block, $hooked_block_type, $relative_position, $parsed_anchor_block, $context ) {

	// Has the hooked block been suppressed by a previous filter?
	if ( is_null( $parsed_hooked_block ) ) {
		return $parsed_hooked_block;
	}

	// Conditionally hook the block into the "gatherpress/venue-facts" pattern.
	if (
		! is_array( $context ) ||
		! isset( $context['name'] ) ||
		( 'gatherpress/venue-details' !== $context['name'] && 'gatherpress/venue-template' !== $context['name'] )
	) {
		return $parsed_hooked_block;
	}

	// Only apply the updated attributes if the block is hooked after a Site Title block.
	if ( ( 'core/post-title' === $parsed_anchor_block['blockName'] || 'core/post-terms' === $parsed_anchor_block['blockName'] ) &&
		'after' === $relative_position
	) {
		$parsed_hooked_block['innerContent']                                       = [ '<p class="gp-onlineevent-or-venue"></p>' ]; // important to get a paragraph injected at all.
		$parsed_hooked_block['attrs']['className']                                 = 'gp-onlineevent-or-venue';
		$parsed_hooked_block['attrs']['placeholder']                               = __( 'No website added, yet.', 'gatherpress' ); // className is not supported for paragraphs, and so we can't set it.
		$parsed_hooked_block['attrs']['metadata']                                  = [];
		$parsed_hooked_block['attrs']['metadata']['bindings']                      = [];
		$parsed_hooked_block['attrs']['metadata']['bindings']['content']           = [];
		$parsed_hooked_block['attrs']['metadata']['bindings']['content']['source'] = 'core/post-meta';
		$parsed_hooked_block['attrs']['metadata']['bindings']['content']['args']   = [];
		// $parsed_hooked_block['attrs']['metadata']['bindings']['content']['args']['key'] = [ 'venue_information_website' ];
		$parsed_hooked_block['attrs']['metadata']['bindings']['content']['args']['key'] = 'venue_information_website';
		// wp_die($parsed_hooked_block);
	}

	return $parsed_hooked_block;
}
















/**
 * Handle returing the block binding value for the current post type.
 *
 * @since 0.1
 *
 * @param array    $source_args    An array of arguments passed via the metadata.bindings.$attribute.args property from the block.
 * @param WP_Block $block_instance The current instance of the block the binding is connected to as a WP_Block object.
 * @param mixed    $attribute_name The current attribute set via the metadata.bindings.$attribute property on the block.
 *
 * @return string The block binding value
 */
function get_block_binding_values( $source_args, $block_instance ) {
	// If no key or user ID argument is set, bail early.
	if ( ! isset( $source_args['key'] ) || ! isset( $block_instance->parsed_block['attrs']['className'] ) ) {
		return null;
	}
	if ( 'gatherpress_event' !== $block_instance->context['postType'] ) {
		return null;
	}
	// DEMO & DEBUG ONLY !!!
	$on_off = ( 1 === rand( 1, 2 ) ) ? '__return_true' : '__return_false';
	\add_filter( 'gatherpress_force_online_event_link', $on_off );
	// \add_filter( 'gatherpress_force_online_event_link', '__return_true' );

	// Get the post ID from context.
	$post_id           = $block_instance->context['postId'];
	$gatherpress_event = new Event( $post_id );
	$url               = $gatherpress_event->maybe_get_online_event_link();

	// DEMO & DEBUG ONLY !!!
	// \remove_all_filters( 'gatherpress_force_online_event_link' );
	
	// error_log( \var_export(
	// $pre_render = '<pre>' . \var_export(
	// [
	// $block_instance->parsed_block['attrs'],
	// $block_instance,
	// ],
	// true 
	// )
	// );

	$url_shorten = $block_instance->parsed_block['attrs']['urlShorten'] ?: 20;

	// Return the data based on the key argument.
	switch ( $source_args['key'] ) {
		case 'url':
			return ( $url ) ? $url : '';
		case 'text':
			// Show a shortened version of the URL or 'null', which resets the 'text' to what is coming from the editor.
			return ( $url ) ? sprintf( '%s', \url_shorten( $url, $url_shorten ) ) : null;
		default:
			return null;
	}
	return null;
}

/**
 * Registers the "gatherpress/onlineevent-or-venue" source for the Block Bindings API.
 * This allows you to access data related to the online-event or the venue of the current event.
 *
 * $source_name: A unique name for your custom binding source in the form of namespace/slug.
 * $source_properties: An array of properties to define your binding source:
 *     label: An internationalized text string to represent the binding source. Note: this is not currently shown anywhere in the UI.
 *     get_value_callback: A PHP callable (function, closure, etc.) that is called when a block’s attribute matches the $source_name parameter.
 *     uses_context: (Optional) Extends the block instance with an array of contexts if needed for the callback.
 *                   For example, if you need the current post ID, you’d set this to [ 'postId' ].
 */
function register_block_bindings_source() {
	\register_block_bindings_source(
		'gatherpress/onlineevent-or-venue',
		array(
			'label'              => __( 'Online-Event Link', 'gatherpress' ),
			'get_value_callback' => __NAMESPACE__ . '\\get_block_binding_values',
			'uses_context'       => [ 'postId' ],
		)
	);
}
