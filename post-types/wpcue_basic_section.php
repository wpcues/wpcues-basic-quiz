<?php
/**
* WpCueBasicSection Class
*/
 if(!class_exists('WpCueBasicSection'))
{
    class WpCueBasicSection
    {
		const POST_TYPE = "wpcuebasicsection";
        /**
		* The Constructor
		*/
		public function __construct()
		{
			// register actions
			add_action('init', array(&$this, 'init'));
		} // END public function __construct()
		/**
		* hook into WP's init action hook
		*/
		public function init()
		{
			// Add Ajax actions
			$this->create_post_type();
			add_filter('get_edit_post_link',array(&$this,'edit_section_link'),10, 3);
			add_action('admin_head',array(&$this,'reset_post_new_link'));
			
		} // END public function init()
		/**
		* Create the post type
		*/
		public function create_post_type()
		{
			$labels = array(
				'name'               => _x( 'Sections', 'post type general name', 'wpcues-quiz-pro' ),
				'singular_name'      => _x( 'Section', 'post type singular name', 'wpcues-quiz-pro' ),
				'menu_name'          => _x( 'Sections', 'admin menu', 'wpcues-quiz-pro' ),
				'name_admin_bar'     => _x( 'Section', 'add new on admin bar', 'wpcues-quiz-pro' ),
				'add_new'            => _x( 'Add New', 'section', 'wpcues-quiz-pro' ),
				'add_new_item'       => __( 'Add New Section', 'wpcues-quiz-pro' ),
				'new_item'           => __( 'New Section', 'wpcues-quiz-pro' ),
				'edit_item'          => __( 'Edit Section', 'wpcues-quiz-pro' ),
				'view_item'          => __( 'View Section', 'wpcues-quiz-pro' ),
				'all_items'          => __( 'All Sections', 'wpcues-quiz-pro' ),
				'search_items'       => __( 'Search Sections', 'wpcues-quiz-pro' ),
				'parent_item_colon'  => __( 'Parent Sections:', 'wpcues-quiz-pro' ),
				'not_found'          => __( 'No Sections found.', 'wpcues-quiz-pro' ),
				'not_found_in_trash' => __( 'No Sections found in Trash.', 'wpcues-quiz-pro' )
			);
		
			$args = array(
				'labels'             => $labels,
				'public'             => false,
				'publicly_queryable' => false,
				'capability_type'    => 'post',
				'show_ui'=>false,
				'has_archive'        => false,
				'hierarchical'       => false,
				'menu_position'      => null,
				'supports'           => array( 'editor', 'author','excerpt')
			);
			register_post_type(self::POST_TYPE,$args);
		
			
		}
		/**
		* create new Section
		*/
		public function set_section(){
			$post=get_default_post_to_edit(self::POST_TYPE,true);
			return $post;
		}
		public function reset_post_new_link(){
			global $post_new_file,$post_type_object;
			if (!isset($post_type_object) || 'wpcuebasicsection' != $post_type_object->name) return false;
			$post_new_file ='edit.php?post_type=wpcuebasicquiz&page=wpcuequiznewsection';
		}
		public function edit_section_link($url,$post_id, $context ){
			global $typenow;
			if($typenow=='wpcuebasicsection'){
				$action='&action=edit';
				$posting='&post='.$post_id;
				$url=admin_url(sprintf('edit.php?post_type=wpcuebasicquiz&page=wpcuequiznewsection'. $action.$posting));
			}
			return $url;
		}
    } // END class WpCueBasicSection
} // END if(!class_exists('WpCueBasicSection'))
/* EOF */