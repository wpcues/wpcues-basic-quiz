<?php
/**
*WpCueBasicBadge class
*/
if(!class_exists('WpCueBasicBadge'))
{
    class WpCueBasicBadge
    {
        const POST_TYPE = "wpcuebasicbadge";public $wpprocuesetting;
		/**
		* The Constructor
		*/
		public function __construct(){
			// register actions
			add_action('init', array(&$this, 'init'));
			$this->wpprocuesetting=get_option('wpcuequiz_setting');
		} // END public function __construct()
		/**
		* hook into WP's init action hook
		*/
		public function init(){
			// Initialize Post Type
			$this->create_post_type();
			add_filter('manage_wpcuebasicbadge_posts_columns',array(&$this,'new_badge_columns'));
			add_action('manage_wpcuebasicbadge_posts_custom_column',array(&$this,'cusotm_badge_columns'),10,2);
			add_action('schedule_wpcuebadgelevel_cron',array(&$this,'schedule_cron'));
			add_filter('post_row_actions',array($this,'my_badge_list'),11,2);
			add_action('wp_ajax_addbadge_action',array(&$this,'add_newbadge'));
			add_action('wp_ajax_trashbadge_action',array(&$this,'trash_badge'));
			add_filter('get_edit_post_link',array(&$this,'edit_badge_link'),10, 3);
			add_action('admin_head',array(&$this,'reset_post_new_link'));
			add_action('update_option_wpcuequiz_setting',array(&$this,'scheduler_cron'),10,2);
			add_action('wp_ajax_wpcuequizbadgesuccess_action',array(&$this,'update_badgestat'));
		} // END public function init()

		/**
		* Create the post type
		*/
		public function create_post_type(){
			$labels = array(
				'name'               => _x( 'Badges', 'post type general name', 'wpcues-basic-quiz' ),
				'singular_name'      => _x( 'Badge', 'post type singular name', 'wpcues-basic-quiz' ),
				'menu_name'          => _x( 'Badges', 'admin menu', 'wpcues-basic-quiz' ),
				'name_admin_bar'     => _x( 'Badge', 'add new on admin bar', 'wpcues-basic-quiz' ),
				'add_new'            => _x( 'Add New', 'Badge', 'wpcues-basic-quiz' ),
				'add_new_item'       => __( 'Add New Badge', 'wpcues-basic-quiz' ),
				'new_item'           => __( 'New Badge', 'wpcues-basic-quiz' ),
				'edit_item'          => __( 'Edit Badge', 'wpcues-basic-quiz' ),
				'view_item'          => __( 'View Badge', 'wpcues-basic-quiz' ),
				'all_items'          => __( 'All Badges', 'wpcues-basic-quiz' ),
				'search_items'       => __( 'Search Badges', 'wpcues-basic-quiz' ),
				'parent_item_colon'  => __( 'Parent Badges:', 'wpcues-basic-quiz' ),
				'not_found'          => __( 'No Badges found.', 'wpcues-basic-quiz' ),
				'not_found_in_trash' => __( 'No Badges found in Trash.', 'wpcues-basic-quiz' )
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
				'supports'           => array( 'title', 'editor'),
				'rewrite' => array('slug'=>'badge')
		
			);
			register_post_type(self::POST_TYPE,$args);
		}
		/**
		*Special Scripts for badge table page
		*/
		public static function wpcue_probadge_admin_scripts(){
			wp_register_script( 'wpprocue-badge', plugins_url( '../js/wpprocue-badge.js', __FILE__ ),array('jquery') );
			wp_enqueue_script('wpprocue-badge');
			wp_register_style( 'createquiz', plugins_url('../css/createquiz.css',__FILE__));
			wp_enqueue_style('createquiz');
		}
		/**
		* create new badge
		*/
		public function set_badge(){
			$post=get_default_post_to_edit(self::POST_TYPE,true);
			return $post;
		}
		/**
		* Ajax function to retrieve Add new link
		*/
		public function add_newbadge(){echo json_encode(array('msg'=>admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequizbadge')));die();}
		/**
		*Edit row actions for Level table
		*/
		public function my_badge_list($actions,$post){
			if($post->post_type=='wpcuebasicbadge' && 'trash' != $post->post_status ){
				$post_type_object = get_post_type_object( $post->post_type );
				$can_edit_post = current_user_can( 'edit_post', $post->ID );
				unset($actions['edit']);
				$action = '&action=edit';
				$posturl='&post='.$post->ID;
				$action='<a href="'.admin_url(sprintf('edit.php?post_type=wpcuebasicquiz&page=wpcuequizbadge'.$action.$posturl)).'">Edit</a>';
				$actions['edit']=$action;
				unset($actions['inline hide-if-no-js']);
				$actions['inline hide-if-no-js'] = '<a href="#" class="editinline" title="' . esc_attr( __( 'Edit this item inline' ) ) . '">' . __( 'Quick&nbsp;Edit' ) . '</a>';
				unset($actions['trash']);
				$actions['trash'] = "<a class='submitdelete' title='" . esc_attr( __( 'Move this item to the Trash' ) ) . "' href='" . get_delete_post_link( $post->ID ) . "'>" . __( 'Trash' ) . "</a>";
				//unset($actions['view']);
				
			}
			return $actions;
		}
		/**
		*Trash Level
		*/
		public function trash_badge(){
			
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
				$sendback=admin_url('edit.php?post_type=wpcuebasicbadge');
				$sendback = add_query_arg( array('trashed' => $trashed, 'ids' => join(',', $post_ids), 'locked' => $locked ), $sendback );
				
				
				echo json_encode(array('msg'=>'success','redirecturl'=>$sendback));
				die();
			}
		/**
		*Add New columns for Badge table
		*/
		public function new_badge_columns($columns){
			$columns['raqpoint']=__('Points','wpcue-basic-quiz');
			$columns['corranswer']=__('%Correct Answer','wpcue-basic-quiz');
			$columns['quiznum']=__('Quizzes','wpcue-basic-quiz');
			$columns['quizcat']=__('Quiz Categories','wpcue-basic-quiz');
			$columns['badgeshortcode']=__('Badge Shortcode','wpcue-basic-quiz');
			unset($columns['date']);
			return $columns;
		}
		/**
		*New custom column handles
		*/
		public function cusotm_badge_columns($column,$post_id){
			global $wpdb;
			$badgemeta=get_post_custom($post_id);
			switch($column){
				case 'raqpoint':
					echo $badgemeta['wpcuebadgepoint'][0];
					break;
				case 'corranswer':
					echo $badgemeta['wpcuebadgecorper'][0];
					break;
				case 'quiznum' :
					echo $badgemeta['wpcuebadgequiznum'][0];
					break;
				case 'quizcat' :
					if(!(empty($badgemeta['wpcuebadgequizcat'][0]))){$catids=maybe_unserialize($badgemeta['wpcuebadgequizcat'][0]);
					$content='';
					$count=count($catids);
					$i=1;
					foreach($catids as $catid){
						$cat=get_term_by('id',$catid,'wpcuebasicquizcat');
						$content.=$cat->name;
						if($i != $count){$content.=', ';}
						$i++;
					}}else{$content='-';}
					echo $content;
					break;
			}
		}
		public function reset_post_new_link(){
			global $post_new_file,$post_type_object;
			if (!isset($post_type_object) || 'wpcuebasicbadge' != $post_type_object->name) return false;
			$post_new_file ='edit.php?post_type=wpcuebasicquiz&page=wpcuequizbadge';
		}
		public function edit_badge_link($url,$post_id, $context ){
			global $typenow;
			if($typenow=='wpcuebasicbadge'){
				$action='&action=edit';
				$posting='&post='.$post_id;
				$url=admin_url(sprintf('edit.php?post_type=wpcuebasicquiz&page=wpcuequizbadge'. $action.$posting));
			}
			return $url;
		}
		
		public function schedule_cron(){
			global $wpdb;
			$table_name=$wpdb->prefix.'wpcuequiz_quizstat';
			$table_name1=$wpdb->prefix.'wpcuequiz_quizstatinfo';
			$table_name2=$wpdb->prefix.'wpcuequiz_badgestat';
			$processids=$wpdb->get_col("SELECT distinct instanceid from $table_name where status=1 and mode=1 and processed=0");
			$userids=$wpdb->get_col("select distinct userid from $table_name where status=1 and mode=1 and processed=0 ");
			if(!(empty($userids))){
				$useridstring='('.implode(",",$userids).')';
				$processidstring='('.implode(",",$processids).')';
				$query='select userid,quizid from '.$table_name.' where status=1 and userid in '.$useridstring.' group by userid,quizid';
				$userquiz=$wpdb->get_results($query,ARRAY_A);
				$query='select a.userid as userid,b.status as status,sum(b.point) as point,count(b.id) as counter from '.$table_name.' a, '.$table_name1.' b  where a.instanceid=b.instanceid and a.status=1 and a.userid in '.$useridstring.' and b.status != -1 group by userid,status order by userid';
				$userstat=$wpdb->get_results($query,ARRAY_A); 	
				$query='SELECT userid,badgeid from '.$table_name2.' where userid in '.$useridstring;
				$issuedbadgeidsbyusers=$wpdb->get_results($query,ARRAY_A);
				$userbadge=array();
				foreach($issuedbadgeidsbyusers as $issuedbadgeidbyuser){
					if(empty($userbadge[$issuedbadgeidbyuser['userid']])){
						$userbadge[$issuedbadgeidbyuser['userid']]=array($issuedbadgeidbyuser['badgeid']);
					}else{
						array_push($userbadge[$issuedbadgeidbyuser['userid']],$issuedbadgeidbyuser['badgeid']);
					}
				} 
				foreach($userquiz as $user){
					$userid=$user['userid'];
					$quizid=$user['quizid'];
					if(empty($userquizstat[$userid]['quizid'])){
						$userquizstat[$userid]['quizid']=array($quizid);
					}else{
						array_push($userquizstat[$userid]['quizid'],$quizid);
					}
					if(empty($userquizstat[$userid]['quizcount'])){
						$userquizstat[$userid]['quizcount']=1;
					}else{
						$userquizstat[$userid]['quizcount']=$userquizstat[$userid]['quizcount']+1;
					}
				}
				foreach($userstat as $stat){
					$userid=$stat['userid'];
					if(isset($userquizstat[$userid]['point'])){
						$userquizstat[$userid]['point']+=$stat['point'];
					}else{
						$userquizstat[$userid]['point']=$stat['point'];
					}
					if(($stat['status']==1) || ($stat['status']==2)){
						if(isset($userquizstat[$userid]['correct'])){
							$userquizstat[$userid]['correct']++;
						}else{$userquizstat[$userid]['correct']=1;}
					}
					if(isset($userquizstat[$userid]['total'])){
						$userquizstat[$userid]['total']++;}else{$userquizstat[$userid]['total']=1;}
				}
				
				foreach($userids as $userid){
					$point=$userquizstat[$userid]['point'];$quizcount=$userquizstat[$userid]['quizcount'];
					if(!(empty($userquizstat[$userid]['correct']))){
						$correctper=($userquizstat[$userid]['correct']*100/($userquizstat[$userid]['total']));
					}
					$quizids=$userquizstat[$userid]['quizid'];
					$this->assignlevel($point,$quizcount,$correctper,$userid,$quizids);
					if(!empty($userbadge[$userid])){$userbardge=$userbadge[$userid];}else{$userbadge=array();}
					$this->assignbadge($point,$quizcount,$correctper,$userid,$quizids,$userbadge);
					$wpdb->query($wpdb->prepare("UPDATE $table_name SET processed=1 where userid = %d and instanceid in $processidstring"),$userid);
				}
			}
		}
		public function assignlevel($point,$quizcount,$correctper,$userid,$quizids){
			$wpprocuesetting=$this->wpprocuesetting;
			$assignedlevel=get_user_meta($userid,'wpcueassignedlevel',true);
			if(!(empty($assignedlevel))){
					$existinglevelrank=get_post_meta($assignedlevel,'wpcuelevelrank',true);
					$nextlevelrank=$existinglevelrank+1;
			}else{$nextlevelrank=1;}
			$args = array(
				'post_type'  => 'wpcuebasiclevel',
				'posts_per_page'=>-1,
				'meta_query' => array(
					array(
						'key'     => 'wpcuelevelrank',
						'value'   =>(int) $nextlevelrank,
						'type'=>'numeric',
					),
					array(
						'key'=>'wpcuelevelpoints',
						'value'=>(int)$point,
						'compare'=> '<=',
						'type'=>'numeric'
					),
					array(
						'key'=>'wpcuelevelpercorrect',
						'value'=>(int)$correctper,
						'type'    => 'numeric',
						'compare'=> '<=',
					),array(
						'key'=>'wpcuelevelquiznum',
						'value'=>(int)$quizcount,
						'type'=>'numeric',
						'compare'=> '<=',
					),
					
				),
			);
			$levelquery=new WP_QUERY($args);
			while ($levelquery->have_posts()){
				$levelquery->the_post();
				$level=$levelquery->post;
				$newlevelid=$level->ID;
				
			}
			wp_reset_postdata();
			if(!(empty($newlevelid))){
				$status=1;
				$newlevelmeta=get_post_custom($newlevelid);
				if(!empty($newlevelmeta['wpcuelevelquizcat'][0])){$newlevelquizcats=maybe_unserialize($newlevelmeta['wpcuelevelquizcat'][0]);}
				if(!(empty($newlevelquizcats))){
					$args = array(
						'post_type' => 'wpcuebasicquiz',
						'post__not_in'=>$quizids,
						'posts_per_page'=>-1,
						'tax_query' => array(
							array(
								'taxonomy' => 'wpcuebasicquizcat',
								'field'    => 'term_id',
								'terms'    =>$newlevelquizcats,
							),
						),
					);
					$quizquery = new WP_Query( $args );
					if($quizquery->found_posts > 0){$status=0;}
				}
				if($status==1){
					update_user_meta($userid,'wpcueassignedlevel',$newlevelid);
				}	
				if(!(empty($wpprocuesetting['basic']['leveladmin'])) || (!(empty($wpprocuesetting['basic']['leveluser'])))){
					$this->send_levelmail($newlevelid,$assignedlevel,$userid);
				}
			}
		}
		public function assignbadge($point,$quizcount,$correctper,$userid,$quizids,$assignedbadges){
			global $wpdb;
			$wpprocuesetting=$this->wpprocuesetting;
			$newbadges=array();
			$args = array(
				'post_type'  => 'wpcuebasicbadge',
				'post__not_in'=>$assignedbadges,
				'posts_per_page'=>-1,
				'meta_query' => array(
					array(
						'key'=>'wpcuebadgepoint',
						'value'=>(int)$point,
						'type'=>'numeric',
						'compare'=> '<=',
					),
					array(
						'key'=>'wpcuebadgecorper',
						'value'=>(int)$correctper,
						'type'=>'numeric',
						'compare'=> '<=',
					),
					array(
						'key'=>'wpcuebadgequiznum',
						'value'=>(int)$quizcount,
						'type'=>'numeric',
						'compare'=> '<=',
					),
				),
			);
			$badgequery=new WP_QUERY($args);
			while ($badgequery->have_posts()){
				$badgequery->the_post();
				$badge=$badgequery->post;
				$badgeid=$badge->ID;
				$status=1;
				$newbadgemeta=get_post_custom($badgeid);
				if(!(empty($newbadgemeta['wpcuebadgequizcat'][0]))){$newbadgequizcats=maybe_unserialize($newbadgemeta['wpcuebadgequizcat'][0]);}
				if(!(empty($newlevelquizcats))){
					$args = array(
						'post_type' => 'wpcuebasicquiz',
						'post__not_in'=>$quizids,
						'posts_per_page'=>-1,
						'tax_query' => array(
							array(
								'taxonomy' => 'wpcuebasicquizcat',
								'field'    => 'term_id',
								'terms'    =>$newlevelquizcats,
							),
						),
					);
				
					$quizquery = new WP_Query( $args );
					if($quizquery->found_posts > 0){$status=0;}
				}
				
				if(!empty($status)){array_push($newbadges,$badgeid);}
			}
			if(!(empty($newbadges))){
				foreach($newbadges as $badgeid){
					$wpcuebadgemozstatus=get_post_meta($badgeid,'wpcuebadgemozstatus',true);
					$badgemoz[$badgeid]['mozstatus']=$wpcuebadgemozstatus;
					$table_name=$wpdb->prefix.'wpcuequiz_badgestat';
					$wpdb->insert($table_name,array('badgeid'=>$badgeid,'userid'=>$userid,'status'=>$wpcuebadgemozstatus),array('%d','%d','%d'));
					$badgeuid=$wpdb->insert_id;
					$badgemoz[$badgeid]['uid']=$badgeuid;
				}
				if(!(empty($wpprocuesetting['basic']['badgeadmin'])) || (!(empty($wpprocuesetting['basic']['badgeadmin'])))){
					$this->send_badgemail($userid,$newbadges,$badgemoz);
				}
			}
		}			
		public function send_levelmail($newlevelid,$oldlevelid,$userid){
			$newlevel=get_post($newlevelid);
			$oldlevel=get_post($oldlevelid);
			$user=get_user_by('id',$userid);
			$pattern=array('/%%USERNAME%%/','/%%EMAIL%%/','/%%NEWLEVEL%%/','/%%OLDLEVEL%%/');
			$replace=array($user->display_name,$user->user_email,$newlevel->post_title,$oldlevel->post_title);
			if(!(empty($wpprocuesetting['basic']['leveladmin']))){
				$email=$wpprocusetting['basic']['adminemail'];
				$subject=$wpprocuesetting['level']['adminemailsubj'];
				$subject=preg_replace($pattern,$replace,$subject);
				$body=$wpprocuesetting['level']['adminemailbody'];
				$body=preg_replace($pattern,$replace,$body);
				add_filter('wp_mail_content_type',array(&$this,'wpcuemail_set_content_type'));
				wp_mail($emailid,$subject,$body);
				remove_filter('wp_mail_content_type',array(&$this,'wpcuemail_set_content_type'));
			}
			if(!(empty($wpprocuesetting['basic']['leveluser']))){
				$emailid=$user->user_email;
				$subject=$wpprocuesetting['level']['useremailsubj'];
				$subject=preg_replace($pattern,$replace,$subject);
				$body=$wpprocuesetting['level']['useremailbody'];
				$body=preg_replace($pattern,$replace,$body);
				add_filter('wp_mail_content_type',array(&$this,'wpcuemail_set_content_type'));
				wp_mail($emailid,$subject,$body);
				remove_filter('wp_mail_content_type',array(&$this,'wpcuemail_set_content_type'));
			}
			
		}
		public function send_badgemail($userid,$newbadges,$badgemoz){
			$wpprocuesetting=$this->wpprocuesetting;
			foreach($newbadges as $newbadgeid){
				$newbadge=get_post($newbadgeid);$mozstatus=$badgemoz[$newbadgeid]['mozstatus'];
				$newbadgimageurl=get_post_meta($newbadgeid,'wpcuebadgeimage',true);
				$newbadgeimage='<img src="'.$newbadgimageurl.'">';
				$user=get_user_by('id',$userid);
				$pattern=array('/%%USERNAME%%/','/%%EMAIL%%/','/%%BADGENAME%%/','/%%BADGEIMAGE%%/');
				$replace=array($user->display_name,$user->user_email,$newbadge->post_title,$newbadgeimage);
				if(!(empty($wpprocuesetting['basic']['badgeadmin']))){
					$email=$wpprocuesetting['basic']['adminemail'];
					$subject=$wpprocuesetting['badge']['adminemailsubj'];
					$subject=preg_replace($pattern,$replace,$subject);
					$body=$wpprocuesetting['badge']['adminemailbody'];
					$body=preg_replace($pattern,$replace,$body);
					add_filter('wp_mail_content_type',array(&$this,'wpcuemail_set_content_type'));
					wp_mail($email,$subject,$body);
					remove_filter('wp_mail_content_type',array(&$this,'wpcuemail_set_content_type'));
				}
				if(!(empty($wpprocuesetting['basic']['badgeuser']))){
					array_push($pattern,'/%%BADGEOPENMOZURL%%/');
					if(!empty($mozstatus)){
						$mozurl=get_site_url().'/wpcuenewbadge/'.$badgemoz[$newbadgeid]['uid'];
						$mozanhor='<a href="'.$mozurl.'">'.$wpprocuesetting['badge']['mozurltext'].'</a>';
						array_push($replace,$mozanhor);
					}else{array_push($replace,'');}
					$emailid=$user->user_email;
					$subject=$wpprocuesetting['badge']['useremailsubj'];
					$subject=preg_replace($pattern,$replace,$subject);
					$body=$wpprocuesetting['badge']['useremailbody'];
					$body=preg_replace($pattern,$replace,$body);
					add_filter('wp_mail_content_type',array(&$this,'wpcuemail_set_content_type'));
					wp_mail($emailid,$subject,$body);
					remove_filter('wp_mail_content_type',array(&$this,'wpcuemail_set_content_type'));
				}
			}
		}
		public function wpcuemail_set_content_type(){
			return "text/html";
		}
		public function scheduler_cron($old_value,$new_value){
			if($new_value['basic']['badgelevelcron'] != $old_value['basic']['badgelevelcron']){
				$curtime=time();
				$prev = $curtime - ($curtime % 3600);
				$next = $prev + 3600;
				if($new_value['basic']['badgelevelcron']==1){
					$recurrence='hourly';
				}elseif($new_value['basic']['badgelevelcron']==2){
					$recurrence='daily';
				}else{$recurrence='twicedaily';}
				wp_clear_scheduled_hook('schedule_wpcuebadgelevel_cron');
				wp_schedule_event($next,$recurrence,'schedule_wpcuebadgelevel_cron');
			} 
		}
		public function update_badgestat(){
			$badgeguid=$_POST['badgeguid'];
			global $wpdb;$table_name=$wpdb->prefix.'wpcuequiz_badgestat';
			$status=$wpdb->update($table_name,array('status'=>0),array('id'=>$badgeguid),array('%d'),array('%d'));
			if($status){echo json_encode(array('msg'=>'success'));}else{echo json_encode(array('msg'=>'failed'));}
			die();
			
		}
    } // END class WpCueBasicBadge
} // END if(!class_exists('WpCueBasicBadge'))
/* EOF */