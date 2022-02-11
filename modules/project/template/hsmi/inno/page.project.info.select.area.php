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

function project_info_select_area($self, $projectInfo) {
	if (!$projectInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $projectInfo->tpid;

	$ret = '';

	$ret .= '<header class="header -box"><nav class="nav -back"><a class="sg-action" data-rel="back" href="javascript:void(0)"><i class="icon -material">arrow_back</i></a></nav><h3>เลือกพื้นที่</h3></header>';

	$ret .= '<script type="text/javascript">
	$(document).ready(function() {
		var changwatValue = $("#edit-area").val()
		//console.log(changwatValue)
		changwatValue.split(",").map(function(changwatIs){
			//console.log(changwatIs)
			$(".ui-menu.-changwatList input[value=\'"+changwatIs+"\']").prop("checked", true)	
		})

		$(".ui-menu.-changwatList input[type=\'checkbox\']").change(function() {
			changwatValue = ""
			$(".ui-menu.-changwatList input[type=\'checkbox\']").each(function(index){
				if ($(this).is(":checked")) {
					changwatValue += $(this).val()+","
				}
			})
			//console.log(changwatValue)
			$("#edit-area").val(changwatValue)
			$("#project-set-search").submit()
		})

	})
	</script>';


	$stmt = 'SELECT
		t.`changwat`, cop.`provname` `changwatName`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %co_province% cop ON cop.`provid` = t.`changwat`
		WHERE t.`parent` = :parent
		GROUP BY `changwat`
		HAVING `changwatName` IS NOT NULL
		ORDER BY CONVERT(`changwatName` USING tis620) ASC
		';

	$dbs = mydb::select($stmt, ':parent', $tpid);
	//$ret .= print_o($dbs);

	foreach ($innoDbs->items as $key => $value) {
		if ($value->catparent) $optionsInno[$value->catid] = $value->name;
	}

	$ui = new Ui(NULL, 'ui-menu -changwatList');
	$ui->addClass('-changwatList');
	$ui->addId('changwatList');

	foreach ($dbs->items as $rs) {
		$cardStr = '<abbr class="checkbox -block"><label><input type="checkbox" value="'.$rs->changwat.'" /><span>'.$rs->changwatName.'</span></label></abbr>';
		$ui->add($cardStr);
	}

	$ret .= $ui->build();

	$ret .= '<nav class="nav -page -sg-text-right"><a class="sg-action btn -primary" data-rel="close"><i class="icon -material">done_all</i><span>เรียบร้อย</span></a></nav>';

	return $ret;
}
?>