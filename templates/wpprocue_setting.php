<form  method='post' action='options.php'>
<?php $setting=get_option('wpcuequiz_setting'); 
?>
<div id='settingstab' class='wrap'>
	<ul class="outertabs reporttabs">	
		<li><a href="#tabs-1">Basic</a></li>
		<li><a href="#tabs-2">Text</a></li>
		<li><a href="#tabs-3">Email Templates</a></li>
		<li><a href="#tabs-4">Payment</a></li>
		<li><a href="#tabs-5">Mozilla Open Badges</a></li>
		<li><a href="#tabs-6">Dialogs</a></li>
    </ul>
	<div id='tabs-1'> 
		<div class="settingtab">
		<?php settings_fields('wpcuebasicquiz_basic_settings'); ?>
        <?php do_settings_sections( 'wpcuebasicquiz_basic_settings' ); ?>
		<?php settings_fields('wpcuebasicquiz_email_options'); ?>
		<?php do_settings_sections('wpcuebasicquiz_email_options'); ?>
		<h3 class="settingtitle">Recaptcha Setting :</h3>
		<?php settings_fields('wpcuebasicquiz_recaptcha_settings' ); ?>
        <?php do_settings_sections( 'wpcuebasicquiz_recaptcha_settings' ); ?>
		</div>
	</div>
	<div id='tabs-2'>
		<div class="settingtab">
		<?php settings_fields('wpcuebasicquiz_text_settings' ); ?>
        <?php do_settings_sections( 'wpcuebasicquiz_text_settings' ); ?>
		</div>
	</div>
	<div id='tabs-3' >
		<div id="emailtabs">
		<ul class="outertabs">
			<li><a href="#emailtabs-1">Badge</a></li>
			<li><a href="#emailtabs-2">Level</a></li>
		</ul>	
		<div id="emailtabs-1">
			<div class="innertabpanel">
				<?php settings_fields('wpcuebasicquiz_badge_adminemails' ); ?>	
				<?php do_settings_sections('wpcuebasicquiz_badge_adminemails'); ?>
				<?php settings_fields('wpcuebasicquiz_badge_useremails' ); ?>	
				<?php do_settings_sections('wpcuebasicquiz_badge_useremails'); ?>
			</div>
		</div>
		<div id="emailtabs-2">
			<div class="innertabpanel">
				<?php settings_fields('wpcuebasicquiz_level_adminemails' ); ?>	
				<?php do_settings_sections('wpcuebasicquiz_level_adminemails'); ?>
				<?php settings_fields('wpcuebasicquiz_level_useremails' ); ?>	
				<?php do_settings_sections('wpcuebasicquiz_level_useremails'); ?>
			</div>
		</div>
		</div>
	</div>
	<div id="tabs-4">
		<div class="settingtab">
			<?php settings_fields('wpcuebasicquiz_payment_methods' ); ?>	
			<?php do_settings_sections('wpcuebasicquiz_payment_methods'); ?>
			<?php settings_fields('wpcuebasicquiz_stripe_details' ); ?>	
			<?php do_settings_sections('wpcuebasicquiz_stripe_details'); ?>
		</div>
	</div>
	<div id="tabs-5">
		<div class="innertabpanel">
			<?php settings_fields('wpcuebasicquiz_issuer_details');?>
			<?php do_settings_sections('wpcuebasicquiz_issuer_details');?>
		</div>
	</div>
	<div id="tabs-6">
		<div class="innertabpanel">
			<?php settings_fields('wpcuwbasicquiz_submit_dialogs');?>
			<?php do_settings_sections('wpcuwbasicquiz_submit_dialogs');?>
			<?php settings_fields('wpcuwbasicquiz_autosubmit_dialogs');?>
			<?php do_settings_sections('wpcuwbasicquiz_autosubmit_dialogs');?>
		</div>
	</div>
			<div class="wpprocuesettingsubmit"><?php submit_button(); ?></div>
</div>
</form>
<script>
jQuery(document).ready(function($){
 $('form').find(':input').each(function(i, elem) {
         var input = $(elem);
		 if((elem.type=='checkbox')||(elem.type=='radio')){
		 input.data('initialState', input.is(":checked"));
		 }else{
         input.data('initialState', input.val());}
    });
function restore() {
    $('form').find(':input').each(function(i, elem) {
         var input = $(elem);
		 if((elem.type=='checkbox')||(elem.type=='radio')){
			input.prop('checked', input.data('initialState'));
		 }else{
         input.val(input.data('initialState'));}
    });
}
$activetab=<?php if(isset($setting['activetab'])){echo ($setting['activetab']-1);}else{echo 0;} ?>;
$('#settingstab').tabs({activate: function(event, ui) {
		restore();
        $('input[name="wpcuequiz_setting[activetab]"]').val((ui.newTab.index()+1));
		}
    },{active:$activetab});
	$('#emailtabs').tabs();
$('#upload_image_button').click(function(){
	formfield = $('#upload_image').attr('name');
    tb_show( '', 'media-upload.php?type=image&amp;TB_iframe=true' );
    return false;
});
window.send_to_editor = function(html) {
	imgurl = jQuery('img',html).attr('src');
 jQuery('#wpcuebasicquiz-setting-issuerlogo').val(imgurl);
 $('#badgeimage').attr('src',imgurl);
 $('#addedimage').show();
 tb_remove();
}
$(document).on('click','#imageremovetool',function(){
jQuery('#wpcuebasicquiz-setting-issuerlogo').val('');
 $('#badgeimage').attr('src','');
 $('#addedimage').hide();
 });
});

</script>