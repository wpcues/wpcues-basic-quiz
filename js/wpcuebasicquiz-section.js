jQuery(document).ready(function($){
$(document).on("click","#publish",function (event){
event.preventDefault();
tinyMCE.triggerSave();
var $sectionname=$('input[name=post_title]').val();
if((typeof $sectionname === 'undefined') || ($.trim($sectionname).length ===0)){alert('Please Enter Section Name');return false;}
if(!($('#quizeditor').is(':hidden'))){alert('Please first save the question');return false;}
$('form#quizax').find(':input.requiredvar').each(function(i, elem){
	var input = $(elem);
	if(input.prop('disabled')){
		input.data('initialState',1);
		input.prop('disabled',false);
	}else{input.data('initialState',0);}
});
var myformdata=$('form#quizax').serialize();
$('#publishing-action').children('.spinner').addClass('waiting ');
$('#publishing-action').children('.spinner').show();
$.ajax({
    type:'POST',
    dataType:'json',
	url:ajaxurl,
    data: { 
        'action': 'wpcuequizsavesectionform_action', //calls wp_ajax_nopriv_ajaxlogin
        'myformdata':myformdata
	},
	success: function (data){
		$('form#quizax').find(':input.requiredvar').each(function(i, elem){
			var input = $(elem);
			if(input.data('disableState')){$(this).prop('disabled',true);}
		});
		$('#publishing-action').children('.spinner').removeClass('waiting');
		$('#publishing-action').children('.spinner').hide();
		if(data.msg=='success'){
			$('input[name="questionschanges"]').val(0);
			$('#publish').val('Update');
			if($('input[name="tax_input[quizcategory][]"]:checked').length===0){
				$('input[name="tax_input[quizcategory][]"][value="1"]').attr('checked', 'checked');
			}
			if($('#original_publish').val() =='Update'){
				$('#message').html('<p>Quiz Updated.</p>');
				$('#questiontable').find('tr').each(function(){
					var newinstanceid=$(this).find('input[name="entityid[]"]').val();
					$(this).find('input[name="instanceid[]"]').val(newinstanceid);
				});
			}else{
				$('#original_publish').val('Update');
				$('#original_post_status').val('publish');
				$('#message').html('<p>Quiz Published</p>');
			}
			$('#message').show();
			if($('#questiontable tr').length){
				$('#questiontable tr').each(function(){
					$(this).find('input[name="instanceid[]"]').val($(this).find('input[name="entityid[]"]').val());
				});
			}
				
		}else{alert('could not save the post');}	
		$('form#quizax').find(':input.requiredvar').each(function(i, elem){
			var input = $(elem);
			if(input.data('initialState')===1){
				input.prop('disabled',true);
			}
		});
	}
});
});
});
