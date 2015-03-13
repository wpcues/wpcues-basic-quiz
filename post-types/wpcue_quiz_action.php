<?php
/**
* Quiz list class to make changes on All Quizzes page
*/
 if(!class_exists('WpCueQuizAction'))
{
    class WpCueQuizAction
    {
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
		//New columns on Quiz table page
			add_filter('manage_wpcuebasicquiz_posts_columns',array(&$this,'new_quiz_columns'));
			add_action('manage_wpcuebasicquiz_posts_custom_column',array(&$this,'cusotm_quiz_columns'),10,2);
			//edit actions being displayed on each rows of Quiz table
			add_filter('post_row_actions',array($this,'my_quiz_list'),10,2);
			add_filter('manage_edit-wpcuebasicquizcat_columns',array(&$this,'new_wpcuebasicquizcat_columns'));
			add_filter ('manage_wpcuebasicquizcat_custom_column',array(&$this,'cusotm_wpcuebasicquizcat_columns'),10,3);
			add_action( 'restrict_manage_posts',array(&$this,'wpprocue_add_wpcuebasicquizcat_filters'));
			add_filter('parse_query',array(&$this,'filterby_wpcuebasicquizcat'));
			add_filter('get_edit_post_link',array(&$this,'edit_quiz_link'),10, 3);
			add_action('admin_head',array(&$this,'reset_post_new_link'));
		}
	
	
	/**
		*Add New columns for quiz table
		*/
		public function new_quiz_columns($columns){
			$columns['questnum']=__('Number of Questions','wpcues-basic-quiz');
			$columns['quizshortcode']=__('Quiz Shortcode','wpcues-basic-quiz');
			return $columns;
		}
		/**
		*New custom column handles
		*/
		public function cusotm_quiz_columns($column,$post_id){
			if($column=='questnum'){
				global $wpdb;
				$quizinfotable=$wpdb->prefix.'wpcuequiz_quizinfo';
				$totalquestion=$wpdb->get_var($wpdb->prepare("select count(id) as totalquestions from $quizinfotable where quizid=%d",$post_id));
				if(empty($totalquestion)){$totalquestion=0;}
				echo $totalquestion;
			}elseif($column=='quizshortcode'){
				echo '[wpcuebasicquiz '.$post_id.']';
			}
		}
		/**
		*Edit row actions for Quiz table
		*/
		public function my_quiz_list($actions,$post){
			if($post->post_type=='wpcuebasicquiz' && 'trash' != $post->post_status ){
				$post_type_object = get_post_type_object( $post->post_type );
				$can_edit_post = current_user_can( 'edit_post', $post->ID );
				unset($actions['edit']);
				$action = '&action=edit';
				$posting='&post='.$post->ID;
				$action='<a href="'.admin_url(sprintf('edit.php?post_type=wpcuebasicquiz&page=wpcuequizaddnew'. $action.$posting)).'">Edit</a>';
				$actions['edit']=$action;
				unset($actions['inline hide-if-no-js']);
				$actions['inline hide-if-no-js'] = '<a href="#" class="editinline" title="' . esc_attr( __( 'Edit this item inline' ) ) . '">' . __( 'Quick&nbsp;Edit' ) . '</a>';
				unset($actions['trash']);
				$actions['trash'] = "<a class='submitdelete' title='" . esc_attr( __( 'Move this item to the Trash' ) ) . "' href='" . get_delete_post_link( $post->ID ) . "'>" . __( 'Trash' ) . "</a>";
				unset($actions['view']);
				if ( $post_type_object->public ) {
					if (!(in_array( $post->post_status, array( 'pending', 'draft', 'future' ) ) )) {
						$action= '<a href="' . get_permalink( $post->ID ) . '" title="' . esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), $post->post_title ) ) . '" rel="permalink">' . __( 'View' ) . '</a>';
					$actions['view']=$action;
					}
					
				}
				
			}
			return $actions;
		}
		
		public function reset_post_new_link(){
			  global $post_new_file,$post_type_object;
			    if (!isset($post_type_object) || 'wpcuebasicquiz' != $post_type_object->name) return false;
				$post_new_file ='edit.php?post_type=wpcuebasicquiz&page=wpcuequizaddnew';
		}
		public function new_wpcuebasicquizcat_columns($columns){
			$columns['quizcatshortcode']=__('Shortcode','wpcues-basic-quiz');
			return $columns;
		}
		public function cusotm_wpcuebasicquizcat_columns($deprecated,$column_name,$term_id){
			if($column_name=='quizcatshortcode'){
				echo '[wpcuebasicquizcat '.$term_id.']';
			}
		}
		public function wpprocue_add_wpcuebasicquizcat_filters(){
			global $typenow ;
			$taxonomy='wpcuebasicquizcat';
			if($typenow=='wpcuebasicquiz'){
				$selected = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
				wp_dropdown_categories(array('show_option_all'=>'View all categories','name' => 'wpcuebasicquizcat','taxonomy'=>'wpcuebasicquizcat','orderby' => 'name','selected' => $selected,'hide_empty'=>0,'hide_if_empty'=> false));
			}
		}
		public function filterby_wpcuebasicquizcat($query){
			global $pagenow;
			$post_type = 'wpcuebasicquiz'; // change HERE
			$taxonomy = 'wpcuebasicquizcat'; // change HERE
			$q_vars = &$query->query_vars;
			if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0) {
				$term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
				$q_vars[$taxonomy] = $term->slug;
			}
		}
		public function edit_quiz_link($url,$post_id, $context ){
			global $typenow;
			if($typenow=='wpcuebasicquiz'){
				$action='&action=edit';
				$posting='&post='.$post_id;
				$url=admin_url(sprintf('edit.php?post_type=wpcuebasicquiz&page=wpcuequizaddnew'. $action.$posting));
			}
			return $url;
		}
	
	 } // END class AnviQuizAction
} // END if(!class_exists('AnviQuizAction'))
/* EOF */