<?php $wpcuebasicquiz_version=get_option('wpcuebasicquiz_version');?>
<div class="wrap">
	<h2>Report</h2>
	<div id="tabs">
		<ul>
            <li><a href="#tabs-1"><?php _e('LeaderBoard','wpcues-basic-quiz'); ?></a></li>
            <li><a href="#tabs-2"><?php _e('Charts','wpcues-basic-quiz');?></a></li>
        </ul>
		<div id="tabs-1">
			<div id="innertabs">
				<ul >
					<li><a href="#innertabs1"><?php _e('Gloabal leaderboard','wpcues-basic-quiz');?></a></li>
					<li><a href="#innertabs2"><?php _e('Quiz Specific','wpcues-basic-quiz');?></a></li>
				</ul>
				<div id="innertabs1">
				<?php 
					$promessage='<div class="announcecontent"><div class="procontent"></div><div class="protext">You can have only five global leaderboard in basic version.To remove this lock and use this feature extensively, please buy the pro version of plugin. </div>
</div>';
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
						if(!empty($wpcuebasicquiz_version) && ($query->found_posts >=5)){
							$disabledglobalboard=1;
						}
						
						echo '<div id="globalleaderboardpromessage" ';
						if(empty($disabledglobalboard)){echo 'style="display:none;"';}
						echo '>';
						echo $promessage;
						echo '</div>';
				?>
				<input type="hidden" name="disabledglobalboard" id="disabledglobalboard" value="<?php echo $disabledglobalboard;?>">
					<div id='divbuttons' style='margin:1em 0em;'>
						<input type='button' name='addglobal-leaderboard' id='add_global_leaderboard' value='Add New' class='button button-secondary' <?php if(!empty($disabledglobalboard)){echo 'disabled';} ?>>
					</div>
					<table id='gloabal_leaderboard' class='widefat fixed'>
						<thead>
							<tr>
								<th><?php _e('Title','wpcues-basic-quiz');?></th><th><?php _e('Shortcode','wpcues-basic-quiz');?></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th><?php _e('Title','wpcues-basic-quiz');?></th><th><?php _e('Shortcode','wpcues-basic-quiz');?></th>
							</tr>
						</tfoot>
						<tbody>
						<?php $rownum=0;
						
						if($query->found_posts>0){
							while ($query->have_posts()){
							$query->the_post();
							$post=$query->post;
							$postcontent=unserialize($post->post_content);
							$msg='<tr id="globalleaderboard-'.$post->ID.'" ';
							if($rownum%2 == 0){
							$msg.='class="alternate"';}
							$msg.='><td>'.$post->post_title.'<div class="row-actions">
<span class="edit"><a href="#globalleaderboardedit">'.__('Edit','wpcues-basic-quiz').'</a> | </span>
<span class="trash">
<a class="submitdelete" title="Delete" href="#globalleaderboarddelete">'.__('Delete','wpcues-basic-quiz').'</a> | </span>
</div>
</td><td>[WpCueBasicLeaderboard '.$post->ID.']</td></tr>';
							echo $msg;
							$rownum++;
							}
						}else{echo '<tr class="noleaderboard alternate" ><td>';_e('No Leaderboards present','wpcues-basic-quiz');echo '</td><td></td></tr>';}
						?>
						</tbody>
					</table>
				</div>
				<div id="innertabs2">
					<?php 
							$promessage='<div class="announcecontent"><div class="procontent"></div><div class="protext">You can have only five quiz specific leaderboard in basic version.To remove this lock and use this feature extensively, please buy the pro version of plugin. </div>
</div>';			
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
						if(!empty($wpcuebasicquiz_version) && ($query->found_posts >=5)){
							$disabledquizboard=1;
						}
						echo '<div id="quizleaderboardpromessage" ';
						if(empty($disabledquizboard)){echo 'style="display:none;"';}
						echo '>';
						echo $promessage;
						echo '</div>';
						
				?>
				<input type="hidden" name="disabledquizboard" id="disabledquizboard" value="<?php echo $disabledquizboard;?>">
					<div id='divbuttons' style='margin:1em 0em;'>
						<input type='button' name='quiz-leaderboard' id='add_quiz_leaderboard' value='Add New' class='button button-secondary' <?php if(!empty($disabledquizboard)){echo 'disabled';} ?>>
					</div>
					<table id='quiz_leaderboard' class='widefat fixed'>
						<thead>
							<tr>
								<th><?php _e('Title','wpcues-basic-quiz');?></th><th><?php _e('Shortcode','wpcues-basic-quiz');?></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th><?php _e('Title','wpcues-basic-quiz');?></th><th><?php _e('Shortcode','wpcues-basic-quiz');?></th>
							</tr>
						</tfoot>
						<tbody>
						<?php $rownum=0;
						if($query->found_posts>0){
							while ($query->have_posts()){
							$query->the_post();
							$post=$query->post;
							$postcontent=unserialize($post->post_content);
							$msg='<tr id="quizleaderboard-'.$post->ID.'" ';
							if($rownum%2 == 0){
							$msg.='class="alternate"';}
							$msg.='><td>'.$post->post_title.'<div class="row-actions">
<span class="edit"><a href="#quizleaderboardedit">'.__('Edit','wpcues-basic-quiz').'</a> | </span>
<span class="trash">
<a class="submitdelete" title="Delete" href="#quizleaderboarddelete">'.__('Delete','wpcues-basic-quiz').'</a> | </span>
</div>
</td><td>[WpCueBasicLeaderboard '.$post->ID.']</td></tr>';
							echo $msg;
							$rownum++;
							}
						}else{echo '<tr class="noleaderboard alternate" ><td>';_e('No Leaderboards present','wpcues-basic-quiz');echo '</td><td></td></tr>';}
						?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div id="tabs-2">
		<?php
			$args=array('post_type'=>'wpcuebasicchart');
			$query=new WP_QUERY($args); 
			$disabledchart=0;
			$promessage='<div class="announcecontent"><div class="procontent"></div><div class="protext">You can have only five charts in basic version.To remove this lock and use this feature extensively, please buy the pro version of plugin. </div>
</div>';
			if(!empty($wpcuebasicquiz_version) && ($query->found_posts >=5)){
				$disabledchart=1;
			}
			echo '<div id="chartpromessage" ';
			if(empty($disabledchart)){echo 'style="display:none;"';}
			echo '>';
			echo $promessage;
			echo '</div>';
		?>
		<input type="hidden" name="disabledchart" id="disabledchart" value="<?php echo $disabledchart;?>">
			<div id='divbuttons' style='margin:1em 0em;'>
					<input type='button' name='addnewchart' id='add_chart' value='Add New' class='button button-secondary' <?php if(!empty($disabledchart)){echo 'disabled';} ?>>
			</div>
			<table id='charttable' class='widefat fixed'>
						<thead>
							<tr>
								<th><?php _e('Title','wpcues-basic-quiz');?></th><th><?php _e('Chart Type','wpcues-basic-quiz');?></th><th><?php _e('Assigned Quiz ID','wpcues-basic-quiz'); ?></th><th><?php _e('Shortcode','wpcues-basic-quiz'); ?></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th><?php _e('Title','wpcues-basic-quiz');?></th><th><?php _e('Chart Type','wpcues-basic-quiz');?></th><th><?php _e('Assigned Quiz ID','wpcues-basic-quiz');?></th><th><?php _e('Shortcode','wpcues-basic-quiz');?></th>
							</tr>
						</tfoot>
						<tbody>
						<?php $rownum=0;
						
						if($query->found_posts>0){
							while ($query->have_posts()){
							$query->the_post();
							$post=$query->post;
							$postcontent=unserialize($post->post_content);
							if($postcontent['type'] == 1){$charttype='Bar Chart';}elseif($postcontent['type'] == 2){$charttype='Pie Chart';}elseif($postcontent['type'] == 3){$charttype='Line Chart';}
							$msg='<tr id="chart-'.$post->ID.'" ';
							if($rownum%2 == 0){
							$msg.='class="alternate"';}
							$msg.='><td>'.$post->post_title.'<div class="row-actions"><span class="edit"><a href="#chartedit">'.__('Edit','wpcues-basic-quiz').'</a> | </span>';
							$msg.='<span class="trash"><a class="submitdelete" title="Delete" href="#chartdelete">'.__('Delete','wpcues-basic-quiz').'</a> | </span></div></td><td>';
							$msg.=$charttype.'</td><td>'.$postcontent['quizid'].'</td><td>[wpcuebasicchart '.$post->ID.']</td></tr>';
							echo $msg;
							$rownum++;
							}
						}else{echo '<tr class="nocharts alternate" ><td>';_e('No charts present','wpcues-basic-quiz');echo '</td><td></td><td></td><td></td></tr>';}
						?>
						</tbody>
					</table>
		</div>
	</div>
	</div>
	<div id='addgloballeaderboard' class='wp-dialog'>
		<form id='globleaderboard' name='globleaderboard' method='post' action=''>
		<input type='hidden' name='action' value='wpcuequizaddleaderboard_action'>
		<input type='hidden' name='globalleaderboardid' id='globalleaderboardid' value='0'>
		<table id='globalleaderboardtable' class='widefat fixed'>
		<tr><td><?php _e('Leaderboard Name','wpcues-basic-quiz');?></td><td><input type="text" value="" name="leaderboardtitle" id='leaderboardtitle'></td></tr>
		<tr>
			<td><?php _e('Order','wpcues-basic-quiz');?></td><td><select name='leaderorder' id='leaderorder'><option value='top'><?php _e('Top','wpcues-basic-quiz');?></option><option value='bottom'><?php _e('Bottom','wpcues-basic-quiz');?></option></select></td></tr>
		
		<tr><td><?php _e('Leaders','wpcues-basic-quiz');?></td><td><input type='text' name='leadersnum' id='leadersnum' value='10'></td></tr>
		<tr><td><?php _e('Based on','wpcues-basic-quiz');?></td>
			<td>
				<select name='leaderbasis' id='leaderbasis'>
					<option value='1'><?php _e('Total Points Collected','wpcues-basic-quiz');?></option>
					<option value='2'>% <?php _e('Correct Answers','wpcues-basic-quiz');?></option>
					<option value='3'><?php _e('Number of unique tests taken','wpcues-basic-quiz');?></option>
				</select>
			</td>
			</tr>
		
		</table>
		</form>
	</div>
	<div id='addquizleaderboard' class='wp-dialog'>
		<form id='quizleaderboard' name='quizleaderboard' method='post' action=''>
		<input type='hidden' name='action' value='wpcuequizaddleaderboard_action'>
		<input type='hidden' name='quizleaderboardid' id='quizleaderboardid' value='0'>
		<table id='quizleaderboardtable' class='widefat fixed'>
		<tr><td><?php _e('Leaderboard Name','wpcues-basic-quiz');?></td><td><input type="text" value="" name="leaderboardtitle" id='quizleaderboardtitle'></td></tr>
		<tr>
			<td><?php _e('Order','wpcues-basic-quiz');?></td><td><select name='leaderorder' id='quizleaderorder'><option value='top'><?php _e('Top','wpcues-basic-quiz');?></option><option value='bottom'><?php _e('Bottom','wpcues-basic-quiz');?></option></select></td></tr>
		
		<tr><td><?php _e('Leaders','wpcues-basic-quiz');?></td><td><input type='text' name='leadersnum' value='10' id='quizleadersnum'></td></tr>
		<tr><td><?php _e('Based on','wpcues-basic-quiz');?></td>
			<td>
				<select name='leaderbasis' id='quizleaderbasis'>
					<option value='1'><?php _e('Total Points Collected','wpcues-basic-quiz');?></option>
					<option value='2'>% <?php _e('Correct Answers','wpcues-basic-quiz');?></option>
				</select>
			</td>
			</tr>
		<tr><td><?php _e('Quiz','wpcues-basic-quiz');?></td><td>
		<select name='quizname' id='quizname'>
		<option value='0' selected><?php _e('Select a quiz','wpcues-basic-quiz');?></option>
		<?php 
			$args=array('post_type'=>'wpcuebasicquiz','post_status'=>'publish');
			$query=new WP_QUERY($args);
			if($query->found_posts>0){
				while ($query->have_posts()){
					$query->the_post();
					$post=$query->post;
					echo '<option value="'.$post->ID.'">'.$post->post_title.'</option>';
				}
			}
		?>
		</select>
		</td></tr>
		</table>
		</form>
	</div>
	<div id='chartform'  class='wp-dialog'>
		<form id='quizchart' name='quizchart' method='post' action=''>
		<input type='hidden' name='action' value='wpcuequizaddchart_action'>
		<input type='hidden' name='chartid' id='chartid' value='0'>
			<table id='quizchartform' class='widefat fixed'>
				<tr><td><?php _e('Chart Name','wpcues-basic-quiz');?></td><td><input type='text' name='chartname' id='chartname'></td></tr>
				<tr><td><?php _e('Chart Type','wpcues-basic-quiz');?></td>
					<td><select name='charttype' id='charttype'>
						<option value='0'><?php _e('select chart type','wpcues-basic-quiz');?></option>
						<option value='1'><?php _e('Bar Chart','wpcues-basic-quiz');?></option>
						<option value='2'><?php _e('Pie Chart','wpcues-basic-quiz');?></option>
						<option value='3'><?php _e('Line Chart','wpcues-basic-quiz');?></option>
						</select>
					</td>
				</tr>
				<tr><td><?php _e('Width','wpcues-basic-quiz');?></td>
				<td><input type='text' name='chartwidth' id='chartwidth' value='600'>
					<select name='chartwidthunit' id='chartwidthunit'><option value='1'><?php _e('Pixel','wpcues-basic-quiz');?></option>
					<option value='2'><?php _e('Percentage','wpcues-basic-quiz');?></option>
					</select>
				</td>
				</tr>
				<tr><td><?php _e('Height','wpcues-basic-quiz');?></td>
				<td><input type='text' name='chartheight' id='chartheight' value='600'>
					<select name='chartheightunit' id='chartheightunit'><option value='1'><?php _e('Pixel','wpcues-basic-quiz');?></option>
					<option value='2'><?php _e('Percentage','wpcues-basic-quiz');?></option>
					</select>
				</td>
				</tr>
				<tr id='chartorder' class='hiddenrow'><td><?php _e('Order','wpcues-basic-quiz');?></td>
				<td>
					<select name='chartorderval'>
						<option value='1'><?php _e('Ascending','wpcues-basic-quiz'); ?></option>
						<option value='2'><?php _e('Descending','wpcues-basic-quiz'); ?></option>
						<option value='3'><?php _e('Fixed order','wpcues-basic-quiz'); ?></option>
					</select>
				</td></tr>
				</tr>
				
				<tr>
				<td><?php _e('Quiz','wpcues-basic-quiz');?></td>
				<td><select name='quizval' id='quizval'>
				<option value='0'><?php _e('Select quiz','wpcues-basic-quiz');?></option>
				<?php 
				$args=array('post_type'=>'wpcuebasicquiz','post_status'=>'publish');
				$query=new WP_QUERY($args);
				if($query->found_posts>0){
					while ($query->have_posts()){
						$query->the_post();
						$post=$query->post;
						echo '<option value="'.$post->ID.'">'.$post->post_title.'</option>';
					}
				}
				?>
				</select>
				</tr>
				<tr id='chartoption' class='hiddenrow'><td><input type='radio' name='chartoptionval' value='1'><?php _e('Quiz Generic','wpcues-basic-quiz');?><input type='radio' name='chartoptionval' value='2'><?php _e('User Specific','wpcues-basic-quiz');?></td>
				<tr id='chartgenericoption' class='hiddenrow'><td><?php _e('Option','wpcues-basic-quiz');?></td><td>
				<ul>
					<li><select name='chartgenericoptionval' id='chartgenericoptionval'>
							<option value='1'><?php _e('By Grades','wpcues-basic-quiz');?></option>
							<option value='2'><?php _e('By Point','wpcues-basic-quiz');?></option>
							<option value='3'><?php _e('By %Correct Answer','wpcues-basic-quiz');?></option>
						</select>
					</li>
					<li class="groupnum hiddenrow">
						Number of divisions (<span class="pointtext hiddenrow">points</span><span class="correctanstext hiddenrow">Correct answer</span>)
						<input type="text" name="groupnum" value="2">
					</li>
				</ul>
				</td></tr>
				<tr id='chartuserrow' class='hiddenrow'><td><?php _e('User','wpcues-basic-quiz');?></td><td>
				<?php $args=array('orderby'=>'ID','id'=>'chartuser','name'=>'chartuser'); wp_dropdown_users($args);?>
				</td></tr>
				<tr  id='chartuseroption' class='hiddenrow'><td><?php _e('Option','wpcues-basic-quiz');?></td>
				<td><ul><li><select name='chartuseroptionval' id='chartuseroptionval'><option value='1'><?php _e('By Question Category','wpcues-basic-quiz');?></option></select></li><li>
				<select name='chartuseroptionsecval' id='chartuseroptionsecval'><option value='1'><?php _e('By Points','wpcues-basic-quiz');?></option><option value='2'>%<?php _e('Correct Answer','wpcues-basic-quiz');?></option></select>
				</li></ul></td></tr>
			</table>
		</form>
	</div>
<style>
.wp-dialog{display:none;}
.ui-dialog-titlebar{display:none;}
.hiddenrow{display:none;}
select option:disabled {
    display:none;
}
.announcecontent{min-height:4em;margin:1em 0.5em;padding:0.75em 3.5em;background:#fff;letter-spacing: 2px;width:90%;}
.procontent{float:left;width:5%;;background:#fff;}.protext{width:95%;margin:0;padding:0;float:right;}


</style>