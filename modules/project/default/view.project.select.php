<?php
/**
* Module :: Description
* Created 2020-01-01
* Modify  2020-01-01
*
* @param
* @return String
*
* @usage R::View("module.method")
*/

$debug = true;

function view_project_select($options = '{}') {
	$defaults = '{result: "html", debug: false, class: "sg-action", rel: null, retUrl: null, title: "เลือกโครงการ", btnText: "เลือกโครงการ"}';
	$options = SG\json_decode($options, $defaults);

	$retUrl = $options->retUrl;

	$cardUi = new Ui('div', 'ui-card project-select -follow');
	//$cardUi->header('<h3>'.$options->title.'</h3>', NULL, array('preText' => _HEADER_BACK));

	// Get project of current user
	$stmt = 'SELECT p.`tpid`, t.`title`, tu.`uid`, tu.`membership`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %topic_user% tu ON tu.`tpid` = p.`tpid`
		WHERE p.`prtype` = "โครงการ" AND p.`project_status` = "กำลังดำเนินโครงการ" AND tu.`uid` = :uid';

	$topicUser = mydb::select($stmt, ':uid', i()->uid);

	if ($options->result == 'items') {
		return $topicUser->items;
	}

	if ($topicUser->count() == 1) {
		// มีโครงการเดียว
		$cardUi->projectId = $topicUser->items[0]->tpid;
	} else if ($topicUser->count()) {
		// มีหลายโครงการ
		foreach ($topicUser->items as $rs) {
			$url = str_replace('$id', $rs->tpid, $retUrl);
			$cardStr = '<div class="header -box -hidden"><h5><i class="icon -material">label</i> <span>'.$rs->title.'</span></h5></div>'
				. '<nav class="nav -card -sg-text-center""><a class="btn -primary -fill" href="'.$url.'" onClick="return false"><i class="icon -material">done</i><span>'.$options->btnText.'</span></a></nav>'
				;

			$cardConfig = array(
					'class' => $options->class,
					'href' => $url,
				);
			if ($options->{'data-rel'}) $cardConfig['data-rel'] = $options->{'data-rel'};
			if ($options->{'data-done'}) $cardConfig['data-done'] = $options->{'data-done'};
			if ($options->{'data-webview'}) $cardConfig['data-webview'] = $options->{'data-webview'};
			$cardUi->add($cardStr,$cardConfig);

		}
	} else {
		$cardUi->error = 'ท่านไม่มีโครงการที่รับผิดชอบในการเขียนบันทึกกิจกรรม';
	}

	return $cardUi;
}
?>