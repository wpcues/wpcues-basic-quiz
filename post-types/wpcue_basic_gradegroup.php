<?php
/**
*WpCueBasicGradeGroup class
*/
if(!class_exists('WpCueBasicGradeGroup'))
{
    class WpCueBasicGradeGroup
    {
        const POST_TYPE = "wpcuebasicgradegroup";
		private $screenbase;
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
			add_action('wp_ajax_wpcuequizaddgradegroup_action',array(&$this,'add_gradegroup'));
			add_action('wp_ajax_wpcuequizaddgrade_action',array(&$this,'add_grade'));
			add_action('wp_ajax_wpcuequizeditgradegroup_action',array(&$this,'add_gradegroup'));
			add_action('wp_ajax_wpcuequizsavegradegroup_action',array(&$this,'save_gradegroup'));
			add_action('wp_ajax_wpcuequizremovegradegroup_action',array(&$this,'remove_gradegroup'));
			add_filter('tiny_mce_before_init', array(&$this,'wpcue_change_mce_options'));
			add_filter('mce_external_plugins', array(&$this,'wpcue_custom_plugins'));
			add_filter('mce_buttons', array(&$this,'wpcue_register_mathslate_button'));
			add_filter('get_edit_post_link',array(&$this,'edit_quiz_link'),10, 3);
			add_action('admin_head',array(&$this,'reset_post_new_link'));
		} // END public function init()

		/**
		* Create the post type
		*/
		public function create_post_type()
		{
			$labels = array(
				'name'               => _x( 'Grade Groups', 'post type general name','wpcues-basic-quiz' ),
				'singular_name'      => _x( 'Grade Group', 'post type singular name', 'wpcues-basic-quiz' ),
				'menu_name'          => _x( 'Grade Groups', 'admin menu', 'wpcues-basic-quiz' ),
				'name_admin_bar'     => _x( 'Grade', 'add new on admin bar', 'wpcues-basic-quiz' ),
				'add_new'            => _x( 'Add New', 'Grade', 'wpcues-basic-quiz' ),
				'add_new_item'       => __( 'Add New Grade Group', 'wpcues-basic-quiz' ),
				'new_item'           => __( 'New Grade Group', 'wpcues-basic-quiz' ),
				'edit_item'          => __( 'Edit Grade Group', 'wpcues-basic-quiz' ),
				'view_item'          => __( 'View Grade Group', 'wpcues-basic-quiz' ),
				'all_items'          => __( 'All Grade Group', 'wpcues-basic-quiz' ),
				'search_items'       => __( 'Search Grade Group', 'wpcues-basic-quiz' ),
				'parent_item_colon'  => __( 'Parent Grade Group:', 'wpcues-basic-quiz' ),
				'not_found'          => __( 'No Grade Group found.', 'wpcues-basic-quiz' ),
				'not_found_in_trash' => __( 'No Grade Group found in Trash.', 'wpcues-basic-quiz' )
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
				'supports'           => array( 'title', 'editor'),
				'rewrite' => array('slug'=>'grade')
				
			);
			register_post_type(self::POST_TYPE,$args);
		}
		public function wpcue_change_mce_options($initArray) {
			// Comma separated string od extendes tags
			// Command separated string of extended elements
			if((!empty($this->screenbase)) &&($this->screenbase=='quiz_page_wpcuequizaddnew')){
			//maybe; set tiny paramter verify_html
				$initArray['verify_html'] = false;
				$initArray['remove_redundant_brs'] = false;
				$initArray['remove_linebreaks'] = false;
				$initArray['force_p_newlines'] = false;
				$initArray['force_br_newlines'] = false;
			}
			return $initArray;
		}
		public function reset_post_new_link(){
			global $post_new_file,$post_type_object;
			if (!isset($post_type_object) || 'wpcuebasicgradegroup' != $post_type_object->name) return false;
			$post_new_file ='edit.php?post_type=wpcuebasicquiz&page=grade';
		}
		public function edit_quiz_link($url,$post_id, $context ){
			global $typenow;
			if($typenow=='wpcuebasicgradegroup'){
				$action='&action=edit';
				$posting='&post='.$post_id;
				$url=admin_url(sprintf('edit.php?post_type=wpcuebasicquiz&page=grade'. $action.$posting));
			}
			return $url;
		}
		/**
		* Add mathslate plugin to tinymce editors
		*/
		public function  wpcue_custom_plugins($plugins_array){
			if((!empty($this->screenbase)) &&($this->screenbase=='quiz_page_wpcuequizaddnew')){
			$plugins = array('mathslate'); //Add any more plugins you want to load here
			//Build the response - the key is the plugin name, value is the URL to the plugin JS
			foreach ($plugins as $plugin ) {
				$plugins_array[ $plugin ] = plugins_url('tinymce/', __FILE__) . $plugin . '/plugin.js';
			}
			}
			return $plugins_array;
		}
		public function wpcue_register_mathslate_button($buttons){
			if((!empty($this->screenbase)) &&($this->screenbase=='quiz_page_wpcuequizaddnew')){array_push($buttons, "mathslate");}
			return $buttons;
		}
		/**
		* create new Grade Group
		*/
		public function set_gradegroup(){
		$post=get_default_post_to_edit(self::POST_TYPE,true);
		return $post;
		}
		/**
		* form to add new grade group
		*/
		public function add_gradegroup(){
			$gradeid=0;$gradetitle='';$gradecontent='';$gradegrouptitle='';$gradebase=1;$gradebasefrom='';$gradebaseto='';$j=2;$grademeta=array();
			$gradecerti=0;
			if(!(empty($_POST['gradegroupid']))){
				$instance=get_post($_POST['gradegroupid']);
				$grademeta=unserialize($instance->post_content);
				$j=count($grademeta['gradeid']);
				$gradegrouptitle=$instance->post_title;
				$gradebase=$grademeta['gradebase'];
			}
			//$this->screenbase=$_POST['screenbase'];
			echo '<div id="gradetitlediv" style="margin:0em 0em;padding:0.25em 0em;">';
			echo '<div id="gradetitlewrap" style="width:60%;"><label class="screen-reader-text" id="title-prompt-text" for="title">';
			_e('Enter Grade Group Name here','wpcues-basic-quiz');echo '</label>';
			echo '<input type="text" name="gradegrouptitle" id="gradegrouptitle" size="30" autocomplete="off" value="'.$gradegrouptitle.'" placeholder="Enter Grade Group Name here" />';
			echo '</div><div style="margin:0.25em 1em;padding:0.25em 0.5em;">';
			_e('Based on','wpcues-basic-quiz');echo ' <select name="gradebasis" id="gradebasis">';
			echo '<option value="1" ';
			if($gradebase==1){echo 'selected';}
			echo '>';_e('Points','wpcues-basic-quiz');echo '</option><option value="2" ';
			if($gradebase==2){echo 'selected';}
			echo '>%';_e('Correct Answers','wpcues-basic-quiz');echo '</option></select>';
			echo '<div style="float:right" class="button add_grade_button">';_e('Add New Grade','wpcues-basic-quiz');echo '</div></div>';
			echo '</div>';
			echo '<div id="gradeeditortabs" class="innertabcontainer">';
			echo '<ul>';
			for($i=1;$i<=$j;$i++){
				echo '<li class="activetab"><a href="#gradeditortab-'.$i.'">'.$i.'</a></li>';
			}
			echo '</ul>';
			for($i=1;$i<=$j;$i++){
				$k=$i-1;
				if(!(empty($grademeta))){
					$gradeid=$grademeta['gradeid'][$k];$gradetitle=$grademeta[$gradeid]['title'];
					$gradecontent=$grademeta[$gradeid]['content'];
					$gradebaseto=$grademeta[$gradeid]['gradebaseto'];
					$gradebasefrom=$grademeta[$gradeid]['gradebasefrom'];
					$gradecerti=$grademeta[$gradeid]['certi'];
				}
				$this->grade_form($i,$gradeid,$gradetitle,$gradecontent,$gradebase,$gradebaseto,$gradebasefrom,$gradecerti);
			}
			echo '</div>';
			echo '<div class="gradegrouptools"><div class="button button-primary cancel_gradegroup_button" style="float:right;margin-right:1em;">';
			_e('Cancel Grade Group','wpcues-basic-quiz');
			echo '</div><div class="button button-primary save_gradegroup_button" style="float:right;">';
			_e('Save Grade Group','wpcues-basic-quiz');echo '</div></div>';
			//$this->screenbase='';
			die();
		}
		public function add_grade(){
			$index=$_POST['index'];$gradebase=$_POST['gradebase'];$gradebaseto='';$gradebasefrom='';$gradetitle='';$gradecontent='';$gradeid=0;
			$gradecerti=0;
			ob_start();
			$this->grade_form($index,$gradeid,$gradetitle,$gradecontent,$gradebase,$gradebaseto,$gradebasefrom,$gradecerti);
			
			echo ob_get_clean();
			die();
		}
		public function grade_form($i,$gradeid,$gradetitle,$gradecontent,$gradebase,$gradebaseto,$gradebasefrom,$gradecerti){
			echo '<div id="gradeditortab-'.$i.'" class="innertabcontent">';
			echo '<div class="gradeclosetools"><div class="button grade_close_button">X</div></div>';
			echo '<p>';_e('Grade Title','wpcues-basic-quiz');echo ' : <input type="text" name="gradetitle-'.$i.'" value="'.$gradetitle.'" id="gradetitle-'.$i.'" style="width:60%;line-height:20px;"/></p>';
			echo '<p>';_e('Grade Description','wpcues-basic-quiz');echo ' : </p>';
			wp_editor( $gradecontent,'grade-'.$i,array('textarea_rows'=>'50','default_editor'=>'tinymce','quicktags'=>true,'dfw'=>true,'editor_height'=>60,'tinymce'=>true,'media_buttons'=>true));
			if($gradebase==1){$gradebasis=__('Points','wpcues-basic-quiz');}elseif($gradebase==2){$gradebasis=__('%Correct Answers','wpcues-basic-quiz');}
			echo '<p><span class="gradebase">'.$gradebasis.'</span> from : <input type="text" name="gradebasefrom-'.$i.'" value="'.$gradebasefrom.'">'; 
			_e('to','wpcues-basic-quiz');echo ' : <input type="text" name="gradebaseto-'.$i.'" value="'.$gradebaseto.'"></p>';
			echo "<p>";_e('Assign Certificate','wpcues-basic-quiz');echo "  : <select name='gradecerti-".$i."'>";
			echo "<option value='0'>";_e('Select any certificate','wpcues-basic-quiz');echo "</option>";
			$certis=get_posts(array('post_type'=>'wpcuecertificate','post_status'=>'publish'));
			foreach($certis as $certi){ 
					echo '<option value="'.$certi->ID.'" ';
					if($certi->ID == $gradecerti){echo 'selected';}
					echo '>'.$certi->post_title.'</option>';
					}  
			echo '</select></p>';
			echo '<input type="hidden" name="gradeid-'.$i.'" value="'.$gradeid.'">';
			echo '<input type="hidden" name="gradeids[]" value="'.$i.'">';
			echo '</div>';
		}
		/**
		*Function to Save grade group
		*/
		public function save_gradegroup(){
			ob_start();
			if(get_magic_quotes_gpc() || function_exists('wp_magic_quotes')){
			$myformdata=stripslashes($_POST['myformdata']);
			}else{$myformdata=$_POST['myformdata'];}
			parse_str($myformdata,$output);
			$gradegroupid=$output['gradegroupid'];
			$inheritgradegroupid=$output['inheritgradegroupid'];
			$gradegrouptitle=$output['gradegrouptitle'];
			$gradeids=$output['gradeids'];
			if(!(empty($output['quizid']))){$quizid=$output['quizid'];$quizstatus=get_post_status($quizid);}
			$postcontent=array();
			$postcontent['gradebase']=$output['gradebasis'];
			$postcontent['defaultquiz']=0;
			foreach($gradeids as $id){
				if(empty($output['gradeid-'.$id])){$gradeid=substr(str_shuffle(MD5(microtime())), 0, 10);}else{$gradeid=$output['gradeid-'.$id];}
				$postcontent['gradeid'][]=$gradeid;
				$postcontent[$gradeid]['content']=wp_kses_post($output['grade-'.$id]);
				$postcontent[$gradeid]['title']=$output['gradetitle-'.$id];
				$postcontent[$gradeid]['gradebasefrom']=$output['gradebasefrom-'.$id];
				$postcontent[$gradeid]['gradebaseto']=$output['gradebaseto-'.$id];
				$postcontent[$gradeid]['certi']=$output['gradecerti-'.$id];
			}
			if($quizstatus=='publish'){
				$quizstatus=1;
				if(empty($gradegroupid)){
					$newgradegroupid=wp_insert_post(array('post_title'=>$gradegrouptitle,'post_type'=>self::POST_TYPE,'post_content'=>serialize($postcontent),'post_status'=>'auto-draft'));
				}else{
					if($gradegroupid != $inheritgradegroupid){
						if(empty($inheritgradegroupid)){
							$newgradegroupid=wp_insert_post(array('post_title'=>$gradegrouptitle,'post_type'=>self::POST_TYPE,'post_content'=>serialize($postcontent),'post_status'=>'inherit','post_parent'=>$gradegroupid));
						}else{
							$newgradegroupid=wp_update_post(array('ID'=>$inheritgradegroupid,'post_title'=>$gradegrouptitle,'post_type'=>self::POST_TYPE,'post_content'=>serialize($postcontent)));}
					}else{
						$newgradegroupid=wp_insert_post(array('post_title'=>$gradegrouptitle,'post_type'=>self::POST_TYPE,'post_content'=>serialize($postcontent),'post_status'=>'inherit','post_parent'=>$gradegroupid));
					}
				}
			}else{
				if($quizstatus=='auto-draft'){
					if(!(empty($_POST['quizname']))){$posttile=$_POST['quizname'];
					}else{$posttitle='Quiz '.$quizid.'-draft';}
					wp_update_post(array('ID'=>$quizid,'post_title'=>$posttitle,'post_status'=>'draft'));
				}
				$quizstatus=0;
				if(empty($gradegroupid)){
					$newgradegroupid=wp_insert_post(array('post_title'=>$gradegrouptitle,'post_type'=>self::POST_TYPE,'post_content'=>serialize($postcontent),'post_status'=>'publish'));
				}else{
					$newgradegroupid=wp_update_post(array('ID'=>$gradegroupid,'post_title'=>$gradegrouptitle,'post_type'=>self::POST_TYPE,'post_content'=>serialize($postcontent)));
				}
				if(!(empty($newgradegroupid))){
					$existinggradegroupid=get_post_meta($quizid,'quizgrade',true);
					if(!(empty($existinggradegroupid))){
						$existinggradegroupid=intval($existinggradegroupid);
						if($existinggradegroupid !=$newgradegroupid){
							wp_delete_post($existinggradegroupid);
							update_post_meta($quizid,'quizgrade',$newgradegroupid);
						}
					}else{add_post_meta($quizid,'quizgrade',$newgradegroupid);}
				}
			}
			if(!(empty($newgradegroupid))){echo json_encode(array('msg'=>'success','gradegroupid'=>$newgradegroupid,'gradegrouptitle'=>$gradegrouptitle,'quizstatus'=>$quizstatus));}else{echo json_encode(array('msg'=>'failure'));}
			echo ob_get_clean();
			die();
		}
		/**
		* Function to edit grade group
		*/
		public function edit_gradegroup(){
			$gradegroupid=$_POST['gradegroupid'];
			$gradegroup=get_post($gradegroupid);
			if(!(is_null($gradegroup))){$gradegroupcontent=unserialize($gradegroup->post_content);}
			if(isset($gradegroupcontent)){
				echo json_encode(array('msg'=>'success','gradegroup'=>$gradegroupcontent,'gradegrouptitle'=>$gradegroup->post_title));
			}else{echo json_encode(array('msg'=>'failure'));}
			die();
		}
		/**
		*Function to remove grade group
		*/
		public function remove_gradegroup(){
			$quizid=$_POST['quizid'];
			$quizstatus=get_post_status($quizid);
			if($quizstatus != 'publish'){
				$gradegroupid=$_POST['gradegroupid'];
				if(wp_delete_post($gradegroupid,true)){$error=0;}else{$error=1;}
				if($error==0){
					delete_post_meta($quizid,'quizgrade');
					echo json_encode(array('msg'=>'success','quizstatus'=>0));
				}else{echo json_encode(array('msg'=>'failure'));}	
			}else{echo json_encode(array('msg'=>'success','quizstatus'=>1));}
			die();
		}
    } // END class WpCueBasicGradeGroup
} // END if(!class_exists('WpCueBasicGradeGroup'))
/* EOF */