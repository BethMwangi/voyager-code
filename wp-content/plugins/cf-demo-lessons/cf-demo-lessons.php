<?php

/**
 * Plugin Name: CF Demo Lessons
 * Description: This plugin registers the lessons custom post types and taxonomies.
 * Plugin URI: http://voyager-code.test
 * Author: Beth Mwangi
 * Author URI: http://www.beth.com
 * Version: 1.0
 * License: GPL2
 * Text Domain: cf-demo-lessons
 *
 * @package cf-demo-lessons
 */

namespace CrowdFavorite\DemoLessons;

if (!class_exists('LessonPlugin')) {
	/**
	 * LessonPlugin
	 */
	class LessonPlugin
	{
		/**
		 * Constructor
		 */
		public function __construct()
		{
			add_action('init', array($this, 'lessonRegisterPostType'));
			add_action('init', array($this, 'lessonRegisterCustomTaxonomy'));
			add_action('cmb2_admin_init', array($this, 'registerLessonMetabox'));
		}

		/**
		 * Register CF lesson CPT
		 *
		 * @return void
		 */
		public function lessonRegisterPostType()
		{
			$labels = array(
				'name' => __('Lessons', 'cf-demo-lessons'),
				'singular_name' => __('Lesson', 'cf-demo-lessons'),
				'menu_name' => _x('Lessons', 'cf-demo-lessons'),
				'add_new' => __('New Lesson', 'cf-demo-lessons'),
				'add_new_item' => __('Add New Lesson', 'cf-demo-lessons'),
				'edit_item' => __('Edit Lesson', 'cf-demo-lessons'),
				'new_item' => __('New Lesson', 'cf-demo-lessons'),
				'view_item' => __('View Lessons', 'cf-demo-lessons'),
				'search_items' => __('Search Lessons', 'cf-demo-lessons'),
				'not_found' => __('No Lessons Found', 'cf-demo-lessons'),
				'not_found_in_trash' => __('No Lessons found in Trash', 'cf-demo-lessons'),
			);
			$args = array(
				'labels' => $labels,
				'has_archive' => true,
				'public' => true,
				'hierarchical' => false,
				'supports' => array(
					'title',
					'editor',
					'author',
					'excerpt',
					'custom-fields',
					'thumbnail',
					'post-formats',
					'page-attributes'
				),
				'taxonomies' => array('lesson_types'),
				'rewrite' => array('slug' => 'lesson'),
				'show_in_rest' => true
			);

			register_post_type('cpt_lessons', $args);
		}

		/**
		 * Register the CF lesson types taxonomy
		 *
		 * @return void
		 */
		public function lessonRegisterCustomTaxonomy()
		{
			$labels = array(
				'name' => __('Lesson Types', 'cf-demo-lessons'),
				'singular_name' => __('Lesson Type', 'cf-demo-lessons'),
				'search_items' => __('Search Lesson Types', 'cf-demo-lessons'),
				'all_items' => __('All Lesson Types', 'cf-demo-lessons'),
				'parent_item' => __('Parent Lesson Type', 'cf-demo-lessons'),
				'parent_item_colon' => __('Parent Lesson Type:', 'cf-demo-lessons'),
				'edit_item' => __('Edit Lesson Type', 'cf-demo-lessons'),
				'update_item' => __('Update Lesson Type', 'cf-demo-lessons'),
				'add_new_item' => __('Add New Lesson Type', 'cf-demo-lessons'),
				'new_item_name' => __('New Lesson Type Name', 'cf-demo-lessons'),
				'menu_name' => __('Lesson Types', 'cf-demo-lessons'),
			);

			register_taxonomy(
				'lesson_types',
				array('cpt_lessons'),
				array(
					'hierarchical' => true,
					'labels' => $labels,
					'show_ui' => true,
					'show_admin_column' => true,
					'query_var' => true,
					'rewrite' => array('slug' => 'type'),
				)
			);
		}

		/**
		 * register the lesson metabox with promoted and order field
		 *
		 * @return void
		 */
		public function registerLessonMetabox()
		{
			/**
			 * Initiate the metabox
			 */
			$cmb = new_cmb2_box(
				array(
					'id' => 'lesson_metabox',
					'title' => __('Lesson', 'cf-demo-lessons'),
					'object_types' => array('cpt_lessons'),
					'context' => 'normal',
					'priority' => 'high',
					'show_names' => true,
				)
			);

			$cmb->add_field(
				array(
					'name' => esc_html__('Promoted', 'cf-demo-lessons'),
					'desc' => esc_html__('', 'cf-demo-lessons'),
					'id' => 'promoted_checkbox',
					'type' => 'checkbox',
				)
			);

			$cmb->add_field(
				array(
					'name' => __('Order', 'cf-demo-lessons'),
					'desc' => __('This is used to order query results', 'cf-demo-lessons'),
					'id' => 'lesson_order',
					'type' => 'text',
					'attributes' => array(
						'type' => 'number',
					),
				)
			);
		}
	}
}

$lesson = new LessonPlugin();
