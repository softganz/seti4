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

function project_info_select_tag($self, $projectInfo) {
	if (!$projectInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $projectInfo->tpid;

	$ret = '';

	$ret .= '<header class="header -box"><nav class="nav -back"><a class="sg-action" data-rel="back" href="javascript:void(0)"><i class="icon -material">arrow_back</i></a></nav><h3>เลือกคำค้น</h3></header>';

	$ret .= '<script type="text/javascript">
	$(document).ready(function() {
		var tagValue = $("#edit-tag").val()
		//console.log(tagValue)
		tagValue.split(",").map(function(tagId){
			//console.log(tagId)
			$(".ui-menu.-tag-list input[value=\'"+tagId+"\']").prop("checked", true)	
		})

		$(".ui-menu.-tag-list input[type=\'checkbox\']").change(function() {
			tagValue = ""
			$(".ui-menu.-tag-list input[type=\'checkbox\']").each(function(index){
				if ($(this).is(":checked")) {
					tagValue += $(this).val()+","
				}
			})
			//console.log(tagValue)
			$("#edit-tag").val(tagValue)
			$("#project-set-search").submit()
		})

	})
	</script>';


	$stmt = 'SELECT
		b.`flddata` `tagName`
		FROM %bigdata% b
			LEFT JOIN %topic% t ON t.`tpid` = b.`keyid`
		WHERE b.`keyname` = "project.info" AND b.`fldname` = "tag" AND t.`parent` = :tpid
		GROUP BY `tagName`
		ORDER BY CONVERT(`tagName` USING tis620) ASC
		';

	$dbs = mydb::select($stmt, ':tpid', $tpid);
	//$ret .= print_o($dbs);

	foreach ($innoDbs->items as $key => $value) {
		if ($value->catparent) $optionsInno[$value->catid] = $value->name;
	}

	$ui = new Ui(NULL, 'ui-menu -tag-list');
	$ui->addId('tag-list');

	foreach ($dbs->items as $rs) {
		$cardStr = '<abbr class="checkbox -block"><label><input type="checkbox" value="'.$rs->tagName.'" /><span>'.$rs->tagName.'</span></label></abbr>';
		$ui->add($cardStr);
	}

	$ret .= $ui->build();

	$ret .= '<nav class="nav -page -sg-text-right"><a class="sg-action btn -primary" data-rel="close"><i class="icon -material">done_all</i><span>เรียบร้อย</span></a></nav>';

	return $ret;
}
?>