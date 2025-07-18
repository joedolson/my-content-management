<?php
/**
 * My Content Management, Custom Post Type manager for WordPress
 *
 * @package     MyContentManagement
 * @author      Joe Dolson
 * @copyright   2011-2025 Joe Dolson
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: My Content Management
 * Plugin URI:  http://www.joedolson.com/my-content-management/
 * Description: Creates a set of common custom post types for extended content management: FAQ, Testimonials, people lists, term lists, etc.
 * Author:      Joseph C Dolson
 * Author URI:  http://www.joedolson.com
 * Text Domain: my-content-management
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/license/gpl-2.0.txt
 * Domain Path: lang
 * Version:     1.7.11
 */

/*
	Copyright 2011-2025  Joe Dolson (email : joe@joedolson.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require 'src/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$mcm_update_checker = PucFactory::buildUpdateChecker(
	'https://github.com/joedolson/my-content-management/',
	__FILE__,
	'my-content-management'
);

// Set the branch that contains the stable release.
$mcm_update_checker->setBranch( 'master' );

include( dirname( __FILE__ ) . '/src/my-content-management.php' );
