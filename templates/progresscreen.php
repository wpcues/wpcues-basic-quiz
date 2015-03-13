<div id='progressscreen'>
<ul class="innertabs">
<li class='quizfinalscreen'><a href="#progressscreen-1"><?php _e('Final Screen','wpcues-basic-quiz');?></a></li>
<li class='intermediatescreen <?php if(empty($basicsetting['login'])){echo 'hiddendiv'; } ?>'><a href="#progressscreen-2"><?php _e('Intermediate Screen','wpcues-basic-quiz');?></a></li>
<li class='completedscreen <?php if(empty($basicsetting['login'])){echo 'hiddendiv'; } ?>'><a href="#progressscreen-3"><?php _e('Completion Screen','wpcues-basic-quiz');?></a></li>
</ul>
<div id='progressscreen-1' class='quizfinalscreen'>
		<?php
		if(!empty($quizmeta['quizfinal'][0])){$defcontent=$quizmeta['quizfinal'][0];}else{
		$defcontent=__('<p>Congratulations - you have completed %%QUIZNAME%%.<br/></p>','wpcues-basic-quiz');}
		wp_editor( $defcontent,'quizfinal',array('textarea_rows'=>50,'editor_class'=> 'requiredvar','editor_height'=>100,'default_editor'=>'tinymce','quicktags'=>true,'dfw'=>true));?>
		<h3 class='smallheader'><?php _e('Usable  Variables','wpcues-basic-quiz');?></h3>
		<div class='entitymsg'>(<?php _e('All the variables can be used in grade descriptions as well','wpcues-basic-quiz');?>.)</div>
		<table id='usablevariable' class="widefat fixed">
		<thead>
		<tr><th class="leftcolumn"><?php _e('Variable','wpcues-basic-quiz');?></th><th class="rightcolumn"><?php _e('Value','wpcues-basic-quiz');?></th></tr>
		</thead>
		<tbody>
		<tr class='quizmodedep'><td class="leftcolumn"><?php _e('%%CORRECT%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Number of correct answers','wpcues-basic-quiz');?></td></tr>
		<tr><td class="leftcolumn"><?php _e('%%TOTAL%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Total number of questions','wpcues-basic-quiz');?></td></tr>
		<tr class='quizmodedep'><td class="leftcolumn"><?php _e('%%POINTS%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Total points scored','wpcues-basic-quiz');?></td></tr>
		<tr class='quizmodedep'><td class="leftcolumn"><?php _e('%%MAXPOINTS%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Maximum number of points','wpcues-basic-quiz');?></td></tr>
		<tr class='quizmodedep'><td class="leftcolumn"><?php _e('%%GRADE%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Assigned Grade','wpcues-basic-quiz');?></td></tr>
		<tr class='quizmodedep'><td class="leftcolumn"><?php _e('%%GDESC%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Show grade detail','wpcues-basic-quiz');?></td></tr>
		<tr><td class="leftcolumn"><?php _e('%%QUIZNAME%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Quiz Name','wpcues-basic-quiz');?></td></tr>
		<tr><td class="leftcolumn"><?php _e('%%UNTRIED%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Number of questions not attempted','wpcues-basic-quiz');?></td></tr>
		<tr class='quizmodedep'><td class="leftcolumn"><?php _e('%%WRONG%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Number of wrong answers','wpcues-basic-quiz');?></td></tr>
		<tr><td class="leftcolumn"><?php _e('%%DATE%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Date on which quiz is taken','wpcues-basic-quiz');?></td></tr>
		<tr><td class="leftcolumn"><?php _e('%%EMAIL%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('User Email Address','wpcues-basic-quiz');?></td></tr>
		<tr><td class="leftcolumn"><?php _e('%%USERNAME%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('User Name','wpcues-basic-quiz');?></td></tr>
		<tr class='quizmodedep'><td class="leftcolumn"><?php _e('%%AVGPOINTS%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Shows the average points scored by others who took the same quiz','wpcues-basic-quiz');?></td></tr>
		<tr class='quizmodedep'><td class="leftcolumn"><?php _e('%%AVGCORRECT%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Shows the perecentage average correct answers given by others who took the same quiz','wpcues-basic-quiz');?></td></tr>
		<tr><td class="leftcolumn"><?php _e('%%TIMEALLOWED%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Time allowed','wpcues-basic-quiz');?></td></tr>
		<tr><td class="leftcolumn"><?php _e('%%TIMEUSED%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Time used','wpcues-basic-quiz');?></td></tr>
		<tr><td class="leftcolumn"><?php _e('%%SOCIALSHARE%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Social Share Buttons','wpcues-basic-quiz');?></td></tr>
		<tr><td class="leftcolumn"><?php _e('%%REPORT%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Complete Question answer report (log) with answers marked by user','wpcues-basic-quiz');?></td></tr>
		<tr><td class="leftcolumn"><?php _e('%%CERTIFICATE%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Certificate','wpcues-basic-quiz');?></td></tr>
		<tr><td class="leftcolumn"><?php _e('%%CERTIFICATELINK%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Certificate Url to be opened in new tab/window','wpcues-basic-quiz');?></td></tr>
		</tbody>
		</table>
		</div>
<div id='progressscreen-2' class='intermediatescreen'>
<p class='entitymsg'><?php _e('This screen will be shown when user has not completed the test.','wpcues-basic-quiz');?></p>
<?php if(empty($quizmeta['quizintermediate'][0])){
	$quizmeta['quizintermediate'][0]=__('<p>You have %%PERCENTQUEST%% completed the quiz %%QUIZNAME%%.</p>','wpcues-basic-quiz');
	} 
wp_editor($quizmeta['quizintermediate'][0],'quizintermediate',array('textarea_rows'=>50,'editor_class'=> 'requiredvar','editor_height'=>60,'default_editor'=>'tinymce','quicktags'=>true,'dfw'=>true)); ?>
<h3 class='smallheader'><?php _e('Usable  Variables','wpcues-basic-quiz');?></h3>
		<div class='entitymsg'>(<?php _e('You can use variables allowed for final screen here also.Additionally, you can also use following variables.','wpcues-basic-quiz');?>)</div>
		<table id='usablevariable' class="widefat fixed">
		<thead>
		<tr><th class="leftcolumn"><?php _e('Variable','wpcues-basic-quiz');?></th><th class="rightcolumn"><?php _e('Value','wpcues-basic-quiz');?></th></tr>
		</thead>
		
		<tbody>
		<tr class='quizmodedep'><td class="leftcolumn"><?php _e('%%PERCENTPOINT%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Percentage Completion on the Basis of Points','wpcues-basic-quiz');?></td></tr>
		<tr><td class="leftcolumn"><?php _e('%%PERCENTQUEST%%','wpcues-basic-quiz');?></td><td class="rightcolumn"><?php _e('Percentage Completion on the Basis of Questions tried','wpcues-basic-quiz');?></td></tr>
		</tbody>
		</table>
</div>
<div id='progressscreen-3' class='completedscreen'>
<p class='entitymsg'><?php _e('This screen will be shown when user has completed the test and he can not take test again.','wpcues-basic-quiz');?></p>
<p class='entitymsg'><?php _e('You can use variables allowed for final screen here.','wpcues-basic-quiz');?></p>
<?php if(empty($quizmeta['quizcomplete'][0])){
	$quizmeta['quizcomplete'][0]=__('<p>You have completed the quiz %%QUIZNAME%%.</p>','wpcues-basic-quiz');
	} 
	wp_editor($quizmeta['quizcomplete'][0],'quizcomplete',array('textarea_rows'=>50,'editor_class'=> 'requiredvar','editor_height'=>60,'default_editor'=>'tinymce','quicktags'=>true,'dfw'=>true)); ?>
</div>
</div>
