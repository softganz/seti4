<?php
/**
* Lock & unlock revieced
* Created 2019-07-25
* Modify  2019-07-30
*
* @param Object $self
* @param Object $projectInfo
* @param Int $dopid
* @return String
*/

$debug = true;

function project_join_rcv_lock($self, $projectInfo, $dopid = NULL) {
	if (!$projectInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $projectInfo->tpid;
	$calId = $projectInfo->calid;

	$ret = '';

	$right = R::Model('project.join.right', $projectInfo);



	$dopaidInfo = R::Model('org.dopaid.doc.get', $dopid);
	if (!$dopaidInfo->dopid)
		return message('error', 'ERROR : ไม่มีข้อมูลใบสำคัญรับเงินตามเงื่อนไขที่ระบุ');

	$headerBack = '<nav class="nav -back"><a class="sg-action" href="javascript:void(0)" data-rel="back" data-width="640" data-height="90%"><i class="icon -material">arrow_back</i></a></nav>';

	if ($dopaidInfo->islock) {
		$ret .= '<header class="header -box">'.$headerBack.'<h3>ปลดล็อคใบสำคัญรับเงิน</h3></header>';
		if ($right->unlockRcv) {
			$ret .= '<div style="padding: 64px 0; text-align: center;"><p>กรุณายืนยันการปลดล็อคใบสำคัญรับเงิน?</p><br /><br /><a class="sg-action btn -primary" href="'.url('project/join/'.$tpid.'/'.$calId.'/rcv.locked/'.$dopid).'" data-rel="none" data-callback="projectJoinLockRcvCallback" data-done="close"><i class="icon -material">lock_open</i><span>ปลดล็อคใบสำคัญรับเงิน</span></a></div>';
		} else {
			$ret .= 'ไม่ได้รับสิทธิ์ในการปลดล็อค';
		}
	} else if (!$dopaidInfo->islock && $right->lockRcv) {
		$ret .= '<header class="header -box">'.$headerBack.'<h3>ล็อคใบสำคัญรับเงิน</h3></header>';
			$ret .= '<div style="padding: 64px 0; text-align: center;"><p>กรุณายืนยันการล็อคใบสำคัญรับเงิน?</p><br /><br /><a class="sg-action btn -primary" href="'.url('project/join/'.$tpid.'/'.$calId.'/rcv.locked/'.$dopid).'" data-rel="none" data-callback="projectJoinLockRcvCallback" data-done="close"><i class="icon -material">lock</i><span>ล็อคใบสำคัญรับเงิน</span></a></div>';
	}

	//$ret .= print_o($dopaidInfo,'$dopaidInfo');

	$ret .= '<script type="text/javascript">
		function projectJoinLockRcvCallback($this, ui) {
		//console.log("Mark Lock")
		var $parent = $("#psnid-'.$dopaidInfo->psnid.'")
		$parent.toggleClass("-locked")
		$parent.find("a.-join-edit").parent(".ui-item").toggleClass("-hidden")
		if ($parent.find("a.-rcv-unlock").length) {
			$parent.find("a.-rcv-unlock").removeClass("-rcv-unlock").addClass("-rcv-locked").find(".icon").text("locked")
		} else {
			$parent.find("a.-rcv-locked").removeClass("-rcv-locked").addClass("-rcv-unlock").find(".icon").text("lock_open")
		}
	}
	</script>';
	return $ret;
}
?>