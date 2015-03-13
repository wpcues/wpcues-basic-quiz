<script>
jQuery(document).ready(function($){
function changemenu(){
$currentmenu=$('li.wp-has-current-submenu');
$currentmenuid=$currentmenu.attr('id');
if((typeof $currentmenuid === 'undefined') || ($currentmenuid !== 'toplevel_page_edit-post_type-wpcuebasicquiz')){
	$currentmenu.removeClass('wp-has-current-submenu');
	$('#toplevel_page_edit-post_type-wpcuebasicquiz').addClass('wp-has-current-submenu');
	$currentanchor=$('#toplevel_page_edit-post_type-wpcuebasicquiz').children('a');
	$currentanchor.removeClass('wp-not-current-submenu');
	$currentanchor.addClass('wp-has-current-submenu');
}
 $('a[href="edit.php?post_type=wpcuebasicquiz&page=wpcuequizcertificate"]').removeClass('current');
$('a[href="edit.php?post_type=wpcuebasicquiz&page=wpcuequizcertificate"]').parent('li').removeClass('current'); 
 $curvar=$('a[href="edit.php?post_type=wpcuecertificate"]');
$curvar.addClass('current');
$curvar.parent('li').addClass('current');}

changemenu();
$(document).on('click','.submitdelete',function(e){
e.preventDefault();
$.ajax({
            type: 'POST',
            dataType: 'json',
			url:ajaxurl,
            data: { 
                'action': 'wpcuequiztrashcerti_action',
				'postid':$('#postid').val()
				},
			success: function(data){
			msg=data.msg;
			if(data.msg=='success'){
			document.location.href=data.redirecturl;
			}else{
			alert('could not be trashed');
			}

}
});
});
$(document).on('click','.handlediv',function(){
$(this).siblings('.inside').toggle();
});
});
</script>
<?php 
$wpcuebasicquiz_version=get_option('wpcuebasicquiz_version');
$promessage='<div class="announcecontent"><div class="procontent"></div><div class="protext">You can have only five certificate in basic version.To remove this lock and use this feature extensively, please buy the pro version of plugin. </div>
</div>
<style>.announcecontent{min-height:4em;margin:1em 0.5em;padding:0.75em 3.5em;background:#fff;letter-spacing: 2px;width:90%;}
.procontent{float:left;width:5%;;background:#fff;}.protext{width:95%;margin:0;padding:0;float:right;}
.procontent::before{font:400 1.5em/1 dashicons;content: "\f312";color:#2EFE2E;padding:0.5em 0.5em;}
</style>';
global $post_type_object;
global $wpdb;
$WpCueBasicCertificate=new WpCueBasicCertificate();
$post_type=$WpCueBasicCertificate::POST_TYPE;
$post_type_object = get_post_type_object($post_type);
if ( ! current_user_can( $post_type_object->cap->edit_posts ))
	wp_die( __( 'Cheatin&#8217; uh?' ) );
if ( is_multisite() ) {
	add_action( 'admin_footer', '_admin_notice_post_locked' );
} else {
	$check_users = get_users( array( 'fields' => 'ID', 'number' => 2 ) );
if ( count( $check_users ) > 1 )
		add_action( 'admin_footer', '_admin_notice_post_locked' );

	unset( $check_users );
}
if(!(empty($_GET['action']))){$action=$_GET['action'];}
if(isset($action)){
$post_id = $post_ID = (int) $_REQUEST['post'];
if(!(empty($_GET['message']))){$message=$_GET['message'];}
if(isset($_POST['original_publish'])){$original_publish=$_POST['original_publish'];}
if(isset($message)){
$post_title=$_POST['post_title'];
$post_content=$_POST['certificate-'.$post_id];
wp_update_post(array('ID'=>$post_id,'post_title'=>$post_title,'post_content'=>$post_content,'post_status'=>'publish'));
if(isset($_POST['adminapproval'])){$certificatemeta['approval']=$_POST['adminapproval'];}
$certificatemeta['certype']=$_POST['certificatetype'];
if(!(isset($certificatemeta['certype']))){$certificatemeta['certype']=2;}
if(!(isset($certificatemeta['approval']))){$certificatemeta['approval']=0;}
update_post_meta($post_id,'wpcuecertificate_det',$certificatemeta);
}
$post=get_post($post_id);

}

if (!(isset($post))){
$count=wp_count_posts('wpcuecertificate');
$totalcount = $count->private + $count->publish + $count->draft + $count->trash + $count->future + $count->pending;
if($totalcount < 5){
$post=$WpCueBasicCertificate->set_certificate();
$post_id=$post->ID;
}else{echo $promessage;}
}else{$totalcount=0;}
if($totalcount < 5 ||(!empty($wpcuebasicquiz_version))){
if($post->post_title=='Auto Draft'){unset($post->post_title);}
if(isset($action)){$title = $post_type_object->labels->edit_item;}else{$title = $post_type_object->labels->add_new_item;}
//print_r($post);
?><div class="wrap newform">
<h2>
<?php echo esc_html( $title );
if ( isset($action) && ($action=='edit') && current_user_can($post_type_object->cap->create_posts ) )
	echo ' <a href="' . esc_url( admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequizcertificate') ) . '" class="add-new-h2  newer">' . esc_html( $post_type_object->labels->add_new ) . '</a>';
?>
</h2>
<?php if(isset($action) && isset($message)){if($message=='1'){$msg='<div id="message" class="updated"><p>'.__('Certificate updated','wpcues-quiz-pro').'.</p></div>';}
elseif($message=='6'){$msg='<div id="message" class="updated"><p>'.__('Certificate Saved','wpcues-quiz-pro').'.</p></div>';}
echo $msg;}
?>
<form id='quizax' name='quiz' method='post' action='<?php if(isset($action)){echo admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequizcertificate&action=edit&message=1&post='.$post_id);}
else{echo admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequizcertificate&action=edit&message=6&post='.$post_id);}?>'>
<div id="poststuff"><div id="post-body" class="metabox-holder columns-2"><div id="post-body-content">
<input type='hidden' name='postid' id='postid' value='<?php echo $post_id; ?>' />
<div id="titlediv" style='margin:1em 0em;padding:1em 0em;'>
<div id="titlewrap"><label class="screen-reader-text" id="title-prompt-text" for="title"><?php _e('Enter Certificate Name here','wpcues-quiz-pro');?></label>
<input type="text" name="post_title" size="30" id="title" autocomplete="off" value='<?php echo $post->post_title; ?>' placeholder="<?php _e('Enter Certificate Name here','wpcues-quiz-pro');?>" /></div>
<div class="inside">
</div></div>
<div class='pinit'>
<?php _e('Please enter html along with css that you want to apply in your certificate.','wpcues-quiz-pro');?>
</div>
<div id="postdivrich" class="postarea edit-form-section">
<?php wp_editor( $post->post_content,'certificate-'.$post_id,array('textarea_rows'=>15,'quicktags'=>true,'dfw'=>true));?>
</div>
<div id='postnot' class='postnot'>
<h3 class='smallheader'><?php _e('Usable  Variables','wpcues-quiz-pro');?></h3>
<div class='entitymsg'><?php _e('(All the variables can be used in grade descriptions as well.)','wpcues-quiz-pro');?></div>
<table id='usablevariable' class="widefat fixed">
		<thead>
		<tr><th><?php _e('Variable','wpcues-quiz-pro');?></th><th><?php _e('Value','wpcues-quiz-pro');?></th></tr>
		</thead>
		
		<tbody>
		<tr><td><?php _e('%%POINTS%%','wpcues-quiz-pro');?></td><td><?php _e('Total points scored','wpcues-quiz-pro');?></td></tr>
		<tr><td><?php _e('%%GRADE%%','wpcues-quiz-pro');?></td><td><?php _e('Assigned Grade','wpcues-quiz-pro');?></td></tr>
		<tr><td><?php _e('%%QUIZNAME%%','wpcues-quiz-pro');?></td><td><?php _e('Quiz Name','wpcues-quiz-pro');?></td></tr>
		<tr><td><?php _e('%%DATE%%','wpcues-quiz-pro');?></td><td><?php _e('Date on which quiz is taken','wpcues-quiz-pro');?></td></tr>
		<tr><td><?php _e('%%USERNAME%%','wpcues-quiz-pro');?></td><td><?php _e('User Name','wpcues-quiz-pro');?></td></tr>
		</tbody>
		</table>
</div>
</div>
<div id="postbox-container-1" class="postbox-container">
<?php $certificatemet =get_post_meta($post_id,'wpcuecertificate_det');
$certificatemeta=maybe_unserialize($certificatemet);
if(!(empty($certificatemeta))){
$certificatemetavalues=$certificatemeta[0];}else{$certificatemetavalues=array();}
 ?>
<?php $can_publish = current_user_can($post_type_object->cap->publish_posts);?>
<div id="side-sortables" class="meta-box-sortables"><div id="submitdiv" class="postbox " >
<div class="handlediv" title="Click to toggle"><br /></div><h3 class='hndle'><span><?php _e('Publish','wpcues-quiz-pro');?></span></h3>
<div class="inside">
<div id="certificatemeta">
<ul><li>
<input type='checkbox' name='adminapproval' value='1' <?php if(isset($certificatemetavalues['approval']) && ($certificatemetavalues['approval']==1)){echo 'checked';}?>/>
<?php _e('Requires admin approval to be issued','wpcues-quiz-pro');?>
</li>
<li><?php _e('Issue certificate as','wpcues-quiz-pro');?> <select name='certificatetype'>
<option value="1" <?php if(!(empty($certificatemetavalues['certype'])) && ($certificatemetavalues['certype']==1)){echo 'selected';}?>><?php _e('pdf','wpcues-quiz-pro');?></option>
<option value="2" <?php if(!(empty($certificatemetavalues['certype'])) && ($certificatemetavalues['certype']==2)){echo 'selected';}?>><?php _e('HTML','wpcues-quiz-pro');?></option></select></li>
</ul>
</div>
<div class="submitbox" id="submitpost">
<div id="major-publishing-actions">
<div id="delete-action">
<?php
if ( current_user_can( "delete_post", $post->ID ) ) {
	if ( !EMPTY_TRASH_DAYS )
		$delete_text = __('Delete Permanently');
	else
		$delete_text = __('Move to Trash');
	?>
<a class="submitdelete deletion" href="#"><?php echo $delete_text; ?></a><?php
} ?>
</div>

<div id="publishing-action">
<span class="spinner"></span>
<?php
if ( !in_array( $post->post_status, array('publish', 'future', 'private') ) || 0 == $post->ID ) {
	if ( $can_publish ) :?>
		<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Save') ?>" />
		<?php submit_button( __( 'Save' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
<?php	endif;
}else{ ?>
		<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Update') ?>" />
		<input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="<?php esc_attr_e('Update') ?>" />
<?php
} ?>
</div>
<div class="clear"></div>
</div>
</div>
</div>
	</div>
	</div>
</div><!-- postbox-->
</div><!-- /post-body-->
</div><!-- /poststuff-->
<style>
h3.smallheader{
font-size: 1em;
padding: 0em 0em;
margin: 1em 0em 0em 0em;
font-weight:bold;
line-height: 2;
}
#certificatemeta{
margin:6px 0 0;padding: 0 12px 12px;
line-height: 1.4em;
font-size: 13px;}
.entitymsg{font-size: 0.875em;margin:0.5em 0.5em;padding:0.5em 1em;}
.entitymsg:before{font:400 14px/1 dashicons;content: "\f339";}
span.entitymsg:before{font:400 10px/1 dashicons;content: "\f155";}
.pinit{margin:1em 0em;padding:1em 0em;background:#fff;}
.pinit:before{font:400 20px/1 dashicons;content: "\f223";padding:0em 0.25em;}
</style>
<?php } ?>