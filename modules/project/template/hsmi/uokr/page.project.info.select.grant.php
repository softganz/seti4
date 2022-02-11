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

function project_info_select_grant($self, $projectInfo) {
	if (!$projectInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $projectInfo->tpid;

	$ret = '';

	$ret .= '<header class="header -box"><nav class="nav -back"><a class="sg-action" data-rel="back" href="javascript:void(0)"><i class="icon -material">arrow_back</i></a></nav><h3>เลือกแหล่งทุน</h3></header>';

	$ret .= '<script type="text/javascript">
	$(document).ready(function() {
		var $formField = $("#edit-grant")
		//console.log($formField)
		$formField.val().split(",").map(function(fieldId){
			console.log(fieldId)
			$(".ui-menu.-select-list input[value=\'"+fieldId+"\']").prop("checked", true)
		})

		$(".ui-menu.-select-list input[type=\'checkbox\']").change(function() {
			innoValue = ""
			$(".ui-menu.-select-list input[type=\'checkbox\']").each(function(index){
				if ($(this).is(":checked")) {
					innoValue += $(this).val()+","
				}
			})
			//console.log(innoValue)
			$formField.val(innoValue)
			$("#project-set-search").submit()
		})

	})
	</script>';


	$stmt = 'SELECT
		  tg.`catid`, tg.`catparent`, tg.`name`, tp.`name` `parentName`, tp.`catid` `parentId`
		FROM %tag% tg
			LEFT JOIN %tag% tp ON tp.`taggroup` = "project:uokr:policy" AND tp.`catid` = tg.`catparent`
		WHERE tg.`taggroup` = "project:uokr:grant" AND tg.`process` = 1;
		-- {group: "parentId"}
		';

	$dbs = mydb::select($stmt);

	$ui = new Ui(NULL, 'ui-menu -select-list');
	$ui->addId('selectList');

	foreach ($dbs->items as $groupRs) {
		$ui->add('<b style="display: block; padding: 4px 8px; font-size: 1.1em;">'.$groupRs[0]->name.'</b>');
		foreach ($groupRs as $item) {
			$ui->add('<abbr class="checkbox -block"><label><input type="checkbox" value="'.$item->catid.'" /><span>'.$item->name.'</span></label></abbr>');
		}
	}

	/*
	foreach ($dbs->items as $rs) {
		$cardStr = '<abbr class="checkbox -block"><label><input type="checkbox" value="'.$rs->catid.'" /><span>'.$rs->name.'</span></label></abbr>';
		$ui->add($cardStr);
	}
	*/

	$ret .= $ui->build();

	$ret .= '<nav class="nav -page -sg-text-right"><a class="sg-action btn -primary" data-rel="close"><i class="icon -material">done_all</i><span>เรียบร้อย</span></a></nav>';

	//$ret .= print_o($dbs,'$dbs');

	return $ret;
}
?>