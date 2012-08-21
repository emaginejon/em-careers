<?php

/*	Plugin Name: EM Careers
	Description: Easily create a careers section on your website
	Version: 1.0.0
	Author: eMagine
	Author URI: http://www.emagineusa.com/
	License: GPL2
	
	Copyright 2012 eMagine  (email : info@emagineusa.com)

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

include_once ABSPATH . 'wp-includes/pluggable.php';
include_once ABSPATH . '/wp-includes/locale.php';
include_once 'core/includes/acf-field-groups.php';

$em_careers = new Em_Careers_Plugin();

class Em_Careers_Plugin
{
	/*--------------------------------------------------------------------------------------
	 *
	 * @var string $base_dir The base directory of the plugin
	 *
	 *--------------------------------------------------------------------------------------*/
	 
	var $base_dir;
	
	/*--------------------------------------------------------------------------------------
	 *
	 * @var string $base_url The base url of the plugin
	 *
	 *--------------------------------------------------------------------------------------*/
	
	var $base_url;
	
	/*--------------------------------------------------------------------------------------
	 *
	 * @var bool $content_filtered Whether the_content has been filtered already or not
	 *
	 *--------------------------------------------------------------------------------------*/
	
	var $content_filtered = false;
	
	/*--------------------------------------------------------------------------------------
	 *
	 * @var array $settings The plugin's settings
	 *
	 *--------------------------------------------------------------------------------------*/
	
	var $settings = array();
	
	/*--------------------------------------------------------------------------------------
	 *
	 * @var bool $settings_saved Have the settings been saved
	 *
	 *--------------------------------------------------------------------------------------*/
	
	var $settings_saved = false;
	
	/*--------------------------------------------------------------------------------------
	 *
	 * @var string $slug The slug for the career post type
	 *
	 *--------------------------------------------------------------------------------------*/
	
	var $slug = 'career';
	
	/*--------------------------------------------------------------------------------------
	 *
	 * @var string $tax_slug The slug for the location taxonomy
	 *
	 *--------------------------------------------------------------------------------------*/
	
	var $tax_slug = 'career-location';
	
	/*--------------------------------------------------------------------------------------
	 *
	 * @var string $version The current plugin version
	 *
	 *--------------------------------------------------------------------------------------*/
	
	var $version = '1.0.0';
	
	/*--------------------------------------------------------------------------------------
	 *
	 * Constructor
	 *
	 * @author jcowher
	 * @since 1.0.0
	 *
	 *--------------------------------------------------------------------------------------*/
	
	function __construct()
	{
		$this->setup();
		
		if ( defined('DOING_AJAX') && DOING_AJAX ) {
			$this->setup_ajax();
			return;
		}
		
		if ( is_admin() )
			$this->setup_admin();
		else
			$this->setup_frontend();
	}
	
	/*--------------------------------------------------------------------------------------
	 *
	 * Activation hook
	 *
	 * @author jcowher
	 * @since 1.0.0
	 *
	 *--------------------------------------------------------------------------------------*/
	
	function activate()
	{
		if ( ! class_exists('Acf') )
			wp_die('<p><strong>Could not activate!</strong> Please install and activate Advanced Custom Fields before activating the careers plugin.<br /><a href="plugins.php">&laquo; Return to plugins</a></p>');
	}
	
	/*--------------------------------------------------------------------------------------
	 *
	 * Display a view
	 *
	 * @author jcowher
	 * @since 1.0.0
	 *
	 *--------------------------------------------------------------------------------------*/
	
	function display_view()
	{
		$screen = get_current_screen();
		$path = $this->base_dir . 'core/views/' . $screen->id . '.php';
		
		if ( file_exists($path) ) {
			include_once $this->base_dir . 'core/classes/class-em-field.php';
			include_once $path;
		}
	}
	
	/*--------------------------------------------------------------------------------------
	 *
	 * Get the current url structure
	 *
	 * @return string
	 * @author jcowher
	 * @since 1.0.0
	 *
	 *--------------------------------------------------------------------------------------*/
	
	function get_url_structure()
	{
		$url = '';
		
		if ( empty($this->settings['parent_page']) )
			$url .= home_url('/');
		else
			$url .= get_page_link($this->settings['parent_page']);
			
		return $url;	
	}
	
	/*--------------------------------------------------------------------------------------
	 *
	 * Parse a HTML template
	 *
	 * @param string $template The name of the template to parse
	 * @return string
	 * @author jcowher
	 * @since 1.0.0
	 *
	 *--------------------------------------------------------------------------------------*/
	
	function parse_template( $template )
	{
		global $post;
		
		$find = array(
			'{name}',
			'{shortdesc}',
			'{fulldesc}',
			'{applylink}',
			'{morelink}',
			'{location}',
		);
		
		$locations = (array) get_the_terms($post->ID, $this->tax_slug);
		$locs = array();
		
		foreach ( $locations as $location ) {
			$locs[] = $location->name;
		}
		
		$replace = array(
			get_the_title(),
			get_field('career_short_description') ? '<div class="description">' . get_field('career_short_description') . '</div>' : '',
			get_field('career_full_description') ? '<div class="description">' . get_field('career_full_description') . '</div>' : '',
			get_page_link($this->settings['apply_page']) . '?job=' . urlencode(get_the_title()),
			get_permalink(),
			empty($locs) ? '' : '<p class="locations">Location: ' . implode(', ', $locs) . '</p>',
		);
		
		return str_replace($find, $replace, $this->settings[$template]);
	}
	
	/*--------------------------------------------------------------------------------------
	 *
	 * Filter the main query so that the apply online page shows up properly
	 *
	 * @param object $query
	 * @author jcowher
	 * @since 1.0.0
	 *
	 *--------------------------------------------------------------------------------------*/
	
	function pre_get_posts( $query )
	{
		$page_slug = sanitize_title_with_dashes(get_the_title($this->settings['apply_page']));
		
		if ( isset($query->query_vars[$this->slug]) && $query->query_vars[$this->slug] == $page_slug ) {
			$query->set('pagename', $page_slug);
			$query->is_page = 1;
			unset($query->query_vars['post_type'], $query->query_vars[$this->slug]);
		}
	}
	
	/*--------------------------------------------------------------------------------------
	 *
	 * Register the plugin's styles and scripts
	 *
	 * @author jcowher
	 * @since 1.0.0
	 *
	 *--------------------------------------------------------------------------------------*/
		
	function register_plugin_styles()
	{
		wp_enqueue_script('jquery');
		wp_enqueue_style('em-careers', $this->base_url . 'core/css/global.css', array(), $this->version, 'all');
	}
	
	/*--------------------------------------------------------------------------------------
	 *
	 * Register the career post type
	 *
	 * @author jcowher
	 * @since 1.0.0
	 *
	 *--------------------------------------------------------------------------------------*/
	
	function register_post_type()
	{
		register_post_type('career', array(
			'has_archive' => false,
			'hierarchical' => true,
			'menu_position' => 5,
			'labels' => array(
				'name' => 'Careers',
				'singular_name' => 'Career',
				'add_new' => 'Add new career',
				'add_new_item' => 'Add new career',
				'edit_item' => 'Edit career',
				'new_item' => 'New career',
				'all_items' => 'All careers',
				'view_item' => 'View career',
				'search_items' => 'Search careers',
				'not_found' => 'No careers found',
				'not_found_in_trash' => 'No careers found in trash',
				'parent_item_colon' => '',
			),
			'public' => true,
			'rewrite' => array(
				'slug' => trim(parse_url($this->get_url_structure(), PHP_URL_PATH), '/'),
				'with_front' => false,
			),
			'supports' => array('title'),
			'taxonomies' => array($this->tax_slug),
		));
	}
	
	/*--------------------------------------------------------------------------------------
	 *
	 * Register the location taxonomy
	 *
	 * @author jcowher
	 * @since 1.0.0
	 *
	 *--------------------------------------------------------------------------------------*/
	
	function register_taxonomy()
	{
		register_taxonomy($this->tax_slug, $this->slug, array(
			'hierarchical' => true,
			'labels' => array(
				'name' => 'Locations',
				'singular_name' => 'Location',
				'search_items' => 'Search locations',
				'all_items' => 'All locations',
				'parent_item' => 'Parent location',
				'parent_item_colon' => 'Parent location:',
				'edit_item' => 'Edit location',
				'update_item' => 'Update location',
				'add_new_item' => 'Add new location',
				'new_item_name' => 'New location name',
			),
		));
	}
	
	/*--------------------------------------------------------------------------------------
	 *
	 * Save the plugin's settings
	 *
	 * @author jcowher
	 * @since 1.0.0
	 *
	 *--------------------------------------------------------------------------------------*/
	
	function save_settings()
	{
		$settings = array();
		
		foreach ( $_POST as $key => $val ) {
			if ( substr($key, 0, 1) == '_' || $key == 'submit' )
				continue;
			
			if ( $key == 'summary_content' )
				$settings[$key] = wp_filter_post_kses(stripslashes($val));
			else
				$settings[$key] = stripslashes($val);
		}
		
		if ( empty($settings) )
			delete_option('em_careers_settings');
		else
			update_option('em_careers_settings', $settings);
			
		$this->settings = $settings;
		$this->settings_saved = true;
		
		flush_rewrite_rules();
	}
	
	/*--------------------------------------------------------------------------------------
	 *
	 * Global hooks
	 *
	 * @author jcowher
	 * @since 1.0.0
	 *
	 *--------------------------------------------------------------------------------------*/
	
	function setup()
	{
		$this->base_url = plugin_dir_url(__FILE__);
		$this->base_dir = plugin_dir_path(__FILE__);
		$this->settings = get_option('em_careers_settings');
		add_action('init', array($this, 'register_post_type'));
		add_action('init', array($this, 'register_taxonomy'));
	}
	
	/*--------------------------------------------------------------------------------------
	 *
	 * Ajax hooks - these only apply during ajax calls
	 *
	 * @author jcowher
	 * @since 1.0.0
	 *
	 *--------------------------------------------------------------------------------------*/
	
	function setup_ajax()
	{
		// Nothing to do here
	}
	
	/*--------------------------------------------------------------------------------------
	 *
	 * Admin hooks - these only apply when in the admin area
	 *
	 * @author jcowher
	 * @since 1.0.0
	 *
	 *--------------------------------------------------------------------------------------*/
	
	function setup_admin()
	{
		register_activation_hook(__FILE__, array($this, 'activate'));
		add_action('admin_menu', array($this, 'setup_menus'));
		
		if ( (isset($_GET['post_type']) && $_GET['post_type'] == $this->slug) || (isset($_GET['post']) && get_post_type($_GET['post']) == $this->slug) )
			add_action('admin_enqueue_scripts', array($this, 'register_plugin_styles'));
			
		if ( isset($_POST['_emnonce']) && wp_verify_nonce($_POST['_emnonce'], 'save_settings') )
			add_action('init', array($this, 'save_settings'));
	}
	
	/*--------------------------------------------------------------------------------------
	 *
	 * Frontend hooks - these only apply when on the frontend of the site
	 *
	 * @author jcowher
	 * @since 1.0.0
	 *
	 *--------------------------------------------------------------------------------------*/
	
	function setup_frontend()
	{
		add_filter('the_content', array($this, 'the_content'));
		add_action('pre_get_posts', array($this, 'pre_get_posts'));
	}
	
	/*--------------------------------------------------------------------------------------
	 *
	 * Setup admin menus
	 *
	 * @author jcowher
	 * @since 1.0.0
	 *
	 *--------------------------------------------------------------------------------------*/
	
	function setup_menus()
	{
		add_submenu_page(sprintf('edit.php?post_type=%s', $this->slug), 'Career Settings', 'Settings', 'publish_posts', 'settings', array($this, 'display_view')); 
	}
	
	/*--------------------------------------------------------------------------------------
	 *
	 * Filter the_content
	 *
	 * @param string $content
	 * @return string
	 * @author jcowher
	 * @since 1.0.0
	 *
	 *--------------------------------------------------------------------------------------*/
	
	function the_content( $content )
	{
		global $post;
		
		if ( $this->content_filtered )
			return $content;
		
		$this->content_filtered = true;
		
		if ( $post->ID == $this->settings['parent_page'] ) {
			$args = array(
				'post_type' => $this->slug,
				'posts_per_page' => -1,
				'meta_query' => array(
					array(
						'key' => 'career_start_date',
						'value' => date('Y-m-d'),
						'compare' => '<=',
						'type' => 'DATE',
					),
					array(
						'key' => 'career_end_date',
						'value' => date('Y-m-d'),
						'compare' => '>=',
						'type' => 'DATE',
					),
				),
			);
			
			if ( ! empty($_GET['location']) ) {
				$args['tax_query'] = array(
					array(
						'taxonomy' => $this->tax_slug,
						'field' => 'slug',
						'terms' => $_GET['location'],
					),
				);
			}
			
			$careers  = new WP_Query($args);
			
			
			$locations = get_terms($this->tax_slug);
			$content .= '<hr />';
			
			if ( ! empty($locations) ) {
				$content .= '<form id="career-location-filter">';
				$content .= '<strong>Filter by location:</strong> ';
				$content .= '<select name="location">';
				$content .= '<option value="">-Select One-</option>';
				
				foreach ( $locations as $location ) {
					$content .= '<option value="' . $location->slug . '"' . (isset($_GET['location']) && $_GET['location'] == $location->slug ? ' selected' : '') . '>' . $location->name . '</option>';
				}
				
				$content .= '</select>';
				$content .= '<input type="submit" value="Filter" />';
				$content .= '</form>';
			}
			
			$content .= '<div class="careers">';
			
			if ( ! $careers->have_posts() ) {
				$content .= '<p>No careers found.</p>';
			}
			
			while ( $careers->have_posts() ) {
				$careers->the_post();
				$content .= $this->parse_template('summary_template');
			}
			
			$content .= '</div>';
			
			wp_reset_postdata();
		} elseif ( is_single() && get_post_type() == $this->slug ) {
			$content = $this->parse_template('detail_template');
		}
		
		return $content;
	}
}