
<?php
/**
* Disabled report by area
* Created 2019-12-01
* Modify  2019-12-01
*
* @param Object $self
* @param Array $_GET
* @return String
*/

$debug = true;

function org_agro_report_area($self) {
	$title='รายงานเกษตรกรลงทะเบียน';

	$getChangwat = post('p');
	$getAmpur = post('a');
	$getTambon = post('t');
	$getReportType = SG\getFirst(post('r'),'amt');

	if ($getChangwat == '*') $getChangwat = '';


	cfg('db.disabled.title',$title);

	$isAdmin = user_access('administer imeds');

	$orderList = array(
		'na' => 'ชื่อ:name',
		'rd' => 'วันที่จดทะเบียน:c.created',
		'cd' => 'วันที่ป้อน:p.created',
		'tb' => 'ตำบล:p.tambon',
		'vi' => 'หมู่บ้าน:p.village+0',
		'age' => 'อายุ:p.birth',
		'label' => 'ป้ายรายงาน:label',
	);

	$provdbs = mydb::select('SELECT DISTINCT `provid`, `provname` FROM %agro_reg% a LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(a.`areacode`,2) HAVING `provname` IS NOT NULL ORDER BY CONVERT(`provname` USING tis620) ASC');

	$ret .= '<form class="report-form sg-form" id="report-form" data-rel="none" method="get" action="'.url('org/api/agro/area').'" data-result="json" data-callback="imedReportDraw" data-report-chart="#report-output-chart" data-report-table="#report-output-table" data-report-detail="#report-output-detail">'
		. '<input type="hidden" name="r" id="reporttype" value="'.$getReportType.'" />'
		. '<input type="hidden" name="g" id="graphtype" value="'.$getGraphType.'" />';

	$ret .= '<h3>'.$title.'</h3>'._NL;

	$ret .= '<div class="form-item">'._NL;
	$ret .= '<label for="prov">จังหวัด : </label>'._NL.'<select name="p" id="prov" class="form-select">'._NL.'<option value="">--ทุกจังหวัด--</option>'._NL;
	foreach ($provdbs->items as $rs) $ret.='<option value="'.$rs->provid.'"'.($rs->provid==$getChangwat?' selected="selected"':'').'>'.$rs->provname.'</option>'._NL;
	$ret .= '</select>'._NL;

	$ret .= '<label for="ampur"> อำเภอ : </label>'._NL.'<select name="a" id="ampur" class="form-select'.($getChangwat ? '' : ' -hidden').'">'._NL.'<option value="">--ทุกอำเภอ--</option>'._NL;

	if ($getChangwat) {
		$stmt = 'SELECT DISTINCT `distid`, `distname` FROM %co_district% cod WHERE SUBSTR(`distid`,1,2)=:prov ORDER BY CONVERT(`distname` USING tis620) ASC';
		foreach (mydb::select($stmt,':prov',$getChangwat)->items as $rs) $ret.='<option value="'.substr($rs->distid,2,2).'"'.(substr($rs->distid,2,2)==$getAmpur?' selected="selected"':'').'>'.$rs->distname.'</option>'._NL;
	}
	$ret .= '</select>'._NL;

	$ret .= '<label for="tambon"> ตำบล : </label>'._NL.'<select name="t" id="tambon" class="form-select">'._NL.'<option value="">--ทุกตำบล--</option>'._NL.'</select>'._NL;
	$ret.='<label for="village"> หมู่บ้าน : </label>'._NL.'<select name="v" id="village" class="form-select">'._NL.'<option value="">--ทุกหมู่บ้าน--</option>'._NL.'</select>'._NL;

	$ret .= '<button class="btn -primary -main" value="ดูรายงาน" type="submit"><i class="icon -forward -white"></i><span>ดูรายงาน</span></button>'._NL;
	$ret .= '</div>'._NL;



	$selectType = '<select name="for_type"><option value="-1">-ทุกประเภท-</option>';
	foreach (mydb::select('SELECT `producttype` FROM %agro_reg% GROUP BY `producttype` HAVING `producttype` != "" ')->items as $key => $item) {
		$selectType .= '<option value="'.$item->producttype.'">'.$item->producttype.'</option>';
	}
	$selectType.='</select>';


	$selectProduct = '<select name="for_product"><option value="-1">-ทุกผลผลิต-</option>';
	foreach (mydb::select('SELECT `productname` FROM %agro_reg% GROUP BY `productname` HAVING `productname` != "" ')->items as $key => $item) {
		$selectProduct .= '<option value="'.$item->productname.'">'.$item->productname.'</option>';
	}
	$selectProduct .= '</select>';

	$selectStandard = '<select name="for_standard"><option value="-1">-ทุกมาตรฐาน-</option>';
	foreach (mydb::select('SELECT `standard` FROM %agro_reg% GROUP BY `standard` HAVING `standard` != "" ')->items as $key => $item) {
		$selectStandard .= '<option value="'.$item->standard.'">'.$item->standard.'</option>';
	}
	$selectStandard .= '</select>';

	$selectLandSize = '<select name="for_landsize"><option value="-1">-ทุกขนาด-</option>';
	foreach (array('1' => '< 1 ไร่', '9' => '1 - 9 ไร่', '49' => '10 - 49 ไร่', '50' => '>= 50 ไร่') as $key => $item) {
		$selectLandSize .= '<option value="'.$key.'">'.$item.'</option>';
	}
	$selectLandSize .= '</select>';

	$reportTypeArray = array(
		'amt' => array('text' => 'พื้นที่'),
		'type' => array('text' => 'ประเภท', 'select' => $selectType),
		'product' => array('text' => 'ผลผลิต', 'select' => $selectProduct),
		'standard' => array('text' => 'มาตรฐาน', 'select' => $selectStandard),
		'landsize' => array('text' => 'ขนาดแปลง', 'select' => $selectLandSize),
	);

	$ret .= '<a href="javascript:void(0)" class="left"><i class="icon -back"></i></a><div class="toolbar">'._NL.'<ul>';

	foreach ($reportTypeArray as $k=>$v) {
		$ret.='<li'.($k=='amt'?' class="active"':'').'><a href="#'.$k.'">'.$v['text'].'</a>';
		if (isset($v['select'])) $ret.=$v['select'];
		$ret.='</li>'._NL;
	}

	$ret.='</ul></div><a href="javascript:void(0)" class="right"><i class="icon -forward"></i></a>'._NL;

	$ret .= '<div class="optionbar"><ul>';
	$ret .= '<li>'
		. '<input type="submit" name="g" value="Pie" class="btn -graph active" /> '
		. '<input type="submit" name="g" value="Bar" class="btn -graph" /> '
		. '<input type="submit" name="g" value="Col" class="btn -graph" /> '
		. '<input type="submit" name="g" value="Line" class="btn -graph" />'
		. '</li>';

	$ret.='<li><input type="checkbox" name="incna" value="yes" checked="checked" /> รวมไม่ระบุ ';

	if (i()->ok) $ret .= '<li><input type="checkbox" name="detail" value="yes" /> แสดงรายชื่อ ';
	$ret.='<select class="form-select" name="o"><option>--เรียงตาม--</option>';
	foreach ($orderList as $k=>$v) $ret.='<option value="'.$k.'">'.substr($v,0,strpos($v,':')).'</option>';
	$ret.='</select></li>';
	if (user_access('access debugging program')) $ret.='<li><input type="checkbox" name="debug" value="yes" /> Debug</li>';

	$ret.='</ul></div>';
	$ret.='</form>';



	$ret.='<div id="report-output">';
	$ret.='<div id="report-output-chart"></div>';
	$ret.='<div id="report-output-table"></div>';
	$ret.='<div id="report-output-detail"></div>';

	$ret.='<br clear="all" /><p><strong>หมายเหตุ</strong><ul><li>แหล่งที่มาของข้อมูลจากการขึ้นทะเบียนเกษตรกรของหน่วยงาน</li><li>กรุณาอย่าเพิ่งนำข้อมูลในรายงานนี้ไปอ้างอิงจนกว่ากระบวนการเก็บรวมรวมข้อมูลเสร็จสมบูรณ์</ul></p>';

	$ret.='</div><!--report-output-->';



	head('js.imed.public.js','<script type="text/javascript" src="imed/js.imed.public.js"></script>');
	//head('table2excel','<script type="text/javascript" src="/js/jquery.table2excel.js"></script>');
	//head('table2excel','<script type="text/javascript" src="/js/js.export.js"></script>');

	// https://github.com/SheetJS/sheetjs
	// https://www.jqueryscript.net/table/Exporting-Html-Tables-To-CSV-XLS-XLSX-Text-TableExport.html
	head('<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.15.1/xlsx.core.min.js"></script>');
	//head('<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/blob-polyfill/1.0.20150320/Blob.js"></script>');
	head('<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.3/FileSaver.min.js"></script>');
	head('<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/TableExport/4.0.11/js/tableexport.min.js"></script>');

	$ret .= '
	<style type="text/css">
		.toolbar.-main {display: none;}
		table.report-summary {width:100%;}
		#report-output-chart {width:100%;height:400px;float:left; background: transparent;}
		table.report-summary {width:100%;float:right;}
		table.report-summary>tbody>tr>td, table.report-summary>tfoot>tr>td {text-align:center;}
		table.report-summary>tbody>tr>td:first-child, table.report-summary>tfoot>tr>td:first-child {text-align:left;}
	</style>

	<script type="text/javascript">
		$.getScript("https://www.google.com/jsapi", function(data, textStatus, jqxhr) {
			google.load("visualization", "1", {packages:["corechart"], callback: drawChart});

			//google.setOnLoadCallback(drawChart);
			function drawChart() {
				$("#report-form").submit()
			}
		});

	function export2excel() {
		console.log("EXPORT")
		$("#imed-detail-list").tableExport({headers: true, formats: ["xlsx", "csv", "txt"], position: "top", exportButtons: true});

		/*
		TableExport(document.getElementsByTagName("trans"), {
			headers: true,     // (Boolean), display table headers (th or td elements) in the <thead>, (default: true)
			footers: true,     // (Boolean), display table footers (th or td elements) in the <tfoot>, (default: false)
			formats: ["xlsx", "csv", "txt"], // (String[]), filetype(s) for the export
			filename: "id",    // (id, String), filename for the downloaded file
			bootstrap: false,  	// (Boolean), style buttons using bootstrap, (default: true)
			exportButtons: true,	// (Boolean), automatically generate the built-in export buttons for each of the specified formats (default: true)
			position: "top",    	// (top, bottom), position of the caption element relative to table, (default: bottom)
			ignoreRows: null,   	// (Number, Number[]), row indices to exclude from the exported file(s) (default: null)
			ignoreCols: null,   	// (Number, Number[]), column indices to exclude from the exported file(s) (default: null)
			trimWhitespace: true	// (Boolean), remove all leading/trailing newlines, spaces, and tabs from cell text in the exported file(s) (default: false)
		});
		*/

		/*
		var table = $("#trans").html()
		$.jsExport({
			type: "excel",
			paging: {
			   paging: true,
			   alternateRowColor: "#f6eded",
			   HeaderColor: "lightblue",
			   textalign: "left",
			   font: "bold 12px arial"
			},
			data: table,
			filename:"mydoc"
		});
		*/

		/*
		$("#trans").table2excel({
			// exclude CSS class
			exclude:".noExl",
			name:"Worksheet Name",
			filename:"SomeFile",//do not include extension
			fileext:".xls" // file extension
		});
		*/
	}

	</script>';

	return $ret;
}
?>