<?php
/**
 * Disabled name listing
 *
 * @param Data Set $dbs
 * @param String $caption
 * @param Array $para
 * @return String
 */
function view_imed_report_name_list($dbs,$caption,$para,$showFields,$label=NULL) {
	if (empty($showFields)) $showFields = 'no,fullname,address,label,regdate,created';
	if (is_string($showFields)) $showFields = explode(',',$showFields);

	$tables = new Table();
	$tables->caption = $caption;

	foreach ($showFields as $fld) {
		list($fld,$text)=explode(':', $fld);
		$thead[$fld]=$fld=='label' ? $label : SG\getFirst($text,$fld);
	}
	$tables->thead=$thead;

	foreach ($dbs->items as $rs) {
		$rs->fullname = trim($rs->fullname);
		$fullname = empty($rs->fullname) ? '(ไม่ระบุชื่อ)' : $rs->fullname;
		$row=array();
		foreach ($showFields as $fld) {
			list($fld) = explode(':', $fld);
			if ($fld == 'no') {
				$row[] = ++$no;
			} else if ($fld == 'fullname') {
				$row[] = '<a class="sg-action" href="'.url('imed', ['pid' => $rs->pid]).'" target="_blank" data-webview="'.htmlspecialchars($fullname).'">'.$fullname.'</a>';
			} else if ($fld == 'address') {
				$row[] = SG\implode_address($rs).($rs->commune?'<br /><strong>'.$rs->commune.'</strong>':'');
			} else if ($fld == 'label') {
				if ($label == 'ช่วงอายุ') {
					$row[] =$rs->label?sg_date($rs->label,'d-m-ปปปป'):'';
				} else {
					$row[]=$rs->label;
				}
			} else if ($fld == 'regdate') {
				$row[] = $rs->regdate?sg_date($rs->regdate,'d-m-ปปปป'):'-';
			} else if ($fld == 'created') {
				$row[] = $rs->created?sg_date($rs->created,'d-m-ปปปป'):'-';
			} else {
				$row[] = $rs->{$fld};
			}
		}
		$tables->rows[] = $row;
	}

	$ret .= $tables->build();

	$ret .= '<style type="text/css">
	.col-fullname {white-space:nowrap}
	.col-created {white-space:nowrap}
	</style>';
	return $ret;
}
?>