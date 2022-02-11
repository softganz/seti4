<?php
/**
* Module Method
* Created 2019-06-01
* Modify  2019-06-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_app_person_search($self, $searchStr = NULL) {
	$searchStr = SG\getFirst($searchStr, post('pn'));
	$ret = '';

	$result = R::Page('imed.api.person', NULL, $searchStr);
	//$ret .= print_o($result);

	if ($result) {
		foreach ($result as $rs) $foundPsnId[] = $rs['value'];

		$stmt = 'SELECT
			`pid` `psnid`
			, COUNT(*) `visits`
			FROM %imed_service%
			WHERE `pid` IN (:psnid)
			GROUP BY `psnid`;
			-- {key: "psnid"}
			';
		$patientList = mydb::select($stmt, ':psnid', 'SET:'.implode(',',$foundPsnId))->items;
		//$ret .= print_o($patientList,'$patientList');

		$cardUi = new Ui('div a','ui-card -patient -sg-flex -co-2');

		foreach ($result as $rs) {
			$rs = (Object) $rs;

			$patientInfo = $patientList[$rs->value];

			if ($rs->value == '...') {
				$cardStr = '... ยังมีอีก ...';
				$cardUi->add($cardStr, array('class'=>'btn ui-item -get-more', 'data-nextpage'=>$rs->nextpage,'style'=>'text-align: center; padding: 16px 0; flex: 1 0 100% !important;'));
			} else {
				$patientName = $rs->prename.' '.$rs->label;
				$cardStr = '<div class="header"><b>'.$patientName.'</b></div>';
				$cardStr .= '<div class="detail">';
				$cardStr .= '<p>'.$rs->desc.'</p>';
				if ($patientInfo->visits) {
					$cardStr .= '<p>เยี่ยมบ้าน <b>'.$patientInfo->visits.'</b> ครั้ง</p>';
				}
				$cardStr .= '</div>';
				$cardUi->add(
					$cardStr,
					array('href'=>url('imed/app/patient/'.$rs->value), 'class'=>'sg-action', 'data-webview' => "true", 'data-webview-title' => $patientName)
				);
			}

			//$cardStr .= print_o($rs,'$rs').print_o($patientInfo,'$patientInfo');

		}

		//if ($dbs->count() % 2) $ui->add('&nbsp;', '{class: "-empty"}');
		$ret .= $cardUi->build();

		//$ret .= print_o($result, '$result');
	}


	return $ret;
}
?>