<?php
/**
 * My Content Management uninstaller.
 *
 * @category Admin
 * @package  My Content Management
 * @author   Joe Dolson
 * @license  GPLv2 or later
 * @link     https://www.joedolson.com/my-content-management/
 */

if ( ! defined( 'ABSPATH' ) && ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
} else {
	delete_option( 'mcm_options' );
	delete_option( 'mcm_version' );
	delete_option( 'mcm_glossary_ignore' );
}