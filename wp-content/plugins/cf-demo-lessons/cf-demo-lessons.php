<?php

/**
 * Plugin Name: CF Lesson Custom Post Types
 * Description: This plugin registers the lessons custom post types and taxonomies.
 * Plugin URI: http://voyager-code.test
 * Author: Beth Mwangi
 * Author URI: http://www.beth.com
 * Version: 1.0
 * License: GPL2
 * Text Domain: cf-demmo-lessons
 *
 * @package cf-cpts
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
                'name' => __('Lessons', 'cf-demmo-lessons'),
                'singular_name' => __('Lesson', 'cf-demmo-lessons'),
                'menu_name' => _x('Lessons', 'cf-demmo-lessons'),
                'add_new' => __('New Lesson', 'cf-demmo-lessons'),
                'add_new_item' => __('Add New Lesson', 'cf-demmo-lessons'),
                'edit_item' => __('Edit Lesson', 'cf-demmo-lessons'),
                'new_item' => __('New Lesson', 'cf-demmo-lessons'),
                'view_item' => __('View Lessons', 'cf-demmo-lessons'),
                'search_items' => __('Search Lessons', 'cf-demmo-lessons'),
                'not_found' => __('No Lessons Found', 'cf-demmo-lessons'),
                'not_found_in_trash' => __('No Lessons found in Trash', 'cf-demmo-lessons'),
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
                'taxonomies' => array('post_tag'),
                'rewrite' => array('slug' => 'lesson'),
                'show_in_rest' => true
            );

            register_post_type('cpt_lessons', $args);
        }

        public function lessonRegisterCustomTaxonomy()
        {
            $labels = array(
                'name' => __('Categories', 'taxonomy name'),
                'singular_name' => __('Category', 'taxonomy singular name'),
                'search_items' => __('Search Categories'),
                'all_items' => __('All Categories'),
                'parent_item' => __('Parent Category'),
                'parent_item_colon' => __('Parent Category:'),
                'edit_item' => __('Edit Category'),
                'update_item' => __('Update Category'),
                'add_new_item' => __('Add New Category'),
                'new_item_name' => __('New Category Name'),
                'menu_name' => __('Categories'),
            );

            register_taxonomy(
                'categories',
                array('lessons'),
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
