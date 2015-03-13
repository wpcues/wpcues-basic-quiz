<?php
/**
*WpCueBasicLevel class
*/
if(!class_exists('WpCueBasicLevel'))
{
    class WpCueBasicLevel
    {
        const POST_TYPE = "wpcuebasiclevel";
        
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
			// Initialize Post Type
			$this->create_post_type();
			add_filter('manage_wpcuebasiclevel_posts_columns',array(&$this,'new_level_columns'));
			add_action('manage_wpcuebasiclevel_posts_custom_column',array(&$this,'cusotm_level_columns'),10,2);
			add_filter('post_row_actions',array($this,'my_level_list'),11,2);
			add_action('wp_ajax_addlevel_action',array(&$this,'add_newlevel'));
			add_action('wp_ajax_trashlevel_action',array(&$this,'trash_level'));
			add_filter('get_edit_post_link',array(&$this,'edit_level_link'),10, 3);
			add_action('admin_head',array(&$this,'reset_post_new_link'));
		} // END public function init()

		/**
		* Create the post type
		*/
		public function create_post_type()
		{
			$labels = array(
				'name'               => _x( 'Levels', 'post type general name', 'wpcues-basic-quiz' ),
				'singular_name'      => _x( 'Level', 'post type singular name', 'wpcues-basic-quiz' ),
				'menu_name'          => _x( 'Levels', 'admin menu', 'wpcues-basic-quiz' ),
				'name_admin_bar'     => _x( 'Level', 'add new on admin bar', 'wpcues-basic-quiz' ),
				'add_new'            => _x( 'Add New', 'Level', 'wpcues-basic-quiz' ),
				'add_new_item'       => __( 'Add New Level', 'wpcues-basic-quiz' ),
				'new_item'           => __( 'New Level', 'wpcues-basic-quiz' ),
				'edit_item'          => __( 'Edit Level', 'wpcues-basic-quiz' ),
				'view_item'          => __( 'View Level', 'wpcues-basic-quiz' ),
				'all_items'          => __( 'All Levels', 'wpcues-basic-quiz' ),
				'search_items'       => __( 'Search Levels', 'wpcues-basic-quiz' ),
				'parent_item_colon'  => __( 'Parent Levels:', 'wpcues-basic-quiz' ),
				'not_found'          => __( 'No Levels found.', 'wpcues-basic-quiz' ),
				'not_found_in_trash' => __( 'No Levels found in Trash.', 'wpcues-basic-quiz' )
			);
			$args = array(
				'labels'             => $labels,
				'public'             => false,
				'publicly_queryable' => false,
				'capability_type'    => 'post',
				'show_ui'=>false,
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => null,
				'supports'           => array( 'title', 'editor'),
				'rewrite' => array('slug'=>'level')
		
			);
			register_post_type(self::POST_TYPE,$args);
		}
		/**
		*Special Scripts for Level table page
		*/
		public static function wpcue_prolevel_admin_scripts(){
			wp_register_script( 'wpcuebasicquiz-level', plugins_url( '../js/wpcuebasicquiz-level.js', __FILE__ ),array('jquery') );
			wp_enqueue_script('wpcuebasicquiz-level');
			wp_register_style( 'wpcuebasicquiz-createquiz', plugins_url('../css/wpcuebasicquiz-createquiz.css',__FILE__));
			wp_enqueue_style('wpcuebasicquiz-createquiz');
		}
		/**
		* create new level
		*/
		public function set_level(){
			$post=get_default_post_to_edit(self::POST_TYPE,true);
			return $post;
		}
		/**
		* Ajax function to retrieve Add new link
		*/
		public function add_newlevel(){echo json_encode(array('msg'=>admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequizlevel')));die();}
		/**
		*Edit row actions for Level table
		*/
		public function my_level_list($actions,$post){
			if($post->post_type=='wpcuebasiclevel' && 'trash' != $post->post_status ){
				$post_type_object = get_post_type_object( $post->post_type );
				$can_edit_post = current_user_can( 'edit_post', $post->ID );
				unset($actions['edit']);
				$action = '&action=edit';
				$postadded='&post='.$post->ID;
				$action='<a href="'.admin_url(sprintf('edit.php?post_type=wpcuebasicquiz&page=wpcuequizlevel'.$action.$postadded)).'">Edit</a>';
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
		*Trash Level
		*/
		public function trash_level(){
			
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
				$sendback=admin_url('edit.php?post_type=wpcuebasiclevel');
				$sendback = add_query_arg( array('trashed' => $trashed, 'ids' => join(',', $post_ids), 'locked' => $locked ), $sendback );
				
				
				echo json_encode(array('msg'=>'success','redirecturl'=>$sendback));
				die();
			}
		/**
		*Add New columns for quiz table
		*/
		public function new_level_columns($columns){
			$columns['rank']=__('Rank','trws');
			$columns['raqpoint']=__('Points','trws');
			$columns['corranswer']=__('%Correct Answer','trws');
			$columns['quiznum']=__('Quizzes','trws');
			$columns['quizcat']=__('Quiz Categories','trws');
			unset($columns['date']);
			return $columns;
		}
		/**
		*New custom column handles
		*/
		public function cusotm_level_columns($column,$post_id){
		$levelmeta=get_post_custom($post_id);
		switch($column){
		case 'rank':
				if(!(empty($levelmeta['wpcuelevelrank']))){echo $levelmeta['wpcuelevelrank'][0];}else{echo 0;}
				break;
		case 'raqpoint':
				if(!(empty($levelmeta['wpcuelevelpoints']))){echo $levelmeta['wpcuelevelpoints'][0];}else{echo 0;}
				break;
		case 'corranswer':
				if(!(empty($levelmeta['wpcuelevelpercorrect']))){echo $levelmeta['wpcuelevelpercorrect'][0];}else{echo 0;}
				break;
		case 'quiznum' :
				if(!(empty($levelmeta['wpcuelevelquiznum']))){echo $levelmeta['wpcuelevelquiznum'][0];}else{echo 0;}
				break;
		case 'quizcat' :
			if(!(empty($levelmeta['wpcuelevelquizcat'][0]))){$catids=maybe_unserialize($levelmeta['wpcuelevelquizcat'][0]);
			
			$content='';
			$count=count($catids);
			$i=1;
			foreach($catids as $catid){
				$cat=get_term_by('id',$catid,'wpcuebasicquizcat');
				$content.=$cat->name;
				if($i != $count){$content.=', ';}
				$i++;
				}
			}else{$content='-';}
				echo $content;
				break;
				}
		}
		public function reset_post_new_link(){
			global $post_new_file,$post_type_object;
			if (!isset($post_type_object) || 'wpcuebasiclevel' != $post_type_object->name) return false;
			$post_new_file ='edit.php?post_type=wpcuebasicquiz&page=wpcuequizlevel';
		}
		public function edit_level_link($url,$post_id, $context ){
			global $typenow;
			if($typenow=='wpcuebasiclevel'){
				$action='&action=edit';
				$posting='&post='.$post_id;
				$url=admin_url(sprintf('edit.php?post_type=wpcuebasicquiz&page=wpcuequizlevel'. $action.$posting));
			}
			return $url;
		}
    } // END class WpCueBasicLevel
} // END if(!class_exists('WpCueBasicLevel'))
/* EOF */