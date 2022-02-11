<?php
/**
* Module Method
* Created 2019-09-01
* Modify  2019-09-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function project_info_select_issue($self, $projectInfo) {
	if (!$projectInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $projectInfo->tpid;

	$ret = '';

	$ret .= '<header class="header -box"><nav class="nav -back"><a class="sg-action" data-rel="back" href="javascript:void(0)"><i class="icon -material">arrow_back</i></a></nav><h3>เลือกประเด็น</h3></header>';

	$ret .= '<script type="text/javascript">
	$(document).ready(function() {
		var issueValue = $("#edit-issue").val()
		//console.log(issueValue)
		issueValue.split(",").map(function(issueId){
			//console.log(issueId)
			$(".ui-menu.-issueList input[value=\'"+issueId+"\']").prop("checked", true)	
		})

		$(".ui-menu.-issueList input[type=\'checkbox\']").change(function() {
			issueValue = ""
			$(".ui-menu.-issueList input[type=\'checkbox\']").each(function(index){
				if ($(this).is(":checked")) {
					issueValue += $(this).val()+","
				}
			})
			//console.log(issueValue)
			$("#edit-issue").val(issueValue)
			$("#project-set-search").submit()
		})

	})
	</script>';


	$stmt = 'SELECT
		tg.`catid`,tg.`name`
		FROM %tag% tg
		WHERE tg.`taggroup` = "project:issue" AND tg.`process` = 2';

	$dbs = mydb::select($stmt);

	foreach ($innoDbs->items as $key => $value) {
		if ($value->catparent) $optionsInno[$value->catid] = $value->name;
	}

	$ui = new Ui(NULL, 'ui-menu -issueList');
	$ui->addClass('-issueList');
	$ui->addId('issueList');

	foreach ($dbs->items as $rs) {
		$cardStr = '<abbr class="checkbox -block"><label><input type="checkbox" value="'.$rs->catid.'" /><span>'.$rs->name.'</span></label></abbr>';
		$ui->add($cardStr);
	}

	$ret .= $ui->build();

	$ret .= '<nav class="nav -page -sg-text-right"><a class="sg-action btn -primary" data-rel="close"><i class="icon -material">done_all</i><span>เรียบร้อย</span></a></nav>';

	return $ret;
}
?>