<?php
/**
* WpCueBasicError Class
*/
 if(!class_exists('WpCueBasicError'))
{
    class WpCueBasicError
    {
		const POST_TYPE = "wpcuebasicerror";
        /**
		* The Constructor
		*/
		public function __construct()
		{
			// register actions
			add_action('init', array(&$this, 'init'));
			add_filter('get_edit_post_link',array(&$this,'edit_error_link'),10, 3);
			add_action('admin_head',array(&$this,'reset_post_new_link'));
			add_action( 'delete_post', array(&$this,'delete_error'),10,1);
			add_action('wp_ajax_wpcuequiztrasherror_action',array(&$this,'trash_error'));
			add_action('wp_ajax_wpcuequizquestiondropdown_action',array(&$this,'questions_dropdown'));
		} // END public function __construct()
		/**
		* hook into WP's init action hook
		*/
		public function init()
		{
			// Add Ajax actions
			$this->create_post_type();
		} // END public function init()
		/**
		* Create the post type
		*/
		public function create_post_type()
		{
			$labels = array(
				'name'               => _x( 'Errors', 'post type general name', 'your-plugin-textdomain' ),
				'singular_name'      => _x( 'Error', 'post type singular name', 'your-plugin-textdomain' ),
				'menu_name'          => _x( 'Errors', 'admin menu', 'your-plugin-textdomain' ),
				'name_admin_bar'     => _x( 'Error', 'add new on admin bar', 'your-plugin-textdomain' ),
				'add_new'            => _x( 'Add New', 'error', 'your-plugin-textdomain' ),
				'add_new_item'       => __( 'Add New Error', 'your-plugin-textdomain' ),
				'new_item'           => __( 'New Error', 'your-plugin-textdomain' ),
				'edit_item'          => __( 'Edit Error', 'your-plugin-textdomain' ),
				'view_item'          => __( 'View Error', 'your-plugin-textdomain' ),
				'all_items'          => __( 'All Errors', 'your-plugin-textdomain' ),
				'search_items'       => __( 'Search Errors', 'your-plugin-textdomain' ),
				'parent_item_colon'  => __( 'Parent Errors:', 'your-plugin-textdomain' ),
				'not_found'          => __( 'No Errors found.', 'your-plugin-textdomain' ),
				'not_found_in_trash' => __( 'No Errors found in Trash.', 'your-plugin-textdomain' )
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
				'supports'           => array('editor', 'author','excerpt')
			);
			register_post_type(self::POST_TYPE,$args);
		
			
		}
		public function set_error(){
			$post=get_default_post_to_edit(self::POST_TYPE,true);
			return $post;
		}
		/**
		*Trash Certificate
		*/
		public function trash_error(){
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
				$sendback=admin_url('edit.php?post_type=wpcuebasicerror');
				$sendback = add_query_arg( array('trashed' => $trashed, 'ids' => join(',', $post_ids), 'locked' => $locked ), $sendback );
				echo json_encode(array('msg'=>'success','redirecturl'=>$sendback));
				die();
		}
		public function reset_post_new_link(){
			global $post_new_file,$post_type_object;
			if (!isset($post_type_object) || 'wpcuebasicerror' != $post_type_object->name) return false;
			$post_new_file ='edit.php?post_type=wpcuebasicquiz&page=wpcuequizerror';
		}
		public function edit_error_link($url,$post_id, $context ){
			global $typenow;
			if($typenow=='wpcuebasicerror'){
				$action='&action=edit';
				$posting='&post='.$post_id;
				$url=admin_url(sprintf('edit.php?post_type=wpccuebasicquiz&page=wpcuequizerror'. $action.$posting));
			}
			return $url;
		}
		public function delete_error($post_id){
			global $post_type;  
			if($post_type != 'wpcuebasicerror'){return;}
			global $wpdb;
			$wpdb->delete($wpdb->prefix.'quizerrorinfo',array('errorid'=>$post_id),array('%d'));
		}
		public function questions_dropdown(){
			$quizid=$_POST['quizid'];
			ob_start();
			global $wpdb;$table_name=$wpdb->prefix.'wpcuequiz_quizinfo';
			$table_name1=$wpdb->prefix.'wpcuequiz_quizerrorinfo';
			$questionids=$wpdb->get_col($wpdb->prepare("select entityid from $table_name where parentid != -1 and quizid=%d",$quizid));
			$i=1;
			$content='<select name="entityid">';
			foreach($questionids as $questionid){
				$content.= '<option value="'.$questionid.'">Q. '.$i.'</option>';
				$i++;
			}
			$content.= '</select>';
			echo json_encode(array('msg'=>'success','content'=>$content));
			echo ob_get_clean();
			die();
		}
		
    } // END class WpCueBasicError
} // END if(!class_exists('WpCueBasicError'))
/* EOF */