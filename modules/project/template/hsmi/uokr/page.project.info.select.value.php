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

function project_info_select_value($self, $projectInfo) {
	if (!$projectInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $projectInfo->tpid;

	$ret = '';

	$ret .= '<header class="header -box"><nav class="nav -back"><a class="sg-action" data-rel="back" href="javascript:void(0)"><i class="icon -material">arrow_back</i></a></nav><h3>เลือกคุณค่าที่เกิดขึ้น</h3></header>';

	$ret .= '<script type="text/javascript">
	$(document).ready(function() {
		var issueValue = $("#edit-value").val()
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
			$("#edit-value").val(issueValue)
			$("#project-set-search").submit()
		})

	})
	</script>';


	$outputList = array(
			1 => 'เกิดความรู้ หรือ นวัตกรรมชุมชน',
			'เกิดการปรับเปลี่ยนพฤติกรรมที่เอื้อต่อสุขภาพ',
			'การสร้างสภาพแวดล้อมที่เอื้อต่อสุขภาพ (กายภาพ สังคม และเศรษฐกิจ)',
			'การพัฒนานโยบายสาธารณะที่เอื้อต่อสุขภาวะ',
			'เกิดกระบวนการชุมชน',
			'มิติสุขภาวะปัญญา / สุขภาวะทางจิตวิญญาณ',
		);

	$ui = new Ui(NULL, 'ui-menu -issueList');
	$ui->addClass('-issueList');
	$ui->addId('issueList');

	foreach ($outputList as $key => $value) {
		$cardStr = '<abbr class="checkbox -block"><label><input type="checkbox" value="'.$key.'" /><span>'.$value.'</span></label></abbr>';
		$ui->add($cardStr);
	}

	$ret .= $ui->build();

	$ret .= '<nav class="nav -page -sg-text-right"><a class="sg-action btn -primary" data-rel="close"><i class="icon -material">done_all</i><span>เรียบร้อย</span></a></nav>';

	return $ret;
}
?>