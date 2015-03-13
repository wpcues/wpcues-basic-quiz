<?php $can_publish = current_user_can($post_type_object->cap->publish_posts);?>
<div id="side-sortables" class="meta-box-sortables"><div id="submitdiv" class="postbox " >
<div class="handlediv" title="Click to toggle"><br /></div><h3 class='hndle'><span><?php _e('Publish','wpcues-basic-quiz');?></span></h3>
<div class="inside">
<div class="submitbox" id="submitpost">
<div id="quizbasicsetting">
<?php $quizinfo=$WpCueBasicQuiz->quizinfo($post_id);?>
<ul>
	<li><?php _e('Quiz Duration (in minutes)','wpcues-basic-quiz');?> : <input type='text' name='quizduration'  class='requiredvar' value='<?php if(!(empty($basicsetting['duration']))){echo intval($basicsetting['duration'])/60;}else{echo 0;} ?>'  style='width:60px;'></li>
	<li><?php _e('Questions per page','wpcues-basic-quiz');?> : 
		<select name="questperpage" id="questnumpage">
			<option value="0" <?php if(empty($basicsetting['questperpage'])){echo 'selected'; }?>><?php _e('All','wpcues-basic-quiz');?></option>
			
			<?php 
			
			if(!empty($quizinfo['totalquestions'])){
						for($i=1;$i<=$quizinfo['totalquestions']-1;$i++){
							echo '<option value="'.$i.'"';
							if($basicsetting['questperpage']==$i){echo 'selected'; }
							echo '>'.$i.'</option>';}
					}
			?>
		</select>
	</li>
	<li class="notifyaqsmail"><input type='checkbox' name='notifyadmin' class='requiredvar' value='1' <?php if(!(empty($basicsetting['notifyadmin']))){echo 'checked'; }?>><?php _e('Notify me when someone takes quiz','wpcues-basic-quiz');?></li>
	<li class="notifyuqsmail"><input type='checkbox' name='notifyuser' class='requiredvar' value='1' <?php if(!(empty($basicsetting['notifyuser']))){echo 'checked'; }?> ><?php _e('Send email to user','wpcues-basic-quiz');?></li>
	<li><?php _e('Mode','wpcues-basic-quiz');?> : <select name="quizmode"><option value="1" <?php if(!(empty($basicsetting['mode'])) && ($basicsetting['mode']==1)){echo 'selected';}?>><?php _e('Exam','wpcues-basic-quiz');?></option><option value="2" <?php if(!(empty($basicsetting['mode'])) && ($basicsetting['mode']==2)){echo 'selected';}?> disabled><?php _e('Practice','wpcues-basic-quiz');?></option></select><span class="procontent"></span></li>
	<li><input type='checkbox' name='loginrequired' id='loginrequired' value='1' class='requiredvar' <?php if(!empty($basicsetting['login'])){echo 'checked';$logindep=1;}else{$logindep=0;}?>><?php _e('Login required to take quiz','wpcues-basic-quiz');?></li>
	<li class='multilognum logindep <?php if($logindep==0){echo 'hiddendiv';} ?>'>
		<?php _e('Number of times user can take quiz','wpcues-basic-quiz');?> : 
		<input type='text' name='lognum' class='requiredvar' value="<?php if(isset($basicsetting['lognum'])){echo $basicsetting['lognum'];}else{echo '0';} ?>" style="width:60px;">
	</li>
	<li class='multiloggap logindep <?php if($logindep==0){echo 'hiddendiv';} ?>'>
		<?php _e('Minimum time interval between each try (in minutes)','wpcues-basic-quiz');?> : 
		<input type='text' name='loggap' class='requiredvar' value='<?php if(isset($basicsetting['loggap'])){echo $basicsetting['loggap'];}else{echo 0;} ?>'  style="width:60px;"/>
	</li>
	<li><?php _e('Disclose Answers in report','wpcues-basic-quiz');?> <select name="discloseans">
			<option value='1' <?php if(empty($basicsetting['discloseans']) || ($basicsetting['discloseans']==1)){echo 'selected';}?>><?php _e('All','wpcues-basic-quiz');?></option>
			<option value="0" <?php if((isset($basicsetting['discloseans']))&&($basicsetting['discloseans']==0)){echo 'selected';}?> disabled><?php _e('None','wpcues-basic-quiz');?></option>
			<option value="2" <?php if((!empty($basicsetting['discloseans']))&&($basicsetting['discloseans']==2)){echo 'selected';}?> disabled><?php _e('only Tried','wpcues-basic-quiz');?></option>
			<option value="3" <?php if((!empty($basicsetting['discloseans']))&&($basicsetting['discloseans']==3)){echo 'selected';}?> disabled><?php _e('Only tried and correct','wpcues-basic-quiz');?></option>
		</select><span class="procontent"></span>
	</li>
</ul>
</div>
<div id="minor-publishing">

<?php // Hidden submit button early on so that the browser chooses the right button when form is submitted with Return key ?>
<div style="display:none;">
<?php submit_button( __( 'Save' ), 'button', 'save' ); ?>
</div>


<div id="major-publishing-actions">
<div id="delete-action">
<?php
if ( current_user_can( "delete_post", $post->ID ) ) {
	if ( !EMPTY_TRASH_DAYS )
		$delete_text = __('Delete Permanently');
	else
		$delete_text = __('Move to Trash');
	?>
<a class="submitdelete deletion" href="<?php echo get_delete_post_link($post->ID); ?>"><?php echo $delete_text; ?></a><?php
} ?>
</div>

<div id="publishing-action">
<span class="spinner"></span>
<?php
if ( !in_array( $post->post_status, array('publish', 'future', 'private') ) || 0 == $post->ID ) {
	if ( $can_publish ) :
		if ( !empty($post->post_date_gmt) && time() < strtotime( $post->post_date_gmt . ' +0000' ) ) : ?>
		<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Schedule') ?>" />
		<?php submit_button( __( 'Schedule' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
<?php	else : ?>
		<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Publish') ?>" />
		<?php submit_button( __( 'Publish' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
<?php	endif;
	else : ?>
		<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Submit for Review') ?>" />
		<?php submit_button( __( 'Submit for Review' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
<?php
	endif;
} else { ?>
		<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Update') ?>" />
		<?php submit_button( __( 'Update' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
<?php
} ?>
</div>
<div class="clear"></div>
</div>
</div>
</div>
	</div>
	</div>