<script>
jQuery(document).ready(function($){
$('#postdivrich').tabs();
function changemenu(){
$currentmenu=$('li.wp-has-current-submenu');
$currentmenuid=$currentmenu.attr('id');
if((typeof $currentmenuid === 'undefined')||($currentmenuid !== 'toplevel_page_edit-post_type-wpcuebasicquiz')){
	$currentmenu.removeClass('wp-has-current-submenu');
	$('#toplevel_page_edit-post_type-wpcuebasicquiz').addClass('wp-has-current-submenu');}
	$currentanchor=$('#toplevel_page_edit-post_type-wpcuebasicquiz').children('a');
	$currentanchor.removeClass('wp-not-current-submenu');
	$currentanchor.addClass('wp-has-current-submenu');
 $('a[href="edit.php?post_type=wpcuebasicquiz&page=wpcuequiznewsection"]').removeClass('current');
$('a[href="edit.php?post_type=wpcuebasicquiz&page=wpcuequiznewsection"]').parent('li').removeClass('current'); 
 $curvar=$('a[href="edit.php?post_type=wpcuebasicsection"]');
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
$table_name=$wpdb->prefix.'wpcuequiz_quizinfo';
$WpCueBasicSection=new WpCueBasicSection(); 
$post_type=$WpCueBasicSection::POST_TYPE;
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
if ( isset( $_GET['post'] ) ){$post_id = $post_ID = (int) $_GET['post'];$post=get_post($post_id);}
else{$post=$WpCueBasicSection->set_section();$post_id=$post->ID;}
if($post->post_title=='Auto Draft'){unset($post->post_title);}
require_once( ABSPATH . 'wp-admin/includes/meta-boxes.php' );
?>

<div class="wrap">
<h2>
<?php echo esc_html( $title );
if ( isset($action) && ($action=='edit') && current_user_can($post_type_object->cap->create_posts ) )
	echo ' <a href="' . esc_url( admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequiznewsection') ) . '" class="add-new-h2  newer">' . esc_html( $post_type_object->labels->add_new ) . '</a>';
?>
</h2>
<div id="message" class="updated hiddendiv"></div>
<form id='quizax' name='quiz' method='post' action=''>
<div id="poststuff"><div id="post-body" class="metabox-holder columns-2">
<div id="post-body-content">
<input type='hidden' name='sectionid' id='sectionid' value='<?php echo $post_id; ?>' class="requiredvar" />
<input type="hidden" id="original_post_status" name="original_post_status" value="<?php echo $post->post_status; ?>" class="requiredvar" />
<div id="titlediv" style='margin:1em 0em;padding:1em 0em;'>
<div id="titlewrap"><label class="screen-reader-text" id="title-prompt-text" for="title">Enter Section Name here</label>
<input type="text" name="post_title" size="30" id="title" autocomplete="off" value='<?php echo $post->post_title; ?>' placeholder="Enter Section Name here" class="requiredvar"/></div>
</div>

<div id="postdivrich" class="postarea edit-form-section">
<ul>
	<li><a href="#sectiontab-1">Description</a></li>
	<li><a href="#sectiontab-2">Question</a></li>
</ul>
<div id="sectiontab-1">
<?php wp_editor( $post->post_content,'sectioncontent',array('wpautop'=>false,'default_editor'=>'tinymce','drag_drop_upload'=>true,'textarea_rows'=>40,'editor_class'=> 'requiredvar','quicktags'=>true,'dfw'=>true,'editor_height'=>300));?>
</div>
<div id="sectiontab-2">
	<div id="questions_section">	
	<div id='divbuttons'>
		<input type='button' name='addquestion' id='add_question_button' value='Add Question' class='button button-secondary'>
	</div>
<div id='quizeditor' class="hiddendiv">
</div>
<?php
$entityids=$wpdb->get_col($wpdb->prepare("select entityid from $table_name where parentid=%d",$post_id));?>
<div id='addedquestion' <?php if(!empty($entityids)){echo 'style="display:block;"';}else{echo 'style="display:none;"';}?>>
	<h2> Added Questions</h2>
	<div class='addedquesttools hiddendiv'>
		<span class='questview' title='View Showing only questions'></span>
		<span class='normview selected' title='Normal view with Questions in Sections'></span>
		<span class='sortquest' title='sort questions and sections'></span>
	</div>
	<table id='questiontable' class="widefat fixed">
		<tbody>
		<?php 
			if(!empty($entityids)){
				WpCueBasicQuestion::getadded_questions($entityids,$post_id,1);
			}
			echo '</tbody></table>'; 	
		?>
</div>
<div id="disabledentities"></div>
</div>
<div class='hiddendiv' id='clonequestsec'></div>
</div>
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
if ( current_user_can( "delete_post", $post_id ) ) {
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
if ( !in_array( $post->post_status, array('publish', 'future', 'private') ) || 0 == $post_id ) {
	if ( $can_publish ) :?>
		<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Publish') ?>" />
		<?php submit_button( __( 'Publish' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
<?php	endif;
} else { 
?>
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