<?php
/**
* iMed :: Patient Poor QT List
* Created 2021-06-01
* Modify  2021-06-01
*
* @param Object $patientInfo
* @return Widget
*
* @usage imed/app/{id}/info.poorqts
*/

$debug = true;

class ImedAppInfoPoorQts {
	var $patientInfo;

	function __construct($patientInfo) {
		$this->patientInfo = $patientInfo;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'แบบสอบถามคนยากลำบาก '.$this->patientInfo->realname,
				'removeOnApp' => true,
			]), // AppBar
			'children' => [
				$this->_list(),
			], // children
		]);
	}

	function _list() {
		$psnId = SG\getFirst($this->patientInfo->psnId,post('id'));

		$qtref = post('qt');

		// R::View('imed.toolbar',$self,'แบบสอบถามคนยากลำบาก','none');

		if (!$psnId) return message('error','Invalid Patient Information');

		$isAccess = $this->patientInfo->RIGHT & _IS_ACCESS;
		$isEdit = $this->patientInfo->RIGHT & _IS_EDITABLE;

		mydb::where('qt.`qtform` = 4 AND qt.`psnid` = :psnid',':psnid',$psnId);
		if (!$isAccess) {
			mydb::where('qt.`uid` = :uid', ':uid', i()->uid);
		}

		$stmt = 'SELECT qt.`qtdate`, qt.`psnid`, qt.`qtref`, qt.`qtstatus`
			FROM %qtmast% qt
			%WHERE%
			';

		$dbs = mydb::select($stmt);

		//$ret.=print_o($dbs,'$dbs');

		$statusList=array(_START=>'กำลังป้อน', _DRAFT=>'แก้ไข', _WAITING=>'รอตรวจ', _COMPLETE=>'อนุมัติ', _CANCEL=>'ยกเลิก', _REJECT=>'ไม่ผ่าน');

		$floatingMenu .= '<div class="btn-floating -right-bottom">'
			. '<a class="sg-action btn -floating -circle48" href="'
			. url('imed/poorman/qt/create/'.$psnId,array('ref' => 'imed.app'))
			. '" data-rel="#main" data-done="moveto: 0,0" data-webview="เพิ่มแบบสอบถามคนยากลำบาก" data-title="เพิ่มแบบสอบถามคนยากลำบาก" data-confirm="ต้องการเพิ่มแบบสอบถามคนยากลำบาก กรุณายืนยัน?"><i class="icon -addbig -white"></i></a></div>';

		if ($qtref) {
			$ret .= R::Page('imed.app.poorman.form',$self,$qtref);
		} else if ($dbs->_num_rows) {
			$tables = new Table();
			foreach ($dbs->items as $rs) {
				$tables->rows[] = array(
					sg_date($rs->qtdate,'ว ดด ปปปป'),
					'<a class="sg-action" href="'.url('imed/app/poorman/form/'.$rs->qtref, array('ref' => 'imed.app')).'" data-webview="แบบสอบถามคนยากลำบาก">แบบสอบถาม #'.$rs->qtref.'</a>',
					//'<a class="sg-action" href="'.url('imed/poorman/info/'.$psnId, array('qt' => $rs->qtref)).'" data-rel="#imed-app" data-webview="แบบสอบถามคนยากลำบาก">แบบสอบถาม #'.$rs->qtref.'</a>',
						$statusList[$rs->qtstatus],
				);
			}
			$ret .= $tables->build();
		} else {
			$ret .= '<p class="notify">ไม่มีข้อมูลคนยากลำบาก</p>';
		}


		if (empty($qtref)) $ret .= $floatingMenu;

		$ret .= '<style type="text/css">
		.form-item.-edit-save {display:none;}
		</style>';
		return $ret;
	}
}
?>