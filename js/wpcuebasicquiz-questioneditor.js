jQuery(document).ready(function($){
$(document).on('change','#questiontype',function(){
var $val=parseInt($('#questiontype').val(),10);
var $origquestiontype=parseInt($('input[name=origquestiontype]').val(),10);
if(typeof $origquestiontype === 'undefined'){$origquestiontype=0;}
if($val===0){$('#questiontype option[value='+$origquestiontype+']').attr('selected','selected');return false;}
var $confirmation=true;$questionchanged=0;
if(!($('#answereditor').is(':empty')) || !($('#secondaryeditor').is(':empty'))){$confirmation1=confirm('You have already added some answers for earlier selected question type.Are you sure you want to change question type?');
if($confirmation1){$questionchanged=1;$confirmation=true;}else{$confirmation=false;}
}
if($confirmation){
$('#answereditor').find('textarea').each(function(){
tinymce.execCommand('mceRemoveEditor',false,$(this).attr('id'));
});
$secondary=[10,11,12,13,14,15,16,17];
$('#answereditor').empty();	
$('#secondaryeditor').empty();
if(jQuery.inArray($val, $secondary)!==-1){
	$.ajax({
		type: 'POST',
		dataType:'html',
		url:ajaxurl,
		data: { 
			'action':'wpcuequizaddsecondaryanswer_action',
			'questiontype':$val,
		},
		success: function(response){
			$('#secondaryeditor').html(response);
			$('input[name=origquestiontype]').val($val);
			if(($('#original_post_status').val()=='publish')&&($('input[name="savequestion_status"]').val()==1)){$('#questionchanged').val($questionchanged);}
		}
	});
}else{
	$.ajax({
		type: 'POST',
		dataType:'html',
		url:ajaxurl,
		data: { 
			'action':'wpcuequizaddinitialanswer_action',
			'questiontype':$val,
		},
		success: function(response){
			if(($('#original_post_status').val()=='publish')&&($('input[name="savequestion_status"]').val()==1)){$('#questionchanged').val($questionchanged);}
			$('#answereditor').html(response);
			$('#answereditorinner').tabs();
			$('#answersboxtab-1').tabs();
			if($val==3){$('#answersboxtab-2').tabs();$('#answertab-1').tabs();}
			$('#answertab-1').find('textarea').each(function(){
				var id=$(this).attr('id');
				$('#answertab-1').find('a[data-editor="'+id+'"]').bind('click',function(){window.wpActiveEditor=id;});
			});
			$('#answereditor').find('textarea').each(function(){
				var id=$(this).attr('id');
				tinymce.execCommand('mceAddEditor',true,id);
				quicktags({id:id});
				QTags._buttonsInit();
			});
			$('input[name=origquestiontype]').val($val);
		}
	});
}

}else{
	$('#questiontype option[value='+$origquestiontype+']').attr('selected','selected');
}
});
$(document).on('change','#namedisplaysetting',function(){
	$namedisplaysetting=parseInt($(this).val(),10);
	$suffix=$('#leftsecondarybar').find('.suffix');
	$title=$('#leftsecondarybar').find('.title');
	switch($namedisplaysetting){
		case 1:
			if($title.is(':hidden')){$title.show();}
			if($suffix.is(':hidden')){$suffix.show();}
			break;
		case 2:
			if($title.is(':hidden')){$title.show();}
			$suffix.hide();
			break;
		case 3:
			$title.hide();
			$suffix.hide();
			break;
		
	}
});
$(document).on('change','#dateformat',function(){
	$dateformat=parseInt($(this).val(),10);$parent=$('#leftsecondarybar').children('ul');
	$datedate=$parent.find('.datedate');
	$yeardate=$parent.find('.yeardate');
	$hoursdate=$parent.find('.hoursdate');
	$minsdate=$parent.find('.minsdate');
	switch($dateformat){
		case 1:
			$monthdate=$parent.find('.monthdate');
			alert($monthdate.index());alert($datedate.index());
			if($monthdate.index() > $datedate.index()){
				var newmonthdate=$monthdate.clone();
				$monthdate.remove();
				$parent.prepend(newmonthdate);
			}
			$parent.find('li').each(function(){
				if($(this).is(':hidden')){$(this).show();}
			});
			break;
		case 2:
			$monthdate=$parent.find('.monthdate');
			if($monthdate.index() < $datedate.index()){
				var newmonthdate=$monthdate.clone();
				$monthdate.remove();
				$datedate.after(newmonthdate);
			}
			$parent.find('li').each(function(){
				if($(this).is(':hidden')){$(this).show();}
			});
			break;
		case 3:
			$monthdate=$parent.find('.monthdate');
			if($monthdate.index() > $datedate.index()){
				var newmonthdate=$monthdate.clone();
				$monthdate.remove();
				$parent.prepend(newmonthdate);
			}
			$newmonth=$parent.find('.monthdate');
			if($newmonth.is(':hidden')){$newmonth.show();}
			if($datedate.is(':hidden')){$datedate.show();}
			if($yeardate.is(':hidden')){$yeardate.show();}
			$hoursdate.hide();
			$minsdate.hide();
			break;
		case 4:
			$monthdate=$parent.find('.monthdate');
			if($monthdate.index() < $datedate.index()){
				var newmonthdate=$monthdate.clone();
				$monthdate.remove();
				$datedate.after(newmonthdate);
			}
			$newmonth=$parent.find('.monthdate');
			if($newmonth.is(':hidden')){$newmonth.show();}
			if($datedate.is(':hidden')){$datedate.show();}
			if($yeardate.is(':hidden')){$yeardate.show();}
			$hoursdate.hide();
			$minsdate.hide();
			break;
		case 5:
			$monthdate=$parent.find('.monthdate');
			$monthdate.hide();$datedate.hide();$yeardate.hide();
			if($hoursdate.is(':hidden')){$hoursdate.show();}
			if($minsdate.is(':hidden')){$minsdate.show();}
			break;
	}
});
$(document).on('change','input[name="mainaddresshow"]',function(){
	$mainaddress=$('#leftsecondarybar').find('.mainaddress');
	if($(this).is(":checked")) {$mainaddress.show();}else{
		$checked=1;
		$('#rightsecondarybar').find('input').each(function(){
			if($(this).is(":checked")){$checked=0; return false;}
		});
		if($checked==0){
			$mainaddress.hide();
		}else{$('input[name="mainaddresshow"]')[0].checked=true;$('#leftsecondarybar').find('.mainaddress').show();}
	}
});
function default_address(){
	$checked=1;
		$('#rightsecondarybar').find('input').each(function(){
			if($(this).is(":checked")){$checked=0; return false;}
		});
		if($checked==1){
			$('input[name="mainaddresshow"]').prop('checked',true);
			$('#leftsecondarybar').find('.mainaddress').show();
		}
}
$(document).on('change','input[name="cityshow"]',function(){
	$cityaddress=$('#leftsecondarybar').find('.cityaddress');
	if($(this).is(":checked")) {$cityaddress.show();}else{
		$cityaddress.hide();
		default_address();
	}
});
$(document).on('change','input[name="stateshow"]',function(){
	$stateaddress=$('#leftsecondarybar').find('.stateaddress');
	if($(this).is(":checked")) {$stateaddress.show();}else{
		$stateaddress.hide();
		default_address();
	}
});
$(document).on('change','input[name="zipshow"]',function(){
	$zipcodeaddress=$('#leftsecondarybar').find('.zipcodeaddress');
	if($(this).is(":checked")) {$zipcodeaddress.show();}else{$zipcodeaddress.hide();default_address();}
});
$(document).on('change','input[name="countryshow"]',function(){
	$countryaddress=$('#leftsecondarybar').find('.countryaddress');
	if($(this).is(":checked")) {$countryaddress.show();}else{$countryaddress.hide();default_address();}
});

//hover states on the static widgets
$(document).on("click",".add_answer_button",function (){
$answerbox=$(this).parent().parent();
var $tabid=parseInt($answerbox.attr('id').split('-')[1],10);
var $questiontype=$('input[name="origquestiontype"]').val();
var $tablist=$('#answersboxtab-'+$tabid).children('ul');
var $lastanchor=$('#answersboxtab-'+$tabid).children('ul').children('li:last').children('a').attr('href');
var $index=parseInt($lastanchor.split('-')[1],10)+1;
var $newitemindex=parseInt($tablist.find('li.activetab').length,10)+1;
var $lastanchorhref=$lastanchor.split('-')[0];
$.ajax({
	type: 'POST',
	dataType:'html',
	url:ajaxurl,
	data: { 
		'action':'wpcuequizaddanswer_action',
		'questiontype':$questiontype,
		'index':$index,
		'tabid':$tabid
	},
	success: function(response){
		$tablist.append('<li class="activetab"><a href="'+$lastanchorhref+'-'+$index+'">'+$newitemindex+'</a></li>');
		$newtabid=$tablist.find('li.activetab:last').index();
		$newdivid=$lastanchorhref.substring(1);
		$('#answersboxtab-'+$tabid).append(response);
		$('#answersboxtab-'+$tabid).tabs('refresh');
		$('#answersboxtab-'+$tabid).tabs({active:$newtabid});
		$('#'+$newdivid+'-'+$index).find('textarea').each(function(){
			var id=$(this).attr('id');
			$('#'+$newdivid+'-'+$index).find('a[data-editor="'+id+'"]').bind('click',function(){window.wpActiveEditor=id;});
			tinymce.execCommand('mceAddEditor',true,id);
			quicktags({id:id});
			QTags._buttonsInit();
		});
		if(($('#original_post_status').val()=='publish')&&($('input[name="savequestion_status"]').val()==1)){$('#questionchanged').val(1);}
		$('#'+$newdivid+'-'+$index).find(':input').not(':button').each(function(i,elem){
			var input=$(elem);input.data('startState',input.val());
		});
		if(($questiontype==1) || ($questiontype==2)){$('#'+$newdivid+'-'+$index).find('.questdepbox').show();}
	}
});
});
$(document).on("click",".answer_close_button",function (){
var $activetabpanel=$(this).parent().parent();
var $currenttabid=$activetabpanel.attr('id');
var $tabparent=$activetabpanel.parent();
var $activetab=$tabparent.children('ul').children('li').find('a[href=#'+$currenttabid+']').parent('li');
var $currentindex=$activetab.index();
var $firstactive=$tabparent.children('ul').children('li.activetab:visible:first').index();
var $lastactive=$tabparent.children('ul').children('li.activetab:visible:last').index();
var $activetabindex;
if($currentindex==$firstactive){$activetabindex=$activetab.next('li.activetab:visible').index();}
else if($currentindex==$lastactive){$activetabindex=$activetab.prev('li.activetab:visible').index();}
else{$activetabindex=$activetab.next('li.activetab:visible').index();}
$activetabpanel.find(':input').not(':button').each(function(i,elem){
var input=$(elem);
if(!(input.prop('disabled'))){input.prop('disabled',true);}
input.data('disableState',1);
});
$activetab.hide();
$activetab.nextAll('li.activetab').each(function(){
var $activetabanchor=$(this).children('a');
var $activetabanchortext=parseInt($(this).text(),10)-1;
$activetabanchor.text($activetabanchortext);
});
$activetab.removeClass('activetab');
$tabparent.tabs( "option", "active",$activetabindex);
if(($('#original_post_status').val()=='publish')&&($('input[name="savequestion_status"]').val()==1)){$('#questionchanged').val(1);}
});
});