jQuery(document).ready(function($){
function questtablealternate(){
$('#questiontable >tbody > tr:odd').css("background-color","#eee");
$('#questiontable > tbody > tr:even').css("background-color","#fff");
$('.secquestadded > tbody > tr:even').css("background-color","#fff");
$('.secquestadded > tbody > tr:odd').css("background-color","#eee");
}
questtablealternate();
$(document).on("click",".questedit",function(event){
event.preventDefault();
if(!($('#quizeditor').is(':hidden'))){alert('editor existing');return false;}
var $par=$(this).closest('.rowdet');
$par.find(':input').not(':button').each(function(i,elem){
var input=$(elem);
input.prop('disabled',false);
input.data('disableState',0);
});
var myformdata=$('form#quizax').serialize();
$i=0;var $sectionids=[];
$('#questiontable').find('.sectentity').each(function(i,elem){
	$sectionids[$i]=$(this).find('input[name="entityid[]"]').first().val();$i++;
});
$.ajax({
	type: 'POST',
	dataType:'html',
	url:ajaxurl,
	data: { 
		'action':'wpcuequizeditquestion_action',
		'myformdata':myformdata,
		'sectionids':$sectionids,
	},
	success: function(response){
		$par.addClass('ediselected');
		if($('#quizeditor').is(':hidden')){$('#quizeditor').show();}
		$('#quizeditor').append(response);
		if($('#answereditor').is(':hidden')){$('#answereditor').show();}
		$('#answereditor').tabs();
		var qtypearr=[1,2,3,4];$val=parseInt($('#questiontype').val(),10);
		if(jQuery.inArray($val,qtypearr) != -1){
			$('#answersbox-1').show();
			if($val==3){
				$('#matchquestion').show();
				$('#answersboxtab-1').tabs();
				$('#answersbox-2').show();
				$('#answersboxtab-2').tabs();
				$('#answertab-1').tabs();
			}else{$('#answersboxtab-1').tabs();}
		}
		$('#quizeditor').find('textarea').each(function(){
			var id=$(this).attr('id');
			tinymce.execCommand('mceAddEditor',true,id);
			quicktags({id:id});
			QTags._buttonsInit();
			$('#'+id+'-tmce').click(function(){MathJax.Hub.Queue(["Typeset",MathJax.Hub,id]);});
		});
		
		switch($val){
			case 1 :$('.singlechoicebox').show();break;
			case 2 :$('.multiplechoicebox').show();break;
			case 3 :$('#matchquestionbox').show();
					break;
			case 4 :$('#sortquestionbox').show();
					$('#sortquestionbox').find(':input').not(':button').each(function(i,elem){
						var input=$(elem);
						if(input.prop('disabled')){input.prop('disabled',false);input.data('disableState',0);}
					});
					break;
			case 5:$('#fillgapsbox').show();break;
			case 6:$('#truefalsebox').show();break;
		}
		$('#answertab-1').find(':input').each(function(i,elem){var input = $(elem);
			input.data('startState',input.val());});
		$('input[name=origquestiontype]').val($val);
	}
});
});
$(document).on('click','.cancel_question_button',function(){
$questionid=$('#questiontable').find('input[name="entityid[]"]:enabled').val();
if(typeof $questionid === 'undefined'){$questionid=0;}
reset_quizeditor($questionid);
});
function reset_quizeditor($questionid){
$('#quizeditor').find('textarea').each(function(){tinymce.execCommand('mceRemoveEditor',false,$(this).attr('id'));});
$('#rowquest-'+$questionid).find(':input').not(':button').each(function(i,elem){
	var input=$(elem);
	input.prop('disabled',true);input.data('disableState',1);
});
$('#quizeditor').empty().hide();
}
$(document).on('click','.questremove',function(event){
event.preventDefault();
var $par=$(this).closest('.questentity');
if($('#original_post_status').val() != 'publish'){
$par.find(':input').not(':button').each(function(i,elem){
var input=$(elem);
input.prop('disabled',false);
input.data('disableState',0);});
var myformdata=$('form#quizax').serialize();
$.ajax({
    type: 'POST',
    dataType:'json',
	url:ajaxurl,
    data: { 
        'action':'wpcuequizremovequestion_action',
		'myformdata':myformdata
	},
	success: function(data){
		if(data.msg=='success'){
			$('input[name="questionschanges"]').val(1);
			$par.remove();
			rename_questtablerows();
			questtablealternate();
		}else{alert('Somer error occured.Please try again');
			$par.find(':input').not(':button').each(function(i,elem){
				var input=$(elem);
				input.prop('disabled',true);
				input.data('disableState',1);
			});
		}	
	}
});
}else{
	var $questionid=parseInt($par.find('input[name="entityid[]"]').val());
	var $instanceid=parseInt($par.find('input[name="instanceid[]"]').val());
	var $htmlcontent='';
	if($questionid !== $instanceid){
		$htmlcontent+='<input type="hidden" name="disabledentity[]" value="'+$instanceid+'" class="requiredvar">';
	}
	$('input[name="questionschanges"]').val(1);
	$par.remove();
	rename_questtablerows();
	questtablealternate();
	$('#disabledentities').append($htmlcontent);
}
});
$(document).on('click','.changequestorder',function(){
event.preventDefault();
var $par=$(this).closest('.questentity');
var $questionid=$par.find('input[name="entityid[]"]').val();
var $instanceid=$('#rowquest-'+$questionid).find('input[name="instanceid[]"]').val();
var $quizid=$('#quizid').val();
var $origquesttable=$('#questiontable').clone();
if($('#original_post_status').val() =='publish'){$publishstatus=1;}else{$publishstatus=0;}
var $info= $("#confirmquestorder");
$info.dialog({                   
	'dialogClass'   : "wp-dialog no-close", 
	'modal'         : true,
	'autoOpen'      : false, 
	'width' : 400,
	'height' : 190,
	'buttons':{
		"OK": function () {
			var $newquestnum=parseInt($('input[name="changequestordernumber"]').val().trim());
			if(!($newquestnum)){$(this).find('.msg').text('Please enter appropriate number');}else{
			if($newquestnum > $('#questiontable').find('.questentity').length){
				$(this).find('.msg').text('Please enter appropriate number.The number exceeds total number of questions in this quiz');
			}else{
				var $curnum=$('#questiontable').find('.questentity').index($par);
				var $newtarget=$('#questiontable').find('.questentity').eq($newquestnum-1);
				if($newquestnum < $curnum+1){
					$newtarget.before($par.clone());
				}else{$newtarget.after($par.clone());}
				$par.remove();
				var $newparsec=$newtarget.closest('.sectentity');
				var $newparsecid;var $newquestpos;
				if($newparsec.length != 0){
					$curpos=$('#rowquest-'+$questionid).index();
					$newparsecid=$newparsec.attr('id').split('-')[1];
					if($curpos==0){
						$lastsecorder=parseFloat($('#rowsec-'+$newparsecid).find('input[name="entityorder[]"]').val());
					}else{
						$lastsecorder=parseFloat($('#rowquest-'+$questionid).prev('tr').find('input[name="entityorder[]"]').val());
					}
					if($('#rowquest-'+$questionid).next('tr').length){
						$nextentityorder=parseFloat($('#rowquest-'+$questionid).next('tr').find('input[name="entityorder[]"]:first').val());
					}else{
						$nextentityorder=parseFloat($('#rowsec-'+$newparsecid).next('tr').find('input[name="entityorder[]"]:first').val());
					}
					$entityorder=($lastsecorder+$nextentityorder)/2;
					
				}else{
					$newparsecid=0;
					if($('#rowquest-'+$questionid).prev('tr').length){
						$prevorder=parseFloat($('#rowquest-'+$questionid).prev('tr').find('input[name="entityorder[]"]:last').val());
					}else{$prevorder=0;}
					if($('#rowquest-'+$questionid).next('tr').length){
						$nextorder=parseFloat($('#rowquest-'+$questionid).next('tr').find('input[name="entityorder[]"]:first').val());
						$entityorder=($prevorder+$nextorder)/2;
					}else{$entityorder=$prevorder+1;}
					
				}
					$.ajax({
						type: 'POST',
						dataType: 'json',
						url:ajaxurl,
						data: {'action':'wpcuequizchangequestorder_action',
							'questionid':$questionid,
							'quizid':$quizid,
							'newsectionid':$newparsecid,
							'newquestpostion':$entityorder,
							'publishstatus':$publishstatus,
							'instanceid':$instanceid},
						success: function(data){
							if(data.msg=='success'){
								$('#rowquest-'+$questionid).find('input[name="entityorder[]"]').val($entityorder);
								$('#rowquest-'+$questionid).find('input[name="parentid[]"]').val($newparsecid);
								rename_questtablerows();
								questtablealternate();
								$('input[name="questionschanges"]').val(1);
								$info.dialog('close');
							}else{
								$('#questiontable').replaceWith($origquesttable);
								$info.find('.msg').text('could not process the request.Please try again');
							}
						}});
				
			}}
		},
		"Cancel": function () {
			$(this).dialog('close');
		}
	}
});
$info.dialog('widget').position({my:"center", at:"center", of:window});
$info.dialog('open');	
});
$(document).on('click','.changeansorder',function(){
var $info= $("#confirmquestorder");
$info.dialog({                   
	'dialogClass'   : "wp-dialog no-close", 
	'modal'         : true,
	'autoOpen'      : false, 
	'position'      : 'center',
	'width' : 400,
	'height' : 190,
	'buttons':{
		"OK": function () {
			}
	}
});
});
$(document).on('click','.sortquest',function(){
$(this).addClass('selected');
});
$(document).on("click","#add_question_button",function (){
if(!($('#quizeditor').is(':hidden'))){alert('Question editor is already Open.');return false;}
$autodraftsavestatus=parseInt($('#autodraftsavestatus').val());
	if($autodraftsavestatus === 1){alert('Please enter Quiz title first');return false;}
$i=0;var $sectionids=[];
	$('#questiontable').find('.sectentity').each(function(i,elem){$sectionids[$i]=$(this).find('input[name="entityid[]"]').first().val();$i++});
$.ajax({
	type: 'POST',
	dataType:'html',
	url:ajaxurl,
	data: { 
		'action':'wpcuequizaddquestion_action',
		'quizid':$('#quizid').val(),
		'sectionid':$('#sectionid').val(),
		'sectionids':$sectionids,
	},
	success: function(response){
		$('#quizeditor').append(response);
		$('#quizeditor').find("textarea").each(function(i){
			var id=$(this).attr('id');
			$('#quizeditor').find('a[data-editor="'+id+'"]').bind('click',function(){window.wpActiveEditor=id;});
			tinymce.execCommand('mceAddEditor',true,id);
			quicktags({id:id});
			QTags._buttonsInit();
		});
		$('#answertab-1').find(':input').each(function(i,elem){var input = $(elem);
			input.data('startState', input.val());
			input.prop('disabled',true);input.data('disableState',1);
		});
		if($('#quizeditor').is(':hidden')){$('#quizeditor').show();}
		$('#tabs').tabs({heightStyle: "content"});
		$('#tabs').tabs('refresh');	
	}
});
});
$(document).on("click",".save_question_button",function (){
 tinyMCE.triggerSave();
var $questiontype=$('input[name="origquestiontype"]').val();
if((typeof  $questiontype === 'undefined')||($questiontype === 0)){alert('please select question type');return false;}
var $questioncontent=$('#newquestion').val();
if((typeof $questioncontent === 'undefined') || ($.trim($questioncontent).length===0)){alert('please enter question details');return false;}
var $breakfunc=0;
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
		alert('please enter answer for answer number'+$questnum);}
		$breakfunc=1;return false;
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
		alert('please enter answer for answer number'+$questnum);}
		$breakfunc=1;return false;
}
});
}
if($breakfunc==1){return false;}
var myformdata=$('form#quizax').serialize();
$sectionid=parseInt($('#sectionvalues').val());
if($('input[name="savequestion_status"]').val() != 1){
if($sectionid !== 0){
$lastsecorder=parseFloat($('#rowsec-'+$sectionid).find('input[name="entityorder[]"]:last').val());
if($('#rowsec-'+$sectionid).next('tr').length){
$nextentityorder=parseFloat($('#rowsec-'+$sectionid).next('tr').find('input[name="entityorder[]"]:first').val());
$entityorder=($lastsecorder+$nextentityorder)/2;
}else{$entityorder=$lastsecorder+1;}
}else{
if($('#questiontable tr').length ==0){$entityorder=1;
}else{$entityorder=parseFloat($('input[name="entityorder[]"]:last').val())+1;alert($entityorder);}
}

}else{
$questionid=$('input[name="entityid[]"]:enabled').val();
$presentsectionid=$('input[name="parentid[]"]:enabled').val();
if($presentsectionid != $sectionid){
if($sectionid !== 0){
$lastsecorder=parseFloat($('#rowsec-'+$sectionid).find('input[name="entityorder[]"]:last').val());
if($('#rowsec-'+$sectionid).next('tr').length){
$nextentityorder=parseFloat($('#rowsec-'+$sectionid).next('tr').find('input[name="entityorder[]"]:first').val());
$entityorder=($lastsecorder+$nextentityorder)/2;
}else{$entityorder=$lastsecorder+1;}
}else{
$entityorder=parseFloat($('input[name="entityorder[]"]:last').val())+1;
}
}else{$entityorder=$('input[name="entityorder[]"]:enabled').val();}
}
$.ajax({
    type:'POST',
    dataType:'json',
	url:ajaxurl,
    data: { 
        'action':'wpcuequizsavequestion_action',
		'myformdata':myformdata,
		'entityorder':$entityorder,
	},
	success: function (data){
		if(data.msg=='saved'){
			var $content=data.content;
			var $instanceid=data.instanceid;
			var $questionid=data.questionid;var $sectionid=data.sectionid;
			var $fincontent;$('input[name="questionschanges"]').val(1);
			if(parseInt($('input[name="savequestion_status"]').val(),10)){
					$fincontent='<td class="row-number-wrapper questnum"></td>'+$content;
					$fincontent+='<td class="handlerow"></td>';
					$('#rowquest-'+$questionid).html($fincontent);
					$('input[name="savequestion_status"]').val(0);
					$target=$('#rowquest-'+$questionid);
					if(pagenow == 'quiz_page_wpcuequizaddnew'){
					$parentsec=$target.closest('.sectentity');
					if($parentsec.length != 0){
						var $prevsectionid=parseInt($parentsec.attr('id').split('-')[1],10);
					}else{var $prevsectionid=0;}
					$stat=(($parentsec.length == 0) && ($sectionid != 0)) ? true : false;
					$anotherstat=(($parentsec.length != 0) && ($sectionid != $prevsectionid)) ? true : false;
					if($stat || $anotherstat){
						if($sectionid != 0){
						$newtargettable=$('#rowsec-'+$sectionid).find('.secquestadded');
						if($newtargettable.length == 0){
							$newtargettable=$('<table class="secquestadded"><tbody></tbody></table>');
							$rowfull=$('#rowsec-'+$sectionid).find('.rowfull');
							$rowfull.append($newtargettable);}
						$newtargettable.append($target.clone()).find('.rowshort').show();
						}else{
							$newtargettable=$('#questiontable');$newtargettable.append($target.clone()).find('.rowshort').show();
						}
						$target.remove();
					}
					}	
			}else{
				if($('#questiontable').find('tr').length===0){$('#addedquestion').css('display','block');}
					$fincontent="<tr id='rowquest-"+$questionid+"'class='rowdet closed questentity'>";
					$fincontent+="<td class='row-number-wrapper questnum'></td>"+$content;
					$fincontent+='<td class="handlerow"></td></tr>';
					if(pagenow == 'quiz_page_wpcuequizaddnew'){
						if(parseInt($sectionid,10) !== 0){
							$parenttable=$('#rowsec-'+$sectionid).find('.secquestadded');
							if($parenttable.length == 0){
								$rescontent='<table class="secquestadded"><tbody></tbody></table>';
								$('#rowsec-'+$sectionid).find('.rowfull').append($rescontent);
								$parenttable=$('#rowsec-'+$sectionid).find('.secquestadded');
							}
							$parenttable.append($fincontent);
						}else{
							$('#questiontable > tbody').append($fincontent);
						}
					}else{
						$('#questiontable > tbody').append($fincontent);
					}
			}
			rename_questtablerows();
			questtablealternate();
			MathJax.Hub.Queue(["Typeset",MathJax.Hub,'rowquest-'+$questionid]);
			reset_quizeditor($questionid);
		}else{alert('could not save the post');}
	}
});
});
function rename_questtablerows(){
	var $i=1;
	$('#questiontable').find('.questnum').each(function(){
		$(this).text('Q.'+$i);
		$i++;
	});
	$i=1;
	$('#questiontable').find('.secnum').each(function(){
		$(this).text('S. '+$i);
		$rowtitle=$(this).siblings('.rowtitle');
		$sectable=$rowtitle.find('.secquestadded');
		$rownum=$sectable.find('tr').length;
		if($rownum==0){
			$rowtitle.find('.questcounttext').text('No Question');
		}else{
			if($rownum==1){  $rowtitle.find('.questcounttext').text($sectable.find('.questnum').text());
			}
			else{ 
			$rowtitle.find('.questcounttext').text($sectable.find('.questnum:first').text()+'-'+$sectable.find('.questnum:last').text());
			}
		}
		$i++;
	});
}
$(document).on("click",".handlerow",function(){
$(this).parent().toggleClass('closed');
$(this).siblings('.rowtitle').children('.rowshort').toggle();
$(this).siblings('.rowtitle').children('.rowfull').toggle();
});
$(document).on({'mouseenter':function (){
$(this).find('.rowactions').show();
},mouseleave:function(){
$(this).find('.rowactions').hide();
}},'.sectentity');
$(document).on({'mouseenter':function (){
$(this).find('.questrowactions').show();
},mouseleave:function(){
$(this).find('.questrowactions').hide();
}},'.questentity');
$(document).on('change','.questioncorrectanswer',function(){
	if(($('#original_post_status').val()=='publish')&&($('input[name="savequestion_status"]').val()==1)){$('#questionchanged').val($questionchanged);}
});
$(document).on('change','.questionpoint',function(){
	if(($('#original_post_status').val()=='publish')&&($('input[name="savequestion_status"]').val()==1)){$('#questionchanged').val($questionchanged);}
});
$(document).on('change','.partialpoint',function(){
	if(($('#original_post_status').val()=='publish')&&($('input[name="savequestion_status"]').val()==1)){$('#questionchanged').val($questionchanged);}
});
});
