<?php
/**
* Module Method
*
* @param 
* @return String
*/

$debug = true;

function imed_app_need_view($self, $psnId) {
	$psnInfo = is_object($psnId) ? $psnId : R::Model('imed.patient.get', $psnId, '{}');
	$psnId = $psnInfo->psnId;

	R::View('imed.toolbar',$self,'ความต้องการ','none');

	$ret = '';

	$stmt = 'SELECT
			n.*
		, u.`username`, u.`name`, CONCAT(p.`name`," ",p.`lname`) `patient_name`
		, nt.`name` `needTypeName`
		FROM %imed_need% n
			LEFT JOIN %users% u USING(`uid`)
			LEFT JOIN %db_person% p USING(`psnid`)
			LEFT JOIN %imed_stkcode% nt ON nt.`stkid` = n.`needtype`
		WHERE `psnid` = :psnid
		ORDER BY `needid` DESC';
	$dbs = mydb::select($stmt, ':psnid', $psnId);

	$ui = new Ui('div','ui-card imed-my-note');
	$ui->addId('imed-my-note');

	if ($dbs->_empty) {
		$ret .=message('notify','ไม่มีข้อมูลความต้องการ');
	} else {
		foreach ($dbs->items as $rs) {
			$ui->add(R::View('imed.need.render',$rs, '{page: "app"}'), '{class: "-urgency-'.$rs->urgency.'", id: "noteUnit-'.$rs->seq.'"}');
		}
	}
	$ret .= $ui->build().'<!-- imed-my-note -->';


	//$ret .= print_o($dbs);


	//$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('imed/app/need/'.$psnId.'/new').'" data-rel="#main"><i class="icon -addbig -white"></i></a></div>';

	/*
	if ($isViewOnly) {
		// Do nothing
	} else if ($isViewOnly) {
		$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/develop/view/'.$tpid,array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -save -white"></i></a></div>';
	} else if ($isAccess) {
		$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('imed/app/need/'.$psnId.'/new').'" data-rel="#main"><i class="icon -addbig -white"></i></a></div>';
	}
	*/

	return $ret;
}
?>