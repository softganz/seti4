<?php
function map_area($self) {
	$mapGroup=SG\getFirst($_REQUEST['gr'],$_REQUEST['mapgroup']);
	$ret['html'].='<nav class="nav iconset -sg-text-right"><a href="javascript:void(0)" data-action="box-close" title="ปิดหน้าต่าง"><i class="icon -close"></i></a></nav>';
	$ret['html'].='<h3>เลือกชั้นแผนที่</h3>';

	$ret['html'].='<form id="user-add-zone" method="get" action="'.url('map/layer',array('gr'=>$mapGroup)).'"><input type="hidden" name="areacode" id="areacode" value="" /><label for="areaname">เลือกพื้นที่</label><input type="text" name="areaname" id="areaname" size="30" value="" class="form-text" size="20" placeholder="ระบุตำบล หรือ อำเภอ หรือ จังหวัด" /></form>';

	$dbs=mydb::select('SELECT DISTINCT `dowhat` FROM %map_networks% WHERE `mapgroup`=:mapgroup AND `dowhat`!="" ORDER BY `dowhat` ASC',':mapgroup',$mapGroup);
	$tags=array();
	foreach (explode(',',$dbs->lists->text) as $value) {
		$value=trim($value);
		if (!array_key_exists($tags, $value)) $tags[$value]='<a href="'.url('map',array('gr'=>$mapGroup,'layer'=>$value)).'">'.$value.'</a>';
	}
	ksort($tags);
	$ret['html'].='<ul><li>'.implode('</li><li>', $tags).'</li></ul>';

	$ret['html'].='
	<script type="text/javascript">
	$(document).ready(function() {
		$("#areaname")
		.autocomplete({
			source: function(request, response) {
				$.get(url+"api/tambon?q="+encodeURIComponent(request.term), function(data){
					response($.map(data, function(item){
					return {
						label: item.label,
						value: item.value
					}
					}))
				}, "json");
			},
			minLength: 2,
			dataType: "json",
			cache: false,
			select: function(event, ui) {
				this.value = ui.item.label;
	//			$("#areaname").value(ui.item.label);
				// Do something with id
				$("#areacode").val(ui.item.value);
				$(this).closest("form").submit();
				return false;
			}
		});
	});
	</script>';
	return $ret['html'];
}
?>