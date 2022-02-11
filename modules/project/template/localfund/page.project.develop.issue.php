<?php
function project_develop_issue($self, $tpid, $action = NULL) {
	$isEdit = $action == 'edit';

	$stmt='SELECT
					tg.`catid`,tg.`name`,tr.`trid`,tr.`refid`
					FROM %tag% tg
						LEFT JOIN %project_tr% tr ON tr.`tpid`=:tpid AND tr.`formid`="develop" AND tr.`part`="supportplan" AND tr.`refid`=tg.`catid`
					WHERE `taggroup`="project:planning"';

	$issueDbs = mydb::select($stmt, ':tpid', $tpid);


	$optionsIssue = array();

	foreach ($issueDbs->items as $rs) {
		if ($isEdit) {
			$optionsIssue[] = '<abbr class="checkbox -block"><label>'.view::inlineedit(array('group'=>'tr:develop:supportplan:'.$rs->catid,'fld'=>'refid','tr'=>$rs->trid,'value'=>$rs->refid,'removeempty'=>'yes', 'callback' => 'projectDevelopIssueChange', 'callback-url' => url('project/develop/'.$tpid.'/view/edit')),$rs->catid.':'.$rs->name,$isEdit,'checkbox').' </label></abbr>';
		} else {
			if ($rs->trid) $optionsIssue[] = $rs->name;
		}
	}

	$ret .= $isEdit ? implode('', $optionsIssue) : implode(' , ', $optionsIssue);

	//view::inlineedit(array('group'=>'tr:develop:indicator','fld'=>'detail1','tr'=>$value->indicatorId, 'class'=>'-fill'),$value->indicatorName,$isEdit,'text')

	//<abbr class="checkbox -block"><label><input type="checkbox" data-type="checkbox" class="{$datainput}" name="goal10year-type1" data-fld="goal10year-type1" value="1" data-removeempty="yes" /> 1. ลดอัตราการสูบบุหรี่ของคนไทยใน พ.ศ. 2557 ลงร้อยละ 10 จาก พ.ศ. 2552</label></abbr>

	$ret .= '<script type="text/javascript">
	function projectDevelopIssueChange($this, ui) {
		//project-develop-problem
		var loadUrl = $this.data("callbackUrl")
		$.get(loadUrl, function(html) {
			//alert(html)
			$("#main").html(html)
		})
		console.log($this.data("callbackUrl"))
	}
	</script>';
	return $ret;
}
?>