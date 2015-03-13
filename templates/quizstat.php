<?php
global $wpdb,$wp;
$table_name = $wpdb->prefix.'wpcuequiz_quizstat';	
if(isset($_GET['tab'])){$activetab=$_GET['tab']-1;}else{$activetab=0;}
?>
<div class="wrap">
<h2><?php _e('Statistics','wpcues-basic-quiz');?></h2>
<div id="tabs" class="quizstattab">
		<ul class="outertabs">
            <li><a href="#tabs-1"><?php _e('Logs','wpcues-basic-quiz');?></a></li>
			<li <?php if($activetab != 1){echo ' style="display:none;"';}?>><a href="#tabs-2"><?php _e('Detailed Report','wpcues-basic-quiz');?></a></li>
        </ul>
		<div id='tabs-1'>
			<div id="logtablestat">
			<?php		
			$statlogs=$wpdb->get_results("select sql_calc_found_rows instanceid,userid,quizid,status,starttime,endtime from $table_name order by endtime desc",ARRAY_A);	
			$count = $wpdb->get_var('SELECT FOUND_ROWS()');
		if(!(empty($statlogs))){
			$pagenum=ceil($count/5);
			if($pagenum > 1){
			$paged=1;
			$firstpage=add_query_arg(array('tab'=>1),admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequizstatistics'));
			if($paged>1){$prevpage=add_query_arg(array('tab'=>1,'paged'=>$paged-1),admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequizstatistics'));}
			else{$prevpage=add_query_arg(array('tab'=>1,'page'=>$paged),admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequizstatistics'));}
			if($paged==$pagenum){$nextpage=add_query_arg(array('tab'=>1,'paged'=>$paged),admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequizstatistics'));}
			else{$nextpage=add_query_arg(array('tab'=>1,'paged'=>$paged+1),admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequizstatistics'));}
			$lastpage=add_query_arg(array('tab'=>1,'paged'=>$pagenum),admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequizstatistics'));
			echo '<div class="tablenav top">';
			echo '<div class="tablenav-pages">';
			echo '<span class="displaying-num">'.$pagenum.' items</span>';
			echo '<span class="pagination-links"><a class="first-page';if($paged==1){echo ' disabled';}echo '" title="Go to the first page" href="'.$firstpage.'">&laquo;</a>';
			echo '<a class="prev-page';if($paged==1){echo ' disabled';}echo '" title="Go to the previous page" href="'.$prevpage.'">&lsaquo;</a>';
			echo '<span class="paging-input"><label for="current-page-selector" class="screen-reader-text">Select Page</label><input class="current-page" id="current-page-selector" title="Current page" type="text" name="paged" value="'.$paged.'" size="1" /> of <span class="total-pages">'.$pagenum.'</span></span>';
			echo '<a class="next-page';if($paged==$pagenum){echo ' disabled';}echo '" title="Go to the next page"	href="'.$nextpage.'">&rsaquo;</a>';
			echo '<a class="last-page';if($paged==$pagenum){echo ' disabled';}echo '" title="Go to the last page" href="'.$lastpage.'">&raquo;</a></span>';
			echo '</div></div>';
			}
			echo '<table class="wp-list-table widefat fixed posts">';
			echo '<thead><tr><th scope="col" class="manage-column">Quiz</th><th  scope="col" class="manage-column">User Id</th><th  scope="col" class="manage-column">Status</th><th scope="col" class="manage-columng">Started on</th><th scope="col" class="manage-columng">Completed on</th></tr></thead>';
			echo '<tfoot><tr><th scope="col" class="manage-column">Quiz</th><th  scope="col" class="manage-column">User Id</th><th  scope="col" class="manage-column">Status</th><th scope="col" class="manage-columng">Started on</th><th scope="col" class="manage-columng">Completed on</th></tr></tfoot>';
			echo '<tbody>';
			$userids=array();$quizids=array();$i=0;
			foreach($statlogs as $statlog){
				$userids[$i]=$statlog['userid'];
				$quizids[$i]=$statlog['quizid'];$i++;
			}
			$args = array('include'=>$userids,'number'=>25,'offset' => 0);
			$user_query=new WP_User_Query($args);
			$userdesc=array();
			if ( ! empty( $user_query->results ) ) {
					foreach ( $user_query->results as $user ) {
						$userdesc['i'.$user->ID]=$user->display_name;
					}
			}
			$args = array('post__in'=>$quizids,'post_type'=>array('wpcuebasicquiz'),'orderby'=>'post__in','posts_per_page' => -1);
			$quizquery = get_posts($args);
			$quizdesc=array();
			foreach($quizquery as $quiz){
				$quizdesc['i'.$quiz->ID]=$quiz->post_title;
			}
			$i=0;
			foreach($statlogs as $statlog){
				$viewurl=add_query_arg(array('instance'=>$statlog['instanceid'],'action'=>'view','tab'=>2),admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequizstatistics'));
				$deleteurl=add_query_arg(array('instance'=>$statlog['instanceid'],'action'=>'delete','tab'=>1),admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequizstatistics'));
				echo '<tr ';
				if(!($i%2)){echo 'class="alternate"';}
				if($statlog['status']==1){$statusdesc='Completed';}else{$statusdesc='Incomplete';}
				echo '><td><a href="'.$viewurl.'">'.$quizdesc['i'.$statlog['quizid']].'</a><div class="row-actions"><span class="view"><a href="'.$viewurl.'">';
				_e('View','wpcues-basic-quiz');echo '</a>|</span><span class="delete"><a href="'.$deleteurl.'">';
				_e('Delete','wpcues-basic-quiz');echo '</a></span></div></td><td>'.$userdesc['i'.$statlog['userid']].'</td><td>'.$statusdesc.'</td><td>'.$statlog['starttime'].'</td><td>'.$statlog['endtime'].'</td></tr>';
				$i++;
			}
			echo '</tbody></table>';
			if($pagenum>1){
				
			echo '<div class="tablenav bottom">';
			echo '<div class="tablenav-pages">';
			echo '<span class="displaying-num">'.$pagenum.' items</span>';
			echo '<span class="pagination-links"><a class="first-page';if($paged==1){echo ' disabled';}echo '" title="Go to the first page" href="'.$firstpage.'">&laquo;</a>';
			echo '<a class="prev-page';if($paged==1){echo ' disabled';}echo '" title="Go to the previous page" href="'.$prevpage.'">&lsaquo;</a>';
			echo '<span class="paging-input"><label for="current-page-selector" class="screen-reader-text">Select Page</label><input class="current-page" id="current-page-selector" title="Current page" type="text" name="paged" value="'.$paged.'" size="1" /> of <span class="total-pages">'.$pagenum.'</span></span>';
			echo '<a class="next-page';if($paged==$pagenum){echo ' disabled';}echo '" title="Go to the next page"	href="'.$nextpage.'">&rsaquo;</a>';
			echo '<a class="last-page';if($paged==$pagenum){echo ' disabled';}echo '" title="Go to the last page" href="'.$lastpage.'">&raquo;</a></span>';
			echo '</div></div>';
			}
		}
		
		?>
		</div>
		</div>
		<div id='tabs-2'>
		<div id="detailedreportstat">
		<?php
			if($activetab==1){
			global $wpdb;
			$WpCueBasicQuiz=new WpCueBasicQuiz();
			$instanceid=$_GET['instance'];
			$table_name=$wpdb->prefix.'wpcuequiz_quizstat';
			$table_name4=$wpdb->prefix.'wpcuequiz_quizinfo';
			$quizstat=$wpdb->get_row($wpdb->prepare("select quizid,UNIX_TIMESTAMP(endtime) as endtime,status from $table_name where instanceid=%d",$instanceid),ARRAY_A);
			$entitystat=$wpdb->get_results($wpdb->prepare("SELECT entityid,questionchange,UNIX_TIMESTAMP(questionchangedate) as questionchangedate from $table_name4 where quizid=%d",$postid),OBJECT_K);
			if(isset($_GET['quiz'])){$quizid=$_GET['quiz'];}else{$quizid=$quizstat['quizid'];}
			if(isset($_GET['user'])){$userid=$_GET['user'];}
			$table_name=$wpdb->prefix.'wpcuequiz_quizstatinfo';
			$entities=$wpdb->get_results($wpdb->prepare("SELECT entityid,answer,reply,status from $table_name where instanceid=%d order by id asc",$instanceid),OBJECT_K);
			$i=1;$entityids=array_keys($entities);
			if(empty($entityids)){
				$entityids=$WpCueBasicQuiz->entityids($quizid);
			}
			$args = array( 'post__in'=>$entityids,'post_type'=>array('wpcuebasicquestion','wpcuebasicsection'),'orderby'=>'post__in','posts_per_page' => -1);
			$entityquery = new WP_Query($args);$report='';
			while ($entityquery->have_posts()){
				$entityquery->the_post();
				$entitypost=$entityquery->post;
				$entityid=$entitypost->ID;
				if($entitypost->post_type=='wpcuebasicquestion'){
				$entitymeta=unserialize($entitypost->post_content);
					if(!empty($entities[$entityid]) && ((empty($entitystat[$entityid]->questionchange)) || ($quizstat['endtime'] > $entitystat[$entityid]->questionchangedate))){
						$answer=$entities[$entityid]->answer;
						$reply=$entities[$entityid]->reply;
						$status=$entities[$entityid]->status;
					}else{
						switch($entitymeta['t']){
							case 1:
								$answer=serialize($entitymeta['a']['id']);
								break;
							case 2:
								$answer=serialize($entitymeta['a']['id']);
								break;
							case 3:
								$answerar['la']=unserialize($entitymeta['la']['id']);
								$answerar['ra']=unserialize($entitymeta['ra']['id']);
								$leftcount=count($questmeta['la']['id']);$rightcount=count($questmeta['ra']['id']);
								if($leftcount <= $rightcount){
									$matchcount=$leftcount;$column='rightcolumn';
								}else{
									$matchcount=$rightcount;$column='leftcolumn';
								}
								$answerar['column']=$column;
								$answerar['count']=$matchcount;
								$answer=serialize($answerar);
								break;
							case 4:
								$answer=serialize($entitymeta['a']['id']);
								break;
							case 5:
								if(!(empty($entitymeta['c']))){$answer=serialize($entitymeta['c']);}else{$answer='';}
								break;
							case 6:
								
								$answer=serialize(array(1,0));
								break;
						}
						$reply='';$status=4;
					}
					$report.=$WpCueBasicQuiz->wpcue_report($answer,$reply,$status,$entitymeta,1,$i,0);
					$i++;
				}
			}
			echo $report;
			}
		?>
		</div>
		</div>
	</div>
<?php

?>
<script>
jQuery(document).ready(function($){
var activetab=<?php echo $activetab; ?>;
$( "#tabs" ).tabs({active: activetab});
$("#quiztabs").tabs(<?php if(isset($activelog) && ($activelog !=0)){echo '{active : 2}';}?>);
$("#usertabs").tabs(<?php if(isset($activelog) && ($activelog !=0)){echo '{active : 1}';}?>);
});
</script>