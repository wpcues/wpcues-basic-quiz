<?php
/**
 * WpCueBasicQuiz class
*/
	 if(!class_exists('WpCueBasicQuiz'))
{
    class WpCueBasicQuiz
    {
        const POST_TYPE = "wpcuebasicquiz";
        public $quizid;
		public $wpprocuesetting;private $socialsharetext;public $certipage;
		/**
		* The Constructor
		*/
		public function __construct()
		{
			// register actions
			add_action('init', array(&$this, 'init'));
			$this->certipage=get_option('wpcue_certi_page');
			$this->wpprocuesetting=get_option('wpcuequiz_setting');
		} // END public function __construct()
		/**
		* hook into WP's init action hook
		*/
		public function init()
		{
			// Initialize Post Type
			$this->create_post_type();
			add_action('before_delete_post',array(&$this,'delete_quiz'));
			add_action('wp_ajax_wpcuequizsavequiz_action',array(&$this,'save_quiz'));
			add_action('wp_ajax_wpcuequizgetquizresult_action',array(&$this,'wpcue_proquiz_final_result'));
			add_action('wp_ajax_nopriv_wpcuequizgetquizresult_action',array(&$this,'wpcue_proquiz_final_result'));
			add_action('wp_ajax_wpcuequizstartquiz_action',array(&$this,'wpcue_proquiz_startquiz'));
			add_action('wp_ajax_nopriv_wpcuequizstartquiz_action',array(&$this,'wpcue_proquiz_startquiz'));
			add_shortcode('wpcuebasicquiz',array(&$this,'quiz_shortcode'));
			add_filter('the_content', array(&$this,'formatcontent_quiz'));
			add_action('wp_enqueue_scripts', array(&$this,'wpcuebasicquiz_scripts' ));
			add_action('wp_ajax_wpcuequizadddepgrade_action',array(&$this,'addgrade_group'));
			add_action('wp_ajax_wpcuequizremdepgrade_action',array(&$this,'remgrade_group'));
			add_action('wp_ajax_wpcuequizsavequizcategory_action',array(&$this,'save_quizcategory'));
			add_filter('json_prepare_post',array(&$this,'json_formatcontent_quiz'),10,3);
		} // END public function init()

		/**
		* Create the post type
		*/
		private function create_post_type()
		{
			$labels = array(
				'name'               => _x( 'Quizzes', 'post type general name', 'wpcues-basic-quiz' ),
				'singular_name'      => _x( 'Quiz', 'post type singular name', 'wpcues-basic-quiz' ),
				'menu_name'          => _x( 'Quizzes', 'admin menu', 'wpcues-basic-quiz' ),
				'name_admin_bar'     => _x( 'Quiz', 'add new on admin bar', 'wpcues-basic-quiz' ),
				'add_new'            => _x( 'Add New', 'quiz', 'wpcues-basic-quiz' ),
				'add_new_item'       => __( 'Add New Quiz', 'wpcues-basic-quiz' ),
				'new_item'           => __( 'New Quiz', 'wpcues-basic-quiz' ),
				'edit_item'          => __( 'Edit Quiz', 'wpcues-basic-quiz' ),
				'view_item'          => __( 'View Quiz', 'wpcues-basic-quiz' ),
				'all_items'          => __( 'All Quizzes', 'wpcues-basic-quiz' ),
				'search_items'       => __( 'Search Quizzes', 'wpcues-basic-quiz' ),
				'parent_item_colon'  => __( 'Parent Quizzes:', 'wpcues-basic-quiz' ),
				'not_found'          => __( 'No Quizzes found.', 'wpcues-basic-quiz' ),
				'not_found_in_trash' => __( 'No Quizzes found in Trash.', 'wpcues-basic-quiz' ),
		
			);
			$args = array(
				'labels'             => $labels,
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => false,
				'capability_type'    => 'post',
				'taxonomies'=>array('wpcuebasicquizcat'),
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => null,
				'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt'),
				'rewrite' => array('slug'=>'quiz')
		
			);
			register_post_type(self::POST_TYPE,$args);
			$labels = array(
				'name'              => _x( 'Quiz Categories', 'taxonomy general name','wpcues-basic-quiz' ),
				'singular_name'     => _x( 'Quiz Category', 'taxonomy singular name','wpcues-basic-quiz'  ),
				'search_items'      => __( 'Search Quiz Categories','wpcues-basic-quiz'  ),
				'all_items'         => __( 'All Quiz Categories','wpcues-basic-quiz'  ),
				'parent_item'       => __( 'Parent Quiz Category','wpcues-basic-quiz'  ),
				'parent_item_colon' => __( 'Parent Quiz Category:','wpcues-basic-quiz'  ),
				'edit_item'         => __( 'Edit Quiz Category','wpcues-basic-quiz'  ),
				'update_item'       => __( 'Update Quiz Category','wpcues-basic-quiz'  ),
				'add_new_item'      => __( 'Add New Quiz Category','wpcues-basic-quiz'  ),
				'new_item_name'     => __( 'New Quiz Category Name','wpcues-basic-quiz'  ),
				'menu_name'         => __( 'Quiz Category','wpcues-basic-quiz' ),
			);
			$arges = array(
				'hierarchical'      => true,
				'labels'            => $labels,
				'public'=>true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => false
			);
			register_taxonomy('wpcuebasicquizcat','wpcuebasicquiz',$arges);
			$uncategorized=__('Uncategorized','wpcues-basic-quiz');
			wp_insert_term($uncategorized,'wpcuebasicquizcat');
		}
		/**
		* Set quiz id
		*/
		public function set_quizid(){
			$post=get_default_post_to_edit(self::POST_TYPE,true);
			$this->quizid=$post->ID;
		}
		
		public function update_quizmeta($quizid,$metakey,$metavalue=false){
			if(!(empty($metavalue))){update_post_meta($quizid,$metakey,$metavalue);}else{delete_post_meta($quizid,$metakey);}
		}
		/**
		*Save Quiz
		*/
		public function save_quiz(){
			global $wpdb;
			if(get_magic_quotes_gpc() || function_exists('wp_magic_quotes')){
			$myformdata=stripslashes($_POST['myformdata']);
			}else{$myformdata=$_POST['myformdata'];}
			$error=0;$postid=0;
			parse_str($myformdata,$output);
			$butval=$output['original_publish'];
			$quiz['ID']=$output['quizid'];
			if(isset($output['quizdesc'])){$quiz['post_content']=wp_kses_post($output['quizdesc']);}else{$quiz['post_content']='';}
			if($output['autodraftsavestatus']){
				$quiz['post_status']='draft';
				$quiz['post_name']=wp_unique_post_slug($output['quizname'],$quiz['ID'],'publish','wpcuebasicquiz',0);
			}else{$quiz['post_status']='publish';}
			$quiz['post_title']=$output['quizname'];
				$post_category=array();		
			if(isset($output['tax_input']['wpcuebasicquizcat'])){$post_category=$output['tax_input']['wpcuebasicquizcat'];}
			if(empty($output['tax_input'])){
				$term=get_term_by('name','Uncategorized','wpcuebasicquizcat');
				$post_category[]=$term->term_id;
			}
			$quiz['tax_input']=array('wpcuebasicquizcat'=>$post_category);
			wp_update_post($quiz);
			$this->update_quizmeta($quiz['ID'],'quizfinal',wp_kses_post($output['quizfinal']));
			if(!empty($output['quizintermediate'])){$this->update_quizmeta($quiz['ID'],'quizintermediate',wp_kses_post($output['quizintermediate']));}
			if(!empty($output['quizcomplete'])){$this->update_quizmeta($quiz['ID'],'quizcomplete',wp_kses_post($output['quizcomplete']));}
			if(!empty($output['adminemailsubject'])){$adminemail['subject']=$output['adminemailsubject'];}
			if(!empty($output['adminemail'])){$adminemail['mail']=$output['adminemail'];}
			if(!empty($output['useremailsubject'])){$useremail['subject']=$output['useremailsubject'];}
			if(!empty($output['useremail'])){$useremail['mail']=$output['useremail'];}
			if(!empty($output['customcss'])){$this->update_quizmeta($quiz['ID'],'customcss',wp_kses_post($output['customcss']));}else{delete_post_meta($quiz['ID'],'customcss');}
			if(isset($output['discloseans'])){$basicsetting['discloseans']=$output['discloseans'];}
			$this->update_quizmeta($quiz['ID'],'quizadminemail',$adminemail);
			$this->update_quizmeta($quiz['ID'],'quizuseremail',$useremail);
			if(isset($output['quizduration'])){$basicsetting['duration']=$output['quizduration']*60;}
			if(isset($output['questperpage'])){$basicsetting['questperpage']=$output['questperpage'];}
			if(!empty($output['quizmode'])){$basicsetting['mode']=$output['quizmode'];}
			if(!empty($output['loginrequired'])){$basicsetting['login']=$output['loginrequired'];}
			if(!empty($output['lognum'])){$basicsetting['lognum']=$output['lognum'];}
			if(!empty($output['loggap'])){$basicsetting['loggap']=$output['loggap'];}
			if(!empty($output['notifyadmin'])){$basicsetting['notifyadmin']=$output['notifyadmin'];}
			if(!empty($output['notifyuser'])){$basicsetting['notifyuser']=$output['notifyuser'];}
			if(!empty($output['autosubmit'])){$basicsetting['autosubmit']=$output['autosubmit'];}
			if(isset($output['discloseans'])){$basicsetting['discloseans']=$output['discloseans'];}
			if(!(empty($basicsetting))){$this->update_quizmeta($quiz['ID'],'basicsetting',$basicsetting);}
			if(!empty($output['randomquest'])){$randomizsetting['randomquest']=$output['randomquest'];}
			if(!empty($output['randomans'])){$randomizsetting['randomans']=$output['randomans'];}
			if(!empty($output['randomquestcat'])){$randomizsetting['randomquestcat']=$output['randomquestcat'];}
			if(!empty($output['randsecexc'])){$randomizsetting['randsecexc']=$output['randsecexc'];}
			if(!empty($output['randsecexcans'])){$randomizsetting['randsecexcans']=$output['randsecexcans'];}
			if(!empty($randomizsetting)){$this->update_quizmeta($quiz['ID'],'randomizsetting',$randomizsetting);}
			if(!empty($output['timer'])){$displaysetting['timer']=$output['timer'];}
			if(!empty($output['disablequizdesc'])){$displaysetting['disablequizdesc']=$output['disablequizdesc'];}
			if(!empty($output['disableintermediate'])){$displaysetting['disableintermediate']=$output['disableintermediate'];}
			if(!empty($output['disablestartbutton'])){$displaysetting['disablestartbutton']=$output['disablestartbutton'];}
			if(!empty($output['intermediatecontrol'])){$displaysetting['intermediatecontrol']=$output['intermediatecontrol'];}
			if(!empty($output['savebuttonstat'])){$displaysetting['savebuttonstat']=$output['savebuttonstat'];}
			if(!empty($output['submitbuttonstat'])){$displaysetting['submitbuttonstat']=$output['submitbuttonstat'];}
			if(!(empty($displaysetting))){$this->update_quizmeta($quiz['ID'],'displaysetting',$displaysetting);}
			if(!empty($output['showanswer'])){$questtools['showanswer']=$output['showanswer'];}
			if(!empty($output['showhint'])){$questtools['showhint']=$output['showhint'];}
			if(!empty($output['reportquest'])){$questtools['reportquest']=$output['reportquest'];}
			if(!empty($output['disabledentity'])){$disabledentities=$output['disabledentity'];}else{$disabledentities=array();}
			if(!(empty($questtools))){$this->update_quizmeta($quiz['ID'],'questtools',$questtools);}
			if($butval=='Update'){
				if(isset($output['gradegroupid'])){$gradegroupid=$output['gradegroupid'];}
				if(isset($output['inheritgradegroupid'])){$inheritgradegroupid=$output['inheritgradegroupid'];}
				if(!(empty($gradegroupid)) && !(empty($inheritgradegroupid)) &&($inheritgradegroupid != $gradegroupid)){
					$newgradegroup=get_post($inheritgradegroupid);
					if(empty($gradegroupid)){$gradegroupid=$inheritgradegroupid;}
					wp_update_post(array('ID'=>$gradegroupid,'post_title'=>$newgradegroup->post_title,'post_content'=>$newgradegroup->post_content,'post_status'=>'publish'));
				}
				$this->update_quizmeta($quiz['ID'],'quizgrade',$gradegroupid);
				if(!(empty($output['questionschanges']))){
					$table_name=$wpdb->prefix.'posts';
					$wpdb->delete($wpdb->prefix.'wpcuequiz_quizinfo',array('quizid'=>$quiz['ID']),array('%d'));
					if(!(empty($output['entityid']))){
						$entityid=$output['entityid'];$parentid=$output['parentid'];$flippedentityids=array_flip($entityid);
						$instanceid=$output['instanceid'];$updateents=array();
						$updateents=$diffent=array_diff($entityid,$instanceid);$combent=array_combine($entityid,$instanceid);
						foreach($diffent as $entity){
							array_push($updateents,$combent[$entity]);
						}
						if(!(empty($updateents))){$updateentids='('.implode(',',$updateents).')';
						$questions=$wpdb->get_results("select ID,post_title,post_content,post_type from $table_name where ID in $updateentids",OBJECT_K); 
						foreach($diffent as $entity){
							$instanceid=$combent[$entity];
							$entitykey=$flippedentityids[$entity];
							if(!empty($questionchangedstatus)){array_push($updatequestinfo,$entity);}
							if($questions[$instanceid]->post_type=='wpcuebasicsection'){$instancemeta=$questions[$instanceid]->post_content;}else{
							$instancemeta=unserialize($questions[$instanceid]->post_content);}
							if(!(empty($instancemeta))){
								$questionid=$entity;
								if($questions[$questionid]->post_type=='wpcuebasicsection'){$questmeta=$questions[$questionid]->post_content;}else{
									$questmeta=unserialize($questions[$questionid]->post_content);}
								if($questions[$questionid]->post_type=='wpcuebasicquestion'){if($questmeta['qc'] != $instancemeta['qc']){
									if($instancemeta['qc'] != -1){
										wp_set_object_terms($questionid,$instancemeta['qc'],'wpcuebasicquestcat');
									}
								}}
								$questmeta=$instancemeta;
								$questtitle=$questions[$instanceid]->post_title;
								if($questions[$questionid]->post_type=='wpcuebasicsection'){$questcontent=$questmeta;}else{
									$questcontent=serialize($questmeta);}
								$wpdb->update($wpdb->posts,array('post_title'=>$questtitle,'post_content'=>$questcontent,'post_status'=>'publish'),array('ID'=>$questionid),array('%s','%s','%s'),array('%d'));
							}
							array_push($disabledentities,$instanceid);
						}
						}
						$point=$output['point'];$category=$output['category'];
						$totalquest=count($entityid);$value='';$totalquestcount=0;
						$questionchangedstat=$output['questionchangedstat'];
						for($i=0;$i<$totalquest;$i++){
							$value.='('.$quiz['ID'].','.$entityid[$i].','.$parentid[$i].','.$point[$i].','.$category[$i].','.($i+1).','.$questionchangedstat[$i].')';
							if(($totalquest>1)&&($i != ($totalquest-1))){$value.=',';}
							if($parentid[$i] != -1){$totalquestcount++;}
						}
						$table_name=$wpdb->prefix.'wpcuequiz_quizinfo';
						$wpdb->query("INSERT INTO $table_name (quizid,entityid,parentid,point,category,entityorder,questionchange) VALUES $value");
					}
				}
			}
			foreach($disabledentities as $entityid){wp_delete_post($entityid);}
			echo json_encode(array('msg'=>'saved','gradegroupid'=>0));
			die();
		}
		
		public function quiz_shortcode($atts){
			wp_register_style( 'tabs_css',plugins_url('../css/jquery-ui-smooth.css',__FILE__));
			wp_enqueue_style('tabs_css');
			wp_enqueue_script('wpcuebasicquiz-frontjs');
			wp_localize_script('wpcuebasicquiz-frontjs','wpcuebasicquizajax',array('ajaxurl' => admin_url('admin-ajax.php')));
			$quizid=$atts[0];
			$quiz=get_post($quizid);
			$quizmeta=get_post_custom($quizid);
			if(empty($quizmeta['customcss'])){
				wp_register_style( 'wpcuebasicquiz-frontmaincss', plugins_url('/../css/wpcuebasicquiz-frontmain.css',__FILE__));
				wp_enqueue_style('wpcuebasicquiz-frontmaincss');
			}else{
				$content.='<style type="text/css">'.$quizmeta['customcss'].'</style>';
			}
			$content.='<div class="postcontainer">';
			$content.='<div class="title">'.$quiz->post_title.'</div>';
			$content.='<div class="quizcontent">'.$this->get_content($quizmeta,$quiz->ID,$quiz->post_title).'</div>';
			return $content;
		}
		
		public function delete_quiz($postid){
			ob_start();
			global $post_type;  
			if($post_type != 'wpcuebasicquiz'){return;}
			global $wpdb;	
			$table_name[0] = $wpdb->prefix.'wpcuequiz_quizinfo';	
			$table_name[1] = $wpdb->prefix.'wpcuequiz_quizstat';
			$table_name[2] = $wpdb->prefix.'wpcuequiz_quizstatinfo';	
			foreach($table_name as $tablename){
				$wpdb->query($wpdb->prepare("Delete from $tablename where quizid=%d",$postid));
			}
		}
		public static function entityids($quizid){
			$entityids=array();
			global $wpdb;$table_name=$wpdb->prefix.'wpcuequiz_quizinfo';
			$entityids=$wpdb->get_col($wpdb->prepare("Select entityid from $table_name where quizid=%d order by entityorder asc",$quizid));
			return $entityids;
		}
		public static function getquestions($quizid){
			$entityids=array();
			global $wpdb;$table_name=$wpdb->prefix.'wpcuequiz_quizinfo';
			$entityids=$wpdb->get_col($wpdb->prepare("Select entityid from $table_name where quizid=%d and parentid != -1 order by entityorder asc",$quizid));
			return $entityids;
		}
	
		public function wpcue_proquiz_startquiz(){
			ob_start();
			global $wpdb;
			$table_name = $wpdb->prefix.'wpcuequiz_quizstat';	
			$quizid=intval($_POST['quizid']);
			$quizmeta=get_post_custom($quizid);$basicsetting=maybe_unserialize($quizmeta['basicsetting'][0]);
			$instanceid=$_POST['instanceid'];$previnstance=$instanceid;
			$user_ID = get_current_user_id();
			if(empty($instanceid)){
				$previnstance=0;
				$post=$wpdb->query($wpdb->prepare("INSERT INTO $table_name (quizid,userid,timeremaining,mode,starttime,endtime,status) VALUES (%d,%d,%d,%d,now(),now(),0)",$quizid,$user_ID,intval($basicsetting['duration']),intval($basicsetting['mode'])));
				$instanceid=$wpdb->insert_id;
			}else{
				$post=$wpdb->query($wpdb->prepare("UPDATE $table_name SET starttime=now(),endtime=now() where instanceid=%d",$instanceid));
			}
			$content=$this->get_mainpage($quizmeta,$quizid,$previnstance);
			if($post){
				echo json_encode(array('msg'=>'success','instance'=>$instanceid,'content'=>$content));
			}else{
				echo json_encode(array('msg'=>'failed'));
			}
			echo ob_get_clean();
			die();
		}
		public function json_formatcontent_quiz( $_post, $post, $context){
			if( 'wpcuebasicquiz' === $post['post_type'] )
				$_post['content'] = $post['post_content'];
			return $_post;
		}
		
		public function formatcontent_quiz($content){
			global $post;
			$tags=wp_kses_allowed_html('post');
			if(($post->post_type == 'wpcuebasicquiz')){
				wp_register_script('mathjax','http://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML');
				wp_enqueue_script('mathjax');
				$quizmeta=get_post_custom($post->ID);$quizid=$post->ID;
				if(empty($quizmeta['customcss'])){
					wp_register_style( 'wpcuebasicquiz-frontmaincss', plugins_url('/../css/wpcuebasicquiz-frontmain.css',__FILE__));
					wp_enqueue_style('wpcuebasicquiz-frontmaincss');
				}else{
					wp_enqueue_style('dynamic-css',site_url().'/wpcuedynamiccss/'.$quizid.'/');
				}
				$content=$this->get_content($quizmeta,$post->ID,$post->post_content,$post->post_title);
			}elseif($post->post_type=='wpcuebasicquestion'){
				remove_filter( 'the_content', 'wpautop' );
			}elseif($post->post_type=='wpcuebasicbadge'){
				$content='<h3>Badge Criteria</h3>';
				$badgemeta=get_post_custom($post->ID);
				$content.='<ul><li><ul><li>Required Total Points</li><li>'; 
				if(isset($badgemeta['wpcuebadgepoint'])){$content.=$badgemeta['wpcuebadgepoint'][0];}else{$content.=0;}
				$content.='</li></ul></li><li><ul><li>Required average % correct answers from all tests</li><li>';
				if(isset($badgemeta['wpcuebadgecorper'])){$content.=$badgemeta['wpcuebadgecorper'][0];}else{$content.=0;}
				$content.='</li></ul></li><li><ul><li>Required number of unque tests taken</li><li>';
				if(isset($badgemeta['wpcuebadgequiznum'])){$content.=$badgemeta['wpcuebadgequiznum'][0];}else{$content.=0;}
				$content.='</li></ul></li>';
				if(!(empty($badgemeta['wpcuebadgequizcat']))){$savedcategories=maybe_unserialize($badgemeta['wpcuebadgequizcat'][0]);}
				if(!(empty($savedcategories))){
					$content.='<li><ul><li>Must take all exam from Quiz Category</li><li>';
					$conent.='</li></ul></li>';
				}
				$content.='</ul>';
			}elseif($post->post_type=='page'){
				$wpprocuesetting=$this->wpprocuesetting;
				if(isset($wpprocuesetting['basic']['userpageid'])&& ($post->ID==$wpprocuesetting['basic']['userpageid'])){
					global $current_user;
					$userid=$current_user->ID;
				}
			}
			return $content;
		}
		public function get_content($quizmeta,$postid,$postcontent,$post_title){
			global $wpdb;
			$wpprocuesetting=$this->wpprocuesetting;
			$table_name = $wpdb->prefix.'wpcuequiz_quizstat';	
			$basicsetting=maybe_unserialize($quizmeta['basicsetting'][0]);
			if(!(empty($quizmeta['displaysetting']))){
				$displaysetting=maybe_unserialize($quizmeta['displaysetting'][0]);
				if(!(empty($displaysetting['displaysubdial']))){
					wp_enqueue_script('jquery-ui-dialog');
					wp_enqueue_style (  'wp-jquery-ui-dialog');
				}
			}
			$userid=get_current_user_id();
			if(empty($basicsetting['login'])){
				$page=0;
			}else{
				if(empty($userid)){
					$page=1;
				}else{
					if(empty($basicsetting['lognum'])){
						$quizlast=$wpdb->get_row($wpdb->prepare("select * from $table_name where userid=%d and quizid=%d and status=0 order by endtime LIMIT 0,1",$userid,$postid),ARRAY_A );
						if(is_null($quizlast)){$page=0;}else{$page=2;}
					}else{
						$trialnum=$wpdb->get_var($wpdb->prepare("select count(*) as count from $table_name where userid=%d and quizid=%d and status=1 order by endtime",$userid,$postid));
						if($trialnum>=$basicsetting['lognum']){
							$page=3;
							$quizlast=$wpdb->get_row($wpdb->prepare("select * from $table_name where userid=%d and quizid=%d and status=1 order by endtime desc LIMIT 0,1",$userid,$postid),ARRAY_A );
						}else{
							$quizlast=$wpdb->get_row($wpdb->prepare("select * from $table_name where userid=%d and quizid=%d and status=0 order by endtime desc LIMIT 0,1",$userid,$postid),ARRAY_A );
							if(is_null($quizlast)){$page=0;}else{$page=2;}
						}
					}
				}
			}
			$content='<input type="hidden" name="quizid" value="'.$postid.'">';
			if(!(empty($wpprocuesetting['submitdial']['status']))){
				$submitdialogcontent='<div class="wp-dialog" id="quizsubmitdialog" style="width:'.$wpprocuesetting['submitdial']['width'].';height:'.$wpprocuesetting['submitdial']['height'].';">'.$wpprocuesetting['submitdial']['dialog'].'</div>';
			}else{$submitdialogcontent='';}
			if(!(empty($wpprocuesetting['autosubdial']['status']))){
				$autosubmitdialogcontent='<div class="wp-dialog" id="autosubmitdialog" style="width:'.$wpprocuesetting['autosubdial']['width'].';height:'.$wpprocuesetting['autosubdial']['height'].';">'.$wpprocuesetting['autosubdial']['dialog'].'</div>';
			}else{$autosubmitdialogcontent='';}
			$content.='<div id="processdiv"><div id="spinnerdiv"><span class="spinner"></span></div><div id="processtextdiv">'.$wpprocuesetting['text']['processingquiz'].'</div></div>';
			switch($page){
				case 0:
					$content.="<div id='quizstartpage'>";
					if(empty($displaysetting['disablequizdesc'])){
						if(isset($postcontent)){$content.='<div id="quizdesc">'.$postcontent.'</div>';}
					}
					if(empty($displaysetting['disablestartscreen'])){ if(isset($quizmeta['quizstart'][0])){$content.="<div class='startpagecontent'>".$quizmeta['quizstart'][0]."</div>";}}
					$content.="<div class='startbutton'>";
					if(empty($displaysetting['disablestartbutton'])){
						$content.="<input type='button' name='startquizbutton' id='startquizbutton' value='".$wpprocuesetting['text']['start']."'>";
						$instanceid=0;
					}else{
						$post=$wpdb->query($wpdb->prepare("INSERT INTO $table_name (quizid,userid,timeremaining,mode,starttime,endtime,status) VALUES (%d,%d,%d,%d,now(),now(),0)",$postid,$userid,intval($basicsetting['duration']),intval($basicsetting['mode'])));
						$instanceid=$wpdb->insert_id;
					}
					$content.="<input type='hidden' name='instanceid' value='".$instanceid."'/>";
					$content.="<input type='hidden' name='quizmode' value='".$basicsetting['mode']."'>";
					if(!empty($wpprouesetting['autosubdial']['status'])){$content.="<input type='hidden' name='autosubmission' value='".$wpprocuesetting['autosubdial']['status']."'>";}
					$content.="</div></div>";
					$content.='<div id="quizmainpage">';
					if(!(empty($displaysetting['disablestartbutton']))){
						$content.='<input type="hidden" name="disablestartbutton" value="'.$displaysetting['disablestartbutton'].'">';
						$content.=$this->get_mainpage($quizmeta,$postid,0);
					}
					$content.='</div>';
					if(!(empty($quizmeta['quizfinal'][0]))){$content.='<div id="quizfinalpage"></div>';}
					break;
				case 1:
					$content.="<div id='quizstartpage'>";
					if(empty($displaysetting['disablequizdesc'])){
						if(isset($postcontent)){$content.='<div id="quizdesc">'.$postcontent.'</div>';}
					}
					$content.="<input type='hidden' name='quizlogin' value='0'>";
					$content.="<input type='hidden' name='instanceid' value='0'/>";
					$content.="<input type='hidden' name='quizmode' value='".$basicsetting['mode']."'>";
					if(!empty($wpprouesetting['autosubdial']['status'])){$content.="<input type='hidden' name='autosubmission' value='".$wpprocuesetting['autosubdial']['status']."'>";}
					$content.="<div class='quizlogintext'>".$wpprocuesetting['text']['logintext']."</div><div class='logincontrol'><a href='";
					if(empty($wpprocuesetting['basic']['login'])){$content.=wp_login_url(get_permalink())."'";}else{$content.="#'";}
					$content.=" title='Login' class='button";if(!(empty($wpprocuesetting['basic']['login']))){$content.=" dialoglogin";}
					$content.="' id='quizloginbutton'>".$wpprocuesetting['text']['login']."</a>";
					$content.='</div></div>';
					
					if(!(empty($wpprocuesetting['basic']['login']))){$content.='<div class="wp-dialog" id="quizllogindialog">'.wp_login_form(array('echo'=>false)).'</div>';}
					break;
				case 2:
					$quizinfo=$this->quizinfo($postid);
					$intermediatecontent=$this->getfinal_content($postid,$quizlast['instanceid'],$quizmeta['quizintermediate'][0],$quizlast,$quizmeta,$post_title,$quizinfo['totalquestions'],$quizinfo['totalpoint']);
					$content.="<div id='quizintermediatepage'><div class='quizintermediatecontent'>".$intermediatecontent[0]."</div>";
					$content.="<div class='startbutton'>";
					$content.="<input type='hidden' name='instanceid' value='".$quizlast['instanceid']."'/>";
					$content.="<input type='hidden' name='quizmode' value='".$basicsetting['mode']."'>";
					if(!empty($wpprouesetting['autosubdial']['status'])){$content.="<input type='hidden' name='autosubmission' value='".$wpprocuesetting['autosubdial']['status']."'>";}
					if(empty($displaysetting['intermediatecontrol'])){$content.="<input type='button' name='continuequizbutton' id='continuequizbutton' value='continue'>";}
					$content.='</div></div>';
					$content.='<div id="quizmainpage">';
					if(!(empty($displaysetting['intermediatecontrol']))){
						$content.='<input type="hidden" name="disableintermediatecontrol" value="'.$displaysetting['intermediatecontrol'].'">';
						$content.=$this->get_mainpage($quizmeta,$postid,$quizlast['instanceid']);
						$post=$wpdb->query($wpdb->prepare("UPDATE $table_name set starttime=now(),endtime=now() where instanceid=%d",$quizlast['instanceid']));
					}
					$content.='</div>';
					if(!(empty($quizmeta['quizfinal'][0]))){$content.='<div id="quizfinalpage"></div>';}
					break;
				case 3:
					$quizinfo=$this->quizinfo($postid);
					$completedcontent=$this->getfinal_content($postid,$quizlast['instanceid'],$quizmeta['quizcomplete'][0],$quizlast,$quizmeta,$post_title,$quizinfo['totalquestions'],$quizinfo['totalpoint']);
					$content.="<div id='quizcompletedpage'><div class='quizcompletedcontent'>".$completedcontent[0]."</div>";
					return $content;
					break;
			}
			$content.=$submitdialogcontent;
			$content.=$autosubmitdialogcontent;
			return $content;
		}
		public function get_mainpage($quizmeta,$postid,$instance){
			global $wpdb;
			$table_name3=$wpdb->prefix.'wpcuequiz_quizinfo';
			$wpprocuesetting=$this->wpprocuesetting;
			$basicsetting=maybe_unserialize($quizmeta['basicsetting'][0]);
			if(!(empty($quizmeta['randomizsetting']))){$randomizsetting=maybe_unserialize($quizmeta['randomizsetting'][0]);}else{$randomizsetting=array();}
			if(!(empty($quizmeta['displaysetting']))){$displaysetting=maybe_unserialize($quizmeta['displaysetting'][0]);}else{$displaysetting=array();}
			if(!(empty($quizmeta['questtools']))){$questtools=maybe_unserialize($quizmeta['questtools'][0]);}else{$questtools=array();}
			$totalquestcount=$wpdb->get_col($wpdb->prepare("select count(*) as counter from $table_name3 where quizid=%d and parentid != -1",$postid));
			$totalquestcount=(int)$totalquestcount[0];
			$entities=$this->get_questions($randomizsetting,$instance,$postid);
			$content='<script>jQuery(document).ready(function($){$(".hiddendiv").hide();});</script>';
			if($instance){
				$table_name1=$wpdb->prefix.'wpcuequiz_quizstatinfo';$table_name2=$wpdb->prefix.'wpcuequiz_quizstat';
				$tablename3=$wpdb->prefix.'wpcuequiz_quizerrorinfo';$table_name4=$wpdb->prefix.'wpcuequiz_quizinfo';
				$quizlast=$wpdb->get_results($wpdb->prepare("select entityid,answer,reply,point,status,disabled from $table_name1 where instanceid=%d",$instance),OBJECT_K);
				$entitystat=$wpdb->get_results($wpdb->prepare("SELECT entityid,questionchange,UNIX_TIMESTAMP(questionchangedate) as questionchangedate from $table_name4 where quizid=%d",$postid),OBJECT_K);
				$quizstat=$wpdb->get_row($wpdb->prepare("SELECT timeremaining,UNIX_TIMESTAMP(endtime) as endtime from $table_name2 where instanceid=%d",$instance),ARRAY_A);
				$errorstat=$wpdb->get_results($wpdb->prepare("select entityid,errorid from $tablename3 where instanceid=%d",$instance),ARRAY_A);
				$errorids=array();$errors=array();$mappederror=array();
				if(!(empty($errorstat))){
					foreach($errorstat as $errorent){
						$entityid=$errorent['entityid'];$errorid=$errorent['errorid'];
						array_push($errorids,$errorent['errorid']);
						if(empty($mappederror[$entityid])){
							$mappederror[$entityid]=array($errorid);
						}else{array_push($mappederror[$entityid],$errorid);}
					}
				}
				if(!(empty($errorids))){
					$tablename4=$wpdb->prefix.'posts';$errorarr='('.implode(',',$errorids).')';
					$errors=$wpdb->get_results("select ID,post_content,post_title from $tablename4 where ID in $errorarr",OBJECT_K);
				}
			}
			
			$secent=$wpdb->get_results($wpdb->prepare("select parentid,count(*) as counter from $table_name3 where parentid != -1 and quizid=%d group by parentid",$postid),OBJECT_K);
		
			if(!(empty($basicsetting['duration']))){
				$content.='<div id="quiztimetools">';
				$totaltime=$basicsetting['duration'];
				if($totaltime >= 3600){
					$hours=floor($totaltime/3600);
					$totaltime=$totaltime-$hours*3600;}
				if($totaltime>=60){	
					$mins=floor($totaltime/60);
					$seconds=$totaltime-$mins*60;
				}else{$mins=0;$seconds=$totaltime;}
				if($instance){$totalremainingtime=$quizstat['timeremaining'];}else{$totalremainingtime=$basicsetting['duration'];}
				if($totalremainingtime >= 3600){
					$hoursremaining=floor($totalremainingtime/3600);
					$totalremainingtime=$totalremainingtime-$hoursremaining*3600;}
				if($totalremainingtime>=60){	
					$minsremaining=floor($totalremainingtime/60);
					$secondsremaining=$totalremainingtime-$minsremaining*60;
				}else{$minsremaining=0;$secondsremaining=$totalremainingtime;}
				$content.='<div id="quizduration">'.$wpprocuesetting['text']['quizduration'];
				if(!(empty($hours))){$content.=$hours.' Hour';}
				if(!(empty($mins))){$content.=$mins.' Minutes';}
				if(!(empty($seconds))){$content.=$seconds.' Seconds';}
				$content.='</div>';
				$content.="<div id='wpcuebasicquiztimercontent'";
				if(empty($displaysetting['timer'])){$content.=" class='hiddendiv'";}
				$content.="><ul><li class='wpcuebasicquiztimerdesc'>".$wpprocuesetting['text']['timeleft']."</li><li class='wpcuebasicquiztimerpoint'>:</li>";
				if(isset($hoursremaining)){
				$content.="<li id='wpcuebasicquiztimerhours' class='wpcuebasicquiztimertimeunit'>";
				if($hoursremaining<10){$content.='0'.$hoursremaining;}else{$content.=$hoursremaining;}
				$content.="</li><li class='wpcuebasicquiztimerpoint'>:</li> ";}
				if(isset($minsremaining)){$content.="<li id='wpcuebasicquiztimermins' class='wpcuebasicquiztimertimeunit'>";
				if($minsremaining<10){$content.='0'.$minsremaining;}else{$content.=$minsremaining;}
				$content.="</li><li class='wpcuebasicquiztimerpoint'>:</li>";}
				$content.="<li id='wpcuebasicquiztimersecs' class='wpcuebasicquiztimertimeunit'>";
				if($secondsremaining<10){$content.='0'.$secondsremaining;}else{$content.=$secondsremaining;}
				$content.="</div>";
				$content.='</div>';
			}
			$content.='<div id="quizmaincontent"><form name="myfromdata" id="quizpost">';
			$content.='<input type="hidden" name="quizid" id="origquizid" value="'.$postid.'" />';
			if(!empty($entities)){
				
				$args = array( 'post__in'=>$entities,'post_type'=>array('wpcuebasicquestion','wpcuebasicsection'),'orderby'=>'post__in','posts_per_page' => -1);
				$entityquery = new WP_Query($args);
				$curquiz=array();
				$i=0;$pagenum=1;$j=1;$rownum=1;$k=0;$totent=count($entities);
				if($basicsetting['questperpage']==0){$totpagenum=1;}else{$totpagenum=ceil($totalquestcount/$basicsetting['questperpage']);}
				$section=0;
				while ($entityquery->have_posts()){
					$entityquery->the_post();
					$entitypost=$entityquery->post;
					if($i==0 && $section==0){$content.='<div id="questpage-'.$pagenum.'" class="questpage';
					if($j>1){$content.=' hiddendiv';}$content.='">';}
					if($entitypost->post_type == 'wpcuebasicsection'){
						$section=1;
						if(!(empty($secent[$entitypost->ID]))){
							$content.='<div id="rowsec-'.$entitypost->ID.'" class="rowsec">'.$entitypost->post_content.'</div>';
							$content.='<input type="hidden" name="questionid[]" value="'.$entitypost->ID.'">';
						}
					
					}else{
						$entitymeta=unserialize($entitypost->post_content);
						$section=0;
						$content.='<div id="rowquest-'.$entitypost->ID.'" class="rowquest"><div class="questnumber">Q.'.$rownum.'</div><div class="mainquest">';
						$content.='<input type="hidden" name="questionid[]" value="'.$entitypost->ID.'">';
						if(empty($randomizsetting['randomans'])){$randomizsetting['randomans']=0;}
						if($instance && !(empty($quizlast))){
							$rejectprev=0;
							if(!(empty($entitystat[$entitypost->ID]->questionchange)) && ($quizstat['endtime'] < $entitystat[$entitypost->ID]->questionchangedate)){
								$rejectprev=1;
							}
							$content.=$this->getquest_det($entitymeta,$entitypost->ID,$randomizsetting['randomans'],$quizlast[$entitypost->ID],$rejectprev);
						}else{
							$content.=$this->getquest_det($entitymeta,$entitypost->ID,$randomizsetting['randomans']);
						}
						$content.='</div>';
						$content.='<div class="questtools">';
						$content.='<p class="questtoolsmsg"></p>';
						$content.='<div class="questtoolsicons">';
						$content.='<ul class=questtootlslist>';	
						if(!(empty($questtools['showanswer']))){
							$content.='<li><a href="#" class="showanswer">Show Answer</a></li>';
						}
						if(!(empty($questtools['reportquest']))){
							$content.='<li><a href="#" class="reportquestion">Report Error</a></li>';
						}
						if(!(empty($questtools['showhint']))){
							$content.='<li><a href="#" class="showhintquestion">Show Hint</a></li>';
						}
						$content.='</ul></div>';
						$content.='<div class="questtoolsblock">';
						if(!(empty($questtools['showanswer']))){
						$content.='<div class="answercontainer hiddendiv">';
						if(empty($entitymeta['correctansdesc'])){$content.='No description for correct answer for this question';
						}else{
						$content.=$entitymeta['correctansdesc'];
						}
						$content.='</div>';}
						if(!(empty($questtools['reportquest']))){
							$content.='<div class="reportquestcontainer hiddendiv">';
							$content.='<div class="reportquestform">';
							$content.='<input type="hidden" name="errorid" value="0" class="reportquestid">';
							$content.='Title : <input type="text" name="errortitle" class="reportquesttitle">';
							$content.='Description : <textarea name="errordesc" class="reportquestdesc"></textarea>';
							$content.='<input type="button" name="saveerror" value="'.__('save','wpcues-basic-quiz').'" class="saveerror">';
							$content.='</div><div class="reportquestadded reportquesttable">';
							if(!(empty($instance)) && (!(empty($errors))) && (!(empty($mappederror[$entityid])))){
								$errorids=$mappederror[$entityid];
								foreach($errorids as $errorid){
									$content.='<div id="error-'.$errorid.'" class="errorentity"><div class="errorinfo">';
									$content.='<div class="errortitle">'.__('Title','wpcues-basic-quiz').' :'.$errors[$errorid]->post_title.'</div>';
									$content.='<div class="errordesc">'.__('Description','wpcues-basic-quiz').' :'.$errors[$errorid]->post_content.'</div></div>';
									$content.='<div class="erroredit"></div><div class="errordelete"></div>';
									$content.='<input type="hidden" name="errorid-'.$entitypost->ID.'[]" value="'.$errorid.'">';
									$content.='<input type="hidden" name="erroraddedstatus-'.$errorid.'" value="1">';
									$content.='<input type="hidden" name="erroreditedstatus-'.$errorid.'" value="0">';
									$content.='<input type="hidden" name="errordesc-'.$errorid.'" value="'.$errors[$errorid]->post_content.'">';
									$content.='<input type="hidden" name="errortitle-'.$errorid.'" value="'.$errors[$errorid]->post_title.'">';
									$content.='</div>';
								}
							}
							$content.='</div>';
							$content.='</div>';
						}
						if(!(empty($questtools['showhint']))){
							$content.='<div class="showhintcontainer hiddendiv">';
							if(empty($entitymeta['anshint'])){
								$content.='No hint for this question';
							}else{
								$content.=$entitymeta['anshint'];
							}
							$content.='</div>';
						}
						$content.='</div>';
						$content.='</div>';
						$content.='</div>';
						$i++;$rownum++;
						if($basicsetting['questperpage'] != 0){
							if(($i==$basicsetting['questperpage']) ||($j==$totalquestcount)) {
							if($totpagenum > 1){
								$content.='<div class="paginationbutton">';
								if($pagenum != $totpagenum){
									if($pagenum==1){
										$content.="<input type='button' name='nextpagebutton' class='nextpagebutton' value='".$wpprocuesetting['text']['next']."'></div>";}
									else{
										$content.="<input type='button' name='prevpagebutton' class='prevpagebutton' value='".$wpprocuesetting['text']['prev']."'>";
										$content.="<input type='button' name='nextpagebutton' class='nextpagebutton' value='".$wpprocuesetting['text']['next']."'></div>";}
								}else{
									if($totpagenum >1){$content.="<input type='button' name='prevpagebutton' class='prevpagebutton' value='".$wpprocuesetting['text']['prev']."'></div>";}
								}
							}
							$content.='<div class="quizsubmittools">';
							if(!(empty($basicsetting['login'])) && (!(empty($displaysetting['savebuttonstat'])))){$content.="<div class='submitquizbutton'><input type='button' name='savequizbut' class='savequizbut' value='Save'></div>";}
							if(!empty($displaysetting['submitbuttonstat'])){
								$content.="<div class='submitquizbutton'><input type='button' name='submitquizbut' class='submitquizbut' value='".$wpprocuesetting['text']['submit']."'></div>";
							}elseif($j==$totalquestcount){
								$content.="<div class='submitquizbutton'><input type='button' name='submitquizbut' class='submitquizbut' value='".$wpprocuesetting['text']['submit']."'></div>";
							}
							$content.='</div>';
							$content.='</div>';
						
							$i=0;$pagenum++;}
						}else{
							if($j == $totalquestcount){
								$content.='<div class="quizsubmittools">';
								$content.="<div class='submitquizbutton'>";
								if(!(empty($basicsetting['login'])) && (!(empty($displaysetting['savebuttonstat'])))){$content.="<div class='submitquizbutton'><input type='button' name='savequizbut' class='savequizbut' value='Save'></div>";}
								$content.="<input type='button' name='submitquizbut' class='submitquizbut' value='".$wpprocuesetting['text']['submit']."'></div>";
								$content.='</div>';
								$content.='</div>';
							}
						}
						$j++;
					}
					$k++;
				
				}
				wp_reset_postdata();
			}
			$content.='</form></div>';
			return $content;
		}
		public function get_questions($randomizsetting,$instanceid,$quizid){
			global $wpdb;
			$table_name1=$wpdb->prefix.'wpcuequiz_quizstatinfo';
			if(!(empty($instanceid))){$status=$wpdb->get_var($wpdb->prepare("select distinct instanceid from $table_name1 where instanceid=%d",$instanceid));if(is_null($status)){$status=0;}}else{$status=0;}
			if(empty($status)){
				$table_name=$wpdb->prefix.'wpcuequiz_quizinfo';
				if(!(empty($randomizsetting['randomquest']))){
					if(!(empty($randomizsetting['randomquestcat']))){
						$questcat=$wpdb->get_results($wpdb->prepare("SELECT entityid,category from $table_name where quizid=%d and parentid=0 group by category,entityid",$quizid),ARRAY_N);
						foreach($questcat as $key=>$value){
							$questcatrow=$value;
							if(!(empty($questcats[$questcatrow[1]]))){array_push($questcats[$questcatrow[1]],$questcatrow[0]);}else{$questcats[$questcatrow[1]]=array($questcatrow[0]);}
						}
						$questcateg=array_keys($questcats);$flipcat=array_flip($questcateg);
						$section=$wpdb->get_col($wpdb->prepare("select entityid from $table_name where quizid=%d and parentid=-1",$quizid));
						$entities=array_merge($questcateg,$section);shuffle($entities);
						foreach($questcateg as $category){
							$flipentities=array_flip($entities);
							if(is_array($questcats[$category])){
								$value=$questcats[$category];shuffle($value);
							}else{$value=$questcats[$value];}
							array_splice($entities,$flipentities[$category],1,$value);
						}
					}else{
						$entities=$wpdb->get_col($wpdb->prepare("SELECT entityid from $table_name where quizid=%d and parentid IN (0,-1)",$quizid));
						shuffle($entities);
						$section=$wpdb->get_col($wpdb->prepare("select entityid from $table_name where quizid=%d and parentid=-1",$quizid));
					}
					
					$secquest=$wpdb->get_results($wpdb->prepare("SELECT parentid,entityid from $table_name where quizid=%d and parentid NOT IN (-1,0) order by parentid asc",$quizid),ARRAY_N);
					foreach($secquest as $key=>$value){
						$secquestrow=$value;
						if(!(empty($secquestion[$secquestrow[0]]))){array_push($secquestion[$secquestrow[0]],$secquestrow[1]);}else{$secquestion[$secquestrow[0]]=array($secquestrow[1]);}
					}
					$flipentities=array_flip($entities);
					foreach($section as $sectionid){
						$flipentities=array_flip($entities);
						if(isset($secquestion[$sectionid])){$value=$secquestion[$sectionid];shuffle($value);
							array_splice($entities,$flipentities[$sectionid]+1,0,$value);
						}
					}
				}else{
					$entities=$wpdb->get_col($wpdb->prepare("SELECT entityid from $table_name where quizid=%d order by entityorder asc",$quizid));
				}
				
			}else{
				$table_name=$wpdb->prefix.'wpcuequiz_quizstatinfo';
				$entities=$wpdb->get_col($wpdb->prepare("SELECT entityid from $table_name where instanceid=%d order by id asc",$instanceid));
			}
			return $entities;
		}
		public function getquest_det($questmeta,$questionid,$randomans,$prevquestdet = false,$rejectprev=false){
			$content='';$disabled=0;
			if($prevquestdet && (!empty($prevquestdet->disabled))){$disabled=1;}
			$content.='<div class="questdesc"><p>';
			if($questmeta['t'] !=5){$content.=$questmeta['desc'];}
			$content.="<input type='hidden' name='disablestatus-".$questionid."' class='disablestatus' value='".$disabled."'>";
			$content.="<input type='hidden' name='questiontype-".$questionid."' value='".$questmeta['t']."'>";
			switch($questmeta['t']){
				case 1:
					$content.='</p></div><div class="answerdesc"><ul class="standardquestion" style="list-style-type:none;">';
					if(($prevquestdet)&&(empty($rejectprev))){
						$answerids=maybe_unserialize($prevquestdet->answer);
					}else{$answerids=$questmeta['a']['id'];
						if(!(empty($randomans))){shuffle($answerids);}
					}
					foreach($answerids as $answer){
						$content.='<li>';
						$content.='<input type="radio" name="answer-'.$questionid.'" value="'.$answer.'" ';
						if(($prevquestdet)&&(empty($rejectprev)) && (!(empty($prevquestdet->reply))) && ($answer==$prevquestdet->reply)){
								$content.='checked';}
						if(!empty($disabled)){$content.=' disabled';}
						$content.='>';
						$content.=$questmeta['a'][$answer]['desc'].'</li>';
					}
					$content.='</ul></div>';
					$content.="<input type='hidden' name='allanswer-".$questionid."' value='".serialize($answerids)."'/>";
					break;
					break;
				case 2:
					$content.='</p></div><div class="answerdesc"><ul class="standardquestion" style="list-style-type:none;">';
					if(($prevquestdet)&&(empty($rejectprev))){$answerids=maybe_unserialize($prevquestdet->answer);$reply=maybe_unserialize($prevquestdet->reply);}
					else{$answerids=$questmeta['a']['id'];
						if(!(empty($randomans))){shuffle($answerids);}
					}
					foreach($answerids as $answer){
						$content.='<li>';
						$content.='<input type="checkbox" name="answer-'.$questionid.'[]" value="'.$answer.'" ';
						if(($prevquestdet)&&(empty($rejectprev)) && (!(empty($reply))) && (in_array($answer,$reply))){
								$content.='checked';}
						$content.='>';
						$content.=$questmeta['a'][$answer]['desc'].'</li>';
					}
					$content.='</ul></div>';
					$content.="<input type='hidden' name='allanswer-".$questionid."' value='".serialize($answerids)."'/>";
					break;
				case 3:
					$stat=0;
					if(($prevquestdet)&&(empty($rejectprev))){
						$answer=maybe_unserialize($prevquestdet->answer);$reply=$prevquestdet->reply;
						$lanswerids=$origlanswerids=$answer['la'];$ranswerids=$origranswerids=$answer['ra'];
						$column=$answer['column'];$count=$answer['count'];
						if(!empty($reply)){
							if($column=='rightcolumn'){
								$stat=1;$lanswerids=$origlanswerids;$ranswerids=$reply;
							}else{
								$lanswerids=$reply;$ranswerids=$origranswerids;
							}
						}
					}else{
						$column='';$count=0;
						$leftcount=count($questmeta['la']['id']);$rightcount=count($questmeta['ra']['id']);
						$origlanswerids=$lanswerids=$questmeta['la']['id'];$origranswerids=$ranswerids=$questmeta['ra']['id'];
						if(!(empty($randomans))){shuffle($lanswerids);shuffle($ranswerids);}
						if($leftcount <= $rightcount){$stat=1;$count=$leftcount;$column='rightcolumn';}else{$count=$rightcount;$column='leftcolumn';}
					}
					$content.='</p>';
					if($stat==0){$content.='<p class="entitymsg">'.__('Sort column A to match to column B entries','wpcues-basic-quiz').'</p></div>';}else{
						$content.='<p class="entitymsg">'.__('Sort column B to match to column A entries','wpcues-basic-quiz').'</p></div>';
					}
					$content.='<div class="answerdesc"><div class="leftcolumnquest">';
					$content.='<p>Column A </p><ul class="leftmatchquestion ';
					if($stat==0){$content.='matchquestion" id="matchquestion-'.$questionid.'"';}
					$content.=' style="list-style-type:none;">';
					foreach($lanswerids as $answer){
						$content.='<li id="answer-'.$questionid.'-'.$answer.'" ';
						if($stat==0){$content.=' class="ui-state-default"';}
						$content.='><input type="hidden" name="lanswer-'.$questionid.'[]" value="'.$answer.'">';
						$content.=$questmeta['la'][$answer]['desc'].'</li>';
					}
					$content.='</ul></div><div class="rightcolumnquest">';
					$content.='<p>Column B </p><ul class="rightmatchquestion ';
					if($stat==1){$content.='matchquestion" id="matchquestion-'.$questionid.'"';}
					$content.=' style="list-style-type:none;">';
					foreach($ranswerids as $answer){
						$content.='<li id="answer-'.$questionid.'-'.$answer.'" ';
						if($stat==1){$content.=' class="ui-state-default"';}
						$content.='><input type="hidden" name="ranswer-'.$questionid.'[]" value="'.$answer.'">';
						$content.=$questmeta['ra'][$answer]['desc'].'</li>';
					}
					$content.='</ul></div>';
					$content.='<input type="checkbox" name="markmatchquestion" value="1"';
					if(!(empty($reply))){$content.=' checked';}
					if($disabled){$content=' disabled';}
					$content.='> Mark as Correct';
					$content.='<input type="hidden" name= "colmatchquestion-'.$questionid.'" value="'.$column.'">';
					$content.='<input type="hidden" name="matchcount-'.$questionid.'" value="'.$count.'">';
					$content.="<input type='hidden' name='lanswerids-".$questionid."' value='".serialize($origlanswerids)."'>";
					$content.="<input type='hidden' name='ranswerids-".$questionid."' value='".serialize($origranswerids)."'>";
					$content.='</div>';
					break;
				case 4:
					if(($prevquestdet)&&(empty($rejectprev))){
							$answerids=$reply=maybe_unserialize($prevquestdet->reply);
							$origansids=maybe_unserialize($prevquestdet->answer);
							if(empty($answerids)){$answerids=$origansids;}
					}else{
					$answerids=$questmeta['a']['id'];
					if(!(empty($randomans))){shuffle($answerids);}
						$origansids=$answerids;
					}
					$content.='</p><p class="entitymsg">Sort the answers in correct order and select the checkbox when done</p></div><div class="answerdesc"><ul class="sortquestion">';
					$i=1;
					foreach($answerids as $answer){
						$content.='<li class="ui-state-default" id="anslist-'.$questionid.'"><input type="hidden" name="answer-'.$questionid.'[]" value="'.$answer.'"> '.$i.'. '.$questmeta['a'][$answer]['desc'].'</li>';
						$i++;
					}
					$content.='</ul><input type="checkbox" name="sortquestionstatus" value="1" ';
					if(!(empty($reply))){$content.='checked';}
					if($disabled){$content.=' disabled';}
					$content.='>Mark as correct';
					$content.="<input type='hidden' name='answerids-".$questionid."' value='".serialize($origansids)."'>";
					$content.='</div>';
					break;
				case 5:
					if(($prevquestdet)&&(empty($rejectprev)) && (!(empty($prevquestdet->reply)))){
						$i=0;$replacements=array();$answerids=maybe_unserialize($prevquestdet->reply);
						foreach($answerids as $answer){
								$replacements[$i]='<input type="text" name="answer-'.$questionid.'[]" class="answrfillthegaps" value="'.$answer.'"';
								if($disabled){$replacements[$i].=' disabled';}$replacements[$i].='>';
								$i++;
						}
						$content.=preg_replace_callback('/\{\{\{(.*)\}\}\}/U',function($matches) use (&$replacements){
									return array_shift($replacements);
											}, $questmeta['desc']);
					}else{$replacement='<input type="text" name="answer-'.$questionid.'[]" class="answrfillthegaps">';
					$content.=preg_replace('/\{\{\{(.*)\}\}\}/U',$replacement, $questmeta['desc']).'</p></div>';}
					break;
				case 6:
					$content.='</div><div class="answerdesc"><ul class="truefalsequestion" style="list-style-type:none">';
					if(($prevquestdet)&&(empty($rejectprev))){$answerids=$prevquestdet->answer;$reply=$prevquestdet->reply;}
					else{$answerids=array(0,1);
					if(!(empty($randomans))){shuffle($answerids);}}
					foreach($answerids as $answer){
						$content.='<li><input type="radio" name="answer-'.$questionid.'" value="'.$answer.'"';
						if((!(empty($reply))) && ($answer==$prevquestdet->reply)){
								$content.='checked';
							}
							if($answer==1){$ansdesc='True';}else{$ansdesc='False';}
						$content.='> '.$ansdesc.'</li>';
					}
					$content.='</ul></div>';
					$content.="<input type='hidden' name='answerids-".$questionid."' value='".serialize($answerids)."'/>";
					break;
				default:
					$content.='wanna dance baby!';
			}
			return $content;
		}
		public function wpcuebasicquiz_scripts(){
			global $post;
			if(isset($post)&& !(is_admin()) && ($post->post_type == 'wpcuebasicquiz' ) ){
				wp_register_style( 'tabs_css', plugins_url('../css/jquery-ui-smooth.css',__FILE__));
				wp_enqueue_style('tabs_css');
				wp_register_script('wpcuequiz-front',plugins_url('/../js/wpcuebasicquiz-front.js',__FILE__),array('jquery','jquery-ui-core','jquery-ui-sortable','jquery-ui-dialog','jquery-form','jquery-ui-draggable'));
				wp_enqueue_script('wpcuequiz-front');
				wp_localize_script('wpcuequiz-front','wpcuebasicquizajax',array('ajaxurl' => admin_url('admin-ajax.php')));
			}
			
		}
		/**
		* Add Grade Group to Quiz
		*/
		public function addgrade_group(){
			$quizid=$_POST['quizid'];
			$gradegroup=$_POST['gradegroup'];
			if(add_post_meta($quizid,'quizgrade',$gradegroup,true)){
				echo json_encode(array('msg'=>'success'));
			}else{
				echo json_encode(array('msg'=>'failed'));
			}
			die();
		}
		public function save_quizcategory(){
			check_ajax_referer('wpprocue-wpcuebasicquizcat-nyspecial','quizcatnonce');
			$quizcategory=$_POST['quizcategory'];
			$parentcategory=$_POST['parentcategory'];
			if($parentcategory != -1){
				$quizcat=wp_insert_term($quizcategory,'wpcuebasicquizcat',array('parent'=>$parentcategory));
			}else{
				$quizcat=wp_insert_term($quizcategory,'wpcuebasicquizcat');
			}
			if(is_wp_error($quizcat)){
			echo json_encode(array('msg'=>'failed'));
			}else{
				$returnval='<li id="wpcuebasicquizcat-'.$quizcat['term_id'].'"><label class="selectit"><input value="'.$quizcat['term_id'].'" type="checkbox" name="tax_input[wpcuebasicquizcat][]" id="in-wpcuebasicquizcat-'.$quizcat['term_id'].'" checked/>'.$quizcategory.'</label></li>';
				echo json_encode(array('msg'=>'success','returnval'=>$returnval));
			}
			die();
		}
	
		/**
		* Remove Grade Group from Quiz
		*/
		public function remgrade_group(){
			$quizid=$_POST['quizid'];
			if(delete_post_meta($quizid,'quizgrade')){
				echo json_encode(array('msg'=>'success'));
			}else{echo json_encode(array('msg'=>'failed'));}
			die();
		}
		public static function getfinal_content($quizid,$instanceid,$content,$quizlast,$quizmeta,$post_title,$totalquestions,$totalpoint,$emailprocess=false,$adminemailsubj=false,$adminemail=false,$useremailsubject=false,$useremail=false,$report=false,$emailreport=false,$grade=false,$gradedesc=false,$certi=false){
			$currentuser=wp_get_current_user();
			$userid=$currentuser->ID;
			$basicsetting=maybe_unserialize($quizmeta['basicsetting'][0]);
			if(!empty($grade)){
				$gradegroupid=$quizmeta['quizgrade'][0];$gradegroup=get_post($gradegroupid);$gradegroupcontent=unserialize($gradegroup->post_content);$gradeid=$quizlast['grade'];
				$grade=$gradegroupcontent[$gradeid]['title'];$gradedesc=$gradegroupcontent[$gradeid]['content'];
			}
			if(isset($basicsetting['duration'])){$timeused=$basicsetting['duration']-($quizlast['timeremaining']);}else{$timeused='';}
			global $wpdb;
			$table_name=$wpdb->prefix.'wpcuequiz_quizstatinfo';$table_name1=$wpdb->prefix.'wpcuequiz_quizstat';$table_name3=$wpdb->prefix.'wpcuequiz_quizinfo';
			$userstat=$wpdb->get_results($wpdb->prepare("SELECT a.status,sum(a.point) as point,count(a.id) as counter from $table_name a,$table_name1 b where b.quizid=%d and a.instanceid=b.instanceid and b.userid != %d and a.status != -1 group by a.status",$quizid,$userid),ARRAY_A);
			$instances=$wpdb->get_var($wpdb->prepare("SELECT count(instanceid) as instances from $table_name1 where instanceid != %d and quizid=%d and userid != %d",$instanceid,$quizid,$userid));
			if(!(empty($instances))){$correctpoint=0;if(!empty($userstat[1])){$correctpoint+=$userstat[1]['point'];}if(!(empty($userstat[2]))){$correctpoint+=$userstat[2]['point'];}$avgpoint=($correctpoint)/$instances;
			if(!empty($userstat[1])){$avgcor=($userstat[1]['counter']*100)/($instances*$totalquestions);}else{$avgcor=0;}}else{$avgpoint=0;$avgcor=0;}
			$quizstat=$wpdb->get_results($wpdb->prepare("SELECT status,sum(point) as point,count(*) as counter from $table_name where instanceid=%d group by status",$instanceid),OBJECT_K);
			$pattern=array('/%%CORRECT%%/','/%%PARTCORRECT%%/','/%%TOTAL%%/','/%%POINTS%%/','/%%MAXPOINTS%%/','/%%GRADE%%/','/%%GDESC%%/','/%%QUIZNAME%%/','/%%UNTRIED%%/','/%%WRONG%%/','/%%DATE%%/','/%%EMAIL%%/','/%%USERNAME%%/','/%%AVGPOINTS%%/','/%%AVGCORRECT%%/','/%%TIMEALLOWED%%/','/%%TIMEUSED%%/','/%%PERCENTPOINT%%/','/%%PERCENTQUEST%%/','/%%REPORT%%/');
			if(!(empty($quizstat[1])) && !(empty($quizstat[2]))){$point=($quizstat[1]->point+$quizstat[2]->point);}
			if(empty($point)){$point=0;}
			if(!empty($quizstat[4])){$progquest=(($totalquestions-$quizstat[4]->counter)*100)/$totalquestions;}else{$progquest=0;}
			$progpoint=$wpdb->get_var($wpdb->prepare("select sum(a.point) as point from $table_name3 a,$table_name b where a.entityid=b.entityid and b.status IN (0,1,2) and a.quizid=%d and b.instanceid=%d",$quizid,$instanceid));
			if(!(empty($quizstat[1]))){$correct=$quizstat[1]->counter;}else{$correct=0;}
			if(!(empty($quizstat[2]))){$partcorrect=$quizstat[2]->counter;}else{$partcorrect=0;}
			if(!(empty($quizstat[4]))){$untried=$quizstat[4]->counter;}else{$untried=0;}
			if(!(empty($quizstat[0]))){$wrong=$quizstat[0]->counter;}else{$wrong=0;}
			$replace=array($correct,$partcorrect,$totalquestions,$point,$totalpoint,$grade,$gradedesc,$post_title,$untried,$wrong,$quizlast['endtime'],$currentuser->user_email,$currentuser->user_login,$avgpoint,$avgcor,$basicsetting['duration'],$timeused,$progpoint.' %',$progquest.' %',$report);
			$contentarray=array();
			$certificatelink=strpos($content,'%%CERTIFICATELINK%%');
			$certificate=strpos($content,'%%CERTIFICATE%%');
			if(($certificatelink != false)||($certificate != false)){
				$permalink=get_site_url().'/wpcuecertificate/'.$instanceid.'/';
				if($certificatelink != false){
					$content=str_replace('%%CERTIFICATELINK%%','<a href="'.$permalink.'">here</a>',$content);
				}
				if($certificate != false){
					$content=str_replace('%%CERTIFICATE%%','<object width="100%" height="200px" data="'.$permalink.'"></object>',$content);
				}
			}
			$contentarray[0]=do_shortcode(preg_replace($pattern,$replace,$content));
			if(!(empty($emailprocess))){
				$emailreplace=array($correct,$partcorrect,$totalquestions,$point,$totalpoint,$grade,$gradedesc,$post_title,$untried,$wrong,$quizlast['endtime'],$currentuser->user_email,$currentuser->user_login,$avgpoint,$avgcor,$basicsetting['duration'],$timeused,$progpoint.' %',$progquest.' %',$emailreport);
				$contentarray[1]=preg_replace($pattern,$emailreplace,$adminemailsubj);
				$contentarray[2]=preg_replace($pattern,$emailreplace,$adminemail);
				$contentarray[3]=preg_replace($pattern,$emailreplace,$useremailsubject);
				$contentarray[4]=preg_replace($pattern,$emailreplace,$useremail);
			}
			return $contentarray;
		}
		public function wpcue_proquiz_final_result(){
			ob_start();
			global $wpdb;
			$wpprocuesetting=$this->wpprocuesetting;
			$table_name = $wpdb->prefix.'wpcuequiz_quizstat';$quizinfotable=$wpdb->prefix.'wpcuequiz_quizinfo';
			if(get_magic_quotes_gpc() || function_exists('wp_magic_quotes')){
			$myformdata=stripslashes($_POST['myformdata']);
			}else{$myformdata=$_POST['myformdata'];}
			parse_str($myformdata,$output);
			$action=$_POST['quizaction'];
			if(!(empty($_POST['matchquestres']))){$matchquestres=$_POST['matchquestres'];}
			$questids=$output['questionid'];
			$current_user = wp_get_current_user();
			$user_ID = $current_user->ID;
			$quizid=intval($output['quizid']);
			$args = array('post__in'=>$questids,'post_type'=>array('wpcuebasicsection','wpcuebasicquestion'),'orderby'=>'post__in','posts_per_page' => -1);
			$entityquery = new WP_Query($args);
			$flippedquestid=array_flip($questids);
			$quizmeta=get_post_custom($quizid);
			$basicsetting=maybe_unserialize($quizmeta['basicsetting'][0]);
			$report='';$emailreport='';$i=1;$j=1;
			if($action=='submit'){
				$report.='<ul class="quizreport">';	
			}
			$quiz=get_post($quizid);$quizstatinfo='';
			$quiztitle=$quiz->post_title;
			$postauthor=intval($quiz->post_author);
			$instanceid=$_POST['instanceid'];$percent=0;$pointscored=0;$errors=array();
			$errortable=$wpdb->prefix.'wpcuequiz_quizerrorinfo';
			if((!(empty($basicsetting['notifyadmin']))) || (!(empty($basicsetting['notifyuser'])))){$emailreport.='<table>';}
			$totalent=$entityquery->found_posts;
			while ($entityquery->have_posts()){
				$entityquery->the_post();
				$entitypost=$entityquery->post;
				$point=0;
				if($entitypost->post_type=='wpcuebasicsection'){
					$quizstatar=array($instanceid,$entitypost->ID,'NULL','NULL',0,-1,0);
					$quizstatinfo.='('.implode(',',$quizstatar).')';
					if($action=='submit'){
						$report.='<li>'.$entitypost->post_content.'</li>';	
					}
					$i--;
				}elseif($entitypost->post_type =='wpcuebasicquestion'){
					$entitymeta=maybe_unserialize($entitypost->post_content);
					if(!empty($output['errorid-'.$entitypost->ID])){
						$errorids=$output['errorid-'.$entitypost->ID];
						foreach($errorids as $errorid){
							$addedstatus=$output['erroraddedstatus-'.$errorid];
							$editstatus=$output['erroreditedstatus-'.$errorid];
							$errortitle=$output['errortitle-'.$errorid];
							$errordesc=$output['errordesc-'.$errorid];
							if(empty($addedstatus)){
								$errorid=wp_insert_post(array('post_title'=>$errortitle,'post_content'=>$errordesc,'post_type'=>'wpcuebasicerror','post_status'=>'publish','post_author'=>$user_ID));
								if(!(empty($errorid))){
									$wpdb->insert($errortable,array('instanceid'=>$instanceid,'quizid'=>$quizid,'errorid'=>$errorid,'entityid'=>$entitypost->ID,'status'=>0),array('%d','%d','%d','%d'));
								}
							}else{
								if(!(empty($editstatus))){wp_update_post(array('ID'=>$errorid,'post_content'=>$errordesc,'post_title'=>$errortile));}
							}
						}
					}
					$disabled=$output['disablestatus-'.$entitypost->ID];
					switch($entitymeta['t']){
					
					case 1:
						
						if(!(empty($output['answer-'.$entitypost->ID]))){
							if(!(empty($entitymeta['c']))){
								if($entitymeta['c']==$output['answer-'.$entitypost->ID]){
									$percent+=100;$pointscored+=$entitymeta['p'];
									$point=$entitymeta['p'];
									$correct=1;
								}else{$correct=0;$point=0;}
							}else{$point=0;$correct=3;}
							$reply=$output['answer-'.$entitypost->ID];
						}else{$reply=NULL;$point=0;$correct=4;}
						$answer=$output['allanswer-'.$entitypost->ID];
						$quizstatinfo.="(".$instanceid.",".$entitypost->ID.",'".$answer."','".$reply."',".$point.",".$correct.",".$disabled.")";
						break;
					case 2:
						if(!(empty($output['answer-'.$entitypost->ID]))){
							if(!(empty($entitymeta['c']))){
							if(empty($entitymeta['partialpoint'])){
								$misans=array_diff($entitymeta['c'],$output['answer-'.$entitypost->ID]);
								if(empty($misans)){
									$point=$entitymeta['totalpoint'];$correct=1;
									$pointscored+=$point;$percent+=100;
								}else{$correct=0;$point=0;}
							}else{
								$answers=array_intersect($output['answer-'.$entitypost->ID],$entitymeta['c']);
								if(!(empty($answers))){
									$entperc=(count($answers)/count($entitymeta['c']))*100;
									$percent+=$entperc;
									foreach($answers as $answer){$point+=$entitymeta['p'][$answer];}
									$pointscored+=$point;
									if(count($answers) == count($entitymeta['c'])){$correct=1;}else{$correct=2;}
								}else{$correct=0;}
							}}else{$point=0;$correct=3;}
							$reply=serialize($output['answer-'.$entitypost->ID]);
						}else{$reply=NULL;$point=0;$correct=4;}
						$answer=$output['allanswer-'.$entitypost->ID];
						$quizstatinfo.="(".$instanceid.",".$entitypost->ID.",'".$answer."','".$reply."',".$point.",".$correct.",".$disabled.")";
						break;
					case 3:
						$column=$output['colmatchquestion-'.$entitypost->ID];
						$matchcount=$output['matchcount-'.$entitypost->ID];
						$answerar['la']=unserialize($output['lanswerids-'.$entitypost->ID]);
						$answerar['ra']=unserialize($output['ranswerids-'.$entitypost->ID]);
						if(!(empty($output['markmatchquestion']))){
							if($column=='rightcolumn'){
								$reply=$rightarray=$output['ranswer-'.$entitypost->ID];
								$leftarray=array_slice($answerar['la'],0,$matchcount );
							}else{
								$reply=$leftarray=$output['lanswer-'.$entitypost->ID];
								$rightarray=array_slice($answerar['ra'],0,$matchcount );
							}
							$combreply=array_combine($leftarray,$rightarray);
							if(!(empty($entitymeta['coranswer']))){
								$misans=array_diff_assoc($entitymeta['coranswer'],$combreply);
								if($entitymeta['partialpoint']==0){
									if(empty($misans)){
										$point=$entitymeta['p'];$percent+=100;$pointscored+=$point;
										$correct=1;
									}else{$correct=0;$point=0;}
								}else{
									$cor=(count($entitymeta['coranswer'])-count($misans))/count($entitymeta['coranswer']);
									$point=$cor*$entitymeta['p'];$pointscored+=$point;$percent+=$cor*100;
									if($cor==count($entitymeta['coranswer'])){$correct=1;}elseif($cor !=0 ){$correct=2;}else{$correct=0;}
								}	
							}else{$point=0;$correct=3;}
							$reply=serialize($reply);
						}else{$reply=NULL;$point=0;$correct=4;}
						$answerar['column']=$column;
						$answerar['count']=$matchcount;
						$answer=serialize($answerar);
						$quizstatinfo.="(".$instanceid.",".$entitypost->ID.",'".$answer."','".$reply."',".$point.",".$correct.",".$disabled.")";
						break;
					case 4:
						if(!(empty($output['sortquestionstatus']))){
						if(!(empty($entitymeta['coranswer']))){
							$misans=array_diff_assoc($entitymeta['coranswer'],$output['answer-'.$entitypost->ID]);
							if($entitymeta['partialpoint']==0){
								if(empty($misans)){$point=$entitymeta['p'];$correct=1;$percent+=100;$pointscored+=$point;}else{$correct=0;$point=0;}
								}else{
									$cor=(count($entitymeta['coranswer'])-count($misans))/count($entitymeta['coranswer']);
									$point=$cor*$entitymeta['p'];$pointscored+=100;$percent+=$cor*100;
									if($cor == count($entitymeta['coranswer'])){$correct=1;}elseif($cor != 0){$correct=2;}else{$correct=0;}
								}	
							}else{$point=0;$correct=3;}
							$reply=serialize($output['answer-'.$entitypost->ID]);
						}else{$reply=NULL;$point=0;$correct=4;}
						$answer=$output['answerids-'.$entitypost->ID];
						$quizstatinfo.="(".$instanceid.",".$entitypost->ID.",'".$answer."','".$reply."',".$point.",".$correct.",".$disabled.")";
						break;
					case 5:
						if(!(empty($output['answer-'.$entitypost->ID])) && (!in_array("",$output['answer-'.$entitypost->ID]))){
							if(!(empty($entitymeta['c']))){
								$result = array_udiff_assoc($entitymeta['c'],$output['answer-'.$entitypost->ID], 'strcasecmp');
								if($entitymeta['partialpoint']==0){
									if(empty($result)){$point=$entitymeta['p'];$correct=1;$pointscored+=$point;$percent+=100;}else{$point=0;$correct=0;}
								}else{
									$cor=(count($entitymeta['c'])-count($result))/count($entitymeta['c']);
									$point=$cor*$entitymeta['p'];$pointscored+=$point;$percent+=$cor*100;
									if($cor == count($entitymeta['coranswer'])){$correct=1;}elseif($cor != 0){$correct=2;}else{$correct=0;}
								}
							}else{$point=0;$correct=3;}
							$reply=serialize($output['answer-'.$entitypost->ID]);
						}else{$reply=NULL;$point=0;$correct=4;}
						if(!(empty($entitymeta['c']))){$answer=serialize($entitymeta['c']);}else{$answer=NULL;}
						$quizstatinfo.="(".$instanceid.",".$entitypost->ID.",'".$answer."','".$reply."',".$point.",".$correct.",".$disabled.")";
						break;
					case 6:
						if(isset($output['answer-'.$entitypost->ID])){
							if(isset($entitymeta['c'])){
								if($output['answer-'.$entitypost->ID]==$entitymeta['c']){
									$point=$entitymeta['p'];$correct=1;$pointscored+=$point;$percent+=100;}else{$correct=0;$point=0;}
							}else{$point=0;$correct=3;}
							$reply=$output['answer-'.$entitypost->ID];
						}else{$reply=NULL;$point=0;$correct=4;}
						$answer=$output['answerids-'.$entitypost->ID];
						$quizstatinfo.="(".$instanceid.",".$entitypost->ID.",'".$answer."','".$reply."',".$point.",".$correct.",".$disabled.")";
						break;
					}
					if($action=='submit'){
						$report.=$this->wpcue_report($answer,$reply,$correct,$entitymeta,$basicsetting['discloseans'],$i,0);
						if((!(empty($basicsetting['notifyadmin']))) || (!(empty($basicsetting['notifyuser'])))){
							$emailreport.=$this->wpcue_report($answer,$reply,$correct,$entitymeta,$basicsetting['discloseans'],$i,1);
						}
					}
					
				}
				if(($totalent>1) && ($j<$totalent)){$quizstatinfo.=',';}
				$i++;$j++;	
			}
			$wpdb->delete($wpdb->prefix.'wpcuequiz_quizstatinfo',array('instanceid'=>$instanceid),array('%d'));
			$table_name1=$wpdb->prefix.'wpcuequiz_quizstatinfo';
			$status=$wpdb->query("INSERT INTO $table_name1 (instanceid,entityid,answer,reply,point,status,disabled) VALUES $quizstatinfo");
			if($action=='submit'){
				$report.='</ul>';
				if((!(empty($basicsetting['notifyadmin']))) || (!(empty($basicsetting['notifyuser'])))){$emailreport.='</table>';}	
			}
			$currtime=$wpdb->get_row("select NOW() as curtime from $table_name");
			$now = new DateTime($currtime->curtime);
			$datesent=$now->format('Y-m-d H:i:s');
			$timeremaining=(int)$_POST['timeremaining'];
			$error=0;
			$quizinfo=$this->quizinfo($quizid);
			$percent=($percent/$quizinfo['totalquestions']);
			if(!(empty($quizmeta['quizgrade'][0]))){$gradedef=$quizmeta['quizgrade'][0];}
			if($action=='submit'){
				$grade='';
				$gradedesc='';
				$certi=0;$assignedgradeid='';
				if(!(empty($gradedef))){
					$gradepost=get_post($gradedef);
					$grademeta=unserialize($gradepost->post_content);
					if($grademeta['gradebase']==1){
						foreach($grademeta['gradeid'] as $gradeid){
							if($pointscored >= intval($grademeta[$gradeid]['gradebasefrom']) && $pointscored < intval($grademeta[$gradeid]['gradebaseto'])){
								$grade=$grademeta[$gradeid]['title'];
								$gradedesc=$grademeta[$gradeid]['content'];
								$certi=$grademeta[$gradeid]['certi'];
								$assignedgradeid=$gradeid;
								break;
							}
						}
					}else{
						foreach($grademeta['gradeid'] as $gradeid){
							if($percent >= intval($grademeta[$gradeid]['gradebasefrom']) && $percent < intval($grademeta[$gradeid]['gradebaseto'])){
								$grade=$grademeta[$gradeid]['title'];
								$gradedesc=$grademeta[$gradeid]['content'];
								$certi=$grademeta[$gradeid]['certi'];
								$assignedgradeid=$gradeid;
								break;
							}
						}
					}
				}
			}
			if($action == 'submit'){
				if(empty($grade)){$grade='';}if(empty($certi)){$certi=0;} 
					$status=$wpdb->update($table_name,array('endtime'=>$datesent,'status'=>1,'timeremaining'=>$timeremaining,'certificate'=>$certi,'grade'=>$assignedgradeid)
					,array('instanceid'=>$instanceid),array('%s','%d','%d','%d','%s'),array('%d'));
					if(!($status)){$error=1;}
			}else{
				$status=$wpdb->update($table_name,array('endtime'=>$datesent,'status'=>0,'timeremaining'=>$timeremaining),array('instanceid'=>$instanceid),array('%s','%d','%d'),array('%d'));
				if($status === false){$error=1;}
			}
			if(($error == 0) &&($action=='submit')){
				
				if((!(empty($basicsetting['notifyadmin']))) || (!(empty($basicsetting['notifyuser'])))){$emailprocess=1;}else{$emailprocess=0;}
				if(!(empty($emailprocess))){if(!(empty($basicsetting['notifyadmin']))){
					$adminemail=maybe_unserialize($quizmeta['quizadminemail'][0]);
					if(!(empty($adminemail['subject']))){
						$adminemailsubj=$adminemail['subject'];
					}else{
						$adminemailsubj='New Quiz Result';
					}
					if(!(empty($adminemail['mail']))){
						$adminemail=$adminemail['mail'];
					}else{$adminemail='User '.$user_ID.' has just taken quiz '.$quizid;}
				}
				if(!(empty($basicsetting['notifyuser']))){
					$useremail=maybe_unserialize($quizmeta['quizuseremail'][0]);
					if(!(empty($useremail['subject']))){
						$useremailsubject=$useremail['subject'];
					}else{
						$useremailsubject='New Quiz Result';
					}
					if(!(empty($useremail['mail']))){
						$useremail=$useremail['mail'];
					}
				}}else{$adminemailsubj='';$adminemail='';$useremailsubject='';$useremail='';}
				if(!(empty($emailprocess))){
					$adminemailrep=strpos($adminemail,'%%REPORT%%');
					$useremailrep=strpos($useremail,'%%REPORT%%');
				}
				$quizlast=$wpdb->get_row($wpdb->prepare("select * from $table_name where instanceid=%d",$instanceid),ARRAY_A );
				$contentarray=$this->getfinal_content($quizid,$instanceid,$quizmeta['quizfinal'][0],$quizlast,$quizmeta,$quiztitle,$quizinfo['totalquestions'],$quizinfo['totalpoint'],$emailprocess,
				$adminemailsubj,$adminemail,$useremailsubject,$useremail,$report,$emailreport,$grade,$gradedesc);
				if(!(empty($emailprocess))){
					if(!(empty($basicsetting['notifyadmin']))){
						$adminemailsubj=$contentarray[1];
						$adminemail=$contentarray[2];
						add_filter('wp_mail_content_type',array(&$this,'wpcuemail_set_content_type'));
						wp_mail($wpprocuesetting['basic']['adminemail'],$adminemailsubj,$adminemail);
						remove_filter('wp_mail_content_type',array(&$this,'wpcuemail_set_content_type'));
					}
					if(($user_ID != 0) && (!(empty($basicsetting['notifyuser'])))){
						$useremailsubject=$contentarray[3];
						$useremail=$contentarray[4];
						add_filter('wp_mail_content_type',array(&$this,'wpcuemail_set_content_type'));
						wp_mail($current_user->user_email,$useremailsubject,$useremail);
						remove_filter('wp_mail_content_type',array(&$this,'wpcuemail_set_content_type'));
					}
				}	
				$finaldesc='<div id="quizfinalcontent">'.$contentarray[0].'</div>';
				$socialdescstat=strpos($contentarray[0],'%%SOCIALSHARE%%');
				if($socialdescstat){
					$socialshare='<iframe src="http://www.facebook.com/plugins/like.php?href='.get_permalink($quizid).'&width&layout=button_count&action=like&show_faces=false&share=false&height=35&appId=" frameBorder="0" width="150" height="25">
</iframe>';
					$socialshare.="<div class='wpcue-twitshare'><a href='".get_permalink($quizid)."' class='twitter-share-button' data-text='anything' data-count='none'>t</a>";
					$socialshare.="<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script></div>";
					$finaldesc=str_replace('%%SOCIALSHARE%%',$socialshare,$finaldesc);
				}	
				echo $finaldesc;
				
			}
			echo ob_get_clean();
			die();
		}
		public function wpcuemail_set_content_type(){
			return "text/html";
		}
		public function wpcue_report($answer,$reply,$status,$questmeta,$discloseans,$questnum,$emailprocess){
			$answer=maybe_unserialize($answer);$reply=maybe_unserialize($reply);
			
			$report='';
			if(!isset($discloseans)){
				$discloseans=0;
			}
			$emailprocess=0;// to delete later on
			$bgcolor=array('correctansreplied'=>'#008800','wrongansreplied'=>'#FF0000','ansreplied'=>'#ffff00','correctans'=>'#0000ff');
			switch($questmeta['t']){
				case 1:
					if(empty($emailprocess)){
						$report.='<li>Q. '.$questnum.' '.$questmeta['desc'].'<ul class="reportans singlerepans">';
					}else{
						$report.='<tr><td>Q. '.$questnum.'</td><td>'.$questmeta['desc'].'</td></tr>';
					}
					foreach($answer as $key=>$answerid){
						$class='';
						switch($discloseans){
							case 0:
								if(($status != 4)&&($reply==$answerid)){$class="ansreplied";}
								break;
							case 1:
								switch($status){
									case 0:
										if($answerid==$reply){$class="wrongansreplied";}elseif($answerid==$questmeta['c']){$class="correctans";}
										break;
									case 1:
										if($answerid==$reply){$class="correctansreplied";}
										break;
									case 3:
										if($answerid==$reply){$class="ansreplied";}
										break;
									case 4:
										if(!empty($questmeta['c']) && ($answerid==$questmeta['c'])){$class="correctans";}
										break;
								}
								break;
							case 2:
								if($status !=4){
								if($answerid==$reply){
									switch($status){
										case 0:
											$class="wrongansreplied";
											break;
										case 1:
											$class="correctansreplied";
											break;
										case 3:
											$class="ansreplied";
											break;
									}
								}elseif(!empty($questmeta['c']) && ($answerid==$questmeta['c'])){$class="correctans";}
								}
								break;
							case 3:
								if($answerid==$reply){if($status==1){$class="correctansreplied";}elseif($status !=4){$class="ansreplied";}}
								break;
									
						}
						if(empty($emailprocess)){
							$report.='<li ';
							$report.='class="'.$class.'"';
							$report.='>'.$questmeta['a'][$answerid]['desc'];
							$report.='</li>';
						}else{
							$report.='<tr><td></td><td><table><tr><td ';
							if(!empty($class)){$report.='bgcolor="'.$bgcolor[$class].'"';}
							$report.='><table><tr><td>'.($key+1).'. </td><td>'.$questmeta['a'][$answerid]['desc'].'</td>';
							if($class=='correctansreplied'){
								$report.= '<td><img src="../img/correctans.png"></td>';
							}elseif($class=='wrongansreplied'){
								$report.= '<td><img src="../img/wrongans.png"></td>';
							}else{$report.='<td></td>';}
							$report.= '</tr></table></td></tr></table></td></tr>';
						}
					}
					if(empty($emailprocess)){$report.='</ul></li>';}
					break;
				case 2:
					if(empty($emailprocess)){
						$report.='<li>Q. '.$questnum.' '.$questmeta['desc'].'<ul class="reportans multiplerepans">';
					}else{
						$report.='<tr><td>Q. '.$questnum.'</td><td>'.$questmeta['desc'].'</td></tr>';
					}
					foreach($answer as $key=>$answerid){
						$class='';
						switch($discloseans){
							case 0:
								if(($status != 4)&&(in_array($answerid,$reply))){$class="ansreplied";}
								break;
							case 1:
								switch($status){
									case 0:
										if(in_array($answerid,$reply)){$class="wrongansreplied";}elseif(in_array($answerid,$questmeta['c'])){$class="correctans";}
										break;
									case 1:
										if(in_array($answerid,$reply)){$class="correctansreplied";}
										break;
									case 2:
										if(in_array($answerid,$reply)){if(in_array($answerid,$questmeta['c'])){$class="correctansreplied";}else{$class="wrongansreplied";}}elseif(in_array($answerid,$questmeta['c'])){$class="correctans";}
										break;
									case 3:
										if(in_array($answerid,$reply)){$class="ansreplied";}
										break;
									case 4:
										if(!empty($questmeta['c']) && (in_array($answerid,$questmeta['c']))){$class="correctans";}
										break;
								}
								break;
							case 2:
								if($status !=4){
									if(in_array($answerid,$reply)){
										switch($status){
											case 0:
												if(in_array($answerid,$questmeta['c'])){$class="correctansreplied";}else{$class="wrongansreplied";}
												break;
											case 1:
												$class="correctansreplied";
												break;
											case 2:
												if(in_array($answerid,$questmeta['c'])){$class="correctansreplied";}else{$class="wrongansreplied";}
												break;
											case 3:
												$class="ansreplied";
												break;
										}
									}elseif(!empty($questmeta['c']) && (in_array($answerid,$questmeta['c']))){$class="correctans";}
								}
								break;
							case 3:
								if(($status !=4) && in_array($answerid,$reply)){if($status==1){$class="correctansreplied";}elseif($status !=4){$class="ansreplied";}}
								break;
									
						}
						if(empty($emailprocess)){
							$report.='<li ';
							$report.='class="'.$class.'"';
							$report.= '>'.$questmeta['a'][$answerid]['desc'].'</li>';
						}else{
							$report.='<tr><td></td><td><table><tr><td';
							if(!empty($class)){$report.='bgcolor="'.$bgcolor[$class].'"';}
							$report.='><table><tr><td>'.($key+1).'. </td><td>'.$questmeta['a'][$answerid]['desc'];
							$report.='</td>';
							if($class=='correctansreplied'){
								$report.= '<td><img src="../img/correctans.png"></td>';
							}elseif($class=='wrongansreplied'){
								$report.= '<td><img src="../img/wrongans.png"></td>';
							}else{$report.='<td></td>';}
							$report.= '</tr></table></td></tr></table></td></tr>';
						}
					
					}
					if(empty($emailprocess)){$report.='</ul></li>';}
					break;
				case 3:
					if(empty($emailprocess)){
						$report.='<li>Q. '.$questnum.' '.$questmeta['desc'].'<ul class="matchleftans">';
					}else{
						$report.='<tr><td>Q. '.$questnum.'</td><td>'.$questmeta['desc'].'</td></tr>';
					}
					foreach($answer['la'] as $key=>$answerid){
						if(empty($emailprocess)){$report.='<li>'.$questmeta['la'][$answerid]['desc'].'</li>';
						}else{$report.='<tr><td></td><td><table><tr><td>'.$key.'</td><td>'.$questmeta['la'][$answerid]['desc'].'</td></tr></table></d></tr>';}
					}
					if(empty($emailprocess)){$report.='</ul><ul class="matchrightans">';}
					foreach($answer['ra'] as $key=>$answerid){
						if(empty($emailprocess)){$report.='<li>'.$questmeta['ra'][$answerid]['desc'].'</li>';
						}else{$report.='<tr><td></td><td><table><tr><td>'.$key.'</td><td>'.$questmeta['ra'][$answerid]['desc'].'</td></tr></table></d></tr>';}
					}
					if(empty($emailprocess)){$report.='</ul>';}
					if($status != 4){
						if(empty($emailprocess)){$report.='<div class="matchrepans"><h3>Replied Answer</h3><ul class="matchrepans">';
						}else{$report.='<tr><td></td><td><table><tr><td>Replied Answer</td><td></td></tr>';}
						foreach($reply as $key=>$answerid){
							if(empty($emailprocess)){
								if($answer['column']=='rightcolumn'){
									$report.='<li>'.$questmeta['ra'][$answerid]['desc'].'</li>';
								}else{
									$report.='<li>'.$questmeta['la'][$answerid]['desc'].'</li>';
								}
							}else{
								if($answer['column']=='rightcolumn'){
									$report.='<tr><td>'.$key.'</td><td>'.$questmeta['ra'][$answerid]['desc'].'</td></tr>';
								}else{
									$report.='<tr><td>'.$key.'</td><td>'.$questmeta['la'][$answerid]['desc'].'</td></tr>';
								}
							}
						}
						if(empty($emailprocess)){$report.='</ul></div>';
						}else{$report.='</table></td></tr>';}
					}
					if(!empty($discloseans) && (!(empty($questmeta['coranswer'])))){
						$disstatus=1;
						if(($discloseans==3)&&($status !=1)){$disstatus=0;}
						if(!empty($disstatus)){
							if(empty($emailprocess)){$report.='<div class="matchcorans"><h3>Correct Answer</h3><ul class="matchcorans">';
							}else{$report.='<tr><td></td><td><table><tr><td>Correct Answer</td><td></td></tr>';}
							if($answer['column']=='rightcolumn'){$answerids=array_keys(array_flip($questmeta['coranswer']));
							}else{$answerids=array_keys($questmeta['coranswer']);}
							foreach($answerids as $key=>$answerid){
								if(empty($emailprocess)){
									if($answer['column']=='rightcolumn'){
										$report.='<li>'.$questmeta['ra'][$answerid]['desc'].'</li>';
									}else{
										$report.='<li>'.$questmeta['la'][$answerid]['desc'].'</li>';
									}
								}else{
									if($answer['column']=='rightcolumn'){
										$report.='<tr><td>'.$key.'</td><td>'.$questmeta['ra'][$answerid]['desc'].'</td></tr>';
									}else{
										$report.='<tr><td>'.$key.'</td><td>'.$questmeta['la'][$answerid]['desc'].'</td></tr>';
									}
								}
							}
						}
							if(empty($emailprocess)){$report.='</ul></div>';
							}else{$report.='</table></td></tr>';}
						
					}
					if(empty($emailprocess)){$report.='</li>';}
					break;
				case 4:
					if(empty($emailprocess)){
						$report.='<li>Q. '.$questnum.' '.$questmeta['desc'].'<ul class="sortans">';
					}else{
						$report.='<tr><td>Q. '.$questnum.'</td><td>'.$questmeta['desc'].'</td></tr>';
					}
					foreach($answer as $key=>$answerid){
						if(empty($emailprocess)){$report.='<li>'.$questmeta['a'][$answerid]['desc'].'</li>';
						}else{$report.='<tr><td></td><td><table><tr><td>'.($key+1).'</td><td>'.$questmeta['a'][$answerid]['desc'].'</td></tr></table></d></tr>';}
					}
					if(empty($emailprocess)){$report.='</ul>';}
					if($status != 4){
						if(empty($emailprocess)){$report.='<div class="sortrepans"><h3>Replied Ans</h3><ul class="sortrepans">';
						}else{$report.='<tr><td></td><td><table><tr><td>Replied Answer</td><td></td></tr>';}
						foreach($reply as $key=>$answerid){
							if(empty($emailprocess)){
								$report.='<li>'.$questmeta['a'][$answerid]['desc'].'</li>';
							}else{
								$report.='<tr><td>'.($key+1).'</td><td>'.$questmeta['a'][$answerid]['desc'].'</td></tr>';
							}
						}
						if(empty($emailprocess)){$report.='</ul></div>';
						}else{$report.='</table></td></tr>';}
					}
					if(!empty($discloseans) && !(empty($questmeta['coranswer']))){
						$disstatus=1;
						if(($discloseans==3)&&($status !=1)){$disstatus=0;}
						if(!empty($disstatus)){
						if(empty($emailprocess)){$report.='<div class="sortcorans"><h3>Correct Ans</h3><ul class="sortcorans">';
							}else{$report.='<tr><td></td><td><table><tr><td>Correct Answer</td><td></td></tr>';}
						foreach($questmeta['coranswer'] as $key=>$answerid){
							if(empty($emailprocess)){
								$report.='<li>'.$questmeta['a'][$answerid]['desc'].'</li>';
							}else{
								$report.='<tr><td>'.($key+1).'</td><td>'.$questmeta['a'][$answerid]['desc'].'</td></tr>';
							}
						}
						if(empty($emailprocess)){$report.='</ul></div>';}else{$report.='</table></td></tr>';}
						}
					}
					if(empty($emailprocess)){$report.='</li>';}
					break;
				case 5:
					if($status != 4){
						$i=0;$replacements=array();
						foreach($reply as $answer){
								$replacements[$i]='<span ';
								if(empty($emailprocess)){$replacements[$i].='class="fillgapsrep';}else{$replacements[$i].='style="border-bottom:1px dashed #000000;"';}
								if((!(empty($show))) && (empty($emailprocess))){
									if(in_array($answer,$questmeta['c'])){$replacements[$i].= ' correctgapsans';}else{$replacements[$i].= ' wronggapsans';}
								}
								$replacements[$i].='">'.$answer;
								if((!(empty($show))) && (!empty($emailprocess))){
									if(in_array($answer,$questmeta['c'])){$replacements[$i].= ' <img src="../img/correctans.png">';
									}else{$replacements[$i].= ' <img src="../img/wrongans.png">';}
								}
								$replacements[$i].='</span>';
								$i++;
						}
						$repcontent=preg_replace_callback('/\{\{\{(.*)\}\}\}/U',function($matches) use (&$replacements){
									return array_shift($replacements);}, $questmeta['desc']);
					}
					if(!(empty($discloseans)) && (!(empty($questmeta['c'])))){
						$i=0;
						$disstatus=1;
						if(($discloseans==3)&&($status !=1)){$disstatus=0;}
						if(!empty($disstatus)){
							foreach($questmeta['c'] as $answer){
								if(empty($emailprocess)){
									$replacements[$i]='<span class="correctgapans">'.$answer.'</span>';}
								else{$replacements[$i]='<span style="border-bottom:1px dashed #000000;">'.$answer.'</span>';}
								$i++;
							}
							$correctcontent=preg_replace_callback('/\{\{\{(.*)\}\}\}/U',function($matches) use (&$replacements){
									return array_shift($replacements);}, $questmeta['desc']);
						}
					}
					$questdesc=preg_replace('/\{\{\{(.*)\}\}\}/U','.......', $questmeta['desc']);
					if(empty($emailprocess)){
						$report.='<li>Q. '.$questnum.'  '.$questdesc.'<ul class="reportans gapsrepans">';
						if(!(empty($repcontent))){$report.='<li class="repgaplist">Replied Answer : '.$repcontent.'</li>';}
						if(!(empty($correctcontent))){$report.='<li class="correctgaplist">Correct Answer : '.$correctcontent.'</li>';}
						$report.='</ul></li>';
					}else{
						$report.='<tr><td>Q. '.$questnum.'</td><td>'.$questdesc.'</td></tr>';
						if(!(empty($repcontent))){$report.='<tr><td></td><td><table><tr><td>Replied Answer : </td><td>'.$repcontent.'</td></tr></table></td></tr>';}
						if(!(empty($correctcontent))){$report.='<tr><td></td><td><table><tr><td>Correct Answer : </td><td>'.$correctcontent.'</td></tr></table></td></tr>';}
					}
					
					break;
				case 6:
					if(empty($emailprocess)){
						$report.='<li>Q. '.$questnum.' '.$questmeta['desc'].'<ul class="reportans truefalserepans">';
					}else{
						$report.='<tr><td>Q. '.$questnum.'</td><td>'.$questmeta['desc'].'</td></tr>';
					}
					foreach($answer as $key=>$answerid){
						$class='';
						if($answerid==1){$ansdesc='True';}else{$ansdesc='False';}
						switch($discloseans){
							case 0:
								if(($status != 4)&&($reply==$answerid)){$class="ansreplied";}
								break;
							case 1:
								switch($status){
									case 0:
										if($answerid==$reply){$class="wrongansreplied";}elseif($answerid==$questmeta['c']){$class="correctans";}
										break;
									case 1:
										if($answerid==$reply){$class="correctansreplied";}
										break;
									case 3:
										if($answerid==$reply){$class="ansreplied";}
										break;
									case 4:
										if(!empty($questmeta['c']) && ($answerid==$questmeta['c'])){$class="correctans";}
										break;
								}
								break;
							case 2:
								if($status !=4){
								if($answerid==$reply){
									switch($status){
										case 0:
											$class="wrongansreplied";
											break;
										case 1:
											$class="correctansreplied";
											break;
										case 3:
											$class="ansreplied";
											break;
									}
								}elseif(!empty($questmeta['c']) && ($answerid==$questmeta['c'])){$class="correctans";}
								}
								break;
							case 3:
								if($answerid==$reply){if($status==1){$class="correctansreplied";}elseif($status !=4){$class="ansreplied";}}
								break;
									
						}
						if(empty($emailprocess)){
							$report.='<li ';
							$report.='class="'.$class.'"';
							$report.= '>'.$ansdesc;
							$report.= '</li>';
						}else{
							$report.='<tr><td></td><td><table><tr><td ';
							if(!empty($class)){$report.='bgcolor="'.$bgcolor[$class].'"';}
							$report.= '><table><tr><td>'.($key+1).'. </td><td>'.$ansdesc.'</td>';
							if($class=='correctansreplied'){
								$report.= '<td><img src="../img/correctans.png"></td>';
							}elseif($class=='wrongansreplied'){
								$report.= '<td><img src="../img/wrongans.png"></td>';
							}else{$report.='<td></td>';}
							$report.= '</tr></table></td></tr></table></td></tr>';
						}
					
					}
					if(empty($emailprocess)){$report.='</ul></li>';}
					break;
			}
			return $report;
		}
		public function quizinfo($quizid){
			global $wpdb;
			$quizinfotable=$wpdb->prefix.'wpcuequiz_quizinfo';
			$quizinfo=$wpdb->get_row($wpdb->prepare("select count(id) as totalquestions, sum(point) as totalpoint from $quizinfotable where quizid=%d and parentid != -1",$quizid),ARRAY_A);
			if(empty($quizinfo['totalquestions'])){$quizinfo['totalquestions']=0;}
			if(empty($quizinfo['totalpoint'])){$quizinfo['totalpoint']=0;}
			return $quizinfo;
		}
		
    } // END class WpCueBasicQuiz
} // END if(!class_exists('WpCueBasicQuiz'))
if(class_exists('WpCueBasicQuiz')){
add_action('wpcue_pro_report','instantiate_wpcuebasicquiz');
function instantiate_wpcuebasicquiz(){
$WpCueBasicQuiz=new WpCueBasicQuiz();
}
}
/* EOF */
