<?php
/*
Plugin Name: WP Password Redirect
Plugin URI: https://github.com/codebureau/WP-Password-Redirect
Description: Allows a central login page for password protected child pages, or posts (with optional custom type). Enter a password and (based on shortcode configuration), you are taken to the newest, child page/post with a matching password.
Version: 1.0.0
Author: Matt Simner
Author URI: https://codebureau.com
License: GPL2
Requires: 2.5

Copyright 2024  Matt Simner  (email : plugins@codebureau.com).  
Ported from Smart Passworded Pages by Brian Layman - 2015.

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Define Constants
 */
define( 'SECONDS_TO_STORE_PW', 864000); // 864000 = 10 Days 

/**
 * WP Password Redirect Class
 * @copyright Copyright (c), Matt Simner
 * @author Matt Simner <plugins@codebureau.com>
 */
 class wpPWRedirect {
	/**
	 * WP Password Redirect
	 * Embeds a form for password submission into a post via a shortcode.
	 */
     function wppwredirect_shortcode( $atts ) {
		global $post;

		extract( shortcode_atts( array(
			'label' => __( 'Enter', 'wppwredirect' ),
			'ID' => 'wpPWLogin',
			'type' => 'page',
			'posttype' => '', 
			'errormsg' => 'You\'ve entered an invalid password',
			'parent' => $post->ID,
		), $atts ) );

		$result =  '<form ID="' . esc_attr( $ID ) . '" method="post" action="' . esc_url( get_permalink() ) . '" >' . PHP_EOL;
		if ( isset( $_GET['wrongpw'] ) ) $result .= '<p id="wpPWError">' . __( $errormsg . '</p>', 'wppwredirect' ) . PHP_EOL;
		$result .= '	<input class="requiredField" type="password" name="wppwPassword" id="wppwPassword" value=""/>' . PHP_EOL;
		$result .= '	<input type="hidden" name="wppwType" value="' .  $type . '" />' . PHP_EOL;
		$result .= '	<input type="hidden" name="wppwPostType" value="' .  $posttype . '" />' . PHP_EOL;
		$result .= '	<input type="hidden" name="wppwParent" value="' .  (int) $parent . '" />' . PHP_EOL;
		$result .= '	<input type="hidden" name="wpPWPage_nonce" value="' . wp_create_nonce( 'wpPWPage' ).'" />' . PHP_EOL;
		$result .= '	<input type="submit" value="' . esc_attr( $label ). '" />' . PHP_EOL;
		$result .= '</form>' . PHP_EOL;
		return $result;
	}

	/**
	 * Password Redirect
	 * Decodes the password, stores it in a cookie and redirects the visitor to that page/post.
	 */
	 function pw_redirect( $perma, $password ) {
		global $wp_version, $wp_hasher;

		// Version 3.6 introduces a new function
		if ( function_exists( 'wp_unslash' ) ) {
			$cookiePW = wp_unslash( $password );
		} else {
			$cookiePW = stripslashes( $password );
		}

		// Version 3.4 and higher has better security on the pw pages
		if ( version_compare( $wp_version, '3.4', '>=' ) ) {
			if ( empty( $wp_hasher ) ) {
				// By default, use the portable hash from phpass
				require_once( ABSPATH . 'wp-includes/class-phpass.php');
				$wp_hasher = new PasswordHash( 8, true );
			}

			// Potentially using a custom hasher, hash the pw
			$cookiePW = $wp_hasher->HashPassword( $cookiePW );
		}		
		
		$secure = ( 'https' === parse_url( home_url(), PHP_URL_SCHEME ) );
		
		// Store password for the length in the constant
		setcookie( 'wp-postpass_' . COOKIEHASH, $cookiePW, time() + SECONDS_TO_STORE_PW, COOKIEPATH, COOKIE_DOMAIN, $secure );
		wp_safe_redirect( $perma );
		
		

		exit();
	}
	
	/**
	 * Process Form
	 * Decodes the password submitted on a form, find a page that uses it and redirects the visitor to that page/post.
	 */
	function process_form() {
		global $wp_version, $wp_hasher;
		if ( isset( $_POST[ 'wppwPassword' ] ) && isset( $_POST[ 'wppwParent' ] ) && wp_verify_nonce( $_POST[ 'wpPWPage_nonce' ], 'wpPWPage' ) ) {
			$parentForm  = (int) $_POST[ 'wppwParent' ] ;
			$password = $_POST[ 'wppwPassword' ];

			if ( function_exists( 'wp_unslash' ) ) {
				$postPassword = wp_unslash( $password );
			} else {
				$postPassword = stripslashes( $password );
			}

			//Set defaults
			$type = $_POST[ 'wppwType' ];
			$posttype = $_POST[ 'wppwPostType' ];

			if ( function_exists( 'pause_exclude_pages' ) ) pause_exclude_pages();

			if ( $type == 'page') {
				$args = array(		
					'sort_order' => 'DESC',
					'sort_column' => 'post_date',
					'hierarchical' => 1,
					'child_of' => $parentForm,
					'parent' => $parentForm,
					'post_type' => 'page',
					'post_status' => 'publish'
				);
				
				$myPages = get_pages( $args );
			}
			elseif ( $type == 'post') {
				$args = array(		
					'sort_order' => 'DESC',
					'sort_column' => 'post_date',
					'post_type' => $posttype,
					'post_status' => 'publish'
				);
				
				$myPages = get_posts( $args );

			}
			if ( function_exists( 'resume_exclude_pages' ) ) resume_exclude_pages();

			// Version 3.4 and higher has better security on the pw pages
			if ( version_compare( $wp_version, '3.4', '>=' ) ) {
				if ( empty( $wp_hasher ) ) {
					// By default, use the portable hash from phpass
					require_once( ABSPATH . 'wp-includes/class-phpass.php' );
					$wp_hasher = new PasswordHash( 8, true );
				}
			}

			foreach( $myPages as $page ) {
				if ( ( $page->post_password == $postPassword ) || ( !empty( $wp_hasher ) && 
						$wp_hasher->CheckPassword( $page->post_password, $postPassword ) ) ) {
					$permalink = get_permalink( $page->ID );
					$this->pw_redirect( $permalink, $postPassword );
				}
			}

			// Nothing more to do here. If we reached here, we've submitted a pw but no match was found. 
			// Allow the page to continue loading, but hack $_GET to indicate the status
			$_GET[ 'wrongpw' ] = TRUE;
		}
	}
}

/**
 * Intialize Plugin
 */
$wppwredirect = new wpPWRedirect();
add_action( 'init', array( $wppwredirect, 'process_form' ) );
add_shortcode( 'wppwredirect', array( $wppwredirect, 'wppwredirect_shortcode' ) );