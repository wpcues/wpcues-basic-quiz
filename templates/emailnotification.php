<div id="emailnotificationtab">
	<ul class="innertabs"><li><a href='#emailnot1'><?php _e('Admin','wpcues-basic-quiz');?></a></li><li><a href="#emailnot2"><?php _e('User','wpcues-basic-quiz');?></a></li></ul>
	<div id="emailnot1">
	<?php 
		if(!(empty($quizmeta['quizadminemail']))){$adminemail=maybe_unserialize($quizmeta['quizadminemail'][0]);}else{$adminemail=array();}
		if(empty($adminemail['subject'])){$adminemail['subject']=__('Quiz Results For %%QUIZNAME%%','wpcues-basic-quiz');} ?>
	<p><label><?php _e('Subject','wpcues-basic-quiz');?> : </label><input type="text" name="adminemailsubject" value="<?php echo $adminemail['subject']; ?>" class="requiredvar" style="width:50%;"></p>
	<?php if(empty($adminemail['mail'])){$adminemail['mail']=__('%%USERNAME%% has taken the quiz %%QUIZNAME%% on %%DATE%%.The report is as follows : %%REPORT%%','wpcues-basic-quiz');} 
		wp_editor( $adminemail['mail'],'adminemail',array('textarea_rows'=>50,'editor_height'=>100,'default_editor'=>'tinymce','editor_class'=> 'requiredvar','quicktags'=>true,'dfw'=>true));?>
	</div>
	<div id="emailnot2">
	<?php 
	if(!(empty($quizmeta['quizuseremail']))){$useremail=maybe_unserialize($quizmeta['quizuseremail'][0]);}else{$useremail=array();}
	if(empty($useremail['subject'])){$useremail['subject']=__('Quiz Results For %%QUIZNAME%%','wpcues-basic-quiz');} ?>
	<p><label><?php _e('Subject','wpcues-basic-quiz');?> : </label><input type="text" name="useremailsubject" value="<?php echo $useremail['subject']; ?>" class="requiredvar" style="width:50%;"></p>
	<?php if(empty($useremail['mail'])){$useremail['mail']=__('Thanks %%USERNAME%% for taking %%QUIZNAME%% quiz.','wpcues-basic-quiz');} 
		wp_editor( $useremail['mail'],'useremail',array('textarea_rows'=>50,'editor_height'=>100,'default_editor'=>'tinymce','editor_class'=> 'requiredvar','quicktags'=>true,'dfw'=>true));?>
	</div>
	<div>
	<h3 class='smallheader'><?php _e('Usable  Variables','wpcues-basic-quiz');?></h3>
		<table id='usablevariable' class="widefat fixed">
		<thead>
		<tr><th class="leftcolumn"><?php _e('Variable','wpcues-basic-quiz');?></th><th class="rightcolumn"><?php _e('Value','wpcues-basic-quiz');?></th></tr>
		</thead>
		<tbody>
		<tr class='personalityquizdep'><td class="leftcolumn"><?php _e('%%RESULT%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Result obtained as per user replies','wpcues-basic-quiz');?></td></tr>
		<tr class='quizmodedep'><td class="leftcolumn"><?php _e('%%CORRECT%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Number of correct answers','wpcues-basic-quiz');?></td></tr>
		<tr><td class="leftcolumn"><?php _e('%%TOTAL%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Total number of questions','wpcues-basic-quiz');?></td></tr>
		<tr class='quizmodedep'><td class="leftcolumn"><?php _e('%%POINTS%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Total points scored','wpcues-basic-quiz');?></td></tr>
		<tr class='quizmodedep'><td class="leftcolumn"><?php _e('%%MAXPOINTS%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Maximum number of points','wpcues-basic-quiz');?></td></tr>
		<tr class='quizmodedep'><td class="leftcolumn"><?php _e('%%GRADE%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Assigned Grade','wpcues-basic-quiz');?></td></tr>
		<tr class='quizmodedep'><td class="leftcolumn"><?php _e('%%GDESC%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Show grade detail','wpcues-basic-quiz');?></td></tr>
		<tr><td class="leftcolumn"><?php _e('%%QUIZNAME%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Quiz Name','wpcues-basic-quiz');?></td></tr>
		<tr><td class="leftcolumn"><?php _e('%%UNTRIED%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Number of questions not attempted','wpcues-basic-quiz');?></td></tr>
		<tr class='quizmodedep'><td class="leftcolumn"><?php _e('%%WRONG%','wpcues-basic-quiz');?>%</td><td class="rightcolumn"><?php _e('Number of wrong answers','wpcues-basic-quiz');?></td></tr>
		<tr><td class="leftcolumn"><?php _e('%%DATE%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Date on which quiz is taken','wpcues-basic-quiz');?></td></tr>
		<tr><td class="leftcolumn"><?php _e('%%EMAIL%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('User Email Address','wpcues-basic-quiz');?></td></tr>
		<tr><td class="leftcolumn"><?php _e('%%USERNAME%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('User Name','wpcues-basic-quiz');?></td></tr>
		<tr class='quizmodedep'><td class="leftcolumn"><?php _e('%%AVGPOINTS%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Shows the average points scored by others who took the same quiz','wpcues-basic-quiz');?></td></tr>
		<tr class='quizmodedep'><td class="leftcolumn"><?php _e('%%AVGCORRECT%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Shows the perecentage average correct answers given by others who took the same quiz','wpcues-basic-quiz');?></td></tr>
		<tr><td class="leftcolumn"><?php _e('%%TIMEALLOWED%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Time allowed','wpcues-basic-quiz');?></td></tr>
		<tr><td class="leftcolumn"><?php _e('%%TIMEUSED%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Time used','wpcues-basic-quiz');?></td></tr>
		<tr><td class="leftcolumn"><?php _e('%%REPORT%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Complete Question answer report (log) with answers marked by user','wpcues-basic-quiz');?></td></tr>
		</tbody>
		</table>
	</div>
</div>