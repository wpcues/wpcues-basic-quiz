jQuery(document).ready(function($){
function wpcuebasicquiztimer(){
$time=0;
if($('#wpcuebasicquiztimerhours').length){$hours=parseInt($('#wpcuebasicquiztimerhours').text(),10);$time+=$hours*3600;}
if($('#wpcuebasicquiztimermins').length){$mins=parseInt($('#wpcuebasicquiztimermins').text(),10);$time+=$mins*60;}
$secs=parseInt($('#wpcuebasicquiztimersecs').text(),10);
$time+=$secs;
if($time > 0){
timer=setTimeout(function() {timeticker($time);},1000);
}else{
$('#quizpost :input').not(':button').prop('disabled',true);
$autosubmission=parseInt($('input[name=autosubmission]').val(),10);
if($autosubmission==1){
$('.submitquizbut').click();
}
}
}
function default_steps(){
if(($('input[name="disablestartbutton"]').length >0) && ($('input[name="disablestartbutton"]').val()==1)){
if($('#wpcuebasicquiztimercontent').length){wpcuebasicquiztimer();}
}
if(($('input[name="disableintermediatecontrol"]').length >0) && ($('input[name="disableintermediatecontrol"]').val()==1)){
if($('#wpcuebasicquiztimercontent').length){wpcuebasicquiztimer();}
}
}
default_steps();
function timeticker($time){
$time--;
if($('#wpcuebasicquiztimerhours').length){$hours=parseInt($('#wpcuebasicquiztimerhours').text(),10);}else{$hours=0;}
if($('#wpcuebasicquiztimermins').length){$mins=parseInt($('#wpcuebasicquiztimermins').text(),10);}else{$mins=0;}
$secs=parseInt($('#wpcuebasicquiztimersecs').text(),10);
if($secs){
$newsecs=$secs-1;
if($mins){$newmins=$mins;}
if($hours){$newhours=$hours;}
}else{
if($mins){$newmins=$mins-1;if($hours){$newhours=$hours;}$newsecs=59;
	}else if($hours){$newsecs=59;$newmins=59;$newhours=$hours-1;}
}
if($newsecs<10){$newsecstext='0'+$newsecs;}else{$newsecstext=$newsecs;}
if(typeof $newmins !='undefined'){if($newmins<10){$newminstext='0'+$newmins;}else{$newminstext=$newmins;}}
if(typeof $newhours !='undefined'){if($newhours<10){$newhourstext='0'+$newhours;}else{$newhourstext=$newhours;}}
if($hours){$('#wpcuebasicquiztimerhours').text($newhourstext);}
if($mins || $newmins){$('#wpcuebasicquiztimermins').text($newminstext);}
$('#wpcuebasicquiztimersecs').text($newsecstext);
if($time==0){clearTimeout(timer);timer=0;$('#quizpost :input').not(':button').prop('disabled',true);}else{
timer =setTimeout(function() {timeticker($time);},1000);}
}
$('.sortquestion').sortable();
$('.matchquestion').each(function(i,elem){
		matchbox=$(this);questionid=parseInt(matchbox.attr('id').split('-')[1],10);
		$('#matchquestion-'+questionid).sortable();
		$('#resmatchbox-'+questionid).sortable();
	/*$('#matchquestion-'+questionid,'#resmatchbox-'+questionid).sortable({
      connectWith: ".resmatchquestion"
    }).disableSelection();*/
	});
$(document).on('click','.submitquizbut,.savequizbut',function(e){
e.preventDefault();
var matchquestres=[];
if($(this).hasClass('submitquizbut')){$action='submit';}else{$action='save';}
$('form#quizpost').find(':input').each(function(i, elem){
	var input = $(elem);
	if(input.prop('disabled')){
		input.data('initialState',1);
		input.prop('disabled',false);
	}else{input.data('initialState',0);}
});
var myformdata=$('form#quizpost').serialize();
$('form#quizpost').find(':input').each(function(i, elem){
				var input = $(elem);
				if(input.data('initialState')){
					input.prop('disabled',true);
				}
			});
$i=0;
$('form#quizpost').find('.resultbox').each(function(){
$questionid=parseInt($(this).attr('id').split('-')[1],10);
$(this).children('li').each(function(e){
$input=$(this).find('input');
matchquestres[$questionid][$i]=$input.attr('value');
$i++;
});});
$instanceid=$('input[name="instanceid"]').val();
$timeremaining=0;
if($('#wpcuebasicquiztimercontent').length){
if($('#wpcuebasicquiztimerhours').length){$hours=parseInt($('#wpcuebasicquiztimerhours').text(),10);$timeremaining+=$hours*3600;}
if($('#wpcuebasicquiztimermins').length){$mins=parseInt($('#wpcuebasicquiztimermins').text(),10);$timeremaining+=$mins*60;}
$secs=parseInt($('#wpcuebasicquiztimersecs').text(),10);
$timeremaining+=$secs;
}
if(($action=='submit')&& ($('#quizsubmitdialog').length)){
$('#quizsubmitdialog').dialog({                   
		'dialogClass'   : "wp-dialog no-close", 
		'modal'         : true,
		'autoOpen'      : false, 
		'buttons':{
				"OK": function(){
					$(this).dialog('close');
					$('#quizstartpage').hide();
					$('#quizintermediatepage').hide();
					$('#quizmainpage').hide();
					$("#processdiv").show();
					$.ajax({
						type:'POST',
						dataType:'html',
						url:wpcuebasicquizajax.ajaxurl,
						data:{'action':'wpcuequizgetquizresult_action',
							'myformdata':myformdata,
							'matchquestres':matchquestres,
							'quizaction':$action,
							'instanceid':$instanceid,
							'timeremaining':$timeremaining},
						success: function (data){
							if($action=='submit'){
								$("#processdiv").hide();
								$('#quizfinalpage').show();
								$('#quizfinalpage').append(data);
								if(typeof timer != 'undefined'){clearTimeout(timer);timer=0;}
								//FB.XFBML.parse(document.getElementById('finalquizsocialshare'));
								MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
							}
						}

						});
						},
						"Cancel": function () { 
							$(this).dialog('close');
						}
					}
				});
	$('#quizsubmitdialog').dialog('widget').position({my:"center", at:"center", of:window});
	$('#quizsubmitdialog').dialog('open');
}else{
	if($action=='submit'){
		$('#quizstartpage').hide();
		$('#quizintermediatepage').hide();
		$('#quizmainpage').hide();
		$('#quizstartpage').hide();
		$("#processdiv").show();
	}
	$.ajax({
		type:'POST',
		dataType:'html',
		url:wpcuebasicquizajax.ajaxurl,
		data:{'action':'wpcuequizgetquizresult_action','myformdata':myformdata,'matchquestres':matchquestres,'quizaction':$action,'instanceid':$instanceid,'timeremaining':$timeremaining},
		success: function (data){
			if($action=='submit'){$("#processdiv").hide();
				$('#quizfinalpage').show();
				$('#quizfinalpage').append(data);
				if(typeof timer != 'undefined'){clearTimeout(timer);timer=0;}
				MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
			}else{
			
			}
		}
	
	});
}
});
$(document).on('click','#startquizbutton , #continuequizbutton ',function(e){
	e.preventDefault();
	$quizlogin=$('input[name="quizlogin"]').val();
	$quizid=$('input[name="quizid"]').val();
	if($(this).attr('id')=='continuequizbutton'){$status=1;}else{$status=0;}
	$instanceid=parseInt($('input[name="instanceid"]').val(),10);
	$quizmode=parseInt($('input[name="quizmode"]').val(),10);
	$.ajax({
		type:'POST',
		dataType:'json',
		url:wpcuebasicquizajax.ajaxurl,
		data:{'action':'wpcuequizstartquiz_action','quizid':$quizid,'instanceid':$instanceid,'quizmode':$quizmode},
		success:function(data){
			if(data.msg=='success'){
				$newinstanceid=data.instance;
				if($instanceid==0){
					$('#quizstartpage').hide();
					$('#startquizbutton').hide();
				}else{
					$('#quizintermediatepage').hide();
					$('#continuequizbutton').hide();
				}
				$('#quizmainpage').show();
				$('#quizmainpage').append(data.content);
				$('#quizmaincontent').find('.questpage:first').show();
				$('input[name="instanceid"]').val($newinstanceid);
				if($('#wpcuebasicquiztimercontent').length){$('#wpcuebasicquiztimercontent').show();wpcuebasicquiztimer();}
				MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
				$('#quizmaincontent').find('.sortquestion').each(function(){$(this).sortable({
					deactivate: function(event,ui){
					$questid=ui.item.attr('id').split('-')[1];
					}
				})
				.disableSelection();});;
				$('#quizmaincontent').find('.matchquestion').each(function(){
					$matchunit=$(this).attr('id');
					$matchid=$matchunit.split('-')[1];
					$resid='resmatchbox-'+$matchid;
					$('#'+$matchunit+', #'+$resid).sortable({
					connectWith: ".resmatchquestion-"+$matchid
					}).disableSelection();
				});
			}else{
				alert('some error occured.Please try again.');
			}
		}
	});
});
$(document).on('click','input[name=nextpagebutton]',function(e){
e.preventDefault();
$curquestpage=$(this).closest('.questpage');
$questpage=$curquestpage.attr('id').split('-');
$questpage[1]=parseInt($questpage[1])+1;
$nextquestpage=$questpage.join('-');
$curquestpage.hide();
$('#'+$nextquestpage).show();
}
);
$(document).on('click','input[name=prevpagebutton]',function(e){
e.preventDefault();
$curquestpage=$(this).closest('.questpage');
$questpage=$curquestpage.attr('id').split('-');
$questpage[1]=parseInt($questpage[1])-1;
$prevquestpage=$questpage.join('-');
$curquestpage.hide();
$('#'+$prevquestpage).show();
}
);
$(document).on('click','#autosubmitquizbut',function(e){
	e.preventDefault();
	$autosubdial=$('input[name="autosubdial"]').val();
	if($autosubdial != 1){$('.submitquizbut').click();}else{
	$info= $("#autosubmitdialog");
				$info.dialog({                   
					'dialogClass'   : "wp-dialog no-close", 
					'modal'         : true,
					'autoOpen'      : false, 
					'buttons':{
						"OK": function(){
							$(this).dialog('close');
							$('.submitquizbut').click();
						},
						"Cancel": function () {
							$(this).dialog('close');
						}
					}
				});
	$info.dialog('widget').position({my:"center", at:"center", of:window});
	$info.dialog('open');	}
});
$(document).on('click','#quizloginbutton',function(e){
if($(this).hasClass('dialoglogin')){
e.preventDefault();
$info=$('#quizllogindialog');
	$info.dialog({                   
					'dialogClass'   : "wp-dialog", 
					'modal'         : true,
					'autoOpen'      : false, 
				});
	$info.dialog('widget').position({my:"center", at:"center", of:window});
	$info.dialog('open');	

}
});
$(document).on('click','.showanswer',function(e){
e.preventDefault();
$questionblock=$(this).closest('.rowquest').find('.mainquest');
alert($questionblock.html());
$questionid=$questionblock.find('input[name="questionid[]"]').val();
$questiontype=parseInt($('input[name="questiontype-'+$questionid+'"]').val(),10);
$marked=0;
switch($questiontype){
	case 1:
		$checkedanswer=$questionblock.find('.answerdesc').find('input[name="answer-'+$questionid+'"]:checked').val();
		if(typeof $checkedanswer === 'undefined'){$marked=0;}else{$marked=1;}
		break;
	case 2:
		$checkedanswer=$questionblock.find('.answerdesc').find('input[name="answer-'+$questionid+'[]"]:checked').val();
		if(typeof $checkedanswer === 'undefined'){$marked=0;}else{$marked=1;}
		break;
	case 3:
		$replies=$questionblock.find('.resmatchquestion').find('li').length;
		if($replies){$marked=1;}
		break;
	case 4:
		$checkedanswer=$questionblock.find('input[name="sortquestionstatus"]').is(':checked');
		if($checkedanswer){$marked=1;}
		break;
	case 5:
		$questionblock.find('input[name="answer-'+$questionid+'[]"]').each(function(i,elem){
			var input = $(elem);
			alert(input.val());
			if($.trim(input.val()) == ''){$marked=0;}else{$marked=1;return false;}
		});
		break;
	case 6:
		$checkedanswer=$questionblock.find('.answerdesc').find('input[name="answer-'+$questionid+'"]:checked').val();
		if(typeof $checkedanswer === 'undefined'){$marked=0;}else{$marked=1;}
		break;
}
if($marked){
	$confirmation=true;
	$confirmation=confirm('Are you sure you want to view the correct answer ? If yes, you will not be able to change your marked answer then');
	alert($confirmation);
	if($confirmation){
		$questionblock.find(':input').not(':button').each(function(i, elem){
			if($(this).hasClass('disablestatus')){$(this).val(1);}
			if(!($(this).prop('disabled'))){$(this).prop('disabled',true);}
		});
		$('.savequizbut').click();
		$questtoolblock=$(this).closest('.questtools');
		$questtoolblock.find('.answercontainer').toggle();
		$questtoolblock.find('.reportquestcontainer').hide();
		$questtoolblock.find('.showhintcontainer').hide();
	}
}else{
	alert('Please reply the question first');
}
});
$(document).on('click','.reportquestion',function(e){
e.preventDefault();
$questtoolblock=$(this).closest('.questtools');
$questtoolblock.find('.reportquestcontainer').toggle();
$questtoolblock.find('.answercontainer').hide();
$questtoolblock.find('.showhintcontainer').hide();
});
$(document).on('click','.showhintquestion',function(e){
e.preventDefault();
$questtoolblock=$(this).closest('.questtools');
$questtoolblock.find('.showhintcontainer').toggle();
$questtoolblock.find('.answercontainer').hide();
$questtoolblock.find('.reportquestcontainer').hide();
});
$(document).on('click','.saveerror',function(e){
e.preventDefault();
$errorcontainer=$(this).closest('.reportquestcontainer');
$target=$errorcontainer.find('.reportquesttable');
$erroidinput=$(this).siblings('.reportquestid');
$errorid=parseInt($erroidinput.val(),10);
$questionid=parseInt($(this).closest('.rowquest').find('input[name="questionid[]"]').val(),10);
if($errorid != 0){
$erroraddedstatus=$('input[name="erroraddedstatus-'+$errorid+'"]').val();
$editstatus=1;
}else{$errorid=Math.random().toString(36).slice(2);$editstatus=0;$erroraddedstatus=0;}
$errortitleinput=$(this).siblings('.reportquesttitle');
$errortitle=$errortitleinput.val();
if((typeof $errortitle === 'undefined') || ($.trim($errortitle).length===0)){alert('please enter error title');return false;}
$errordescinput=$(this).siblings('.reportquestdesc');
$errordesc=$errordescinput.val();
if((typeof $errordesc === 'undefined') || ($.trim($errordesc).length===0)){alert('please enter error detail');return false;}
if($editstatus != 1){$content='<div id="error-'+$errorid+'" class="errorentity">';}
$content+='<div class="errorinfo">';
$content+='<div class="errortitle">Title :'+$errortitle+'</div>';
$content+='<div class="errordesc">Description :'+$errordesc+'</div></div>';
$content+='<div class="erroedit"></div><div class="errodelete"></div>';
$content+='<input type="hidden" name="errorid-'+$questionid+'[]" value="'+$errorid+'">';
$content+='<input type="hidden" name="erroraddedstatus-'+$errorid+'" value="'+$erroraddedstatus+'">';
$content+='<input type="hidden" name="erroreditedstatus-'+$errorid+'" value="'+$editstatus+'">';
$content+='<input type="hidden" name="errordesc-'+$errorid+'" value="'+$errordesc+'">';
$content+='<input type="hidden" name="errortitle-'+$errorid+'" value="'+$errortitle+'">';
if($editstatus != 1){$content+='</div>';}
if($editstatus != 1){$target.append($content);}else{$('#error-'+$errorid).html($content);}
$erroidinput.val(0);$errortitleinput.val('');$errordescinput.val('');
});
$(document).on('click','.erroedit',function(e){
$errorid=parseInt($(this).closest('.errorentity').attr('id').split('-')[1],10);
$errortitle=$('input[name="errortitle-'+$errorid+'"]').val();
$errordesc=$('input[name="errordesc-'+$errorid+'"]').val();
$errorcontainer=$(this).closest('.reportquestcontainer');
$errorcontainer.find('.reportquestid').val($errorid);
$errorcontainer.find('.reportquesttitle').val($errortitle);
$errorcontainer.find('.reportquestdesc').val($errordesc);
});
$(document).on('click','.errodelete',function(e){
$(this).closest('.errorentity').remove();
});
});