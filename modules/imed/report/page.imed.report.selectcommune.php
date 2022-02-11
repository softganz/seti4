<?php
function imed_report_selectcommune() {
	$ret = '<h3 class="title -box">เลือกชุมชน</h3>';
	//$ret.=print_o(post(),'post');
	if (post('prov')) mydb::where('`changwat` = :changwat',':changwat',post('prov'));
	if (post('ampur')) mydb::where('`ampur` = :ampur',':ampur',post('ampur'));

	$stmt = 'SELECT DISTINCT `commune`
					FROM %db_person% p
					%WHERE%
					ORDER BY CONVERT(`commune` USING tis620) ASC';

	$dbs = mydb::select($stmt,$where['value']);

	//$ret.=print_o($dbs,'$dbs');
	/*
	$tables->rows[]=array('<input class="select-commune" type="radio" name="commune" value=""> ทุกชุมชน');
	foreach ($dbs->items as $rs) {
		if (empty($rs->commune)) continue;
		$tables->rows[]=array('<input class="select-commune" type="radio" name="commune" value="'.$rs->commune.'"> '.$rs->commune);
	}
	*/

	$ret .= '<nav class="nav -sg-text-right"><a class="sg-action btn -primary" data-rel="close"><i class="icon -save -white"></i><span>เรียบร้อย</span></a></nav>';

	$tables = new Table();
	$tables->rows[] = array('<label><input class="select-commune" type="checkbox" name="selcommune[]" value=""> ทุกชุมชน</label>');
	foreach ($dbs->items as $rs) {
		if (empty($rs->commune)) continue;
		$tables->rows[] = array('<label class="-block"><input class="select-commune" type="checkbox" name="selcommune[]" value="'.$rs->commune.'"> '.$rs->commune.'</label>');
	}

	$ret .= $tables->build();

	$ret.='<nav class="nav -sg-text-right"><a class="sg-action btn -primary" data-rel="close"><i class="icon -save -white"></i><span>เรียบร้อย</span></a></nav>';

	$ret.='<script type="text/javascript">
	var oldCheck=$("input[name=\'commune\']").val()

	if (oldCheck!="") {
		var ciContact = oldCheck.split(",")
		var $inputs = $("input[name^=selcommune]");
		for (var j = 0; j < ciContact.length; j++) {
			$inputs.filter(\'[value="\' + ciContact[j] + \'"]\').attr("checked","checked");
		}
	}

	$(".select-commune").change(function() {
		notify($(this).val())
		//$("input[name=\'commune\']").val(oldCheck+","+$(this).val());
		//$(".commune-name").text($(this).val()==""?"ทุกชุมชน":$(this).val())


    var valuesArray = $(\'input[name^="selcommune"]:checked\').map(function () {  
            return this.value;
            }).get().join(",");
		$("input[name=\'commune\']").val(valuesArray);
		//$(".commune-name").text($(this).val()==""?"ทุกชุมชน":valuesArray)


		//alert($("input[name=\'commune\']").val())
		//$.colorbox.close();
	})
	</script>';
	return $ret;
}
?>