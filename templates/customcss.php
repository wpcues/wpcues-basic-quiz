<div id="customcss">
<?php if(!(isset($quizmeta['setting']['customcss']))){$quizmeta['setting']['customcss']='';} 
	wp_editor($quizmeta['setting']['customcss'],'customcsseditor',array('textarea_rows'=>30,'editor_class'=> 'requiredvar','quicktags'=>true,'dfw'=>true));
?>	
</div>