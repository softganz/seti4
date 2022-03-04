<?php
/**
* iMed Widget :: Admit Status
* Created 2021-08-20
* Modify  2021-08-20
*
* @param Array $args
* @return Widget
*
* @usage import('widget:imed.admit.status')
* @usage new ImedAdmitStatusWidget([])
*/

$debug = true;

class ImedAdmitStatusWidget extends Widget {
	var $more;

	function __construct($args = []) {
		parent::__construct($args);
	}

	function build() {
		$admitCount = mydb::select('SELECT COUNT(*) `amt` FROM %db_person% WHERE `admit` = "YES" LIMIT 1')->amt;

		$joins = [];

		mydb::where('m.`qtform` = "SMIV"');
		if ($this->orgId) {
			mydb::where('sp.`orgId` = :orgId', ':orgId', $this->orgId);
			$joins[] = 'LEFT JOIN %imed_socialpatient% sp ON sp.`orgid` = ":orgId" AND sp.`psnid` = m.`psnid`';
		}

		mydb::value('$JOIN$', implode(_NL, $joins), false);
		$patientCount = mydb::select(
			'SELECT
			COUNT(IF(`value` <= 9, 1, NULL)) `green`
			, COUNT(IF(`value` BETWEEN 10 AND 18, 1, NULL)) `yellow`
			, COUNT(IF(`value` >= 19, 1, NULL)) `red`
			FROM (
				SELECT m.`psnid`, m.`value`
				FROM %qtmast% m
					$JOIN$
				%WHERE%
				GROUP BY m.`psnid`
				ORDER BY m.`qtdate` DESC
			) a
			LIMIT 1
			'
		);
		// debugMsg($patientCount, '$patientCount');

		// mydb::where('m.`value` >= 19');
		// mydb::value('$JOIN$', implode(_NL, $joins), false);
		// if (i()->username == 'softganz') debugMsg(mydb::select('SELECT m.`psnid`, m.`value`
		// 		FROM %qtmast% m
		// 			$JOIN$
		// 		%WHERE%
		// 		GROUP BY m.`psnid`
		// 		ORDER BY m.`qtdate` DESC'),'AAA');

		return new ScrollView([
			'child' => new Card([
				'children' => [
					new ListTile([
						'title' => 'สถานะผู้ป่วยจิตเวช',
						'trailing' => new Row([
							'children' => [
								$this->more ? $this->more : NULL,
							],
						]),
					]),
					new Row([
						'class' => 'dashboard -type1 imed-psyc-status',
						'children' => [
							new Container([
								'class' => '-green',
								'children' => [
									'<span>อาการปกติ</span>',
									'<span class="-number">'.$patientCount->green.'</span>',
									// '<span>คน</span>',
								],
							]),
							new Container([
								'class' => '-yellow',
								'children' => [
									'<span>เฝ้าระวัง</span>',
									'<span class="-number">'.$patientCount->yellow.'</span>',
									// '<span>คน</span>',
								],
							]),
							new Container([
								'class' => '-red',
								'children' => [
									'<span>ติดตามใกล้ชิด</span>',
									'<span class="-number">'.$patientCount->red.'</span>',
									// '<span>คน</span>',
								],
							]),
							new Container([
								'class' => '-admit',
								'children' => [
									'<span>ADMIT</span>',
									'<span class="-number">'.$admitCount.'</span>',
									// '<span>คน</span>',
								],
							]),
						], // children
					]), // Row
				], // children
			]), // Card
		]);
	}

}
?>