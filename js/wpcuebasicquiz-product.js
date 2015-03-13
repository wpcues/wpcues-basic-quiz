jQuery(document).ready(function($){
function itemtablealternate(){
$('#itemtable >tbody > tr:odd').css("background-color","#eee");
$('#itemtable > tbody > tr:even').css("background-color","#fff");
$('.itemtable > tbody > tr:even').css("background-color","#fff");
$('.itemtable > tbody > tr:odd').css("background-color","#eee");
}
itemtablealternate();
function rename_itemtablerows(){
	var $i=1;
	$('#itemtable').find('.itemnum').each(function(){
		$(this).text($i);
		$i++;
	});
}
$('#postdivrich').tabs();
$(document).on('click','.submitdelete',function(e){
	e.preventDefault();
	$.ajax({
            type: 'POST',
            dataType: 'json',
			url:ajaxurl,
            data: { 
                'action': 'wpcuequiztrashproduct_action',
				'postid':$('#postid').val()
				},
			success: function(data){
			msg=data.msg;
			if(data.msg=='success'){
				document.location.href=data.redirecturl;
			}else{
				alert('could not be trashed');
			}

		}
	});
});
$(document).on('click','.handlediv',function(){
	$(this).siblings('.inside').toggle();
});
$(document).on('click','#add_quizitem_button ,#add_quizcatitem_button , .pageitemlink',function(e){
	e.preventDefault();
	if($(this).attr('id') == 'add_quizitem_button'){
		if($('#producteditor').is(':visible')){alert('Item Editor already open');return false;}
		$itemtype=1;$page=1;
	}else if($(this).attr('id') == 'add_quizcatitem_button'){
		if($('#producteditor').is(':visible')){alert('Item Editor already open');return false;}
		$itemtype=2;$page=1;
	}else{
		$page=parseInt($(this).data('value'),10);
		$itemtype=$('#itemtype').val();	
	}
	if($('input[name="selectallstatus"]').length){
		$selectall=$('input[name="selectallstatus"]').val()
	}else{$selectall=0;}
	$productid=$('#postid').val();
	$.ajax({
            type: 'POST',
            dataType: 'json',
			url:ajaxurl,
            data: { 
                'action': 'wpcuefetchitemlist_pageaction',
				'itemtype':$itemtype,
				'page':$page,
				'selectall':$selectall,
				'productid':$productid,
			},
			success: function(data){
				if(data.msg == 'success'){
					$('#producteditor').html(data.content);
					$('#producteditor').show();
				}else{
					alert('Your request to fetch item failed. Please try again');
				}
			}
	});
});
$(document).on('click','.saveimportitem',function(){
$importitems=[];$i=0;$importitemtitles=[];
$('input[name="importitems[]"]:checked').each(function(){$importitems[$i]=$(this).val();$i++;});
$i=0;
$('input[name="importitemtitles[]"]').each(function(){$importitemtitles[$i]=$(this).val();$i++;});
if(typeof $importitems =='undefined'){return false;}
$productid=$('#postid').val();
$itemtype=$('#itemtype').val();
var newindex=$('#itemtable').find('.addeditemrow').length+1;
if($('#original_post_status').val() !='publish'){
	$.ajax({
            type: 'POST',
            dataType: 'json',
			url:ajaxurl,
            data: { 
                'action': 'wpcuesaveitemlist_pageaction',
				'items':$importitems,
				'productid':$productid,
				'itemtype':$itemtype
			},
			success: function(data){
				if(data.msg == 'success'){
					var content='';
					jQuery.each($importitems,function(index,value){
						content+='<tr id="rowitem-'+value+'" class="addeditemrow"><td class="itemnum">'+(newindex+index)+'<td>'+$importitemtitles[index]+'</td><td>';
						content+='<input type="hidden" name="addeditem[]" value="'+value+'">';
						content+='<input type="hidden" name="addeditemtype[]" value="'+$itemtype+'">';
						if($itemtype==1){
							content+='Quiz';
						}else{
							content+='Quiz Category';
						}
						content+='</td><td class="removeitem"></td></tr>';
					});
					$('#itemtable').append(content);
					$('#itemtable').show();
					itemtablealternate();
					$('#producteditor').empty();
					$('#producteditor').hide();
				}else{
					alert('Your request to fetch item failed. Please try again');
				}
			}
	});
}else{
	var content='';
	jQuery.each($importitems,function(index,value){
		content+='<tr id="rowitem-'+value+'" class="addeditemrow"><td class="itemnum">'+(newindex+index)+'<td>'+$importitemtitles[index]+'</td><td>';
		content+='<input type="hidden" name="addeditem[]" value="'+value+'">';
		content+='<input type="hidden" name="addeditemtype[]" value="'+$itemtype+'">';
		if($itemtype==1){
			content+='Quiz';
		}else{
			content+='Quiz Category';
		}
		content+='</td><td class="removeitem"></td></tr>';
	});
	$('#itemtable').append(content);
	$('#itemtable').show();
	itemtablealternate();
	$('#producteditor').empty();
	$('#producteditor').hide();
}
});
$(document).on('click','.cancelimportitem',function(){
	$('#producteditor').empty();
	$('#producteditor').hide();
});
$(document).on('click','.removeitem',function(){
	$itemid=$(this).siblings().find('input[name="addeditem[]"]').val();
	$productid=$('#postid').val();
	alert($('#itemtable').find('tr').length);
	if($('#original_post_status').val() !='publish'){
	$.ajax({
            type: 'POST',
            dataType: 'json',
			url:ajaxurl,
            data: { 
                'action': 'wpcueremove_item',
				'itemid':$itemid,
				'productid':$productid
			},
			success: function(data){
				if(data.msg == 'success'){
					$('#rowitem-'+$itemid).remove();
					rename_itemtablerows();
					itemtablealternate();
					if(!($('#itemtable').find('tr').length)){
						$('#itemtable').hide();
					}
				}else{
					alert('Your request to fetch item failed. Please try again');
				}
			}
	});
	}else{
		$('#rowitem-'+$itemid).remove();
		rename_itemtablerows();
		itemtablealternate();
	}
});
$(document).on('change','#productcurrency',function(){
$productcurrency=$(this).val();
if($productcurrency == -1){
$('#customCurrency').show();
}else{
$('#customCurrency').hide();
}
});
});