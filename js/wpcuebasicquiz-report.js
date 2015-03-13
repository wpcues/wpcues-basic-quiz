jQuery(document).ready(function($){
$( "#tabs" ).tabs();
$("#innertabs").tabs();
$(document).on('click','#add_global_leaderboard',function(){
	var $info= $("#addgloballeaderboard");
	
    $info.dialog({                   
        'dialogClass'   : "wp-dialog", 
        'modal'         : true,
		'autoOpen'      : false, 
		position: { 
				my: "center", 
				at: "center" 
			},
		'width' : 600,
		'height' : 300,
		'open':function(event,ui){$originalhtm=$('#addgloballeaderboard').html();},
		 buttons: {
        "Save": function() {
		var data2=$('#globleaderboard').serialize();
			$.ajax({
            type: 'POST',
            dataType:'json',
			url:ajaxurl,
            data:data2,
			success: function(data){
			$postid=data.postid;
			$disabledstatus=data.disabledstatus;
			$('#disabledglobalboard').val($disabledstatus);
			if($disabledstatus==1){
				$('#add_global_leaderboard').prop('disabled',true);
				$('#globalleaderboardpromessage').show();
			}
			var noleaderboard=$('.noleaderboard');
			if((typeof(noleaderboard) != 'undefined') && (noleaderboard !== null)){
			noleaderboard.remove();}
			$title=data.title;
			$rownum=$('#gloabal_leaderboard tr').length;
			$rownum++;
			$htmlcontent='<tr id="globleaderboard-'+$postid+'" ';
			if($rownum % 2 == 0){
			$htmlcontent+='class="alternate"';}
			$htmlcontent+='><td>';$htmlcontent+=$title;
			$htmlcontent+='<div class="row-actions"><span class="edit"><a href="#globalleaderboardedit">Edit</a> | </span><span class="trash"><a class="submitdelete" title="Delete" href="#globalleaderboarddelete">Delete</a> | </span></div>';
			$htmlcontent+='</td><td>[wpcuebasicleader '+$postid+']</td></tr>';
			
			$('#gloabal_leaderboard > tbody').prepend($htmlcontent);
			}
			});
			 
          $( this ).dialog( "close" );
		  $('#addgloballeaderboard').html($originalhtm);
		  
        },
        Cancel: function() {
		
          $( this ).dialog( "close" );
		  $('#addgloballeaderboard').html($originalhtm);
        }
      }
	});
		
	$info.dialog('open');	
	
	});
	$(document).on('click','#add_quiz_leaderboard',function(){
	var $info= $("#addquizleaderboard");
	$info.dialog({                   
        'dialogClass'   : "wp-dialog", 
        'modal'         : true,
		'autoOpen'      : false, 
		position: { 
				my: "center", 
				at: "center" 
			},
		'width' : 600,
		'height' : 300,
		'open':function(event,ui){$originalhtm=$('#addquizleaderboard').html();},
		 buttons: {
        "Save": function() {
		var data2=$('#quizleaderboard').serialize();
			$.ajax({
            type: 'POST',
            dataType:'json',
			url:ajaxurl,
            data:data2,
			success: function(data){
			$postid=data.postid;
			$disabledstatus=data.disabledstatus;
			$('#disabledquizboard').val($disabledstatus);
			if($disabledstatus==1){
				$('#add_quiz_leaderboard').prop('disabled',true);
				$('#quizleaderboardpromessage').show();
			}
			var noleaderboard=$('.noleaderboard');
			if((typeof(noleaderboard) != 'undefined') && (noleaderboard !== null)){
			noleaderboard.remove();}
			$title=data.title;
			
			$rownum=$('#quiz_leaderboard tr').length;
			$rownum++;
			$htmlcontent='<tr id="quizleaderboard-'+$postid+'" ';
			if($rownum % 2 == 0){
			$htmlcontent+='class="alternate"';}
			$htmlcontent+='><td>';$htmlcontent+=$title;
			$htmlcontent+='<div class="row-actions"><span class="edit"><a href="#quizleaderboardedit">Edit</a> | </span><span class="trash"><a class="submitdelete" title="Delete" href="#quizleaderboarddelete">Delete</a> | </span></div>';
			$htmlcontent+='</td><td>[wpcuebasicleader '+$postid+']</td></tr>';
			$('#quiz_leaderboard > tbody').prepend($htmlcontent);
			}
			});
          $( this ).dialog( "close" );
		  $('#addquizleaderboard').html($originalhtm);
        },
        Cancel: function() {
          $( this ).dialog( "close" );
		  $('#addquizleaderboard').html($originalhtm);
        }
      }
	});
		
	$info.dialog('open');	
	
	});
	$(document).on('click','#add_chart',function(){
	var $info= $("#chartform");
	$info.dialog({                   
        'dialogClass'   : "wp-dialog", 
        'modal'         : true,
		'autoOpen'      : false, 
		position: { 
				my: "center", 
				at: "center" 
			},
		'width' : 600,
		'height' : 510,
		'open':function(event,ui){$originalhtm=$('#chartform').html();},
		 buttons: {
        "Save": function() {
		var data2=$('#quizchart').serialize();
			$.ajax({
            type: 'POST',
            dataType:'json',
			url:ajaxurl,
            data:data2,
			success: function(data){
			if(data.msg=='success'){
				$disabledstatus=data.disabledstatus;
				$('#disabledchart').val($disabledstatus);
				if($disabledstatus==1){
					$('#add_chart').prop('disabled',true);
					$('#chartpromessage').show();
				}
				$postid=data.postid;
			$content=data.content;
			var nocharts=$('.nocharts');
			if((typeof(nocharts) != 'undefined') && (nocharts !== null)){
			nocharts.remove();}
			$rownum=$('#charttable tr').length;
			$rownum++;
			$htmlcontent='<tr id="chart-'+$postid+'" ';
			if($rownum % 2 == 0){
			$htmlcontent+='class="alternate"';}
			$htmlcontent+='>';$htmlcontent+=$content;$htmlcontent+='</tr>';
			$('#charttable > tbody').prepend($htmlcontent);
			}else{alert('Sorry, Chart could not be added');}
			}
			});
          $( this ).dialog( "close" );
		  $('#chartform').html($originalhtm);
        },
        Cancel: function() {
          $( this ).dialog( "close" );
		  $('#chartform').html($originalhtm);
        }
      }
	});
		
	$info.dialog('open');	
	
	});
	$(document).on('change','#charttype',function() {   
		$charttype=$(this).val();
		$("#chartoption").show();
		$chartoption=$('input[name="chartoptionval"]:checked').val();
		if($chartoption==1){
			$chartgenericoption=$('#chartgenericoptionval').val();
			if($chartgenericoption != 1){
				if($charttype==3){
					$('#chartgenericoption').find('.groupnum').hide();
				}else{
					$('#chartgenericoption').find('.groupnum').show();
				}
			}
		}
	});
	$(document).on('change','#chartgenericoptionval',function(){
		$option=$(this).val();
		$charttype=$('#charttype').val();
		$('#chartgenericoption').find('.pointtext').hide();
		$('#chartgenericoption').find('.correctanstext').hide();
		if($charttype != 3){if($option==2){
			$('#chartgenericoption').find('.groupnum').show();
			$('#chartgenericoption').find('.pointtext').show();
		}else if($option==3){
			$('#chartgenericoption').find('.groupnum').show();
			$('#chartgenericoption').find('.correctanstext').show();
		}else{
			$('#chartgenericoption').find('.groupnum').hide();
		}
		}
	});
	$(document).on('change','input:radio[name="chartoptionval"]',function(){
		        if ($(this).is(':checked') && $(this).val() == '1') {
				$('#chartgenericoption').show();
				$('#chartuseroption').hide();
				$('#chartuserrow').hide();
				}else if($(this).is(':checked') && $(this).val() == '2'){$('#chartgenericoption').hide();$('#chartuseroption').show();$('#chartuserrow').show();}
	});
$(document).on('click','a[href="#globalleaderboardedit"]',function(){
	event.preventDefault();
$par=$(this).closest('tr');
$parid=$par.attr('id');
$postids=$parid.split('-');
$postid=$postids[1];
$.ajax({
            type: 'POST',
            dataType:'json',
			url:ajaxurl,
            data:{'action':'wpcuequizretrieveleaderboardinfo_action','postid':$postid},
			success: function(data){
				$leaderboardtitle=data.leaderboardtitle;
				$leaderboardid=data.leaderboardid;
				$leaderorder=data.leaderorder;
				$leadersnum=data.leadersnum;
				$leaderbasis=data.leaderbasis;
				$('#globalleaderboardid').val($leaderboardid);
				$('#leaderboardtitle').val($leaderboardtitle);
				$('#leaderorder option[value='+$leaderorder+']').attr('selected','selected');
				$('#leadersnum').val($leadersnum);
				$('#leaderbasis option[value='+$leaderbasis+']').attr('selected','selected');
				var $info= $("#addgloballeaderboard");
	
				$info.dialog({                   
					'dialogClass'   : "wp-dialog", 
					'modal'         : true,
					'autoOpen'      : false, 
					position: { 
				my: "center", 
				at: "center" 
			},
					'width' : 600,
					'height' : 300,
					'open':function(event,ui){$originalhtm=$('#addgloballeaderboard').html();},
					buttons: {
						"Save": function() {
							var data2=$('#globleaderboard').serialize();
							$.ajax({
								type: 'POST',
								dataType:'json',	
								url:ajaxurl,
								data:data2,
								success: function(data){
									$disabledstatus=data.disabledstatus;
									$('#disabledglobalboard').val($disabledstatus);
									if($disabledstatus==1){
										$('#add_global_leaderboard').prop('disabled',true);
										$('#globalleaderboardpromessage').show();
									}
									$postid=data.postid;
									$title=data.title;
									
									$htmlcontent='<td>';$htmlcontent+=$title;
									$htmlcontent+='<div class="row-actions"><span class="edit"><a href="#globalleaderboardedit">Edit</a> | </span><span class="trash"><a class="submitdelete" title="Delete" href="#globalleaderboarddelete">Delete</a> | </span></div>';
									$htmlcontent+='</td><td>[wpcuebasicleader '+$postid+']</td>';
			
								$par.html($htmlcontent);
							}
							});
							$( this ).dialog( "close" );
							  $('#addgloballeaderboard').html($originalhtm);
						},	
						Cancel: function() {
						$( this ).dialog( "close" );
						  $('#addgloballeaderboard').html($originalhtm);
						}
					}
				});
		
			$info.dialog('open');	
	
			}
	});

});
$(document).on('click','a[href="#quizleaderboardedit"]',function(){
event.preventDefault();
$par=$(this).closest('tr');
$parid=$par.attr('id');
$postids=$parid.split('-');
$postid=$postids[1];
$.ajax({
            type: 'POST',
            dataType:'json',
			url:ajaxurl,
            data:{'action':'wpcuequizretrieveleaderboardinfo_action','postid':$postid},
			success: function(data){
				$leaderboardtitle=data.leaderboardtitle;
				$leaderboardid=data.leaderboardid;
				$leaderorder=data.leaderorder;
				$leadersnum=data.leadersnum;
				$leaderbasis=data.leaderbasis;
				$quizid=data.quizid;
				$('#quizleaderboardid').val($leaderboardid);
				$('#quizleaderboardtitle').val($leaderboardtitle);
				$('#quizleaderorder option[value='+$leaderorder+']').attr('selected','selected');
				$('#quizleadersnum').val($leadersnum);
				$('#quizleaderbasis option[value='+$leaderbasis+']').attr('selected','selected');
				$('#quizname option[value='+$quizid+']').attr('selected','selected');
				var $info= $("#addquizleaderboard");
	
				$info.dialog({                   
					'dialogClass'   : "wp-dialog", 
					'modal'         : true,
					'autoOpen'      : false, 
					position: { 
				my: "center", 
				at: "center" 
			},
					'width' : 600,
					'height' : 300,
					'open':function(event,ui){$originalhtm=$('#addquizleaderboard').html();},
					buttons: {
						"Save": function() {
							var data2=$('#quizleaderboard').serialize();
							$.ajax({
								type: 'POST',
								dataType:'json',
								url:ajaxurl,
								data:data2,
								success: function(data){
								$disabledstatus=data.disabledstatus;
								$('#disabledquizboard').val($disabledstatus);
								if($disabledstatus==1){
									$('#add_quiz_leaderboard').prop('disabled',true);
									$('#quizleaderboardpromessage').show();
								}
								$postid=data.postid;
								$title=data.title;
								$htmlcontent='<td>';$htmlcontent+=$title;
								$htmlcontent+='<div class="row-actions"><span class="edit"><a href="#quizleaderboardedit">Edit</a> | </span><span class="trash"><a class="submitdelete" title="Delete" href="#quizleaderboarddelete">Delete</a> | </span></div>';
								$htmlcontent+='</td><td>[wpcuebasicleader '+$postid+']</td>';
								$par.html($htmlcontent);
								}
							});
					$( this ).dialog( "close" );
					$('#addquizleaderboard').html($originalhtm);
					},	
					Cancel: function() {
						$( this ).dialog( "close" );
						$('#addquizleaderboard').html($originalhtm);
					}
				}
			});
		
			$info.dialog('open');	
	
			}
	});

});
$(document).on('click','a[href="#globalleaderboarddelete"] , a[href="#quizleaderboarddelete"]',function(){
if($(this).attr('href')=='#globalleaderboarddelete'){
	$leaderboardtype=0;
}else{
	$leaderboardtype=1;
}
alert($leaderboardtype);
$par=$(this).closest('tr');
$parid=$par.attr('id');
$postids=$parid.split('-');
$postid=$postids[1];	
$.ajax({
            type: 'POST',
            dataType:'json',
			url:ajaxurl,
            data:{'action':'wpcuequizdeleteleaderboard_action','postid':$postid,'leaderboardtype':$leaderboardtype},
			success: function(data){
				if(data.msg == 'success'){
					$disabledstatus=data.disabledstatus;
					if($leaderboardtype==0){
						$('#disabledglobalboard').val($disabledstatus);
						if($disabledstatus==0){
							if($('#add_global_leaderboard').prop('disabled')){$('#add_global_leaderboard').prop('disabled',false);}
							$('#globalleaderboardpromessage').hide();
						}	
					}else{
						$('#disabledquizboard').val($disabledstatus);
						if($disabledstatus==0){
							if($('#add_quiz_leaderboard').prop('disabled')){$('#add_quiz_leaderboard').prop('disabled',false);}
							$('#quizleaderboardpromessage').hide();
						}	
					}
					$par.remove();
				}else{
				alert('could not delete the entry');
				}
			}
		});
	});
$(document).on('click','a[href="#chartdelete"]',function(){
$par=$(this).closest('tr');
$parid=$par.attr('id');
$postids=$parid.split('-');
$postid=$postids[1];	
$.ajax({
            type: 'POST',
            dataType:'json',
			url:ajaxurl,
            data:{'action':'wpcuequizdeletechart_action','postid':$postid},
			success: function(data){
				if(data.msg == 'success'){
					$disabledstatus=data.disabledstatus;
					$('#disabledchart').val($disabledstatus);
					if($disabledstatus==0){
						if($('#add_chart').prop('disabled')){$('#add_chart').prop('disabled',false);}
						$('#chartpromessage').hide();
					}	
					$par.remove();
				}else{
				alert('could not delete the entry');
				}
			}
		});
	});
$(document).on('click','a[href="#chartedit"]',function(){
$par=$(this).closest('tr');
$parid=$par.attr('id');
$postids=$parid.split('-');
$postid=$postids[1];
$originfucker=$('#chartform').html();

$.ajax({
            type: 'POST',
            dataType:'json',
			url:ajaxurl,
            data:{'action':'wpcuequizretrievechartinfo_action','postid':$postid},
			success: function(data){
				$postcontent=data.postcontent;
				$chartname=$postcontent['name'];
				$charttype=$postcontent['type'];
				$chartwidth=$postcontent['width'];
				$chartwidthunit=$postcontent['widthunit'];
				$chartheight=$postcontent['height'];
				$chartheightunit=$postcontent['heightunit'];
				$chartbarspace=$postcontent['barspace'];
				$chartbarspaceunit=$postcontent['barspaceunit'];
				$chartoption=$postcontent['option'];
				$chartorder=$postcontent['order'];
				$quizid=$postcontent['quizid'];
				$genericoption=$postcontent['genericoption'];
				$useroption=$postcontent['useroption'];
				$useroptionsec=$postcontent['useroptionsec'];
				if($chartoption==2){
					$chartuser=$postcontent['chartuser'];
				}
				$('#chartid').val($postid);
				$('#chartname').val($chartname);
				$('#charttype option[value='+$charttype+']').attr('selected','selected');
				$('#chartwidth').val($chartwidth);
				$('#chartwidthunit option[value='+$chartwidthunit+']').attr('selected','selected');
				$('#chartheight').val($chartheight);
				$('#chartheightunit option[value='+$chartheightunit+']').attr('selected','selected');
				$('#quizval').val($quizid);
				$('#barspacing').show();
				$('#chartoption').show();
				$('#chartorder').show();
				$('input[name=chartoptionval][value='+$chartoption+']').prop('checked',true);
				$('#chartorderval option[value='+$chartorder+']').attr('selected','selected');
				$('#chartgenericoption').find('input[name="groupnum"]').val($postcontent['groupnum']);
				if($chartoption==1){
					$('#chartgenericoption').show();
					$('#chartgenericoptionval option[value='+$genericoption+']').attr('selected', 'selected')
					if($genericoption==2){
						if($charttype !=3){$('#chartgenericoption').find('.groupnum').show();}
						$('#chartgenericoption').find('.pointtext').show();
					}else if($genericoption==3){
						if($charttype !=3){$('#chartgenericoption').find('.groupnum').show();}
						$('#chartgenericoption').find('.correctanstext').show();
					}else{
						$('#chartgenericoption').find('.groupnum').hide();
					}
				}else{
					$("#chartuseroption").show();
					$('#chartuserrow').show();
					$("#chartuser").val($chartuser);
					$('#chartuseroptionval option[value='+$useroption+']').attr('selected','selected');
					$('#chartuseroptionsecval option[value='+$useroptionsec+']').attr('selected','selected');
				}
				var $info= $("#chartform");
				$info.dialog({                   
					'dialogClass'   : "wp-dialog", 
					'modal'         : true,
					'autoOpen'      : false, 
					position: { 
						my: "center", 
						at: "center" 
					},
					'width' : 600,
					'height' : 510,
					
					buttons: {
						"Save": function() {
							var data2=$('#quizchart').serialize();
							$.ajax({
								type: 'POST',
								dataType:'json',
								url:ajaxurl,
								data:data2,
								success: function(data){
								if(data.msg=='success'){
								$disabledstatus=data.disabledstatus;
								$('#disabledchart').val($disabledstatus);
								if($disabledstatus==1){
									$('#add_chart').prop('disabled',true);
									$('#chartpromessage').show();
								}	
								$postid=data.postid;
								$htmlcontent=data.content;
								$par.html($htmlcontent);
								}else{alert('Sorry,Chart data could not be saved');}
								}
							});
						$( this ).dialog( "close" );
						
						$('#chartform').html($originfucker);
					},	
					Cancel: function() {
					
						$( this ).dialog( "close" );
						$('#chartform').html($originfucker);
					}
				}
			});
		
			$info.dialog('open');	
			
			}
	});

});
});