<?php
/**
* iMed :: Patient Barthel Index
* Created 2021-06-01
* Modify  2021-06-01
*
* @param Object $patientInfo
* @return Widget
*
* @usage imed/app/{id}/info.barthel
*/

$debug = true;

class ImedAppInfoBarthel {
	var $patientInfo;

	function __construct($patientInfo) {
		$this->patientInfo = $patientInfo;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->patientInfo->realname,
				'removeOnApp' => true,
			]), // AppBar
			'children' => [
				$this->_list(),
			], // children
		]);
	}

	function _list() {
		$psnId = $this->patientInfo->psnId;

		$isAccess=$psnInfo->RIGHT & _IS_ACCESS;

		mydb::where('s.`pid` = :pid', ':pid', $psnId);
		mydb::where('s.`service` IN ("Treatment","Home Visit","Web Distance Treatment","Vitalsign")');

		if (!$isAccess) {
			mydb::where('s.`uid` = :uid', ':uid', i()->uid);
		}

		$stmt = 'SELECT
			  b.*, s.*, u.`username`, u.`name`, CONCAT(p.`name`," ",p.`lname`) patient_name
			, q2.`q2_score`, q2.`q9_score`
			FROM %imed_service% s
				LEFT JOIN %users% u USING (uid)
				LEFT JOIN %db_person% p ON p.`psnid`=s.`pid`
				LEFT JOIN %imed_barthel% b USING(`seq`)
				LEFT JOIN %imed_2q9q% q2 USING(`seq`)
			%WHERE%
			ORDER BY s.`seq` DESC';
		$dbs = mydb::select($stmt);

		//$ret.=print_o($dbs,'$dbs');

		$tables = new Table();
		$tables->addClass('imed-vitalsign-item -center');
		$tables->thead = '<tr><th rowspan="2">วันที่</th><th colspan="2">Barthel ADL</th><th colspan="2">ซึมเศร้า</th><th>น้ำตาล</th><th>ความดัน</th></tr><tr><th>คะแนน</th><th>สภาวะ</th><th>2Q</th><th>9Q</th><th>(mg/dL)</th><th>(มม.ปรอท)</th></tr>';

		foreach ($dbs->items as $rs) {
			$barthel = R::Model('imed.barthel.level', $rs->score);

			$tables->rows[]=array(
				sg_date($rs->timedata,'d/m/ปป'),
				$rs->score,
				'<i class="icon -local -barthel-'.$barthel->level.'"></i>',
				$rs->q2_score === 0 ? 'ไม่มี' : $rs->q2_score,
				$rs->q9_score,
				$rs->fbs,
				$rs->sbp || $rs->dbp ? $rs->sbp.'/'.$rs->dbp : ''
			);
		}
		$ret.=$tables->build();

		//$ret.=print_o($psnInfo,'$psnInfo');

		return $ret;
	}
}
?>