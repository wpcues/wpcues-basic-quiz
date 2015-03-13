<?php $instanceid=$_GET['instance'];
global $wpdb;
$table_name=$wpdb->prefix.'wpcuequiz_quizstat';
$result=$wpdb->get_row($wpdb->prepare("select quizid,grade,userid,endtime from $table_name where instanceid=%d",$instanceid),ARRAY_A);
if(!(empty($result['grade']))){
$gradegroupid=(int)get_post_meta($result['quizid'],'quizgrade');
$gradegroup=get_post($gradegroupid);$grademeta=unserialize($gradegroup->post_content);
$certi=(int)$grademeta[$result['grade']]['certi'];
$certificate=get_post($certi);
$certificatemet =get_post_meta($certi,'wpcuecertificate_det');$certificatemeta=maybe_unserialize($certificatemet);
$certificatemetavalues=$certificatemeta[0]; 
if(empty($certificatemetavalues['approval'])){
$certificatecontent=$certificate->post_content;
if($certificatemetavalues['certype']==1){
include(sprintf("%s/../mpdf/mpdf.php", dirname(__FILE__)));
$mpdf=new mPDF('utf-8',array(100,50));
$mpdf->WriteHTML($certificatecontent);
$mpdf->Output();
exit;
}else{
echo '<!DOCTYPE html><html><head></head><body>'.$certificatecontent.'</body></html>';
}
}else{
_e('This certificate need admin approval to be issued. You will be notified when approved.','wpcues-quiz-pro');
}
}else{
	$post=get_post($result['quizid']);
	global $current_user;
	get_currentuserinfo();
 if (is_user_logged_in() && $current_user->ID == $post->post_author)  {
		_e('Please add suitable Grade group to your quiz first','wpcues-quiz-pro');
	}
}
?>