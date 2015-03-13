<?php
/**
*WpCueBasicCertificate class
*/
if(!class_exists('WpCueBasicCertificate'))
{
	class WpCueBasicCertificate{
        const POST_TYPE = "wpcuecertificate";
        public $usergroupid;
		/**
		* The Constructor
		*/
		public function __construct(){
			// register actions
			add_action('init', array(&$this, 'init'));	
		} // END public function __construct()
		/**
		* hook into WP's init action hook
		*/
		public function init(){
			// Initialize Post Type
			$this->create_post_type();
			add_filter('post_row_actions',array($this,'my_certi_list'),11,2);
			add_action('wp_ajax_wpcuequizaddcerti_action',array(&$this,'add_newcerti'));
			add_action('wp_ajax_wpcuequiztrashcerti_action',array(&$this,'trash_certi'));
			add_filter('get_edit_post_link',array(&$this,'edit_quiz_link'),10, 3);
			add_action('admin_head',array(&$this,'reset_post_new_link'));
		} // END public function init()

		/**
		* Create the post type
		*/
		public function create_post_type()
		{
			$labels = array(
				'name'               => _x( 'Certificates', 'post type general name', 'wpcues-quiz-pro' ),
				'singular_name'      => _x( 'Certificate', 'post type singular name', 'wpcues-quiz-pro' ),
				'menu_name'          => _x( 'Certificates', 'admin menu', 'wpcues-quiz-pro' ),
				'name_admin_bar'     => _x( 'Certificate', 'add new on admin bar', 'wpcues-quiz-pro' ),
				'add_new'            => _x( 'Add New', 'Certificate', 'wpcues-quiz-pro' ),
				'add_new_item'       => __( 'Add New Certificate', 'wpcues-quiz-pro' ),
				'new_item'           => __( 'New Certificate', 'wpcues-quiz-pro' ),
				'edit_item'          => __( 'Edit Certificate', 'wpcues-quiz-pro' ),
				'view_item'          => __( 'View Certificate', 'wpcues-quiz-pro' ),
				'all_items'          => __( 'All Certificates', 'wpcues-quiz-pro' ),
				'search_items'       => __( 'Search Certificates', 'wpcues-quiz-pro' ),
				'parent_item_colon'  => __( 'Parent Certificates:', 'wpcues-quiz-pro' ),
				'not_found'          => __( 'No Certificates found.', 'wpcues-quiz-pro' ),
				'not_found_in_trash' => __( 'No Certificates found in Trash.', 'wpcues-quiz-pro' )
			);
			$args = array(
				'labels'             => $labels,
				'public'             => true,
				'publicly_queryable' => true,
				'capability_type'    => 'post',
				'show_ui'=>false,
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => null,
				'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt'),
				'rewrite' => array('slug'=>'certificate')
		
			);
			register_post_type(self::POST_TYPE,$args);
		}
		/**
		* create new certificate
		*/
		public function set_certificate(){
			$post=get_default_post_to_edit(self::POST_TYPE,true);
			return $post;
		}
		public function reset_post_new_link(){
			global $post_new_file,$post_type_object;
			if (!isset($post_type_object) || 'wpcuecertificate' != $post_type_object->name) return false;
			$post_new_file ='edit.php?post_type=wpcuebasicquiz&page=wpcuequizcertificate';
		}
		public function edit_quiz_link($url,$post_id, $context ){
			global $typenow;
			if($typenow=='wpcuecertificate'){
				$action='&action=edit';
				$posting='&post='.$post_id;
				$url=admin_url(sprintf('edit.php?post_type=wpcuebasicquiz&page=wpcuequizcertificate'. $action.$posting));
			}
			return $url;
		}
		/**
		*Edit row actions for Certificate table
		*/
		public function my_certi_list($actions,$post){
			if($post->post_type=='wpcuecertificate' && 'trash' != $post->post_status ){
				$post_type_object = get_post_type_object( $post->post_type );
				$can_edit_post = current_user_can( 'edit_post', $post->ID );
				unset($actions['edit']);
				$action = '&action=edit';
				$postid='&post='.$post->ID;
				$action='<a href="'.admin_url(sprintf('edit.php?post_type=wpcuebasicquiz&page=wpcuequizcertificate'.$action.$postid)).'">Edit</a>';
				$actions['edit']=$action;
				unset($actions['inline hide-if-no-js']);
				$actions['inline hide-if-no-js'] = '<a href="#" class="editinline" title="' . esc_attr( __( 'Edit this item inline' ) ) . '">' . __( 'Quick&nbsp;Edit' ) . '</a>';
				unset($actions['trash']);
				$actions['trash'] = "<a class='submitdelete' title='" . esc_attr( __( 'Move this item to the Trash' ) ) . "' href='" . get_delete_post_link( $post->ID ) . "'>" . __( 'Trash' ) . "</a>";
				unset($actions['view']);
				
			}
			return $actions;
		}
		/**
		*Trash Certificate
		*/
		public function trash_certi(){
			
			$post_id=$_POST['postid'];
			$trashed = $locked = 0;
			if ( !current_user_can( 'delete_post', $post_id) )
					wp_die( __('You are not allowed to move this item to the Trash.') );

			if ( wp_check_post_lock( $post_id ) ) {
					$locked++;
					continue;
			}
			$post_ids=array();
			$post_ids[0]=$post_id;
			
			if ( !wp_trash_post($post_id) )
				wp_die( __('Error in moving to Trash.') );
				$trashed++;
				$sendback=admin_url('edit.php?post_type=wpcuecertificate');
				$sendback = add_query_arg( array('trashed' => $trashed, 'ids' => join(',', $post_ids), 'locked' => $locked ), $sendback );
				
				
				echo json_encode(array('msg'=>'success','redirecturl'=>$sendback));
				die();
			}
		
		
    } // END class WpCueBasicCertificate
} // END if(!class_exists('WpCueBasicCertificate'))
/* EOF */