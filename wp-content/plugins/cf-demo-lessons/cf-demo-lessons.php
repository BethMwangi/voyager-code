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

if (!class_exists('LessonPlugin')) {
    class LessonPlugin
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('init', array($this, 'lessonRegisterPostType'));
            add_action('init', array($this, 'lessonRegisterCustomTaxonomy'));
        }

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

        public function lessonRegisterCustomTaxonomy()
        {
            $labels = array(
                'name' => __('Lesson Types', 'cf-demo-lessons'),
                'singular_name' => __('Lesson Type', 'cf-demo-lessons'),
                'search_items' => __('Search Lesson Types'),
                'all_items' => __('All Lesson Types'),
                'parent_item' => __('Parent Lesson Type'),
                'parent_item_colon' => __('Parent Lesson Type:'),
                'edit_item' => __('Edit Lesson Type'),
                'update_item' => __('Update Lesson Type'),
                'add_new_item' => __('Add New Lesson Type'),
                'new_item_name' => __('New Lesson Type Name'),
                'menu_name' => __('Lesson Types'),
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
    }
}

$lesson = new LessonPlugin();
