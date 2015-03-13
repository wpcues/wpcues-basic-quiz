<script>
jQuery(document).ready(function($){
function changemenu(){
	$currentmenu=$('li.wp-has-current-submenu');
	$currentmenuid=$currentmenu.attr('id');
	if((typeof $currentmentuid === 'undefined') || ($currentmenuid !== 'toplevel_page_edit-post_type-wpcuebasicquiz')){
		$currentmenu.removeClass('wp-has-current-submenu');
		$('#toplevel_page_edit-post_type-wpcuebasicquiz').addClass('wp-has-current-submenu');
		$currentanchor=$('#toplevel_page_edit-post_type-wpcuebasicquiz').children('a');
		$currentanchor.removeClass('wp-not-current-submenu');
		$currentanchor.addClass('wp-has-current-submenu');
	}
	$('a[href="edit.php?post_type=wpcuebasicquiz&page=wpcuequizproduct"]').removeClass('current');
	$('a[href="edit.php?post_type=wpcuebasicquiz&page=wpcuequizproduct"]').parent('li').removeClass('current'); 
	$curvar=$('a[href="edit.php?post_type=wpcuebasicproduct"]');
	$curvar.addClass('current');
	$curvar.parent('li').addClass('current');
}
changemenu();
$('#productexpiry').datepicker();
$(document).on('click','#publish',function(e){
	e.preventDefault();
	$addeditem=$('#itemtable').find('.addeditemrow').length;
	if($addeditem ==0){
		$('#messagesubmit').html('<p>Please add any item first.</p>');
		$('#messagesubmit').show();
	}else{$('#quizax').submit();}
});
});
</script>
<?php 
$wpcuebasicquiz_version=get_option('wpcuebasicquiz_version');
$promessage='<div class="announcecontent"><div class="procontent"></div><div class="protext">You can create only twenty products in basic version.To remove this lock and use this feature extensively, please buy the pro version of plugin. </div>
</div>
<style>.announcecontent{min-height:4em;margin:1em 0.5em;padding:0.75em 3.5em;background:#fff;letter-spacing: 2px;width:90%;}
.procontent{float:left;width:5%;;background:#fff;}.protext{width:95%;margin:0;padding:0;float:right;}
.procontent::before{font:400 1.5em/1 dashicons;content: "\f312";color:#2EFE2E;padding:0.5em 0.5em;}
</style>';
global $post_type_object;
global $wpdb;$table_name=$wpdb->prefix.'wpcuequiz_productinfo';
$WpCueBasicProduct=new WpCueBasicProduct();
$post_type=$WpCueBasicProduct::POST_TYPE;
$post_type_object = get_post_type_object($post_type);
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
if(!(empty($_GET['action']))){$action=$_GET['action'];}
if(isset($action)){
$post_id = $post_ID = (int) $_REQUEST['post'];
if(!(empty($_GET['message']))){$message=$_GET['message'];}
if(isset($message)){
if(isset($_POST['original_publish'])){$original_publish=$_POST['original_publish'];}
	$post_title=$_POST['post_title'];
	$post_content=$_POST['productdescription'];echo $post_content;
	wp_update_post(array('ID'=>$post_id,'post_title'=>$post_title,'post_content'=>$post_content,'post_status'=>'publish'));
	update_post_meta($post_id,'wpcueproductprice',$_POST['productprice']);
	update_post_meta($post_id,'wpcueproductcurrency',$_POST['productcurrency']);
	update_post_meta($post_id,'wpcueproductexpiry',$_POST['productexpiry']);
	update_post_meta($post_id,'wpcueproductunits',$_POST['productunits']);
	echo $original_publish;
if(isset($original_publish) && ($original_publish == 'Update')){
if(!(empty($_POST['addeditem']))){
	$existingitems=$wpdb->get_col($wpdb->prepare("Select itemid from $table_name where productid=%d",$post_id));
	$addeditems=$_POST['addeditem'];
	$addeditemtype=$_POST['addeditemtype'];$mappeditemtype=array_combine($addeditems,$addeditemtype);
	if(!(empty($existingitems))){
		$deleteditems=array_diff($existingitems,$addeditems);
		$newitems=array_diff($addeditems,$existingitems);
	}else{
		$newitems=$addeditems;	
	}
	if(!(empty($deleteditems))){
		foreach($deleteditems as $deleteditemid){
			$wpdb->delete($table_name,array('productid'=>$post_id,'itemid'=>$deleteditemid),array('%d','%d'));
		}
	}
	if(!(empty($newitems))){
		$i=1;$totalcount=count($newitems);echo $totalcount;
		foreach($newitems as $newitemid){
			$value='('.$post_id.','.$newitemid.','.$mappeditemtype[$newitemid].')';
			if($i < $totalcount){$value.=',';}
		}
		$wpdb->query("INSERT INTO $table_name ( productid, itemid, itemtype ) VALUES $value");
	}
}
}
}
	$post=get_post($post_id);
	$productmeta=get_post_custom($post_id);
	$existingitems=$wpdb->get_col($wpdb->prepare("Select itemid from $table_name where productid=%d",$post_id));
	$existingquiz=$wpdb->get_col($wpdb->prepare("Select itemid from $table_name where productid=%d and itemtype=1",$post_id));
}
if (!(isset($post))){
	$count=wp_count_posts('wpcuebasicproduct');
	$totalcount = ($count->private) + $count->publish + $count->draft + $count->trash + $count->future + $count->pending;
if($totalcount < 10){
	$post=$WpCueBasicProduct->set_product();
	$post_id=$post->ID;
	$existingitems=array();$existingquizcat=array();$productmeta=array();
}else{echo $promessage;}
}
if($totalcount < 10 ||(!empty($wpcuebasicquiz_version))){
if($post->post_title=='Auto Draft'){unset($post->post_title);}
if(isset($action)){$title = $post_type_object->labels->edit_item;}else{$title = $post_type_object->labels->add_new_item;}

?><div class="wrap newform">
<h2>
<?php echo esc_html( $title );
if ( isset($action) && ($action=='edit') && current_user_can($post_type_object->cap->create_posts ) )
	echo ' <a href="' . esc_url( admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequizproduct') ) . '" class="add-new-h2  newer">' . esc_html( $post_type_object->labels->add_new ) . '</a>';
?>
</h2>
<?php if(isset($action) && isset($message)){if($message=='1'){$msg='<div id="message" class="updated"><p>'.__('Product updated','wpcues-quiz-pro').'.</p></div>';}
elseif($message=='6'){$msg='<div id="message" class="updated"><p>'.__('Product Saved','wpcues-quiz-pro').'.</p></div>';}
echo $msg;}
?>
<div id="messagesubmit" class="updated hiddendiv"></div>
<form id='quizax' name='quiz' method='post' action='<?php if(isset($action)){echo admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequizproduct&action=edit&message=1&post='.$post_id);}
else{echo admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequizproduct&action=edit&message=6&post='.$post_id);}?>'>
<div id="poststuff"><div id="post-body" class="metabox-holder columns-2"><div id="post-body-content">
<input type="hidden" id="original_post_status" name="original_post_status" value="<?php echo esc_attr( $post->post_status) ?>" class="requiredvar" />
<input type='hidden' name='postid' id='postid' value='<?php echo $post_id; ?>' />
<div id="titlediv" style='margin:1em 0em;padding:1em 0em;'>
<div id="titlewrap"><label class="screen-reader-text" id="title-prompt-text" for="title"><?php _e('Enter Product Name here','wpcues-quiz-pro');?></label>
<input type="text" name="post_title" size="30" id="title" autocomplete="off" value='<?php echo $post->post_title; ?>' placeholder="<?php _e('Enter Product Name here','wpcues-quiz-pro');?>" /></div>
<div class="inside">
</div></div>
<div id="postdivrich" class="postarea edit-form-section">
<ul class="outertabs">
	<li><a href="#producttab-1">Description</a></li>
	<li><a href="#producttab-2">Items</a></li>
</ul>
<div id="producttab-1">
<div id="productdesc">
<?php wp_editor( $post->post_content,'productdescription',array('textarea_rows'=>15,'quicktags'=>true,'dfw'=>true));?>
</div>
</div>
<div id="producttab-2">
<div id="productitems">
<div id="additembuttons">
	<input type='button' name='addquizitem' id='add_quizitem_button' value='Add Quiz' class='button button-secondary'>
	<input type='button' name='addquizcatitem' id='add_quizcatitem_button' value='Add Quiz Category' class='button button-secondary'>
</div>
<div id="producteditor">
</div>
<div id="addeditems">
	<table id='itemtable' class="widefat fixed <?php if(empty($existingitems)){echo 'hiddendiv';} ?>">
		<?php
			if(!(empty($existingitems))){
				if(!(empty($existingquiz))){
					$posttable=$wpdb->prefix.'posts';$implodexistingquiz='('.implode(',',$existingquiz).')';
					$quiz=$wpdb->get_results("select ID,post_title from $posttable where ID in $implodexistingquiz",OBJECT_K);
				}
				$i=1;
				foreach($existingitems as $itemid){
					if(isset($quiz[$itemid])){
						echo '<tr id="rowitem-'.$itemid.'" class="addeditemrow"><td class="itemnum">'.$i.'<td>'.$quiz[$itemid]->post_title.'</td><td>';
						echo '<input type="hidden" name="addeditem[]" value="'.$itemid.'">';
						echo '<input type="hidden" name="addeditemtype[]" value="1">Quiz';
						echo '</td><td class="removeitem"></td></tr>';
					}else{
						$category=$category=get_term_by('id',$itemid,'wpcuebasicquizcat');
						echo '<tr id="rowitem-'.$itemid.'" class="addeditemrow"><td class="itemnum">'.$i.'<td>'.$category->name.'</td><td>';
						echo '<input type="hidden" name="addeditem[]" value="'.$itemid.'">';
						echo '<input type="hidden" name="addeditemtype[]" value="2">Quiz';
						echo '</td><td class="removeitem"></td></tr>';
					}
					$i++;
				}
			}
		?>
	</table>
</div>
</div>
</div>
</div>
</div>
<div id="postbox-container-1" class="postbox-container">
<?php $productmeta =get_post_custom($post_id,'wpcuecertificate_det');
 ?>
<?php $can_publish = current_user_can($post_type_object->cap->publish_posts);?>
<div id="side-sortables" class="meta-box-sortables"><div id="submitdiv" class="postbox " >
<div class="handlediv" title="Click to toggle"><br /></div><h3 class='hndle'><span><?php _e('Publish','wpcues-quiz-pro');?></span></h3>
<div class="inside">
<div id="productmeta">
<ul>
<li><ul><li>Open till :</li><li><input type='text' name='productexpiry' id='productexpiry' value='<?php if(isset($productmeta['wpcueproductexpiry'])){echo $productmeta['wpcueproductexpiry'][0];} ?>'></li></ul></li>
<li><ul><li>Price :</li><li><input type='text' name='productprice' value='<?php if(isset($productmeta['wpcueproductprice'])){echo $productmeta['wpcueproductprice'][0];}else{echo 0;}?>'></li></ul></li>
<li><ul><li>Currency :</li><li><select name="productcurrency" id='productcurrency'>
<?php $customcur=0; ?>
	<option value='USD' <?php if(!(empty($productmeta['wpcueproductcurrency'])) && ($productmeta['wpcueproductcurrency'][0]=='USD')){echo 'selected';} ?>>$</option>
    <option  value='EUR' <?php if(!(empty($productmeta['wpcueproductcurrency'])) && ($productmeta['wpcueproductcurrency'][0]=='EUR')){echo 'selected';} ?>>&euro;</option>
    <option  value='GBP' <?php if(!(empty($productmeta['wpcueproductcurrency'])) && ($productmeta['wpcueproductcurrency'][0]=='GBP')){echo 'selected';} ?>>&pound;</option>
    <option  value='JPY' <?php if(!(empty($productmeta['wpcueproductcurrency'])) && ($productmeta['wpcueproductcurrency'][0]=='JPY')){echo 'selected';} ?>>&yen;</option>
    <option  value='AUD' <?php if(!(empty($productmeta['wpcueproductcurrency'])) && ($productmeta['wpcueproductcurrency'][0]=='AUD')){echo 'selected';} ?>>AUD</option>
    <option  value='CAD' <?php if(!(empty($productmeta['wpcueproductcurrency'])) && ($productmeta['wpcueproductcurrency'][0]=='CAD')){echo 'selected';} ?>>CAD</option>
    <option  value='CHF' <?php if(!(empty($productmeta['wpcueproductcurrency'])) && ($productmeta['wpcueproductcurrency'][0]=='CHF')){echo 'selected';} ?>>CHF</option>
    <option  value='CZK' <?php if(!(empty($productmeta['wpcueproductcurrency'])) && ($productmeta['wpcueproductcurrency'][0]=='CZK')){echo 'selected';} ?>>CZK</option>
    <option  value='DKK' <?php if(!(empty($productmeta['wpcueproductcurrency'])) && ($productmeta['wpcueproductcurrency'][0]=='DKK')){echo 'selected';} ?>>DKK</option>
    <option  value='HKD' <?php if(!(empty($productmeta['wpcueproductcurrency'])) && ($productmeta['wpcueproductcurrency'][0]=='HKD')){echo 'selected';} ?>>HKD</option>
    <option  value='HUF' <?php if(!(empty($productmeta['wpcueproductcurrency'])) && ($productmeta['wpcueproductcurrency'][0]=='HUF')){echo 'selected';} ?>>HUF</option>
    <option  value='ILS' <?php if(!(empty($productmeta['wpcueproductcurrency'])) && ($productmeta['wpcueproductcurrency'][0]=='ILS')){echo 'selected';} ?>>ILS</option>
    <option  value='MXN' <?php if(!(empty($productmeta['wpcueproductcurrency'])) && ($productmeta['wpcueproductcurrency'][0]=='MXN')){echo 'selected';} ?>>MXN</option>
    <option  value='NOK' <?php if(!(empty($productmeta['wpcueproductcurrency'])) && ($productmeta['wpcueproductcurrency'][0]=='NOK')){echo 'selected';} ?>>NOK</option>
    <option  value='NZD' <?php if(!(empty($productmeta['wpcueproductcurrency'])) && ($productmeta['wpcueproductcurrency'][0]=='NZD')){echo 'selected';} ?>>NZD</option>
    <option  value='PLN' <?php if(!(empty($productmeta['wpcueproductcurrency'])) && ($productmeta['wpcueproductcurrency'][0]=='PLN')){echo 'selected';} ?>>PLN</option>
    <option  value='SEK' <?php if(!(empty($productmeta['wpcueproductcurrency'])) && ($productmeta['wpcueproductcurrency'][0]=='SEK')){echo 'selected';} ?>>SEK</option>
    <option  value='SGD' <?php if(!(empty($productmeta['wpcueproductcurrency'])) && ($productmeta['wpcueproductcurrency'][0]=='SGD')){echo 'selected';} ?>>SGD</option>
    <option  value='ZAR' <?php if(!(empty($productmeta['wpcueproductcurrency'])) && ($productmeta['wpcueproductcurrency'][0]=='ZAR')){echo 'selected';} ?>>ZAR</option>
    <option value="-1" <?php if(!(empty($productmeta['wpcueproductcurrency'])) && ($productmeta['wpcueproductcurrency'][0]==-1)){echo 'selected';$customcur=1;} ?>  >Custom</option>
</select> 
<?php echo '<input type="text" id="customCurrency" name="custom_currency" ';
if(empty($customcur)){echo 'style="display:none"';$value='USD';}else{$value=$productmeta['wpcueproductcustomcurrency'][0];}
echo 'value="'.$value.'">';
?>
</li></ul></li>
<li><ul><li>No. of Units :</li><li><input type='text' name='productunits' value='<?php if(isset($productmeta['wpcueproductunits'])){echo $productmeta['wpcueproductunits'][0];}else{echo 0;}?>'></li></ul></li>

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
#additembuttons{width:100%;float:left;margin:0em;padding:0em;min-height:1em;}
#productdesc,#productitems{margin:0.5em 1em;padding:0.5em 1em;}
.selectallbutton{margin:0.5em 0.5em;padding:0.5em 2em;width:80%;}
.selectallitem{float:right;}
#producteditor{margin:1em 0.5em;padding:0.5em 0.5em;display:none;}
.itemeditorbox{margin:2em 0em;border:2px;background:#fff;padding:0.25em 1em;width:50%;}
.removeitem::after{content: "\f335";color:blue;font:400 1.5em/1 dashicons; vertical-align: -50%;cursor:pointer;cursor:hand;}
#productmeta{margin:0.25em 0.25em;padding:0.25em 0.5em;}

.itemnum{}
</style>
<?php } ?>