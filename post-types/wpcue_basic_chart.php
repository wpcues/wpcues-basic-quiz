<?php
/**
*WpCueBasicChart class
*/
if(!class_exists('WpCueBasicChart'))
{
    class WpCueBasicChart
    {
        const POST_TYPE = "wpcuebasicchart";
		private $wpcuebasicquiz_version;
        
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
			$this->wpcuebasicquiz_version=get_option('wpcuebasicquiz_version');
			add_action('wp_ajax_wpcuequizaddchart_action',array(&$this,'add_chart'));
			add_action('wp_ajax_wpcuequizdeletechart_action',array(&$this,'delete_chart'));
			add_action('wp_ajax_wpcuequizretrievechartinfo_action',array(&$this,'retrieve_chart'));
			add_shortcode('wpcuebasicchart',array(&$this,'chart_shortcode'));
			//wp_register_script('wpprocue-report', plugins_url('/js/wpprocue-report.js', __FILE__ ),array('jquery-ui-dialog','jquery-form','jquery'));
		} // END public function init()

		/**
		* Create the post type
		*/
		public function create_post_type()
		{
		
			$args = array(
				'public'             => false,
				'publicly_queryable' => false,
				'capability_type'    => 'post',
				'show_ui'=>false,
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => null,
				'supports'           => array( 'title', 'author')
			);
			register_post_type(self::POST_TYPE,$args);
	}
	
	public function add_chart(){
		$chartname=$_POST['chartname'];
		$chartid=$_POST['chartid'];
		$postcontent['type']=$_POST['charttype'];
		$postcontent['width']=$_POST['chartwidth'];
		$postcontent['widthunit']=$_POST['chartwidthunit'];
		$postcontent['height']=$_POST['chartheight'];
		$postcontent['heightunit']=$_POST['chartheightunit'];
		$postcontent['option']=$_POST['chartoptionval'];
		$postcontent['genericoption']=$_POST['chartgenericoptionval'];
		$postcontent['useroptionsec']=$_POST['chartuseroptionsecval'];
		$postcontent['useroption']=$_POST['chartuseroptionval'];
		$postcontent['order']=$_POST['chartorderval'];
		$postcontent['quizid']=$_POST['quizval'];
		$postcontent['groupnum']=$_POST['groupnum'];
		if($postcontent['option']==2){$postcontent['chartuser']=$_POST['chartuser'];}
		if($postcontent['type'] == 1){$charttype='Bar Chart';}elseif($postcontent['type'] == 2){$charttype='Pie Chart';}elseif($postcontent['type'] == 3){$charttype='Line Chart';}
		$post_content=serialize($postcontent);
		if(!empty($chartid)){
			$postid=wp_update_post(array('ID'=>$chartid,'post_title'=>$chartname,'post_content'=>$post_content,'post_status'=>'publish','post_type'=>'wpcuebasicchart'));
		}else{
			$postid=wp_insert_post(array('post_title'=>$chartname,'post_content'=>$post_content,'post_status'=>'publish','post_type'=>'wpcuebasicchart'));
		}
		if(!empty($postid) &&($postcontent['quizid'] == -1)){
			$anviprocurrep=get_option('anviprocurrep');if(empty($anviprocurrep)){$anviprocurrep=array();}
			array_push($anviprocurrep,$postid);
			update_option('anviprocurrep',$anviprocurrep);
		}
		
		$content='<td>'.$chartname.'<div class="row-actions"><span class="edit"><a href="#chartedit">'.__('Edit','wpcues-quiz-pro').'</a> | </span><span class="trash">';
		$content.='<a class="submitdelete" title="Delete" href="#chartdelete">'.__('Delete','wpcues-quiz-pro').'</a> | </span></div></td><td>'.$charttype.'</td><td>';
		$content.=$postcontent['quizid'].'</td><td>[wpcuebasicchart '.$postid.']</td>';
		if(!empty($postid)){$disabledstatus=$this->check_chartstatus();
			echo json_encode(array('msg'=>'success','postid'=>$postid,'content'=>$content,'disabledstatus'=>$disabledstatus));
		}else{
			echo json_encode(array('msg'=>'failed'));	
		}
		die();
	}
	public function delete_chart(){
		$postid=$_POST['postid'];
		$post=get_post($postid);$postmeta=unserialize($post->post_content);
		if($postmeta['quizid'] == -1){
			$anviprocurrep=get_option('anviprocurrep');
			if(!empty($anviprocurrep)){
				$key=array_search($postid,$anviprocurrep);
				if($key){unset($anviprocurrep[$key]);update_option('anviprocurrep',$anviprocurrep);}
			}
		}
		if(wp_delete_post($postid,true)){
			$disabledstatus=$this->check_chartstatus();
			echo json_encode(array('msg'=>'success','disabledstatus'=>$disabledstatus));
		}else{
			echo json_encode(array('msg'=>'failure'));
		}
		die();
	}
	public function retrieve_chart(){
		$postid=$_POST['postid'];
		$post=get_post($postid);
		$postcontent=unserialize($post->post_content);
		$postcontent['name']=$post->post_title;
		echo json_encode(array('postcontent'=>$postcontent));
		die();
	}
	public function chart_shortcode($atts){
		global $wpdb;
		$table_name1 = $wpdb->prefix.'wpcuequiz_quizstat';$table_name2=$wpdb->prefix.'wpcuequiz_quizstatinfo';	
		$table_name=$wpdb->prefix.'wpcuequiz_quizinfo';
		wp_register_script( 'flot-excanvas', plugins_url( '/../js/flot/excanvas.min.js', __FILE__ ), array('jquery'), '3.6.3');
		wp_register_script( 'flot', plugins_url( '/../js/flot/jquery.flot.min.js', __FILE__ ), array('jquery'), '3.6.4');
		wp_register_script( 'flot-pie', plugins_url( '/../js/flot/jquery.flot.pie.min.js', __FILE__ ), array('jquery'), '3.6.5');
		wp_enqueue_script('flot');
		wp_enqueue_script('flot-pie');
		wp_enqueue_script('wpcuebasicquiz-axislabel',plugins_url('/../js/flot/jquery.flot.axislabels.js',__FILE__));
		wp_enqueue_script('wpcuebasicquiz-chart',plugins_url( '/../js/wpcuebasicquiz-chart.js', __FILE__ ));
		$chartid=$atts[0];
		$chart=get_post($chartid);
		$chartoptions=unserialize($chart->post_content);
		if($chartoptions["widthunit"] == 1){$widthunit='px';}elseif($chartoptions['widthunit'] == 2 ){$widthunit='%';}
		if($chartoptions["heightunit"] == 1){$heightunit='px';}elseif($chartoptions['heightunit'] == 2 ){$heightunit='%';}
		$quizid=$chartoptions['quizid'];
		if(($quizid == -1) && ($atts['status']=1)){$quizid=$atts['quizid'];}
		$data=array();
		$option=$chartoptions['option'];$charttype=$chartoptions['type'];
		if($option == 1){
			$optionval=$chartoptions['genericoption'];
			switch($optionval){
				case 1:
					$gradegroupid=$this->gradegroup($quizid);
					if(!empty($gradegroupid)){
					$gradegroup=get_post($gradegroupid);
					$grademeta=unserialize($gradegroup->post_content);
					$results=$wpdb->get_results($wpdb->prepare("SELECT grade,count(*) as count from $table_name1 where status=1 and mode=1 and quizid=%d group by grade order by grade",$quizid),OBJECT_K);
					$i=1;
					if(!(empty($grademeta))){
						foreach($grademeta['gradeid'] as $gradeid){
							$grade=$grademeta[$gradeid]['title'];
							if(isset($results[$gradeid])){
								$data[$grade]=intval($results[$gradeid]->count);
							}else{$data[$grade]=0;}
							$i++;
						}
						if($charttype==2){
							$totnum=array_sum($data);
							foreach($data as $index=>$value){$data[$index]=($value/$totnum)*100;}
						}
					}
					
					}
					if(!(empty($data))){
						$chartmeta['xaxis']=__('Grade','wpcues-quiz-pro');
						if($chartoptions['order']==1){asort($data);}elseif($chartoptions['order']==2){arsort($data);}
						$chartmeta['type']=$charttype;$chartmeta['yaxis']=__('Number of Users','wpcues-quiz-pro');
					}
					break;
				case 2:
					$userpoints=$wpdb->get_results($wpdb->prepare("SELECT userid,sum(point) as point,count(distinct b.instanceid) as instancecount  from $table_name1 a,$table_name2 b where a.status=1 and a.mode=1 and a.quizid=%d and a.instanceid=b.instanceid group by userid",$quizid),OBJECT_K);
					$totalpoint=$wpdb->get_var($wpdb->prepare("SELECT sum(point) as totalpoint from $table_name where quizid=%d",$quizid));
					if($charttype !=3){
						$groupnum=$chartoptions['groupnum'];
						$step=$totalpoint/$groupnum;
						for($i=1;$i<=$groupnum;$i++){
								$data[($i-1)*$step.'-'.($i*$step)]=0;
						}
						foreach($userpoints as $userpoint){
							$point=$userpoint->point/$userpoint->instancecount;
							$index=ceil($point/$step);
							$data[($index-1)*$step.'-'.($index*$step)]++;
						}
					}else{
						$data[0]=0;$data[$totalpoint]=0;
						foreach($userpoints as $userpoint){
							$point=round($userpoint->point/$userpoint->instancecount);
							if(empty($data[$point])){$data[$point]=1;}else{$data[$point]++;}
						}
					}
					if(!(empty($data))){
						$chartmeta['xaxis']=__('Point','wpcues-quiz-pro');
						if($chartoptions['order']==1){asort($data);}elseif($chartoptions['order']==2){arsort($data);}
						$chartmeta['type']=$charttype;$chartmeta['yaxis']=__('Number of Users','wpcues-quiz-pro');
						if($charttype==3){$chartmeta['genericoption']=$optionval;}
					}
					break;
				case 3:
					$usercorrects=$wpdb->get_results($wpdb->prepare("SELECT userid,count(distinct entityid) as questcount,count(id) as correctcount,count(distinct b.instanceid) as instancecount  from $table_name1 a,$table_name2 b where a.status=1 and a.mode=1 and a.quizid=%d and a.instanceid=b.instanceid and b.status IN (1,2) group by userid",$quizid),OBJECT_K);
					$usercount=$wpdb->get_results($wpdb->prepare("SELECT userid,count(distinct entityid) as questcount,count(distinct b.instanceid) as instancecount  from $table_name1 a,$table_name2 b where a.status=1 and a.mode=1 and a.quizid=%d and a.instanceid=b.instanceid group by userid",$quizid),OBJECT_K);
					if($charttype !=3){
						$groupnum=$chartoptions['groupnum'];
						$step=100/$groupnum;
						for($i=1;$i<=$groupnum;$i++){
								$data[($i-1)*$step.'-'.($i*$step)]=0;
						}
						foreach($usercorrects as $usercorrect){
							$percentcorrect=($usercorrect->correctcount/($usercount[$usercorrect->userid]->instancecount*$usercount[$usercorrect->userid]->questcount))*100;
							$index=ceil($percentcorrect/$step);
							$data[($index-1)*$step.'-'.($index*$step)]++;
						}
					}else{
						$data[0]=0;$data[100]=0;
						foreach($usercorrects as $usercorrect){
							$percentcorrect=round(($usercorrect->correctcount/($usercount[$usercorrect->userid]->instancecount*$usercount[$usercorrect->userid]->questcount))*100);
							echo $percentcorrect;
							if(empty($data[$percentcorrect])){$data[$percentcorrect]=1;}else{$data[$percentcorrect]++;}
						}
					}
					if(!(empty($data))){
						$chartmeta['xaxis']=__('%Correct Answer','wpcues-quiz-pro');
						if($chartoptions['order']==1){asort($data);}elseif($chartoptions['order']==2){arsort($data);}
						$chartmeta['type']=$charttype;$chartmeta['yaxis']=__('Number of Users','wpcues-quiz-pro');
						if($charttype==3){$chartmeta['genericoption']=$optionval;}
					}
					break;
			}
		}else{
			$optionval=$chartoptions['useroption'];	
			$secoptionval=$chartoptions['useroptionsec'];
			$userid=$chartoptions['chartuser'];
			if($optionval==1){
				switch($secoptionval){
					case 1:
						$results=$wpdb->get_results($wpdb->prepare("SELECT entityid,sum(point) as point from $table_name1 a,$table_name2 b where a.status=1 and a.mode=1 and a.quizid=%d and a.userid=%d and a.instanceid=b.instanceid group by entityid",$quizid,$userid),OBJECT_K);
						$instancecount=$wpdb->get_var($wpdb->prepare("SELECT count(instanceid) as instancecount from $table_name1 where status=1 and mode=1 and quizid=%d and userid=%d",$quizid,$userid));
						$entityids=$wpdb->get_results($wpdb->prepare("select category,entityid from $table_name where quizid=%d",$quizid),ARRAY_A);
						foreach($entityids as $entityid){
							if(!isset($cat[$entityid['category']])){
								$cat[$entityid['category']]=$results[$entityid['entityid']]->point/$instancecount;
							}else{
								$cat[$entityid['category']]+=$results[$entityid['entityid']]->point/$instancecount;
							}
						}
						$categories=array_keys($cat);
						$taxonomies=array(
							'wpcuebasicquestcat'
							);
						$args = array('fields'=>'id=>name','include'=>$categories); // or names
						$terms = get_terms($taxonomies, $args);
						foreach($cat as $key=>$value){if($key==-1){$data['uncategorized']=$value;}else{$data[$terms[$key]]=$value;}}
						if(!(empty($data))){
							$chartmeta['xaxis']=__('Question Category','wpcues-quiz-pro');
						if($chartoptions['order']==1){asort($data);}elseif($chartoptions['order']==2){arsort($data);}
							$chartmeta['type']=$charttype;$chartmeta['yaxis']=__('Points','wpcues-quiz-pro');
						}
						break;
					case 2:
						$results=$wpdb->get_results($wpdb->prepare("SELECT entityid,b.status as status from $table_name1 a,$table_name2 b where a.status=1 and a.mode=1 and a.quizid=%d and a.userid=%d and a.instanceid=b.instanceid ",$quizid,$userid),ARRAY_A);
						$instancecount=$wpdb->get_var($wpdb->prepare("SELECT count(instanceid) as instancecount from $table_name1 where status=1 and mode=1 and quizid=%d and userid=%d",$quizid,$userid));
						$entityids=$wpdb->get_results($wpdb->prepare("select entityid,category from $table_name where quizid=%d",$quizid),OBJECT_K);
						foreach($results as $result){
							$entityid=$result['entityid'];
							$category=$entityids[$entityid]->category;
							if(($result['status']==1)||($result['status']==2)){
								if(!isset($cat[$category])){
									$cat[$category]=1;
								}else{
									$cat[$category]++;
								}
							}
							if(empty($catent[$category])){
									$catent[$category]=array($entityid);
							}else{
								if(!in_array($entityid,$catent[$category])){array_push($catent[$category],$entityid);}
							}
							
						}
						$categories=array_keys($catent);
						$taxonomies=array(
							'wpcuebasicquestcat'
							);
						$args = array('fields'=>'id=>name','include'=>$categories); // or names
						$terms = get_terms($taxonomies, $args);
						foreach($cat as $key=>$value){if($key==-1){$data['uncategorized']=($value/($instancecount*count($catent[$key])))*100;
						}else{$data[$terms[$key]]=($value/($instancecount*count($catent[$key])))*100;}}
						if(!(empty($data))){
							$chartmeta['xaxis']=__('Question Category','wpcues-quiz-pro');
						if($chartoptions['order']==1){asort($data);}elseif($chartoptions['order']==2){arsort($data);}
							$chartmeta['type']=$charttype;$chartmeta['yaxis']=__('% Correct','wpcues-quiz-pro');
						}
						break;
				}
			}
		}
		
		if(!(empty($chartmeta))){
			$content='<div id="anviprochartholder" style="width:'.$chartoptions["width"].$widthunit.';height:'.$chartoptions["height"].$heightunit.';margin:0 auto"></div>';
			wp_localize_script('anvipro-chart', 'anviprochart', array('data'=>$data,'chartoptions'=>$chartmeta));}else{$content='No Grade ';}	
		return $content;
	}
		public static function gradegroup($quizid){
			$grademeta=array();
			$gradedef=get_post_meta($quizid,'quizgrade',true);
			if(empty($gradedef)){
				$post_categories = wp_get_object_terms( $quizid,'quizcategory');
				foreach($post_categories as $postcat){
					$gradedef=get_option('anviprocatgrade_'.$postcat->term_id);
					if($gradedef){break;}
				}
				if(!($gradedef)){
					$gradedef=get_option('anviprodefaultgrade');}
			}
			
			return $gradedef;
		}
		public function check_chartstatus(){
			$chartcount=wp_count_posts('wpcuebasicchart')->publish;
			$disabledchart=0;
			if(!empty($this->wpcuebasicquiz_version) && ($chartcount >=5)){
				$disabledchart=1;
			}
			return $disabledchart;
		}
    } // END class AnviProGrade
} // END if(!class_exists('AnviProGrade'))
/* EOF */