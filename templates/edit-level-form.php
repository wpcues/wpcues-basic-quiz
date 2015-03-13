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
 $('a[href="edit.php?post_type=wpcuebasicquiz&page=wpcuequizlevel"]').removeClass('current');
$('a[href="edit.php?post_type=wpcuebasicquiz&page=wpcuequizlevel"]').parent('li').removeClass('current'); 
 $curvar=$('a[href="edit.php?post_type=wpcuebasiclevel"]');;
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
                'action': 'trashlevel_action',
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
$WpCueBasicLevel=new WpCueBasicLevel();
$post_type=$WpCueBasicLevel::POST_TYPE;
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
if(isset($_GET['action'])){$action=$_GET['action'];}
$error=0;
if(isset($action)){
$post_id = $post_ID = (int) $_REQUEST['post'];
if(isset($_GET['message'])){$message=$_GET['message'];}
if(isset($message)){
$post_title=$_POST['post_title'];
$leveldesc=$_POST['level-'.$post_id];
update_post_meta($post_id,'wpcuelevelrank',$_POST['levelrank']);
update_post_meta($post_id,'wpcuelevelpoints',$_POST['levelreqdpoints']);
update_post_meta($post_id,'wpcuelevelpercorrect',$_POST['levelcorrans']);
update_post_meta($post_id,'wpcuelevelquiznum',$_POST['levelnumquiz']);
if(!empty($_POST['taxinput'])){update_post_meta($post_id,'wpcuelevelquizcat',$_POST['taxinput']);}else{
	delete_post_meta($post_id,'wpcuelevelquizcat');
}
wp_update_post(array('ID'=>$post_id,'post_title'=>$post_title,'post_content'=>$leveldesc,'post_status'=>'publish'));
}
if($error != 1){
$post=get_post($post_id);
$levelmeta=get_post_custom($post_id);}
}
if((!(isset($post))) )
{}
if (!(isset($post))&& ($error != 1)){
$count=wp_count_posts('wpcuebasiclevel');
$totalcount = $count->private + $count->publish + $count->draft + $count->trash + $count->future + $count->pending;
if($totalcount < 5 ||(!empty($wpcuebasicquiz_version))){
$post=$WpCueBasicLevel->set_level();$post_id=$post->ID;$levelmeta=array();
}else{echo $promessage;}
}else{$totalcount=0;}
if($totalcount < 5){

if($post->post_title=='Auto Draft'){unset($post->post_title);}
if(isset($action)){$title = $post_type_object->labels->edit_item;}else{$title = $post_type_object->labels->add_new_item;}
?><div class="wrap newform">
<h2>
<?php echo esc_html( $title );
if ( isset($action) && ($action=='edit') && current_user_can($post_type_object->cap->create_posts ) )
	echo ' <a href="' . esc_url( admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequizlevel') ) . '" class="add-new-h2  newer">' . esc_html( $post_type_object->labels->add_new ) . '</a>';
?>
</h2>
<?php if(isset($action) && (isset($message))){if($error==1){
$msg='<div id="message" class="updated"><p>Sorry, the level could not be saved.Please change level rank and save again.
There is another level existing having same rank</p></div>';}
elseif($error==2){
$msg='<div id="message" class="updated"><p>Sorry, the level could not be updated as there is already a level associated with the new rank that you have entered.</p></div>';
}else{if($message=='1'){$msg='<div id="message" class="updated"><p>Level updated.</p></div>';}
elseif($message=='6'){$msg='<div id="message" class="updated"><p>Level Published.</p></div>';}
echo $msg;
}

}

?>
<form id='quizax' name='quiz' method='post' action='<?php if(isset($action)){echo admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequizlevel&action=edit&message=1&post='.$post_id);}
else{echo admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequizlevel&action=edit&message=6&post='.$post_id);}?>'>
<div id="poststuff"><div id="post-body" class="metabox-holder columns-2"><div id="post-body-content">
<input type='hidden' name='postid' id='postid' value='<?php echo $post_id; ?>' />
<div id="titlediv" style='margin:1em 0em;padding:1em 0em;'><div id="titlewrap"><label class="screen-reader-text" id="title-prompt-text" for="title">Enter Level Name here</label>
<input type="text" name="post_title" size="30" id="title" autocomplete="off" value='<?php if($error==1){echo $post_title;}else{echo $post->post_title;} ?>' placeholder="Enter Level Name here" /></div>
<div class="inside">
</div></div><div id="postdivrich" class="postarea edit-form-section">
<?php wp_editor( $post->post_content,'level-'.$post_id,array('textarea_rows'=>15,'quicktags'=>true,'dfw'=>true));?>
</div></div>
<div id="postbox-container-1" class="postbox-container">
<?php $can_publish = current_user_can($post_type_object->cap->publish_posts);?>
<div id="side-sortables" class="meta-box-sortables"><div id="submitdiv" class="postbox " >
<div class="handlediv" title="Click to toggle"><br /></div><h3 class='hndle'><span>Publish</span></h3>
<div class="inside">
<div class="submitbox" id="submitpost">
<div id="submitrequirement">
<ul>
	<li><ul><li>Level Rank</li><li><input type='text' name='levelrank' value='<?php if(isset($levelmeta['wpcuelevelrank'])){echo $levelmeta['wpcuelevelrank'][0];}else{echo 0;}?>'></li></ul></li>
	<li><ul><li>Required Total Points</li><li><input type='text' name='levelreqdpoints' value='<?php if(isset($levelmeta['wpcuelevelpoints'])){echo $levelmeta['wpcuelevelpoints'][0];}else{echo 0;}?>'></li></ul></li>
	<li><ul><li>Required average % correct answers from all tests</li><li><input type='text' name='levelcorrans' value='<?php if(isset($levelmeta['wpcuelevelpercorrect'])){echo $levelmeta['wpcuelevelpercorrect'][0];}else{echo 0;}?>'></li></ul></li>
	<li><ul><li>Required number of unque tests taken</li><li><input type='text' name='levelnumquiz' value='<?php if(isset($levelmeta['wpcuelevelquiznum'])){echo $levelmeta['wpcuelevelquiznum'][0];}else{echo 0;}?>'></li></ul></li>
	<li><ul><li>Must take all exam from Quiz Category</li><li>
	<?php
	if(!(empty($levelmeta['wpcuelevelquizcat']))){$wpcuebasicquizcat=maybe_unserialize($levelmeta['wpcuelevelquizcat'][0]);}
	$args=array('orderby'=>'name','order'=>'ASC','hide_empty'=>false,'parent'=>'0');
	$terms=get_terms('wpcuebasicquizcat',$args);
	if(isset($terms)){
	echo '<ul>';
	foreach($terms as $term){
	$listelem ='<li><input type="checkbox" name="taxinput[]" value="';
	$listelem.=$term->term_id;
	$listelem.='"';
	if(isset($wpcuebasicquizcat)){if(in_array($term->term_id,$wpcuebasicquizcat)){$listelem.='checked';}}
	$listelem.='>'.$term->name;
	echo $listelem;
	$child=get_term_children($term->term_id,'wpcuebasicquizcat');
	if(isset($child)){
	echo '<ul class="children">';
	foreach($child as $childterm){
			$term = get_term_by( 'id', $childterm,'wpcuebasicquizcat' );
			$listelem ='<li><input type="checkbox" name="taxinput[]" value="';
			$listelem.=$term->term_id;
			$listelem.='"';
			if(isset($wpcuebasicquizcat)){if(in_array($term->term_id,$wpcuebasicquizcat)){$listelem.='checked';}}
			$listelem.='>'.$term->name;
			$listelem.='</li>';
			echo $listelem;
			}
	echo '</ul>';
		}
	echo '</li>';
	}
	echo '</ul>';
	}
	?>
	</li></ul></li>
</ul>
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
ul.children{margin:1px 20px;}
#submitrequirement{margin:0.25em 0.25em;padding:0.25em 0.5em;}
</style>
<?php } ?>