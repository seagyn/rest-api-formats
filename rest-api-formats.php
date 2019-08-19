<?php
/**
 * A plugin that handles the returning of different formats from the HTTP API
 *
 * @package RESTApiFormats
 *
 * @wordpress-plugin
 * Plugin Name: REST API Formats
 * Description: Allows you to return differnt formats from the REST API. Only XML at this point..
 * Version:     1.0.0
 * Text Domain: rest-api-formats
 * License:     GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

add_filter(
	'rest_pre_serve_request',
	function ( $served, $result, $request, $server ) {
		if ( ! isset( $request['format'] ) || empty( $request['format'] ) || 'json' === $request['format'] ) {
			return $served;
		}

		switch ( $request['format'] ) {
			case 'xml':
				header( 'Content-Type: application/xml; charset=' . get_option( 'blog_charset' ) );
				echo xml_conversion( $result->data );
				$served = true;
				break;
		}
		return $served;
	},
	10,
	4
);

/**
 * Converts an array into XML.
 *
 * @param array  $array Array that you want to convert to XML.
 * @param object $xml XML object getting built by the array.
 */
function xml_conversion( $array, $xml = null ) {
	if ( is_null( $xml ) ) {
		$xml = new SimpleXMLElement( '<result/>' );
	}

	foreach ( $array as $key => $value ) {
		if ( is_array( $value ) ) {
			if ( is_int( $key ) ) {
				xml_conversion( $value, $xml->addChild( 'item' ) );
			} else {
				xml_conversion( $value, $xml->addChild( $key ) );
			}
		} else {
			if ( is_int( $key ) ) {
				$xml->addChild( 'item', $value );
			} else {
				$xml->addChild( $key, $value );
			}
		}
	}

	return $xml->asXML();
}
