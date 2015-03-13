<script>
jQuery(document).ready(function($){
function changemenu(){
$currentmenu=$('li.wp-has-current-submenu');
$currentmenuid=$currentmenu.attr('id');
if((typeof $currentmenuid === 'undefined')||($currentmenuid !== 'toplevel_page_edit-post_type-wpcuebasicquiz')){
	alert('pratima ki chut');
	$currentmenu.removeClass('wp-has-current-submenu');
	$('#toplevel_page_edit-post_type-wpcuebasicquiz').addClass('wp-has-current-submenu');
	$currentanchor=$('#toplevel_page_edit-post_type-wpcuebasicquiz').children('a');
	$currentanchor.removeClass('wp-not-current-submenu');
	$currentanchor.addClass('wp-has-current-submenu');
}
 $('a[href="edit.php?post_type=wpcuebasicquiz&page=wpcuequizerror"]').removeClass('current');
$('a[href="edit.php?post_type=wpcuebasicquiz&page=wpcuequizerror"]').parent('li').removeClass('current'); 
 $curvar=$('a[href="edit.php?post_type=wpcuebasicerror"]');
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
        'action': 'wpcuequiztrasherror_action',
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
$(document).on('change','#quizerror',function(){
$quizid=$(this).val();
$.ajax({
	type: 'POST',
	dataType:'json',
	url:ajaxurl,
	data: { 
		'action':'wpcuequizquestiondropdown_action',
		'quizid':$quizid,
	},
	success: function(data){
		if(data.msg=='success'){
			console.log(data.content);
			$('.questselect').html(data.content);
		}
	}
});
});
});
</script>
<?php 
global $post_type_object;
global $wpdb;
$WpCueBasicError=new WpCueBasicError();
$post_type=$WpCueBasicError::POST_TYPE;
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
$table_name=$wpdb->prefix.'wpcuequiz_quizerrorinfo';
if(!empty($_GET['action'])){$action=$_GET['action'];}
if(isset($action)){
$post_id = $post_ID = (int) $_REQUEST['post'];
if(!empty($_GET['message'])){$message=$_GET['message'];}
if(isset($_POST['original_publish'])){$original_publish=$_POST['original_publish'];}
if(isset($original_publish)){
$post_title=$_POST['post_title'];
$post_content=$_POST['error-'.$post_id];
$quizid=$_POST['quizid'];
$entityid=$_POST['entityid'];
$status=$_POST['status'];
$instanceid=$_POST['instanceid'];
wp_update_post(array('ID'=>$post_id,'post_title'=>$post_title,'post_content'=>$post_content,'post_status'=>'publish'));
if($original_publish =='publish'){
$wpdb->update($table_name,array('entityid'=>$entityid,'instanceid'=>$instanceid,'quizid'=>$quizid,'status'=>$status),array('errorid'=>$post_id),array('%d','%d','%d','%d'),array('%d')); 
}else{
$wpdb->insert($table_name,array('instanceid'=>$instanceid,'entityid'=>$entityid,'errorid'=>$post_id,'quizid'=>$quizid,'status'=>$status),array('%d','%d','%d','%d','%d'));
}
}
$post=get_post($post_id);
}


if (!(isset($post)))
{$post=$WpCueBasicError->set_error();$post_id=$post->ID;}

if($post->post_title=='Auto Draft'){unset($post->post_title);}
if(isset($action)){$title = $post_type_object->labels->edit_item;}else{$title = $post_type_object->labels->add_new_item;}
//print_r($post);
?><div class="wrap newform">
<h2>
<?php echo esc_html( $title );
if ( isset($action) && ($action=='edit') && current_user_can($post_type_object->cap->create_posts ) )
	echo ' <a href="' . esc_url( admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequizerror') ) . '" class="add-new-h2  newer">' . esc_html( $post_type_object->labels->add_new ) . '</a>';
?>
</h2>
<?php if(isset($action) && !(empty($message))){if($message=='1'){$msg='<div id="message" class="updated"><p>Error updated.</p></div>';}
elseif($message=='6'){$msg='<div id="message" class="updated"><p>Error Saved.</p></div>';}
if(!empty($msg)){echo $msg;}
}

?>
<form id='quizax' name='quiz' method='post' action='<?php if(isset($action)){echo admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequizerror&action=edit&message=1&post='.$post_id);}
else{echo admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequizerror&action=edit&message=6&post='.$post_id);}?>'>
<div id="poststuff"><div id="post-body" class="metabox-holder columns-2"><div id="post-body-content">
<input type='hidden' name='postid' id='postid' value='<?php echo $post_id; ?>' />
<div id="titlediv" style='margin:1em 0em;padding:1em 0em;'>
<div id="titlewrap"><label class="screen-reader-text" id="title-prompt-text" for="title">Enter Error Subject here</label>
<input type="text" name="post_title" size="30" id="title" autocomplete="off" value='<?php echo $post->post_title; ?>' placeholder="Enter Error Subject here" /></div>
<div class="inside">
</div></div>
<div id="postdivrich" class="postarea edit-form-section">
<?php wp_editor( $post->post_content,'error-'.$post_id,array('textarea_rows'=>15,'quicktags'=>true,'dfw'=>true));?>
</div>
</div>
<div id="postbox-container-1" class="postbox-container">
<?php $can_publish = current_user_can($post_type_object->cap->publish_posts);?>
<div id="side-sortables" class="meta-box-sortables"><div id="submitdiv" class="postbox " >
<div class="handlediv" title="Click to toggle"><br /></div><h3 class='hndle'><span>Publish</span></h3>
<div class="inside">
<div class="submitbox" id="submitpost">
<div id="errormeta">
<?php 
$errormeta=$wpdb->get_row($wpdb->prepare("select instanceid,quizid,entityid,status from $table_name where errorid=%d",$post_id),ARRAY_A );
if(empty($errormeta)){$errormeta=array();}
if(empty($errormeta['instanceid'])){$errormeta['instanceid']=0;}
echo '<input type="hidden" name="instanceid" value="'.$errormeta['instanceid'].'">';
echo '<ul>';
echo '<li> Quiz : ';
if(!empty($errormeta['quizid'])){echo get_post_title($errormeta['quizid']);}
echo '<span class="quizselect';
if(!empty($errormeta['quizid'])){echo ' hiddendiv';}
echo '"><select name="quizid" id="quizerror">';
echo '<option value="0">Select Quiz</option>';
$args = array('post_type'=>array('wpcuebasicquiz'),'posts_per_page' => -1);
$quizquery = new WP_Query($args);$i=0;
while ($quizquery->have_posts()){
	$quizquery->the_post();
	$quiz=$quizquery->post;
	echo '<option value="'.$quiz->ID.'"';
	if(!(empty($errormeta['quizid'])) && ($errormeta['quizid']==$quiz->ID)){
		echo ' selected';
	}else{if($i==0){$quizid=$quiz->ID;}}
	echo '>'.$quiz->post_title.'</option>';
	$i++;
}
echo '</select></span></li>';
echo '<li>Question ; ';
if(!empty($errormeta['entityid'])){echo get_post_title($errormeta['entityid']);}
echo '<span class="questselect';
if(!empty($errormeta['entityid'])){echo ' hiddendiv';}
echo '"><select name="entityid">';
$table_name1=$wpdb->prefix.'quizinfo';
$entities=$wpdb->get_col($wpdb->prepare("select entityid from $table_name1 where parentid != -1 and quizid = %d order by entityorder asc",$quizid));
$i=1;
foreach($entities as $entityid){
	echo '<option value="'.$entityid.'"';
	if(!(empty($errormeta['entityid'])) && ($errormeta['entityid']==$entityid)){
		echo ' selected';
	}
	echo '>Q. '.$i.'</option>';
	$i++;
}
echo '</select></span></li>';
if(!empty($errormeta) && !isset($errormeta['status'])){$errormeta['status']=0;}
echo '<li>Status : <select name="status">';
echo '<option value="0"';
if(empty($errortmeta['status'])){echo ' selected';}
echo '>open</option>';
echo '<option value="1"';
if(!empty($errortmeta['status'])){echo ' selected';}
echo '>Closed</option>';
echo '</select></li></ul>';
?>
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
<style>
#errormeta{
margin:6px 0 0;padding: 0 12px 12px;
line-height: 1.4em;
font-size: 13px;}
</style>