<?php
/**
*WpCueBasicLeaderboard class
*/
if(!class_exists('WpCueBasicLeaderboard'))
{
    class WpCueBasicLeaderboard
    {
        const POST_TYPE = "wpcuebasicleader";
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
			add_action('wp_ajax_wpcuequizaddleaderboard_action',array(&$this,'add_leaderboard'));
			add_action('wp_ajax_wpcuequizretrieveleaderboardinfo_action',array(&$this,'retrieveleaderboard_info'));
			add_action('wp_ajax_wpcuequizdeleteleaderboard_action',array(&$this,'delete_leaderboard'));
			add_shortcode('wpcuebasicleader',array(&$this,'leaderboard_shortcode'));
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
	public function add_leaderboard(){
		$leaderboardtitle=$_POST['leaderboardtitle'];
		if(isset($_POST['globalleaderboardid'])){$globalleaderboardid=$_POST['globalleaderboardid'];}
		if(isset($globalleaderboardid)){$postid=$globalleaderboardid;}
		if(isset($_POST['quizleaderboardid'])){$quizleaderboardid=$_POST['quizleaderboardid'];}
		if(isset($quizleaderboardid)){$postid=$quizleaderboardid;}
		$post_content['leaderorder']=$_POST['leaderorder'];
		$post_content['leadersnum']=$_POST['leadersnum'];
		$post_content['leaderbasis']=$_POST['leaderbasis'];
		if(!empty($_POST['quizname'])){$post_content['quizid']=$_POST['quizname'];}
		$postcontent=serialize($post_content);
		if(!(empty($postid))){
			$postid=wp_update_post(array('ID'=>$postid,'post_title'=>$leaderboardtitle,'post_content'=>$postcontent,'post_status'=>'publish','post_type'=>'wpcuebasicleader'));
		}else{
			$postid=wp_insert_post(array('post_title'=>$leaderboardtitle,'post_content'=>$postcontent,'post_status'=>'publish','post_type'=>'wpcuebasicleader'));
		}
		if(isset($post_content['quizid']) && !(empty($postid)) ){update_post_meta($postid,'leaderboardtype',1);}else{update_post_meta($postid,'leaderboardtype',0);}
		if(empty($post_content['quizid'])){$disabledstatus=$this->check_globalstatus();}else{$disabledstatus=$this->check_quizstatus();}
		echo json_encode(array('postid'=>$postid,'title'=>$leaderboardtitle,'disabledstatus'=>$disabledstatus));
		die();
		}
	public function retrieveleaderboard_info(){
		$postid=$_POST['postid'];
		$post=get_post($postid);
		$leaderboardtitle=$post->post_title;
		$postcontent=unserialize($post->post_content);
		$leaderorder=$postcontent['leaderorder'];
		$leadersnum=$postcontent['leadersnum'];
		$leaderbasis=$postcontent['leaderbasis'];
		if(!empty($postcontent['quizid'])){$quizid=$postcontent['quizid'];}else{$quizid=0;}
		echo json_encode(array('leaderboardid'=>$postid,'leaderboardtitle'=>$leaderboardtitle,'leaderorder'=>$leaderorder,'leadersnum'=>$leadersnum,'leaderbasis'=>$leaderbasis,'quizid'=>$quizid));
		die();
	}
	public function delete_leaderboard(){
		$postid=$_POST['postid'];
		$leaderboardtype=$_POST['leaderboardtype'];
		if(wp_delete_post($postid,true)){
			if(!empty($leaderboardtype)){$disabledstatus=$this->check_quizstatus();}else{$disabledstatus=$this->check_globalstatus();}
			echo json_encode(array('msg'=>'success','disabledstatus'=>$disabledstatus));
		}else{
			echo json_encode(array('msg'=>'failure'));
		}
		die();
	}
	public function leaderboard_shortcode($atts){
		global $wpdb;
		$table_name1=$wpdb->prefix.'wpcuequiz_quizstat';	
		$table_name2=$wpdb->prefix.'wpcuequiz_quizstatinfo';
		$table_name3=$wpdb->prefix.'users';
		$leaderboardid=$atts[0];
		$leaderboard=get_post($leaderboardid);
		$option=unserialize($leaderboard->post_content);
		$type=$option['leaderboardtype'];
		$content='<div class="leaderboard"><h2>'.$leaderboard->post_title.'</h2><table class="wpcueleader">';
		$basis=$option['leaderbasis'];
		$order=$option['leaderorder'];
		$num=$option['leadersnum'];
		if($type=='quiz'){
			$quizid=$option['quizid'];
			if($quizid==-1){
				$quizid=$atts['quizid'];
				if(!($quizid)){$content='true to core';return $content;}
			}
			if($quizid){
				if($basis==1){
					$content.='<thead><tr><th>'.__('User','wpcues-basic-quiz').'</th><th>'.__('Points','wpcues-basic-quiz').'</th></tr></thead><tbody>';
					if($order=='top'){
						$leaders=$wpdb->get_results($wpdb->prepare("select a.userid as userid,sum(b.point) as point from $table_name1 a join $table_name2 b where a.instanceid=b.instanceid and a.status=1 and a.quizid=%d and a.userid !=0 and a.mode=1 group by userid order by point desc LIMIT %d",$quizid,$num),OBJECT_K);
					}else{
						$leaders=$wpdb->get_results($wpdb->prepare("select a.userid as userid,sum(b.point) as point from $table_name1 a join $table_name2 b where a.instanceid=b.instanceid and a.status=1 and a.quizid=%d and a.userid !=0 and a.mode=1 group by userid order by point asc LIMIT %d",$quizid,$num),OBJECT_K);
					}
					$userids=array_keys($leaders);$users='('.implode(',',$userids).')';
				}else{
					$content.='<thead><tr><th>'.__('User','wpcues-basic-quiz').'</th><th>% '.__('Correct Answers','wpcues-basic-quiz').'</th></tr></thead><tbody>';
					if($order=='top'){
						$leaders=$wpdb->get_results($wpdb->prepare("select a.userid as userid,count(b.id) as counter from $table_name1 a join $table_name2 b where a.instanceid=b.instanceid and a.status=1 and b.status IN (1,2) and a.Quizid=%d and a.userid !=0 and a.mode=1 group by a.userid order by counter desc LIMIT %d",$quizid,$num),OBJECT_K);
					}else{
						$leaders=$wpdb->get_results($wpdb->prepare("select a.userid as userid,count(b.id) as counter from $table_name1 a join $table_name2 b where a.instanceid=b.instanceid and a.status=1 and b.status IN (1,2) and a.quizid=%d and a.userid !=0 and a.mode=1 group by a.userid order by counter asc LIMIT %d",$quizid,$num),OBJECT_K);
					}	
					$userids=array_keys($leaders);$users='('.implode(',',$userids).')';	
					$totalpoint=$wpdb->get_results($wpdb->prepare("select a.userid as userid,count(b.id) as counter from $table_name1 a join $table_name2 b where a.instanceid=b.instanceid and a.userid in $users and a.status=1 and a.mode=1 and a.quizid=%d group by userid",$quizid),OBJECT_K);
				}
				$user_query=$wpdb->get_results("select ID,display_name from $table_name3 where ID in $users",OBJECT_K);
				if($leaders){foreach($leaders as $leader){
					$userid=$leader->userid;if(isset($user_query[$userid])){$displayname=$user_query[$userid]->display_name;}else{$displayname='Unknown';}
					if($basis==1){$point=$leader->point;}else{$point=(($leader->counter * 100)/($totalpoint[$userid]->counter));}
						$content.='<tr><td>'.$displayname.'</td><td>'.$point.'</td></tr>';
				}}else{$content.='<tr class="norecords"><td>'.__('Nobody has taken this quiz right now.','wpcues-basic-quiz').'</tr></td>';}
					$content.='</tbody>';
			}else{$content.="<tr class='norecords'><td>".__('Donot have assigned quiz','wpcues-basic-quiz')."</td></tr>";}
		}else{
			if($basis==1 || $basis==2){
				if($basis==1){
					$content.='<thead><tr><th>'.__('User','wpcues-basic-quiz').'</th><th>'.__('Points','wpcues-basic-quiz').'</th></tr></thead><tbody>';
					if($order=='top'){
						$leaders=$wpdb->get_results($wpdb->prepare("select a.userid as userid,sum(b.point) as point from $table_name1 a join $table_name2 b where a.instanceid=b.instanceid and a.status=1 and a.mode=1 and a.userid !=0 group by userid order by point desc LIMIT %d",$num));
					}
					else{
						$leaders=$wpdb->get_results($wpdb->prepare("select a.userid as userid,sum(b.point) as point from $table_name1 a join $table_name2 b where a.instanceid=b.instanceid and a.status=1 and a.mode=1 and a.userid !=0 group by userid order by point asc LIMIT %d",$num));
					}
				}elseif($basis==2){
					$content.='<thead><tr><th>'.__('User','wpcues-basic-quiz').'</th><th>% '.__('Correct Answers','wpcues-basic-quiz').'</th></tr></thead><tbody>';
					if($order=='top'){
						$leaders=$wpdb->get_results($wpdb->prepare("select a.userid as userid,count(b.id) as counter from $table_name1 a join $table_name2 b where a.instanceid=b.instanceid and a.status=1 and b.status in (1,2) and a.mode=1 and a.userid != 0 group by a.userid order by counter desc LIMIT %d",$num),OBJECT_K);
					}else{
						$leaders=$wpdb->get_results($wpdb->prepare("select a.userid as userid,count(b.id) as counter from $table_name1 a join $table_name2 b where a.instanceid=b.instanceid and a.status=1 and b.status in (1,2) and a.mode=1 and a.userid != 0 group by a.userid order by counter asc LIMIT %d",$num),OBJECT_K);
					}
				}
				$userids=array_keys($leaders);$users='('.implode(',',$userids).')';
				if($basis==2){
					$totalpoint=$wpdb->get_results("select a.userid as userid,count(b.id) as counter from $table_name1 a join $table_name2 b where a.instanceid=b.instanceid and a.userid in $users and a.status=1 and a.mode=1 group by a.userid",OBJECT_K);
				}
				$user_query=$wpdb->get_results("select ID,display_name from $table_name3 where ID in $users",OBJECT_K);
				if($leaders){foreach($leaders as $leader){
						$userid=$leader->userid;if(isset($user_query[$userid])){$displayname=$user_query[$userid]->display_name;}else{$displayname='Unknown';}
						if($basis==1){$point=$leader->point;}else{$point=(($leader->counter * 100)/($totalpoint[$userid]->counter));}
						$content.='<tr><td>'.$displayname.'</td><td>'.$point.'</td></tr>';
					}
					$content.='</tbody>';
				}
			}else{
				$content.='<thead><tr><th>'.__('User','wpcues-basic-quiz').'</th><th>'.__('Number of Quizzes taken','wpcues-basic-quiz').'</th></tr></thead><tbody>';
				if($order=='top'){
					$leaders=$wpdb->get_results($wpdb->prepare("select b.user_login as username,count(distinct a.quizid) as counter from $table_name1 a join $table_name3 b where a.userid=b.ID and a.status=1 and a.userid !=0 and a.mode=1 group by a.userid order by counter desc limit %d",$num));
				}else{
					$leaders=$wpdb->get_results($wpdb->prepare("select b.user_login as username,count(distinct a.Quizid) as counter from $table_name a join $table2 b where a.userid=b.ID and a.status=1 and a.userid !=0 and a.mode=1 group by a.userid order by counter asc limit %d",$num));
				}
				if($leaders){foreach($leaders as $leader){
						$content.='<tr><td>'.$leader->username.'</td><td>'.$leader->counter.'</td></tr>';
					}
					$content.='</tbody>';
				}
			}
			
		}
		$content.='</table></div>';
		return $content;
	}
	public function check_globalstatus(){
		$args=array('post_type'=>'wpcuebasicleader',
								'meta_query' => array(
									array(
										'key'     => 'leaderboardtype',
										'value'   =>0,
										'type'=>'numeric',
									)
								)
							);
		$query=new WP_QUERY($args); $disabledglobalboard=0;
		if(!empty($this->wpcuebasicquiz_version) && ($query->found_posts >=5)){
				$disabledglobalboard=1;
		}
		return $disabledglobalboard;
	}
		public function check_quizstatus(){
		$args=array('post_type'=>'wpcuebasicleader',
								'meta_query' => array(
									array(
										'key'     => 'leaderboardtype',
										'value'   =>1,
										'type'=>'numeric',
									)
								)
							);
		$query=new WP_QUERY($args); $disabledquizboard=0;
		if(!empty($this->wpcuebasicquiz_version) && ($query->found_posts >=5)){
				$disabledquizboard=1;
		}
		return $disabledquizboard;
	}
    } // END class WpCueBasicLeaderboard
} // END if(!class_exists('WpCueBasicLeaderboard'))
/* EOF */