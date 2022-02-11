<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

/**
 * Delete car
 *
 * @param Integer $id
 * @return String
 */
function icar_delete($self,$id) {
	$carInfo=icar_model::get_by_id($id);

	R::View('icar.toolbar', $self, NULL, NULL, $carInfo);

	//$ret.=print_o($carInfo,'$carInfo');
	//$ret.=empty($carInfo->saledate)?'Empty':'saledate';
	$self->theme->title=$carInfo->carname;

	if ($carInfo->sold) return $ret.message('error','รถได้ขายไปแล้ว ไม่สามารถลบรายการนี้ได้');

	$isAdmin = user_access('administer icars');
	$isDelete = ($isAdmin || in_array($carInfo->iam, array('OWNER','MANAGER','OFFICER')));

	//$ret.=$is_shop?'Yes is shop':'No is not shop';

	if (!$isDelete) return $ret.message('error','access denied');


	mydb::query('UPDATE %topic% SET `uid`=:uid WHERE `tpid`=:tpid LIMIT 1',':tpid',$id,':uid',i()->uid);
	$ret.='<div id="icar-delete"></div>';
	// $ret.='<script type="text/javascript">
	// $(document).ready(function() {
	// 	$.get(url+"paper/'.$id.'/edit/delete",function(data) {
	// 		$("#icar-delete").html(data);
	// 	});
	// });
	// </script>';
	return $ret;
}
?>