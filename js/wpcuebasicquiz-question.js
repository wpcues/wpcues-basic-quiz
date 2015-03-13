jQuery(document).ready(function($){
var questiontype=$('input[name=origquestiontype]').val();
$('#answersbox-1').show();
if(questiontype==3){
	$('#matchquestion').show();
	$('#answersboxtab-1').tabs();
	$('#answersbox-2').show();
	$('#answersboxtab-2').tabs();
	$('#answertab-1').tabs();
}else{$('#answersboxtab-1').tabs();}
$('#answereditor').tabs();
switch(questiontype){
	case 1 :$('.singlechoicebox').show();break;
	case 2 :$('.multiplechoicebox').show();break;
	case 3 :$('#matchquestionbox').show();
			$('#matchquestionbox').find(':input').not(':button').each(function(i,elem){
				var input=$(elem);
				if(input.prop('disabled')){input.prop('disabled',false);input.data('disableState',0);}
			});
			break;
	case 4 :$('#sortquestionbox').show();
			$('#sortquestionbox').find(':input').not(':button').each(function(i,elem){
				var input=$(elem);
				if(input.prop('disabled')){input.prop('disabled',false);input.data('disableState',0);}
			});
			break;
	
}
$(document).on("click","#publish",function (event){
event.preventDefault();
 tinyMCE.triggerSave();
var $questiontype=$('input[name="origquestiontype"]').val();
if((typeof  $questiontype === 'undefined')||($questiontype === 0)){alert('please select question type');return false;}
var $questioncontent=$('#newquestion').val();
if((typeof $questioncontent === 'undefined') || ($.trim($questioncontent).length===0)){alert('please enter question details');return false;}
if(!($('#answersbox-1').is(':hidden'))){
$('#answersboxtab-1').find('li.activetab:visible').each(function(){
var $activeanchor=$(this).children('a');
var $questnum=parseInt($activeanchor.text(),10);
var $activetabpanel=$activeanchor.attr('href').substring(1);
var $answercontent=$('#'+$activetabpanel).find('textarea').val();
if((typeof $answercontent === 'undefined') || ($.trim($answercontent).length===0)){
	if($questiontype==3){
		alert('please enter answer for answer number'+$questnum+' in left column');
	}else{
		alert('please enter answer for answer number'+$questnum);}return false;
}
});
}
if(!($('#answersbox-2').is(':hidden'))){
$('#answersboxtab-2').find('li.activetab:visible').each(function(){
var $activeanchor=$(this).children('a');
var $questnum=parseInt($activeanchor.text(),10);
var $activetabpanel=$activeanchor.attr('href').substring(1);
var $answercontent=$('#'+$activetabpanel).find('textarea').val();
if((typeof $answercontent === 'undefined') || ($.trim($answercontent).length===0)){
	if($questiontype==3){
		alert('please enter answer for answer number'+$questnum+' in right column');
	}else{
		alert('please enter answer for answer number'+$questnum);}return false;
}
});
}
$('#publishing-action').children('.spinner').addClass('waiting ');
$('#publishing-action').children('.spinner').show();
var myformdata=$('form#quizax').serialize();
$.ajax({
    type:'POST',
    dataType:'json',
	url:ajaxurl,
    data: { 
        'action':'wpcuequizsavequestion_action',
		'myformdata':myformdata
	},
	success: function (data){
		if(data.msg=='saved'){
			if(parseInt($('input[name="savequestion_status"]').val(),10)){
				$('#message').html('<p>Question Updated</p>');
			}else{
					$('#message').html('<p>Question Published</p>');
			}
			$('#message').show();
		}else{alert('could not save the post');}
		$('#publishing-action').children('.spinner').removeClass('waiting');
		$('#publishing-action').children('.spinner').hide();
	}
});
});

});