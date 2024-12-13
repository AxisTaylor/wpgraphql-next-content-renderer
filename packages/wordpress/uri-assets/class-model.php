<?php
/**
 * Class Model - Defines the Model class for the plugin.
 * 
 * @package NextPress\Uri_Assets  
 * @since 0.0.1
 */

namespace NextPress\Uri_Assets;

use GraphQLRelay\Relay;
use WPGraphQL\Data\NodeResolver;
use WPGraphQL\Model\Model as Base;

/**
 * Class Tax_Rate
 *
 * @property string $data
 *
 * @property string   $ID
 * @property string   $id
 * @property string   $uri
 * @property string[] $enqueuedScriptsQueue
 * @property string[] $enqueuedStylesheetsQueue
 *
 * @package WPGraphQL\WooCommerce\Model
 */
class Model extends Base {
    /**
	 * Model constructor
	 *
	 * @param string $uri URI/Path.
	 */
	public function __construct( $uri ) {
		$this->data                = $uri;
		$allowed_restricted_fields = [
			'isRestricted',
			'isPrivate',
			'isPublic',
			'id',
			'databaseId',
		];

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		$restricted_cap = apply_filters( 'uri_assets_restricted_cap', '' );

		parent::__construct( $restricted_cap, $allowed_restricted_fields, null );
	}

    /**
	 * Determines if the order item should be considered private
	 *
	 * @return bool
	 */
	protected function is_private() {
		return false;
	}

    	/**
	 * Get the handles of all scripts enqueued for a given content node
	 *
	 * @param array<string, string> $queue      List of scripts for a given content node.
	 * @param \WP_Dependencies      $wp_assets  A Global assets object.
	 *
	 * @return string[]
	 */
	protected function flatten_enqueued_assets_list( array $queue, $wp_assets ) {
		$registered_scripts = $wp_assets->registered;
		$handles            = [];
		foreach ( $queue as $handle ) {
			if ( empty( $registered_scripts[ $handle ] ) ) {
				continue;
			}

			/** @var \_WP_Dependency $script */
			$script    = $registered_scripts[ $handle ];
			$handles[] = $script->handle;

			$dependencies = $this->flatten_enqueued_assets_list( $script->deps, $wp_assets );
			if ( empty( $dependencies ) ) {
				continue;
			}

			array_unshift( $handles, ...$dependencies );
		}

		return array_values( array_unique( $handles ) );
	}

	/**
	 * Initializes the Order field resolvers.
	 */
	protected function init() {
		if ( empty( $this->fields ) ) {
			$this->fields = [
                'ID' => function() {
                    return $this->data;
                },
                'id' => function() {
                    return ! empty( $this->data ) ? Relay::toGlobalId( 'asset', $this->data ) : null;
                },
                'uri' => function() {
                    return $this->data;
                },
                'enqueuedScriptsQueue' => function() {
                    global $wp_scripts;

					// Simulate WP template rendering.
					ob_start();
					do_action( 'wp_head' );
					/**
					 * We only need to call "$source->contentRendered;" for the simulation,
					 * however it is being assigned to a variable to quiet phpstan.
					 */
                    $context = \WPGraphQL::get_app_context();
                    $node_resolver = new NodeResolver( $context );
		            $content = $node_resolver->resolve_uri( $this->data );
					unset( $content );
					do_action( 'get_sidebar', null, [] );
					do_action( 'wp_footer' );
					ob_get_clean();

					// Sort and organize the enqueued scripts.
					$queue = $this->flatten_enqueued_assets_list( $wp_scripts->queue ?? [], $wp_scripts );

					// Reset the scripts queue to avoid conflicts with other queries.
					$wp_scripts->reset();
					$wp_scripts->queue = [];

					return $queue;
                },
                'enqueuedStylesheetsQueue' => function() {
                    global $wp_styles;

					// Simulate WP template rendering.
					ob_start();
					wp_head();
					/**
					 * We only need to call "$source->contentRendered;" for the simulation,
					 * however it is being assigned to a variable to quiet phpstan.
					 */
					$context = \WPGraphQL::get_app_context();
                    $node_resolver = new NodeResolver( $context );
		            $content = $node_resolver->resolve_uri( $this->data );
					unset( $content );
					do_action( 'get_sidebar', null, [] );
					wp_footer();
					ob_get_clean();

					// Sort and organize the enqueued stylesheets.
					$queue = $this->flatten_enqueued_assets_list( $wp_styles->queue ?? [], $wp_styles );

					$wp_styles->reset();
					$wp_styles->queue = [];

					return $queue;
                },
            ];
		}//end if
	}
}