<?php
/**
 * Class Schema
 * 
 * @package NextPress\Uri_Assets;
 * @since 0.0.1
 */

namespace NextPress\Uri_Assets;

use WPGraphQL\Data\Connection\EnqueuedScriptsConnectionResolver;
use WPGraphQL\Data\Connection\EnqueuedStylesheetConnectionResolver;

class Schema {
    /**
     * Schema constructor
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Initializes the class.
     * 
     * @return void
     */
    private function init() {
        add_action( 'graphql_register_types', [ $this, 'register_schema' ] );
    }

    /**
     * Register the schema
     * 
     * @return void
     */
    public function register_schema() {
        register_graphql_object_type(
            'UriAssets',
            [
                'interfaces' => [ 'Node' ],
                'fields' => [
                    'id' => [
                        'type' => 'ID',
                        'description' => __( 'The global ID of the URI Assets object.', 'nextpress' ),
                    ],
                    'uri' => [
                        'type' => 'String',
                        'description' => __( 'The URI of the assets.', 'nextpress' ),
                    ],
                ],
                'connections' => [
                    'enqueuedScripts'     => [
						'toType'  => 'EnqueuedScript',
						'resolve' => static function ( $source, $args, $context, $info ) {
							$resolver = new EnqueuedScriptsConnectionResolver( $source, $args, $context, $info );

							return $resolver->get_connection();
						},
					],
					'enqueuedStylesheets' => [
						'toType'  => 'EnqueuedStylesheet',
						'resolve' => static function ( $source, $args, $context, $info ) {
							$resolver = new EnqueuedStylesheetConnectionResolver( $source, $args, $context, $info );
							return $resolver->get_connection();
						},
					],
                ],
            ]
        );
    
        register_graphql_field(
            'RootQuery',
            'assetsByUri',
            [
                'type' => 'UriAssets',
                'args' => [
                    'uri' => [
                        'type' => ['non_null' => 'String' ],
                        'description' => __( 'The URI of the asset to query.', 'nextpress' ),
                    ],
                ],
                'resolve' => function ( $root, $args, $context, $info ) {
                    return $context->get_loader( 'uri_assets' )->load( $args['uri'] );
                },
            ]
        );
    }
}