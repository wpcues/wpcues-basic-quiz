<script>
jQuery(document).ready(function($){
function changemenu(){
$currentmenu=$('li.wp-has-current-submenu');
$currentmenuid=$currentmenu.attr('id');
if((typeof $currentmenuid === 'undefined')||($currentmenuid !== 'toplevel_page_edit-post_type-wpcuebasicquiz')){
	$currentmenu.removeClass('wp-has-current-submenu');
	$('#toplevel_page_edit-post_type-wpcuebasicquiz').addClass('wp-has-current-submenu');}
 $('a[href="edit.php?post_type=wpcuebasicquiz&page=wpcuequiznewquestion"]').removeClass('current');
$('a[href="edit.php?post_type=wpcuebasicquiz&page=wpcuequiznewquestion"]').parent('li').removeClass('current'); 
 $curvar=$('a[href="edit.php?post_type=wpcuebasicquestion"]');
$curvar.addClass('current');
$curvar.parent('li').addClass('current');}

changemenu();
$(document).on('click','.handlediv',function(){
$(this).siblings('.inside').toggle();
});
});		
</script>
<?php 
global $wpdb;
$WpCueBasicQuestion=new WpCueBasicQuestion(); 
$post_type=$WpCueBasicQuestion::POST_TYPE;
$post_type_object = get_post_type_object($post_type);
if(!empty($_GET['action'])){$action=$_GET['action'];}
if(isset($action)){$title = $post_type_object->labels->edit_item;}else{$title=$post_type_object->labels->add_new_item;}


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

if(isset($action)){
$questionid = $post_ID = (int) $_REQUEST['post'];
$question=get_post($questionid);
$questmeta=unserialize($question->post_content);
$poststatus=$question->post_status;
}else{$questmeta=array();$questionid=0;$poststatus='auto-draft';}

?>
<div class="wrap">
<h2>
<?php echo esc_html( $title );
if ( isset($action) && ($action=='edit') && current_user_can($post_type_object->cap->create_posts ) )
	echo ' <a href="' . esc_url( admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequiznewquestion') ) . '" class="add-new-h2  newer">' . esc_html( $post_type_object->labels->add_new ) . '</a>';
?>
</h2>
<div id="message" class="updated hiddendiv"></div>
<form id='quizax' name='quiz' method='post' action=''>
<div id="poststuff"><div id="post-body" class="metabox-holder columns-2">
<input type="hidden" id="original_post_status" name="original_post_status" value="<?php echo esc_attr( $poststatus) ?>" class="requiredvar" />
<div id="post-body-content">
<div id="quizeditor">
<?php $WpCueBasicQuestion->quiz_formdesc($questmeta,$questionid); ?>
</div>
</div>
<div id="postbox-container-1" class="postbox-container">
<div id="side-sortables" class="meta-box-sortables"><div id="submitdiv" class="postbox " >
<div class="handlediv" title="Click to toggle"><br /></div><h3 class='hndle'><span>Publish</span></h3>
<div class="inside">
<div class="submitbox" id="submitpost">
<div id="major-publishing-actions">
<div id="delete-action">
<?php
if (!(empty($questionid)) && current_user_can( "delete_post",$questionid) ) {
	if ( !EMPTY_TRASH_DAYS )
		$delete_text = __('Delete Permanently');
	else
		$delete_text = __('Move to Trash');
	?>
<a class="submitdelete deletion" href="#"><?php echo $delete_text; ?></a><?php
} ?>
</div>
<?php $can_publish = current_user_can($post_type_object->cap->publish_posts);?>
<div id="publishing-action">
<span class="spinner"></span>
<?php
if ( !in_array( $poststatus, array('publish', 'future', 'private') ) || 0 == $questionid ) {
	if ( $can_publish ) :?>
		<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Publish') ?>" />
		<?php submit_button( __( 'Publish' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
<?php	endif;
} else { ?>
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