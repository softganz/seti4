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

function project_info_select_inno($self, $projectInfo) {
	if (!$projectInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $projectInfo->tpid;

	$ret = '';

	$ret .= '<header class="header -box"><nav class="nav -back"><a class="sg-action" data-rel="back" href="javascript:void(0)"><i class="icon -material">arrow_back</i></a></nav><h3>เลือกนวัตกรรม</h3></header>';

	$ret .= '<script type="text/javascript">
	$(document).ready(function() {
		var innoValue = $("#edit-inno").val()
		//console.log(innoValue)
		innoValue.split(",").map(function(innoId){
			console.log(innoId)
			$(".ui-menu.-innoList input[value=\'"+innoId+"\']").prop("checked", true)	
		})

		$(".ui-menu.-innoList input[type=\'checkbox\']").change(function() {
			innoValue = ""
			$(".ui-menu.-innoList input[type=\'checkbox\']").each(function(index){
				if ($(this).is(":checked")) {
					innoValue += $(this).val()+","
				}
			})
			//console.log(innoValue)
			$("#edit-inno").val(innoValue)
			$("#project-set-search").submit()
		})

	})
	</script>';


	$stmt = 'SELECT
		  tg.`catid`, tg.`catparent`, tg.`name`
		FROM %tag% tg
		WHERE tg.`taggroup` = "project:inno" AND tg.`process` = 1';

	$dbs = mydb::select($stmt);

	$ui = new Ui(NULL, 'ui-menu -innoList');
	$ui->addClass('-innoList');
	$ui->addId('innoList');

	foreach ($dbs->items as $rs) {
		if (empty($rs->catparent)) {
			$ui->add('<b style="display: block; padding: 4px 8px; font-size: 1.1em;">'.$rs->name.'</b>');
			foreach ($dbs->items as $innoItem) {
				if ($innoItem->catparent != $rs->catid) continue;
				$ui->add('<abbr class="checkbox -block"><label><input type="checkbox" value="'.$innoItem->catid.'" /><span>'.$innoItem->name.'</span></label></abbr>');
				/*
					view::inlineedit(
						array(
							'group'=>'bigdata:project.info:'.$innoItem->catid,
							'fld' => 'inno',
							'fldref' => $innoItem->catid,
							'tr' => $innoItem->bigid,
							'value' => $innoItem->flddata,
							'removeempty'=>'yes',
						),
						$innoItem->catid.':'.$innoItem->name,
						$isEdit,
						'checkbox')
					.' </label></abbr>');
					*/
			}
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

	return $ret;
}
?>