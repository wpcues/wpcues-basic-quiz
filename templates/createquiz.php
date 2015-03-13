<script>
jQuery(document).ready(function($){
function changemenu(){
 var results = new RegExp('[\?&]action=([^&#]*)').exec(window.location.href);
 if(results != null){
 $('a[href="edit.php?post_type=wpcuebasicquiz&page=wpcuequizaddnew"]').removeClass('current');
$('a[href="edit.php?post_type=wpcuebasicquiz&page=wpcuequizaddnew"]').parent('li').removeClass('current'); 
 $curvar=$('a[href="edit.php?post_type=wpcuebasicquiz"]');
$curvar.addClass('current');
$curvar.parent('li').addClass('current');}

}
changemenu();
$(document).on('click','.handlediv',function(){
$inside=$(this).siblings('.inside');
$postbox=$(this).closest('.postbox');
if($inside.is(':hidden')){$inside.toggle();$postbox.removeClass('closed');
}else{$inside.toggle();$postbox.addClass('closed');}

});
});		
</script>
<?php 
$WpCueBasicQuiz=new WpCueBasicQuiz(); 
$wpprocuesetting=$WpCueBasicQuiz->wpprocuesetting;
$post_type=$WpCueBasicQuiz::POST_TYPE;
$post_type_object = get_post_type_object($post_type);
if(isset($_GET['action'])){$action=$_GET['action'];}
if(isset($action)){$title = $post_type_object->labels->edit_item;}else{$title=$post_type_object->labels->add_new_item;}
if ( ! current_user_can( $post_type_object->cap->edit_posts ) )
	wp_die( __( 'Cheatin&#8217; uh?' ) );
if ( is_multisite() ) {
	add_action( 'admin_footer', '_admin_notice_post_locked' );
} else {
	$check_users = get_users( array( 'fields' => 'ID', 'number' => 2 ) );

	if ( count( $check_users ) > 1 )
		add_action( 'admin_footer', '_admin_notice_post_locked' );

	unset( $check_users );
}
if ( isset( $_GET['post'] ) )
 	{$post_id = $post_ID = (int) $_GET['post'];$WpCueBasicQuiz->quizid=$post_id;}
else{$WpCueBasicQuiz->set_quizid();$post_id=$WpCueBasicQuiz->quizid;}
$sectionids=array();
$usergroupids=array();
if ( $post_id ){
	$post = get_post($post_id );
	$quizmeta=get_post_custom($post_id);
	if(!(empty($quizmeta['basicsetting']))){$basicsetting=maybe_unserialize($quizmeta['basicsetting'][0]); }else{$basicsetting=array();}
	}
if($post->post_title=='Auto Draft'){unset($post->post_title);}
require_once( ABSPATH . 'wp-admin/includes/meta-boxes.php' );
 ?>
 
<div class="wrap">
	<h2><?php
echo esc_html( $title );
if ( isset($action) && current_user_can($post_type_object->cap->create_posts ) )
	echo ' <a href="' . esc_url( admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequizaddnew') ) . '" class="add-new-h2  newer">' . esc_html( $post_type_object->labels->add_new ) . '</a>';
print_r(get_role('demo_manager'));	
?></h2>
<div id="message" class="updated hiddendiv"></div>
<form id='quizax' name='quiz' method='post' action=''>
<input type='hidden' name='quizid' id='quizid' value='<?php echo $post_id; ?>' class="requiredvar">
<input type='hidden' name='origquiztype' id='origquiztype' value='1' class="requiredvar">
<input type='hidden' name='quiztype' id='quiztype' value='1' class="requiredvar">
<?php if($post->post_status=='auto-draft'){$autodraftsavestatus=1;}else{$autodraftsavestatus=0;}?>
<input type='hidden' name='autodraftsavestatus' id='autodraftsavestatus' value='<?php echo $autodraftsavestatus;?>' class="requiredvar">
<input type="hidden" name="questionschanges" value="0" class="requiredvar">
<div id="poststuff">
<input type="hidden" id="original_post_status" name="original_post_status" value="<?php echo esc_attr( $post->post_status) ?>" class="requiredvar" />
<div id="post-body" class="metabox-holder columns-2">
<div id="post-body-content">
	<div id="titlediv">
		<div id="titlewrap">
			<input name='quizname' type="text" id="title" placeholder="Enter Quiz title here" value="<?php echo $post->post_title;?>" class="requiredvar">
		</div>
	</div>
	<div id="edit-slug-box" style="margin-bottom:30px;">
			<strong>WordPress Shortcode:</strong>
				<input type="text" style="color:#999;" value="[wpcuebasicquiz <?php echo $post_id; ?>]" id="shortcode-field" readonly="readonly"/>
				<span id="embedquiz"><a href="<?php echo admin_url('post-new.php?content=[wpcuebasicquiz '.$post_id.']');?> " class="button <?php if($post->post_status=='auto-draft'){echo 'disabled';}?>">Embed Quiz in New Post</a></span>
	</div>
	<div id="tabs" class='tabcontainer'>
		<ul class="outertabs">
            <li><a href="#tabs-1">Description</a></li>
			<li><a href="#tabs-2">Questions</a></li>
			<li><a href="#tabs-3">Grade</a></li>
			<li class="progresscreen"><a href="#tabs-4">Progress Screen</a></li>
			<li><a href="#tabs-5">Email</a></li>
			<li><a href="#tabs-6">Custom CSS</a></li>
        </ul>
	
	
<!-- left column containg form -->
	 <div id='tabs-1'  class='tabcontent'>
		<div id="basictab">
			<?php if(!(isset($post->post_content))){$quizdesc='';}else{$quizdesc=$post->post_content;}
			wp_editor( $quizdesc,'quizdesc',array('wpautop'=>true,'default_editor'=>'tinymce','drag_drop_upload'=>true,'textarea_rows'=>40,'editor_class'=> 'requiredvar','quicktags'=>true,'dfw'=>true,'editor_height'=>400));?>
		</div>
	</div>
	<div id='tabs-3' class='tabcontent'>
		<div id='quizdesc-2' class='gradegroup'>
			<div id='gradebuttons' style='margin:0.25em 0em;padding:0.25em 0em;'>
				<?php if(!(empty($quizmeta['quizgrade']))){$gradegroupid=$quizmeta['quizgrade'][0];}
				if(empty($gradegroupid)){$gradegroupid=0;
				}else{$gradegroupid=intval($gradegroupid);}
					if(!(empty($gradegroupid))){$gradegroup=get_post($gradegroupid);$gradegroupcontent=unserialize($gradegroup->post_content);}?>
					<input type='button' name='addgrade' id='add_grade_button' value='Add New Grade Group' class='button button-secondary<?php if(!(empty($gradegroupid))){echo ' disabled';}?>'>
			</div>
			<div id='gradeeditor' class="hiddendiv">
			
			</div>
			<div id='finalgrade' >
				<input type="hidden" name="gradegroupid" value='<?php echo $gradegroupid;?>' class="requiredvar">
				<input type="hidden" name="inheritgradegroupid" value='<?php echo $gradegroupid;?>' class="requiredvar">
			<?php 
				echo '<div class="gradegroupadded';
				if(empty($gradegroupid)){echo ' hiddendiv';} echo '">';
				echo '<table class="widefat fixed"><tr class="gradegroupaddedrow">';
				if(!(empty($gradegroupid))){
					echo  '<td>'.$gradegroup->post_title.'<div class="row-actions"><span><a href="#" class="gradegroupedit">Edit</a> | </span><span><a href="#" class="gradegroupremove">Remove</a></div></td>';
					 } 
					 echo '</tr></table>';
					 echo '</div>';
			
			?>
			</div>
		</div>
	</div>
	<div id='tabs-2' class='tabcontent'>
	
	<?php include(sprintf("%s/templates/createquestion.php", realpath(dirname(__FILE__) . '/..'))); ?>
	</div>
	  <div id='tabs-4' class='tabcontent'>
	  
	<?php include(sprintf("%s/templates/progresscreen.php", realpath(dirname(__FILE__) . '/..')));
	  ?>
	  </div>
	  <div id='tabs-5' class='tabcontent'>
	
	<?php include(sprintf("%s/templates/emailnotification.php", realpath(dirname(__FILE__) . '/..')));
	  ?>
	  </div>
	  
	  <div id='tabs-6' class='tabcontent'>
		<div id="customcssblock">
			<?php if(!(empty($quizmeta['customcss']))){
					$customcss=maybe_unserialize($quizmeta['customcss'][0]);
				}else{
					$request = wp_remote_get(plugins_url('../css/wpcuebasicquiz-frontmain.css',__FILE__));
					$response = wp_remote_retrieve_body( $request );
					$customcss=$response;
				}
				wp_editor($customcss,'customcss',array('wpautop'=>true,'default_editor'=>'tinymce','drag_drop_upload'=>false,'textarea_rows'=>40,'editor_class'=> 'requiredvar','quicktags'=>true,'dfw'=>true,'editor_height'=>400));?>	
		</div>
	  </div>
	  </div>
	  <!-- right  column containg ends here -->

	
	</div> <!-- post-body content -->
	<div id="postbox-container-1" class="postbox-container">
		<?php include(sprintf("%s/templates/quizrightsidebar.php",realpath(dirname(__FILE__).'/..'))); ?>
	</div>
	</div>
	</div> <!--post-body -->
	</div> <!-- post stuff -->
	</form>
	<div id="dialog-confirm" class='wp-dialog'></div>
	<div id="confirmquestorder" class='wp-dialog'>
		<div class="changequestorder"><p class="msg"></p><p><input type="text" name="changequestordernumber" placeholder="Shift to No."></p></div>
	</div>
	<div id='confirmansorder' class='wp-dialog'>
	</div>