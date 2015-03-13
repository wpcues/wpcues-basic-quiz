<?php
if(!class_exists('WpCueBasicQuestion'))
{
    /**
     * A PostTypeTemplate class that provides 3 additional meta fields
     */
    class WpCueBasicQuestion
    {
        const POST_TYPE = "wpcuebasicquestion";
        public $questionid;
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
			add_action( 'wp_ajax_wpcuequizsavequestion_action', array(&$this,'save_question'));
			add_action('wp_ajax_wpcuequizeditquestion_action',array(&$this,'quiz_form'));
			add_action('wp_ajax_wpcuequizaddquestion_action',array(&$this,'quiz_form'));
			add_action('wp_ajax_wpcuequizaddinitialanswer_action',array(&$this,'addinitial_answer'));
			add_action('wp_ajax_wpcuequizaddsecondaryanswer_action',array(&$this,'addinitial_secondary'));
			add_action('wp_ajax_wpcuequizaddanswer_action',array(&$this,'add_answer'));
			add_action('wp_ajax_wpcuequizremovequestion_action',array(&$this,'ajaxremove_question'));
			add_action('wp_ajax_wpcuequizchangequestorder_action',array(&$this,'changeorder_question'));
			add_action('wp_ajax_wpcuequizchangeansorder_action',array(&$this,'changeorder_answer'));
			add_filter('get_edit_post_link',array(&$this,'edit_question_link'),10, 3);
			add_action('admin_head',array(&$this,'reset_post_new_link'));
		} // END public function init()

		/**
		* Create the post type
		*/
		public function create_post_type(){
			$labels = array(
			'name'               => _x( 'Questions', 'post type general name', 'wpcues-basic-quiz' ),
			'singular_name'      => _x( 'Question', 'post type singular name', 'wpcues-basic-quiz' ),
			'menu_name'          => _x( 'Questions', 'admin menu', 'wpcues-basic-quiz' ),
			'name_admin_bar'     => _x( 'Question', 'add new on admin bar', 'wpcues-basic-quiz' ),
			'add_new'            => _x( 'Add New', 'question', 'wpcues-basic-quiz' ),
			'add_new_item'       => __( 'Add New Question', 'wpcues-basic-quiz' ),
			'new_item'           => __( 'New Question', 'wpcues-basic-quiz' ),
			'edit_item'          => __( 'Edit Question', 'your-plugin-textidomain' ),
			'view_item'          => __( 'View Question', 'wpcues-basic-quiz' ),
			'all_items'          => __( 'All Questions', 'wpcues-basic-quiz' ),
			'search_items'       => __( 'Search Questions', 'wpcues-basic-quiz' ),
			'parent_item_colon'  => __( 'Parent Questions:', 'wpcues-basic-quiz' ),
			'not_found'          => __( 'No Questions found.', 'wpcues-basic-quiz' ),
			'not_found_in_trash' => __( 'No Questions found in Trash.', 'wpcues-basic-quiz' )
			);
		
		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => false,
			'capability_type'    => 'post',
			'show_ui'=>false,
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'editor', 'author','excerpt')
		);
		register_post_type(self::POST_TYPE,$args);
		$labels = array(
			'name'              => _x( 'Question Categories', 'taxonomy general name','wpcues-basic-quiz' ),
			'singular_name'     => _x( 'Question Category', 'taxonomy singular name','wpcues-basic-quiz' ),
			'search_items'      => __( 'Search Question Categories','wpcues-basic-quiz' ),
			'all_items'         => __( 'All Question Categories','wpcues-basic-quiz' ),
			'parent_item'       => __( 'Parent Question Category','wpcues-basic-quiz' ),
			'parent_item_colon' => __( 'Parent Question Category:','wpcues-basic-quiz' ),
			'edit_item'         => __( 'Edit Question Category','wpcues-basic-quiz' ),
			'update_item'       => __( 'Update Question Category','wpcues-basic-quiz' ),
			'add_new_item'      => __( 'Add New Question Category','wpcues-basic-quiz' ),
			'new_item_name'     => __( 'New Question Category Name','wpcues-basic-quiz' ),
			'menu_name'         => __( 'Question Category','wpcues-basic-quiz' ),
		);

		$arges = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'public'=>false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => false,
			'rewrite'           => array( 'slug' => 'questioncategory' ),
		);

		register_taxonomy('wpcuebasicquestcat','wpcuebasicquestion',$arges);
		
		}
		
		/**
		* Set question id
		*/
		public function set_questionid(){
			$post=get_default_post_to_edit(self::POST_TYPE,true);
			$this->questionid=$post->ID;
		}
		/**
		* Save the post
		*/
		public function save_post()
		{
			
		} // END public function save_post
		public function reset_post_new_link(){
			global $post_new_file,$post_type_object;
			if (!isset($post_type_object) || 'wpcuebasicquestion' != $post_type_object->name) return false;
			$post_new_file ='edit.php?post_type=wpcuebasicquiz&page=wpcuequiznewquestion';
		}
		public function edit_question_link($url,$post_id, $context ){
			global $typenow;
			if($typenow=='wpcuebasicquestion'){
				$action='&action=edit';
				$posting='&post='.$post_id;
				$url=admin_url(sprintf('edit.php?post_type=wpcuebasicquiz&page=wpcuequiznewquestion'. $action.$posting));
			}
			return $url;
		}
		
		/**
		* Ajax Handler to save Question
		*/
		public function save_question(){
			global $wpdb;
			ob_start();
			if(get_magic_quotes_gpc() || function_exists('wp_magic_quotes')){
			$myformdata=stripslashes($_POST['myformdata']);
			}else{$myformdata=$_POST['myformdata'];}
			parse_str($myformdata,$output);
			if(!empty($_POST['entityorder'])){$entityorder=(float)$_POST['entityorder'];}
			$questiondesc=wp_kses_post($output['newquestion']);
			if(isset($output['sectionid'])){$sectionid=intval($output['sectionid']);}
			if(isset($output['quizid'])){$quizid=intval($output['quizid']);}
			if(isset($output['entityid'])){$questionid=intval($output['entityid'][0]);}
			if(isset($output['instanceid'])){$instanceid=intval($output['instanceid'][0]);}
			if(isset($output['category'])){$prevcategoryid=intval($output['category'][0]);}
			if(isset($output['questionchanged'])){$questionchanged=$output['questionchanged'];}else{$questionchanged=0;}
			$questionstatus=intval($output['savequestion_status']);
			$newquestion=0;
			if(!(empty($quizid)) || (!(empty($sectionid)))){
				$poststatus=$output['original_post_status'];
				if($poststatus =='publish'){
					if(!(empty($questionid))){if($instanceid==$questionid){$newquestion=1;$inherited=1;}}else{$newquestion=1;}
				}else{if(empty($questionid)){$newquestion=1;}}
			}else{if(empty($questionid)){$newquestion=1;}}
			if(!empty($newquestion)){
				$question=get_default_post_to_edit(self::POST_TYPE,true);
				if(!(empty($questionid))){$prevquestionid=$questionid;$questionid=$question->ID;}else{$prevquestionid=$questionid=$question->ID;}
			}else{$prevquestionid=$questionid;$questionid=$instanceid;}
			$questmeta=array();
			$questmeta['t']=$output['origquestiontype'];
			if(empty($sectionid)){$questmeta['s']=$output['sectionvalues'];$sectionstatus=1;}else{$questmeta['s']=$sectionid;$sectionstatus=0;}
			$questmeta['qc']=intval($output['questcatform']);
			$questmeta['desc']=$questiondesc;
			if(in_array($questmeta['t'] ,array(1,2,3,4,7))){
				if($questmeta['t']==3){
					foreach($output['1answerid'] as $key=>$value){
						if($questionstatus){
							$answerid=$value;
							if($answerid==$output['1tabids'][$key]){
								$answerid=substr(str_shuffle(MD5(microtime())), 0, 10);
							}
						}else{
							$answerid=substr(str_shuffle(MD5(microtime())), 0, 10);
						}
						if(!(empty($questmeta['la']['id']))){
							array_push($questmeta['la']['id'],$answerid);
						}else{$questmeta['la']['id']=array($answerid);}
						$questmeta['la'][$answerid]['desc']=wp_kses_post($output['1answeredittab-'.$value]);
					}
					foreach($output['2answerid'] as $key=>$value){
						if($questionstatus){
							$answerid=$value;
							if($answerid==$output['1tabids'][$key]){
								$answerid=substr(str_shuffle(MD5(microtime())), 0, 10);
							}
						}else{
							$answerid=substr(str_shuffle(MD5(microtime())), 0, 10);
						}
						if(!(empty($questmeta['ra']['id']))){
							array_push($questmeta['ra']['id'],$answerid);
						}else{$questmeta['ra']['id']=array($answerid);}
						$questmeta['ra'][$answerid]['desc']=wp_kses_post($output['2answeredittab-'.$value]);
					}						
				}else{
					if($questmeta['t']==2){
						$coransarr=array();
					}
					$totalpoint=0;$questmeta['p']=array();
					foreach($output['1answerid'] as $key=>$value){
						if($questionstatus){
							$answerid=$value;
							if($answerid==$output['1tabids'][$key]){
								$answerid=substr(str_shuffle(MD5(microtime())), 0, 10);
							}
						}else{
							$answerid=substr(str_shuffle(MD5(microtime())), 0, 10);
						}
						if(!(empty($questmeta['a']['id']))){
							array_push($questmeta['a']['id'],$answerid);
						}else{$questmeta['a']['id']=array($answerid);}
						$questmeta['a'][$answerid]['desc']=wp_kses_post($output['1answeredittab-'.$value]);
						if($questmeta['t']==2){if((!(empty($output['coranswer'])))&& (in_array($output['1tabids'][$key],$output['coranswer']))){
							array_push($coransarr,$answerid);
							$questmeta['p'][$answerid]=$output['points-'.$output['1tabids'][$key]];
							$totalpoint+=$questmeta['p'][$answerid];
						}}elseif($questmeta['t']==1){
							if((!(empty($output['coranswer']))) && ($output['coranswer']==$output['1tabids'][$key])){
								$questmeta['c']=$answerid;
								$questmeta['p']=$output['points-'.$output['1tabids'][$key]];
								$totalpoint=$questmeta['p'];
							}
						}elseif($questmeta['t']==7){
							$questmeta['p'][$answerid]=$output['points-'.$output['1tabids'][$key]];
						}
					}
					if(empty($questmeta['p'])){$questmeta['p']=0;}
					if($questmeta['t']==2){$questmeta['c']=$coransarr;}
				}
			}
			switch($questmeta['t']){
				case 2:
					$questmeta['totalpoint']=$totalpoint;
					if(!empty($questmeta['c'])){
						if(!(empty($output['partialpoint']))){$questmeta['partialpoint']=$output['partialpoint'];}else{$questmeta['partialpoint']=0;}
					}else{$questmeta['partialpoint']=0;}
					break;
				case 3:
					if(!(empty($output['coranswer']))){
						$questmeta['c']=$output['coranswer'];
						$coranswer=str_getcsv($questmeta['c']);
						foreach($coranswer as $answergrp){
							$ans=explode(',',$answergrp);
							$questmeta['coranswer'][$questmeta['la']['id'][intval($ans[0])-1]]=$questmeta['ra']['id'][intval($ans[1])-1];
						}
						$totalpoint=$output['points'];
						$questmeta['p']=$output['points'];
						if(!(empty($output['partialpoint']))){$questmeta['partialpoint']=$output['partialpoint'];}else{$questmeta['partialpoint']=0;}
					}else{$totalpoint=0;$questmeta['p']=0;$questmeta['partialpoint']=0;}
					break;
				case 4:
					if(!(empty($output['coranswer']))){
						$questmeta['c']=$output['coranswer'];
						$coranswer=explode(',',$questmeta['c']);
						foreach($coranswer as $key=>$answer){
							$questmeta['coranswer'][$key]=$questmeta['a']['id'][intval($answer)-1];
						}
						$totalpoint=$output['points'];
						$questmeta['p']=$output['points'];
						if(!(empty($output['partialpoint']))){$questmeta['partialpoint']=$output['partialpoint'];}else{$questmeta['partialpoint']=0;}
					}else{$totalpoint=0;$questmeta['p']=0;$questmeta['partialpoint']=0;}
					break;
				case 5:
					preg_match_all('/\{\{\{(.*)\}\}\}/U',$questmeta['desc'],$matches);
					$questmeta['c']=$matches[1];
					if(!(empty($questmeta['c']))){
						$totalpoint=$output['points'];$questmeta['p']=$output['points'];
						if(!(empty($output['partialpoint']))){$questmeta['partialpoint']=$output['partialpoint'];}else{$questmeta['partialpoint']=0;}
					}else{$totalpoint=0;$questmeta['p']=0;$questmeta['partialpoint']=0;}
					break;
				case 6:
					if(isset($output['coranswer'])){$questmeta['c']=$output['coranswer'];$totalpoint=$output['points'];$questmeta['p']=$output['points'];
					}else{$totalpoint=0;$questmeta['p']=0;}
					break;
				
			}
			$questtitle=wpcues_basic_quiz::summary($questmeta['desc'],100,true);
			if(isset($output['anshint'])){$questmeta['anshint']=$output['anshint'];}
			if(isset($output['correctansdesc'])){$questmeta['correctansdesc']=$output['correctansdesc'];}
			if(!(empty($inherited))){
				$postid=$wpdb->update($wpdb->posts,array('post_title'=>$questtitle,'post_content'=>serialize($questmeta),'post_status'=>'inherit','post_parent'=>$prevquestionid),array('ID'=>$questionid),array('%s','%s','%s','%d'),array('%d'));
			}else{
				$postid=$wpdb->update($wpdb->posts,array('post_title'=>$questtitle,'post_content'=>serialize($questmeta),'post_status'=>'publish'),array('ID'=>$questionid),array('%s','%s','%s'),array('%d'));
			}
			if(empty($newquestion)){if(!(empty($prevcategoryid)) && ($prevcategoryid != -1)){wp_remove_object_terms($questionid,'questcategory');}}
			if($questmeta['qc'] != -1){wp_set_object_terms( $questionid, $questmeta['qc'], 'wpcuebasicquestcat');}
			if(!(empty($quizid)) || (!(empty($sectionid)))){
				if(empty($quizid)){$quizid=0;}
				if(empty($sectionid)){$sectionid=intval($questmeta['s']);}
				$questioncat=intval($questmeta['qc']);
				$table_name = $wpdb->prefix.'wpcuequiz_quizinfo';	
				if($newquestion){
					if($poststatus != 'publish'){
						$wpdb->query($wpdb->prepare("INSERT INTO $table_name (quizid,entityid,parentid,entityorder,category,point) VALUES (%d,%d,%d,%f,%d,%d)",$quizid,$questionid,$sectionid,$entityorder,$questioncat,$totalpoint));
						if(!(empty($quizid))){
							$totalpoint=$wpdb->get_results($wpdb->prepare("select sum(point) as point,count(*) as counter from $table_name where quizid=%d and parentid != -1",$quizid),ARRAY_A);
						}
					}
					
				}else{
					if($poststatus != 'publish'){
						$wpdb->update($table_name,array('parentid'=>$sectionid,'point'=>$totalpoint,'category'=>$questioncat,'entityorder'=>$entityorder),array('quizid'=>$quizid,'entityid'=>$questionid),array('%d','%d','%d','%f'),array('%d','%d'));
						if(!(empty($quizid))){
							$totalpoint=$wpdb->get_results($wpdb->prepare("select sum(point) as point,count(*) as counter from $table_name where quizid=%d and parentid != -1",$quizid),ARRAY_A);
						}
					}
				}
				if($questmeta['t']==2){$point=$questmeta['totalpoint'];}else{$point=$questmeta['p'];}
				$content=$this->getsave_question($questmeta,$questtitle,$questionid,$prevquestionid,$sectionid,$point,$questioncat,$entityorder,$sectionstatus,$questionchanged);
			}else{$content='';}if(empty($prevsectionid)){$prevsectionid=$questmeta['s'];}
			echo json_encode(array('msg'=>'saved','content'=>$content,'questionid'=>$prevquestionid,'instanceid'=>$questionid,'sectionid'=>$questmeta['s'],'prevsectionid'=>$prevsectionid));
			echo ob_get_clean();
			die();
	
	}
	public function getsave_question($questmeta,$questtitle,$questionid,$prevquestionid,$sectionid,$point,$category,$entityorder,$sectionstatus,$questionchanged){
		$content="<td class='rowtitle'><div class='rowshort'><p>";
		$content.=$questtitle."</p></div><div class='rowfull' id='rowfull-".$prevquestionid."'><p>".$questmeta['desc']."</p>";
		$content.='<input type="hidden" name="entityid[]" value="'.$prevquestionid.'" disabled class="requiredvar">';
		$content.='<input type="hidden" name="instanceid[]" value="'.$questionid.'" disabled class="requiredvar">';
		$content.='<input type="hidden" name="parentid[]" value="'.$sectionid.'" disabled class="requiredvar">';
		$content.='<input type="hidden" name="point[]" value="'.$point.'" disabled class="requiredvar">';
		$content.='<input type="hidden" name="category[]" value="'.$category.'" disabled class="requiredvar">';
		$content.='<input type="hidden" name="entityorder[]" value="'.$entityorder.'" disabled class="requiredvar">';
		$content.='<input type="hidden" name="questionchangedstat[]" value="'.$questionchanged.'" disabled class="requiredvar">';
		if(in_array($questmeta['t'],array(1,2,4))){
			$content.="<ol  class='createquestlist'>";
			foreach($questmeta['a']['id'] as $answerid){$content.='<li>'.$questmeta['a'][$answerid]['desc'].'</li>';}
			$content.="</ol>";
		}elseif($questmeta['t']==3){
			$content.="<h3>Left Column</h3><ol class='createquestlist'>";
			foreach($questmeta['la']['id'] as $answerid){$content.='<li>'.$questmeta['la'][$answerid]['desc'].'</li>';}
			$content.="</ol><h3>Right Column</h3><ol class='createquestlist'>";
			foreach($questmeta['ra']['id'] as $answerid){$content.='<li>'.$questmeta['ra'][$answerid]['desc'].'</li>';}
			$content.="</ol>";
		}
		$content.="</div><div class='questrowactions'><span><a href='#' class='questedit'>";$content.=__('Edit','wpcues-basic-quiz');$content.="</a> | </span><span><a href='#' class='questremove'>".__('Remove','wpcues-basic-quiz')."</a> ";
		if(empty($sectionstatus)){
		$content.="| </span><span><a href='#'  class='changequestorder'>".__('Change Question Order','wpcues-basic-quiz')."</a> | </span><span><a href='#' class='changeansorder'>".__('change answer order','wpcues-basic-quiz')."</a></span></div>";
		}else{$content.='</span></div>';}
		$content.="</td>";
		return $content;
	}
		/**
		* Ajax handler to edit question
		*/
		public function quiz_form(){
			ob_start();
			if(!(empty($_POST['myformdata']))){
				if(get_magic_quotes_gpc() || function_exists('wp_magic_quotes')){
					$myformdata=stripslashes($_POST['myformdata']);
				}else{$myformdata=$_POST['myformdata'];}
				parse_str($myformdata,$output);
				if(!(empty($output['quizid']))){$quizid=$output['quizid'];}
				if(!(empty($output['sectionid']))){$sectionid=$output['sectionid'];}
				if(!(empty($output['questionchanged']))){$questionchanged=$output['questionchanged'];}
				$questionid=$output['entityid'][0];
				$instanceid=$output['instanceid'][0];
				$instance=get_post($instanceid);
				$questmeta=unserialize($instance->post_content);
			}else{
				$questmeta=array();$questionid=0;$sectionids=array();
				if(!empty($_POST['poststatus'])){$poststatus=$_POST['poststatus'];}
				if(!(empty($_POST['quizid']))){
					$quizid=$_POST['quizid'];
				}
				if(!(empty($_POST['sectionid']))){$sectionid=$_POST['sectionid'];}
				
			}
			if(!empty($_POST['sectionids'])){$sectionids=$_POST['sectionids'];}else{$sectionids=array();}
			if(empty($quizid)){$quizid=0;}if(empty($sectionid)){$sectionid=0;}
			if(empty($questionchanged)){$questionchanged=0;}
			if((empty($quizid)) && ((empty($sectionid)))){$butstatus=0;}else{$butstatus=1;}
			$this->quiz_formdesc($questmeta,$questionid,$sectionids,$quizid,$questionchanged,$butstatus);
			echo ob_get_clean();
			die();
		}
		public function quiz_formdesc($questmeta,$questionid,$sectionids=false,$quizid=false,$butstatus=false,$questionchanged=false){
			global $wpdb;$table_name=$wpdb->prefix.'wpcuequiz_quizinfo';
			echo '<input type="hidden" name="curquestionid" value="'.$questionid.'">';
			$this->question_form($questmeta,$sectionids,$butstatus,$questionchanged);
			if(!($quizid)){
				echo '<input type="hidden" name="entityid[]" value="'.$questionid.'">';
				echo '<input type="hidden" name="instanceid[]" value="'.$questionid.'">';
			}
		}
		public function question_form($questmeta,$sectionids,$questionchanged=false,$butstatus=false){
			echo '<div id="questioneditor"><div class="question_top"><h2 class="question_title">'.__('Question:','wpcues-basic-quiz').'</h2>';
			echo '<input type="hidden" name="questionchanged" id=questionchanged" value="'.$questionchanged.'"></div>';
			echo '<div class="questiontools"><div class="questiontypetool"><select name="questiontype" id="questiontype">';
			echo '<option value="0">'.__('Select Question type','wpcues-basic-quiz').'</option>';
			echo '<option value="1" ';
			if((!(empty($questmeta))) &&($questmeta['t']==1)){echo 'selected';}
			echo '>'.__('Multiple Choice : Single Correct','wpcues-basic-quiz').'</option>';
			echo '<option value="2"';
			if((!(empty($questmeta))) &&($questmeta['t']==2)){echo 'selected';}
			echo '>'.__('Multiple Choice : Multiple Correct','wpcues-basic-quiz').'</option>';
			echo '<option value="3"';
			if((!(empty($questmeta))) &&($questmeta['t']==3)){echo 'selected';}
			echo '>'.__('Match the answers','wpcues-basic-quiz').'</option>';
			echo '<option value="4"';
			if((!(empty($questmeta))) &&($questmeta['t']==4)){echo 'selected';}
			echo '>'.__('Sort the values','wpcues-basic-quiz').'</option>';
			echo '<option value="5"';
			if((!(empty($questmeta))) &&($questmeta['t']==5)){echo 'selected';}
			echo '>'.__('Fill the gaps','wpcues-basic-quiz').'</option>';
			echo '<option value="6"';
			if((!(empty($questmeta))) &&($questmeta['t']==6)){echo 'selected';}
			echo '>'.__('True False','wpcues-basic-quiz').'</option>';
			echo '<option value="7"';
			if((!(empty($questmeta))) &&($questmeta['t']==7)){echo 'selected';}
			echo '>'.__('Open Ended','wpcues-basic-quiz').'</option>';
			echo '</select></div>';
			echo '<div class="sectool">';
			$divheader='<div id="addedsectvalues" ';
			if(!empty($sectionids)){ $divheader.='style="display:block">';}else{$divheader.='style="display:none">';}
			echo $divheader;
			echo '<select name="sectionvalues" id="sectionvalues"><option value=0>'.__('Select Section','wpcues-basic-quiz').'</option>';
			if(!empty($sectionids)){
				$args = array( 'post__in'=>$sectionids,'post_type'=>'wpcuebasicsection','orderby'=>'post__in','posts_per_page' => -1);
					$query1 = new WP_Query($args);
					while ($query1->have_posts()){
						$query1->the_post();
						$entity=$query1->post;
						echo "<option value='".$entity->ID."'";
						if(!(empty($questmeta['s'])) && ($entity->ID==$questmeta['s'])){echo ' selected';}
						echo ">".$entity->post_title."</option>";
					}
				wp_reset_postdata();
			}
			echo '</select></div></div>';
			echo '<div class="questioncattool">';
			$catarg=array('name'=>'questcatform',
							'show_option_none'=>__('Select Question Category','wpcues-basic-quiz'),
							'id'=>'questcatform',
							'hide_empty'=>false,
							'taxonomy'=>'wpcuebasicquestcat');
			if(!(empty($questmeta)) &&($questmeta['qc'] != -1)){$catarg['selected']=$questmeta['qc'];}
			wp_dropdown_categories($catarg);
			echo '</div></div><div class="item_section">';
			if(!empty($questmeta)){
				$questdesc=$questmeta['desc'];$savequestion_status=1;
				$anshint=$questmeta['anshint'];$correctansdesc=$questmeta['correctansdesc'];
			}else{
				$questdesc='';$savequestion_status=0;$anshint='';$correctansdesc='';
			}
			wp_editor($questdesc,'newquestion',array('wpautop'=>true,'default_editor'=>'tinymce','textarea_rows'=>50,'editor_height'=>40,'quicktags'=>true,'dfw'=>true,'media_buttons'=>true));
			if(empty($questmeta['t'])){$questiontype=0;}else{$questiontype=$questmeta['t'];}
			echo '</div><input type="hidden" name="origquestiontype" value="'.$questiontype.'"><input type="hidden" name="savequestion_status"  value="'.$savequestion_status.'">';
			echo "</div><div id='answereditor' class='innertabcontainer'>";
			$secondary=range(9,16);	
			if(!(empty($questmeta)) && (!(in_array($questmeta['t'],$secondary)))){
				$this->answereditor($questmeta['t'],$questmeta);
			}
			echo '</div>';
			echo '<div id="secondaryeditor">';
			if(!empty($questmeta) && (in_array($questmeta['t'],$secondary))){
				if(!(empty($questmeta['secondarysetting']))){$secondarysetting=$questmeta['secondarysetting'];
				}else{$secondarysetting='';}
				$this->secondary_box($secondarysetting,$questmeta['t']);
			}
			echo '</div>';
			if(!empty($butstatus)){echo '<div class="savequestion"><div class="save_question_button button button-primary">'.__('Save Question','wpcues-basic-quiz').'</div>';
			echo '<div class="button button-primary cancel_question_button">'.__('Cancel','wpcues-basic-quiz').'</div></div>';}
		}
		public function secondary_box($secondarysetting,$questiontype){
			echo '<table class="widefat fixed">';	
			switch($questiontype){
					case 10:
						$this->name_box($secondarysetting);
						break;
					case 11:
						$this->emailid_box($secondarysetting);
						break;
					case 12:
						$this->phonenumber_box($secondarysetting);
						break;
					case 13:
						$this->address_box($secondarysetting);
						break;
					case 14:
						$this->datetime_box($secondarysetting);
						break;
					case 15:
						$this->url_box($secondarysetting);
						break;
					case 16:
						$this->file_box($secondarysetting);
						break;
					case 17:
						$this->number_box($secondarysetting);
						break;
				}
				echo '</table>';
		}
		public function name_box($secondarysetting){
			echo '<tr><td id="leftsecondarybar">';
			echo '<ul><li class="title';
			if(!(empty($secondarysetting['display']))&&($secondarysetting['display']==3)){echo ' hiddendiv';}
			echo '"><input type="text" name="title" value="'.$secondarysetting['title'].'" placeholder="Title"></li>';
			echo '<li class="firstname"><input type="text" name="firstname" value="'.$secondarysetting['firstname'].'" placeholder="First Name"></li>';
			echo '<li class="lastname"><input type="text" name="lastname" value="'.$secondarysetting['lastname'].'" placeholder="Last Name"></li>';
			echo '<li class="suffix';
			if(!(empty($secondarysetting['display'])) && ($secondarysetting['display'] !=1)){echo ' hiddendiv';}
			echo '"><input type="text" name="suffix" value="'.$secondarysetting['suffix'].'" placeholder="Suffix"></li></ul>';
			echo '</td>';
			echo '<td id="rightsecondarybar">';
			echo '<h3>Display Setting</h3><select name="namedisplaysetting" id="namedisplaysetting">';
			echo '<option value="1"';
			if($secondarysetting['display']==1){echo ' selected';}
			echo '>Full Name</option>';
			echo '<option value="2"';
			if($secondarysetting['display']==2){echo ' selected';}
			echo '>Title,First, Last</option>';
			echo '<option value="3"';
			if($secondarysetting['display']==3){echo ' selected';}
			echo '>First and Last</option>';
			echo '</td></tr>';
		}
		public function emailid_box($secondarysetting){
			echo '<tr id="mainsecondarybar"><td><input type="text" name="emailid" value="'.$secondarysetting['emailid'].'" placeholder="(e.g. john@example.com)"></div></td></tr>';
		}
		public function phonenumber_box($secondarysetting){
			echo '<tr><td id="leftsecondarybar"><ul><li><input type="text" name="phonenumber" value="'.$secondarysetting['phone'].'" placeholder=" e.g. 1 201-234-5678"></li>';
			echo '<li>Chosen Country : </li>';
			$country=array('AF'=>'Afghanistan','AL'=>'Albania','DZ'=>'Algeria','AS'=>'American Samoa','AD'=>'Andorra');
			echo '<li><select name="countrylist">';
			foreach($country as $key=> $value){
					echo '<option  value="'.$key.'"';
					if($key==$secondarysetting['country']){echo ' selected';}
					echo '>'.$value.'</option>';
			}
			echo '</select></li></ul></td>';
			echo '<td id="rightsecondarybar"><input type="checkbox" name="countrychange" value="1"';
			if(!(empty($secondarysetting['countrychange'])) && ($secondarysetting['countrychange']==1)){echo ' checked';} 
			echo '>Allow Country Change</td></tr>';
		}
		public function address_box($secondarysetting){
			echo '<tr><td id="leftsecondarybar"><ul>';
			echo '<li class="mainaddress"><ul><li><input type="text" name="addressline1" value="'.$secondarysetting['addressline1'].'" placeholder="Address Line 1"></li>';
			echo '<li><input type="text" name="addressline2" value="'.$secondarysetting['addressline1'].'" placeholder="Address Line 2"></li></ul></li>';
			echo '<li><ul class="secondaryaddress"><li class="cityaddress"><input type="text" name="city" value="'.$secondarysetting['city'].'" placeholder="City"></li>';
			echo '<li class="stateaddress"><input type="text" name="state" value="'.$secondarysetting['state'].'" placeholder="State"></li></ul></li>';
			echo '<li><ul class="secondaryaddress"><li class="zipcodeaddress';
			if(empty($secondarysetting['zipshow'])){echo ' hiddendiv';}
			echo '"><input type="text" name="zipcode" value="'.$secondarysetting['zipcode'].'" placeholder="Zipcode"></li>';
			echo '<li class="countryaddress';
			if(empty($secondarysetting['countryshow'])){echo ' hiddendiv';}
			echo '"><input type="text" name="country" value="'.$secondarysetting['country'].'" placeholder="Country"></li></ul></li>';
			echo '</ul></td><td id="rightsecondarybar">';
			echo '<ul><li><input type="checkbox" name="mainaddresshow" value="1"';
			if(empty($secondarysetting) || (!(empty($secondarysetting['mainaddresshow'])))){echo 'checked';}
			echo '>Place</li>';
			echo '<li><input type="checkbox" name="cityshow" value="1"';
			if((empty($secondarysetting)) || (!(empty($secondarysetting['cityshow'])))){echo ' checked';} echo '>City</li>';
			echo '<li><input type="checkbox" name="stateshow" value="1"';
			if((empty($secondarysetting)) || (!(empty($secondarysetting['stateshow'])))){echo ' checked';}echo '>State</li>';
			echo '<li><input type="checkbox" name="zipshow" value="1"';
			if(!(empty($secondarysetting['zipshow']))){echo ' checked';} echo '>Zip</li>';
			echo '<li><input type="checkbox" name="countryshow" value="1"';
			if(!(empty($secondarysetting['countryshow']))){echo ' checked'; }echo '>Country</li>';
			echo '</ul></td></tr>';
		}
		public function datetime_box($secondarysetting){
			echo '<tr><td id="leftsecondarybar">';
			echo '<ul><li class="monthdate"><input type="text" name="monthdate" value="'.$secondarysetting['monthdate'].'" placeholder="MM"></li>';
			echo '<li class="datedate"><input type="text" name="datedate" value="'.$secondarysetting['datedate'].'" placeholder="DD"></li>';
			echo '<li class="yeardate"><input type="text" name="yeardate" value="'.$secondarysetting['yeardate'].'" placeholder="YYYY"></li>';
			echo '<li class="hoursdate';
			if(empty($secondarysetting['dateformat'])){echo ' hiddendiv';}
			echo '"><input type="text" name="hoursdate" value="'.$secondarysetting['hoursdate'].'" placeholder="HH"></li>';
			echo '<li class="minsdate';
			if(empty($secondarysetting['dateformat'])){echo ' hiddendiv';}
			echo '"><input type="text" name="minsdate" value="'.$secondarysetting['minsdate'].'" placeholder="MM"></li></ul>';
			echo '</td>';
			echo '<td id="rightsecondarybar">';
			echo '<ul><li>Date Format </li>';
			echo '<li><select name="dateformat" id="dateformat">';
			echo '<option value="1"';
			if($secondarysetting['dateformat']==1){echo ' selected';}
			echo '>mm/dd/yyyy hh:mm</option>';
			echo '<option value="2"';
			if($secondarysetting['dateformat']==2){echo ' selected';}
			echo '>dd/mm/yyyy hh:mm</option>';
			echo '<option value="3"';
			if(empty($secondarysetting['dateformat']) || ($secondarysetting['dateformat']==3)){echo ' selected';}
			echo '>mm/dd/yyyy</option>';
			echo '<option value="4"';
			if($secondarysetting['dateformat']==4){echo ' selected';}
			echo '>dd/mm/yyyy</option>';
			echo '<option value="5"';
			if($secondarysetting['dateformat']==5){echo ' selected';}
			echo '>hh:mm</option>';
			echo '</select></li></ul>';
			echo '</td></tr>';
		}
		public function url_box($secondarysetting){
			echo '<tr><td id="mainsecondarybar"><input type="text" name="url" value="'.$secondarysetting['url'].'" placeholder="(e.g. http://www.example.com)"></td></tr>';
		}
		public function file_box($secondarysetting){}
		public function number_box($secondarysetting){
				echo '<tr><td id="leftsecondarybar"><ul><li>Minimum <input type="text name="minnum" value="'.$secondarysetting['minnum'].'">';
				echo ' Maximum <input type="text" name="maxnum" value="'.$secondarysetting['maxnum'].'">';
				echo ' Default <input type="text" name="defaultnum" value="'.$secondarysetting['defaultnum'].'">';
				echo '</li><li>Label <input type="text" name="labelnum" value="'.$secondarysetting['labelnum'].'">';
				echo '</li></ul></td><td id="rightsecondarybar">';
				echo '<ul><li><input type="checkbox" name="slidershow"';
				if(!(empty($secondarysetting['slidershow']))){echo 'checked'; } 
				echo '>Use Slider</li>';
				echo '<li><ul><li>Number Format</li><li><select name="numberformat">';
				echo '<option value="0"';
				if(empty($secondarysetting['numberformat'])){echo 'selected';}
				echo '>No decimals</option>';
				echo '<option value="1"';
				if($secondarysetting['numberformat']==1){echo 'selected';}
				echo '>0.0</option>';
				echo '<option value="2"';
				if($secondarysetting['numberformat']==2){echo 'selected';}
				echo '>0.00</option>';
				echo '<option value="3"';
				if($secondarysetting['numberformat']==3){echo 'selected';}
				echo '>0.000</option>';
				echo '<option value="4"';
				if($secondarysetting['numberformat']==4){echo 'selected';}
				echo '>0.0000</option>';
				echo '<option value="5"';
				if($secondarysetting['numberformat']==5){echo 'selected';}
				echo '>0.00000</option>';
				echo '</select></li></ul></li>';
				echo '</ul></td></tr>';
		}
		public function answereditor($questiontype,$questmeta){
			echo '<div id="answereditorinner">';
			if($questiontype==7){
				echo "<ul  class='inneranstabs'><li><a href='#answertab-1'>".__('Correct Answers','wpcues-basic-quiz')."</a></li>";
			}else{
			echo "<ul  class='inneranstabs'><li><a href='#answertab-1'>".__('Answer','wpcues-basic-quiz')."</a></li>";}
			echo "<li><a href='#answertab-2'>".__('Hint','wpcues-basic-quiz')."</a></li><li><a href='#answertab-3'>".__('Correct Answer Description','wpcues-basic-quiz')."</a></li></ul>";
			echo "<div id='answertab-1' class='innertabcontent'>";
			if($questiontype==7){
				echo 'Matching Mode : <select name="matchingmode"><option value="1"';
				if(!(empty($questmeta['matching'])) && ($questmeta['matching']==1)){echo ' selected';}
				echo '>Loose</option>';
				echo '<option value="2"';
				if(!(empty($questmeta['matching'])) && ($questmeta['matching']==2)){echo ' selected';}
				echo '>User Answer Text contains correct answer</option>
				<option value="3"';
				if(!(empty($questmeta['matching'])) && ($questmeta['matching']==3)){echo ' selected';}
				echo '>Correct Answer contains whole user answer</option>
				<option value="4"';
				if(!(empty($questmeta['matching'])) && ($questmeta['matching']==4)){echo ' selected';}
				echo '>Exact match case (Insensitive)</option></select>';
			}
			$this->addinitial_main($questiontype,$questmeta);
			echo "</div><div id='answertab-2' class='innertabcontent'>";
			if(empty($questmeta['anshint'])){$anshint='';}else{$anshint=$questmeta['anshint'];}
			if(empty($questmeta['correctansdesc'])){$correctansdesc='';}else{$correctansdesc=$questmeta['correctansdesc'];}
			wp_editor($anshint,'anshint',array('wpautop'=>false,'default_editor'=>'tinymce','textarea_rows'=>50,'editor_height'=>40,'quicktags'=>true,'dfw'=>true,'media_buttons'=>true));
			echo "</div><div id='answertab-3' class='innertabcontent'>";
			wp_editor($correctansdesc,'correctansdesc',array('wpautop'=>true,'default_editor'=>'tinymce','textarea_rows'=>50,'editor_height'=>40,'quicktags'=>true,'dfw'=>true,'media_buttons'=>true));
			echo '</div>';
			echo '</div>';
		}
		public function answerbox($questiontype,$questmeta,$status){
			$j=2;if($questiontype==7){$j=1;}
			if($questiontype==3){
				if($status==1){
					echo "<ul id='matchquestion' class='innertabs'>";
					echo "<li><a href='#answersbox-1'>".__('Left Column','wpcues-basic-quiz')."</a></li><li><a href='#answersbox-2'>".__('Right Column','wpcues-basic-quiz')."</a></li></ul>";
					if(!(empty($questmeta))){$j=count($questmeta['la']['id']);}
				}elseif(!(empty($questmeta))){$j=count($questmeta['ra']['id']);}
			}elseif(!(empty($questmeta))){$j=count($questmeta['a']['id']);}
			echo '<div id="answersbox-'.$status.'" class="innertabcontent"><div class="answeraddtools">';
			echo '<div class="button add_answer_button">'.__('Add Answer','wpcues-basic-quiz').'</div></div><div id="answersboxtab-'.$status.'">';
			echo '<ul class="innernumtabs">';
			for($i=1;$i<=$j;$i++){
				echo '<li class="activetab"><a href="#'.$status.'answereditortab-'.$i.'">'.$i.'</a></li>';
			} 
			echo '</ul>';
			for($i=1;$i<=$j;$i++){
				$point=0;$corstatus=0;$answerid=$i;$ansdesc='';
				if(!(empty($questmeta))){
					$k=$i-1;
					if($status==1){
						if($questiontype==3){$answerid=$questmeta['la']['id'][$k];$ansdesc=$questmeta['la'][$answerid]['desc'];
						}else{$answerid=$questmeta['a']['id'][$k];$ansdesc=$questmeta['a'][$answerid]['desc'];}	
						if(($questiontype==1)&&(!(empty($questmeta['c'])))&&($questmeta['c']==$answerid)){$point=$questmeta['p'];$corstatus=1;}
						if(($questiontype==2)&&(!(empty($questmeta['c'])))&&(in_array($answerid,$questmeta['c']))){$point=$questmeta['p'][$answerid];$corstatus=1;}
					}else{$answerid=$questmeta['ra']['id'][$k];$ansdesc=$questmeta['ra'][$answerid]['desc'];}
				}
				$this->answer_form($i,$questiontype,$answerid,$ansdesc,$status,$point,$corstatus);
			}
			echo '</div></div>';
		}
		public function answer_form($i,$questiontype,$answerid,$ansdesc,$boxstatus,$point,$corstatus){
			echo "<div id='".$boxstatus."answereditortab-".$i."'><div class='answerclosetools'><div class='button answer_close_button'>X</div></div>";
				wp_editor($ansdesc,$boxstatus.'answeredittab-'.$answerid,array('wpautop'=>true,'default_editor'=>'tinymce','textarea_rows'=>40,'editor_height'=>40,'quicktags'=>true,'dfw'=>true,'media_buttons'=>true));
				echo '<input type="hidden" name="'.$boxstatus.'answerid[]" value="'.$answerid.'">';
				echo '<input type="hidden" name="'.$boxstatus.'tabids[]" value="'.$i.'">';
				if($questiontype==1){
					$this->single_choicebox($i,$point,$corstatus);	
				}
				if($questiontype==2){
					$this->multiple_choicebox($i,$point,$corstatus);
				}
				if($questiontype==7){
					$this->openend_box($i,$point);
				}
				echo '</div>';
		
		}
		public function single_choicebox($i,$point,$status){
			echo '<div id="singlechoicebox-'.$i.'" class="questdepbox singlechoicebox"><table class="widefat fixed">';
			echo '<tr><td>'.__('Points','wpcues-basic-quiz').'</td><td><input type="text" name="points-'.$i.'" class="questionpoint" value="'.$point.'"></td></tr>';
			echo '<tr><td>'.__('Correct Answer','wpcues-basic-quiz').' :</td><td><input type="radio" name="coranswer" class="questioncorrectanswer" value="'.$i.'" ';
			if(!(empty($status))){echo 'checked ';}
			echo '/></td></tr></table></div>';
		}
		public function multiple_choicebox($i,$point,$status){
			echo '<div id="multiplechoicebox-<?php echo $i;?>" class="questdepbox multiplechoicebox"><table class="widefat fixed">';
			echo '<tr><td>'.__('Points','wpcues-basic-quiz').'</td><td><input type="text" name="points-'.$i.'" class="questionpoint" value="'.$point.'"></td></tr>';
			echo '<tr><td>'.__('Correct Answer','wpcues-basic-quiz').' :</td><td><input type="checkbox" name="coranswer[]"  class="questioncorrectanswer" value="'.$i.'" ';
			if(!(empty($status))){echo 'checked ';}
			echo '/></td></tr></table></div>';
		}
		public function openend_box($i,$point){
			echo '<div id="openendedbox-<?php echo $i;?>" class="questdepbox openendedbox"><table class="widefat fixed">';
			echo '<tr><td>'.__('Points','wpcues-basic-quiz').'</td><td><input type="text" name="points-'.$i.'" class="questionpoint" value="'.$point.'"></td></tr>';
			echo '</table></div>';
		}
		public function match_questionbox($point,$coranswer){
			if(empty($coranswer)){$coranswer='';$disabledstatus=1;}else{$disabledstatus=0;}
			echo '<div id="matchquestionbox" class="questdepbox"><table class="widefat fixed"><tr><td>'.__('Points','wpcues-basic-quiz').'</td>';
			echo '<td><input type="text" name="points" class="questionpoint" value="'.$point.'"></td></tr><tr><td>'.__('Correct Answer','wpcues-basic-quiz').' :</td>';
			echo "<td><input type='text' name='coranswer' value='".$coranswer."'  class='questioncorrectanswer' /></td></tr>";
			echo '<tr><td colspan="2"><div class="entitymsg">'.__('Please enter whatever your correct matching order is, as "1,1","2,3","3,4","4,2".','wpcues-basic-quiz').'</div></td></tr>';
			echo '</table></div>';
		}
		public function sort_box($point,$coranswer){
			if(empty($coranswer)){$coranswer='';$disabledstatus=1;}else{$disabledstatus=0;}
			echo '<div id="sortquestionbox" class="questdepbox"><table class="widefat fixed"><tr><td>'.__('Points','wpcues-basic-quiz').'</td>';
			echo '<td><input type="text" name="points" class="questionpoint" value="'.$point.'"></td></tr><tr><td>'.__('Correct Answer'.'wpcues-basic-quiz').' :</td>';
			echo '<td><input type="text" name="coranswer" value="'.$coranswer.'"  class="questioncorrectanswer" /></td></tr>';
			echo '<tr><td colspan="2"><div class="entitymsg">'.__('Please enter whatever your correct sorting order as 1,2,3,4 or 2,4,3,1 i.e. separated by comma.','wpcues-basic-quiz').'</div></td></tr>';
			echo '</table></div>';
		}
		public function truefalse_box($point,$coranswer){
			echo '<div id="truefalsebox" class="questdepbox ';
			echo '">';
			echo '<table class="widefat fixed">';
			echo '<tr><td style="width:30%;">';_e('Correct Answer','wpcues-basic-quiz');echo ' : </td>';
			echo '<td style="width:70%;">';_e('True','wpcues-basic-quiz');echo ' <input type="radio" name="coranswer" value="1"  class="questioncorrectanswer"  ';
			if($coranswer==1){echo 'checked';}
			echo '>';
			_e('False','wpcues-basic-quiz');
			echo '<input type="radio" name="coranswer" value="0" class="questioncorrectanswer" ';
			if($coranswer==0){echo 'checked';}
			echo '></td></tr><tr><td style="width:30%;">';
			_e('Points','wpcues-basic-quiz');echo ' : </td><td style="width:70%;">';
			echo '<input type="text" name="points" value="'.$point.'" class="questionpoint"></td></tr></table></div>';
		}
		public function fillgaps_box($point,$disabledstatus){
			echo '<div id="fillgapsbox" class="questdepbox"><table class="widefat fixed"><tr><td style="width:12%">';
			_e('Points','wpcues-basic-quiz');echo ' : </td>';
			echo '<td style="width:88%"><input type="text" name="points" class="questionpoint" value="'.$point.'"></td></tr><tr>';
			echo '<td colspan="2"><input type="checkbox" name="partialpoint" class="partialpoint" value="1">';
			_e('Award weighted point to partial correct answers','wpcues-basic-quiz');
			echo '</td></tr>';
			echo '</table></div>';
			
		}
		public function partialmark_box($partialpoint){
			echo '<div id="partialmarkbox"><ul>';
			echo '<li><input type="checkbox" name="partialpoint" class="partialpoint" value="1"';
			if($partialpoint == 1){echo 'checked';}
			echo '>Award weighted point to partial correct answers</li>';
			echo '</ul></div>';
		}
		public function addinitial_main($questiontype,$questmeta){
			if(!empty($questmeta['partialpoint'])){$partialpoint=$questmeta['partialpoint'];}else{$partialpoint=0;}
			switch($questiontype){
					case 1:$this->answerbox($questiontype,$questmeta,1);break;
					case 2:$this->answerbox($questiontype,$questmeta,1);$this->partialmark_box($partialpoint);break;
					case 3:$this->answerbox($questiontype,$questmeta,1);
							$this->answerbox($questiontype,$questmeta,2);
							if(!(empty($questmeta['p']))){$point=$questmeta['p'];}else{$point=0;}
							if(!(empty($questmeta['c']))){$coranswer=$questmeta['c'];}else{$coranswer=array();}
							$this->match_questionbox($point,$coranswer);
							$this->partialmark_box($partialpoint);
							break;
					case 4:
							$this->answerbox($questiontype,$questmeta,1);
							if(!(empty($questmeta['p']))){$point=$questmeta['p'];}else{$point=0;}
							if(!(empty($questmeta['c']))){$coranswer=$questmeta['c'];}else{$coranswer=array();}
							$this->sort_box($point,$coranswer);
							$this->partialmark_box($partialpoint);
							break;
					case 5:
							if(!(empty($questmeta['p']))){$point=$questmeta['p'];}else{$point=0;}
							$this->fillgaps_box($point,0);
							$this->partialmark_box($partialpoint);
							break;
					case 6:
							if(empty($questmeta['c'])){$coranswer=-2;
							}else{$coranswer=$questmeta['c'];}
							if(!(empty($questmeta['p']))){$point=$questmeta['p'];}else{$point=0;}
							$this->truefalse_box($point,$coranswer);
							break;
							
					case 7:	$this->answerbox($questiontype,$questmeta,1);break;
							break;
					case 8:
							break;
					case 9:
							break;
				}
			
		}
		public function addinitial_answer(){
			ob_start();
			$questiontype=$_POST['questiontype'];
			$questmeta=array();
			$this->answereditor($questiontype,$questmeta);
			echo ob_get_clean();
			die();
		}
		
		public function addinitial_secondary(){
			$questiontype=$_POST['questiontype'];
			$secondarysetting=0;
			$this->secondary_box($secondarysetting,$questiontype);
			die();
		}
		public function add_answer(){
			ob_start();
			$questiontype=$_POST['questiontype'];
			$tabid=$_POST['tabid'];
			$index=$_POST['index'];
			$this->answer_form($index,$questiontype,$index,'',$tabid,0,0);
			echo ob_get_clean();
			die();
		}
		public function ajaxremove_question(){
			global $wpdb;$error=0;
			if(get_magic_quotes_gpc() || function_exists('wp_magic_quotes')){
				$myformdata=stripslashes($_POST['myformdata']);
			}else{$myformdata=$_POST['myformdata'];}
			parse_str($myformdata,$output);
			$entityid=$output['entityid'][0];
			$quizid=$output['quizid'];
			$table_name=$wpdb->prefix.'wpcuequiz_quizinfo';
			$resultid=$wpdb->query($wpdb->prepare("DELETE from $table_name  where quizid=%d and entityid=%d",$quizid,$entityid));
			if($resultid===false){$error=1;};
			if($error == 0){
				echo json_encode(array('msg'=>'success'));
			}else{
				echo json_encode(array('msg'=>'failed'));
			}
			die();
		}
	public function changeorder_question(){
		global $wpdb;
		$questionid=$_POST['questionid'];
		$quizid=$_POST['quizid'];
		$newsection=$_POST['newsectionid'];$instanceid=$_POST['instanceid'];
		$newquestposition=$_POST['newquestpostion'];
		$publishstatus=$_POST['publishstatus'];
		$error=0;
		$question=get_post($instanceid);
		$questmeta=unserialize($question->post_content);
		$prevparent=$questmeta['s'];
		if($prevparent != $newsection){
			$questmeta['s']=$newsection;
			if($instanceid==$questionid){
				$instanceid=$wpdb->insert($wpdb->posts,array('post_title'=>$question->post_title,'post_content'=>serialize($questmeta),'post_status'=>'inherit','post_parent'=>$questionid),array('%s','%s','%s','%d'));
			}else{
				$instanceid=$wpdb->update($wpdb->posts,array('post_title'=>$question->post_title,'post_content'=>serialize($questmeta),'post_status'=>'publish'),array('ID'=>$instanceid),array('%s','%s','%s'),array('%d'));
			}
		}
		if(empty($publishstatus)){
			$resultid=$wpdb->update($wpdb->prefix.'wpcuequiz_quizinfo',array('entityorder'=>$newquestposition),array('quizid'=>$quizid,'entityid'=>$entityid),array('%s'),array('%d'));
		}
		echo json_encode(array('msg'=>'success','instanceid'=>$instanceid));
		die();
	}
	public static function getadded_questions($entityids,$quizid,$sectionstatus=false){
		if(!empty($entityids)){
			$entityids = esc_sql( $entityids );
			global $wpdb;$table_name=$wpdb->prefix.'wpcuequiz_quizinfo';
			if($sectionstatus){
				$entityorder=$wpdb->get_results($wpdb->prepare("select entityid,entityorder from $table_name where parentid=%d order by entityorder asc",$quizid),OBJECT_K);
			}else{
				$entityorder=$wpdb->get_results($wpdb->prepare("select entityid,entityorder from $table_name where quizid=%d order by entityorder asc",$quizid),OBJECT_K);
			}
			$secent=$wpdb->get_results($wpdb->prepare("select parentid,count(*) as counter from $table_name where quizid=%d and parentid != -1 group by parentid",$quizid),OBJECT_K);
			if($sectionstatus){
				WpCueBasicQuestion::addedquestions_display($entityids,$secent,$entityorder,$sectionstatus);
			}else{
				WpCueBasicQuestion::addedquestions_display($entityids,$secent,$entityorder);
			}
			
		}
	}
	public static function addedquestions_display($entityids,$secent,$entityorder,$sectionstatus=false){
		$args = array( 'post__in'=>$entityids,'post_type'=>array('wpcuebasicquestion','wpcuebasicsection'),'orderby'=>'post__in','posts_per_page' => -1);
					$query1 = new WP_Query($args);
					$ansnum=0;$sectionstat=0;$lastquest=0;$sectionid=0;$rownum=1;$entnum=1;$secnum=1;$i=0;
					while ($query1->have_posts()){
					$query1->the_post();
					$quest=$query1->post;
					$questionid=$quest->ID;
						if($quest->post_type=='wpcuebasicsection')	{
							$sectionstat=1;
							$sectionid=$quest->ID;
							if(!(empty($secent)) && (!(empty($secent[$sectionid])))){$count=$secent[$sectionid]->counter;}else{$count=0;}
							$ent=$i+$count;
							?>
						<tr id='rowsec-<?php echo $sectionid; ?>' class='rowdet closed sectentity <?php if($count==0){echo 'secnoquest'; }else{echo 'secwithquest';} ?>'>
						<td class='row-number-wrapper secnum'>S. <?php echo $secnum;?></td>
						<td class='rowtitle'>
						<div class='rowshort'><div class='sectionname' style="float:left;"><p>
						<?php echo  $quest->post_title; ?></p></div><div class='questcounttext' style="float:right;">
						<?php if($count != 0){$text='Q.';$text.=$rownum;if($count >1 ){$text.='-Q.';$text.=($rownum+$count-1);}echo $text; }else{echo 'No Question'; } ?>
						</div></div><div class='rowfull'><div class="sectioncontent">
							<p class='sectitle'><?php echo  $quest->post_title; ?></p>
							<p class='secdesc'>
						<?php echo $quest->post_content; ?></p>
						<input type="hidden" name="entityid[]" value="<?php echo $sectionid; ?>" disabled class="requiredvar">
						<input type="hidden" name="instanceid[]" value="<?php echo $sectionid; ?>" disabled class="requiredvar">
						<input type="hidden" name="parentid[]" value="-1" disabled class="requiredvar">
						<input type="hidden" name="point[]" value="0" disabled class="requiredvar">
						<input type="hidden" name="category[]" value="0" disabled class="requiredvar">
						<input type="hidden" name="entityorder[]" value="<?php echo $entityorder[$sectionid]->entityorder; ?>" disabled class="requiredvar">
						</div>
							<?php if(isset($ent)){if($i==$ent){$sectionstat=0;
								echo '</div>';
								echo "<div class='rowactions'><span><a href='#' class='sectionedit'>";
								_e('Edit Section','wpcues-basic-quiz');
								echo "</a> | </span><span><a href='#' class='sectionremove'>";
								_e('Remove Section','wpcues-basic-quiz');
								echo "</a> | </span><span><a href='#' class='sectiondelete'>";
								_e('Delete Section','wpcues-basic-quiz');echo "</a></span></div>";
								echo '</td><td class="handlerow"></td></tr>';}else{echo '<table class="secquestadded"><tbody>';}
							$entnum++;$secnum++;
							}}
						elseif($quest->post_type=='wpcuebasicquestion'){ $questionid=$quest->ID;?>
						<tr id='rowquest-<?php echo $questionid; ?>' class='rowdet closed questentity'>
						<td class='row-number-wrapper questnum'>Q. <?php echo $rownum; ?></td>
						<td class='rowtitle'>
						<div class='rowshort'><p><?php $questcontent=unserialize($quest->post_content);
						echo wpcues_basic_quiz::summary($questcontent['desc'],100,true);?></p></div>
						<div class='rowfull'><p><?php echo $questcontent['desc']; ?></p>
						<input type="hidden" name="questiontype-<?php echo $questionid; ?>" value="<?php echo $questcontent['t']; ?>" disabled class="requiredvar">
						<input type="hidden" name="entityid[]" value="<?php echo $questionid; ?>" disabled class="requiredvar">
						<input type="hidden" name="instanceid[]" value="<?php echo $questionid; ?>" disabled class="requiredvar">
						<input type="hidden" name="parentid[]" value="<?php echo $questcontent['s'];?>" disabled class="requiredvar">
						<?php if($questcontent['t']==2){$point=$questcontent['totalpoint'];}else{$point=$questcontent['p'];}?>
						<input type="hidden" name="point[]" value="<?php echo $point;?>" disabled class="requiredvar">
						<input type="hidden" name="category[]" value="<?php echo $questcontent['qc'];?>" disabled class="requiredvar">
						<input type="hidden" name="entityorder[]" value="<?php echo $entityorder[$questionid]->entityorder; ?>" disabled class="requiredvar">
							<?php
							if(in_array($questcontent['t'],array(1,2,3,4))){
							if($questcontent['t'] != 3){
								if(!(empty($questcontent['a']['id']))){
									
									echo '<ol class="createquestlist answersort-'.$questionid.'">'; 
									foreach($questcontent['a']['id'] as $answerid){
										echo '<li>'.$questcontent['a'][$answerid]['desc'].'<input type="hidden" name="finanswerid-'.$questionid.'[]" value="'.$answerid.'"  disabled class="requiredvar"></li>'; 
									}
									echo '</ol>';
								}
							}else{
								if(!(empty($questcontent['la']['id']))){
								echo '<h3>Left Column</h3>';
								echo '<ol class="createquestlist answersort-'.$questionid.'">'; 
								foreach($questcontent['la']['id'] as $answerid){
									echo '<li class="answersort-'.$questionid.'">'.$questcontent['la'][$answerid]['desc'].'<input type="hidden" name="finanswerid-'.$questionid.'[]" value="'.$answerid.'"  disabled class="requiredvar"></li>'; 
								}
								echo '</ol>';
								}
								if(!(empty($questcontent['ra']['id']))){
								echo '<h3>Right Column</h3>';
								echo '<ol class="createquestlist answersort-'.$questionid.'">'; 
								foreach($questcontent['ra']['id'] as $answerid){
									echo '<li class="answersort-'.$questionid.'">'.$questcontent['ra'][$answerid]['desc'].'<input type="hidden" name="finanswerid-'.$questionid.'[]" value="'.$answerid.'" disabled class="requiredvar"></li>'; 
								}
								echo '</ol>';
								}
								
							}
							}
							echo '</div><div class="questrowactions"><span><a href="#" class="questedit">';_e('Edit','wpcues-basic-quiz');echo '</a> | </span><span><a href="#" class="questremove">';_e('Remove','wpcues-basic-quiz');echo '</a>';
							if($sectionstatus){echo '</span></div>';
							}else{echo '| </span><span><a href="#"  class="changequestorder">';_e('Change Question Order','wpcues-basic-quiz');echo '</a> | </span><span><a href="#" class="changeansorder">';_e('change answer order','wpcues-basic-quiz');echo '</a></span></div>';}
								echo "</td><td class='handlerow'></td></tr>";
							
							if($sectionstat != 0){
							if(isset($ent)){
								if($i==$ent){
									$sectionstat=0;
									echo '</tbody></table></div>';
									echo "<div class='rowactions'><span><a href='#' class='sectionedit'>";
									_e('Edit Section','wpcues-basic-quiz');echo "</a> | </span><span><a href='#' class='sectionremove'>";
									_e('Remove Section','wpcues-basic-quiz');echo "</a> | </span><span><a href='#' class='sectionremove'>";_e('Remove Section','wpcues-basic-quiz');echo "</a></span></div>";
									echo '</td><td class="handlerow"></td></tr>';
								}
							}}
							$rownum++;
						}
						$i++;
					}wp_reset_postdata();
				
	
	}
} // END if(!class_exists('WpCueBasicQuestion'))
}
/* EOF */



