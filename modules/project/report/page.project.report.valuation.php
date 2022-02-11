<?php
/**
* Project :: Report of Follow Valuation
* Created 2019-03-13
* Modify  2021-04-10
*
* @param Object $self
* @return String
*
* @usage project/report/valuation
*/

$debug = true;

function project_report_valuation($self) {
	// Data Model
	$getArea = post('area');
	$getYear = post('year');
	$getChangwat = post('prov');
	$getValuation = post('value');

	$isDebug = user_access('access debugging program') && post('debug');

	$followCfg = cfg('project');

	//debugMsg($followCfg, '$followCfg');
	$parts = [
		'inno'=>'เกิดความรู้ด้านการสร้างเสริมสุขภาพและนวัตกรรมเชิงระบบสุขภาพ',
			'inno.1' => '	ความรู้ใหม่ / องค์ความรู้ใหม่',
			'inno.2' => '	สิ่งประดิษฐ์ / ผลผลิตใหม่',
			'inno.3' => '	กระบวนการใหม่',
			'inno.4' => '	วิธีการทำงาน / การจัดการใหม่',
			'inno.5' => '	การเกิดกลุ่ม / โครงสร้างในชุมชนใหม่',
			'inno.6' => '	แหล่งเรียนรู้ใหม่',
			'inno.99' => '	อื่นๆ',
		'behavior' => 'เกิดการปรับเปลี่ยนพฤติกรรมที่เอื้อต่อสุขภาพ',
			'behavior.1' => '	การดูแลสุขอนามัยส่วนบุคคล',
			'behavior.2' => '	การบริโภค',
			'behavior.3' => '	การออกกำลังกาย',
			'behavior.4' => '	การลด ละ เลิก อบายมุข เช่น การพนัน เหล้า บุหรี่',
			'behavior.5' => '	การลดพฤติกรรมเสี่ยง เช่น พฤติกรรมเสี่ยงทางเพศ การขับรถโดยประมาท',
			'behavior.6' => '	การจัดการอารมณ์ / ความเครียด',
			'behavior.7' => '	การดำรงชีวิต / วิถีชีวิต เช่น การใช้ภูมิปัญญาท้องถิ่น / สมุนไพรในการดูแลสุขภาพตนเอง',
			'behavior.8' => '	พฤติกรรมการจัดการตนเอง ครอบครัว ชุมชน',
			'behavior.9' => '	อื่นๆ',
		'environment' => 'การสร้างสภาพแวดล้อมที่เอื้อต่อสุขภาพ (กายภาพ สังคม และเศรษฐกิจ)',
			'environment.1' => '	กายภาพ  เช่น  มีการจัดการขยะ  ป่า  น้ำ  การใช้สารเคมีเกษตร  และการสร้างสิ่งแวดล้อมในครัวเรือนที่ถูกสุขลักษณะ',
			'environment.2' => '	สังคม เช่น มีความปลอดภัยในชีวิตและทรัพย์สิน ลดการเกิดอุบัติเหตุ ครอบครัวอบอุ่น การจัดสภาพแวดล้อมที่เอื้อต่อเด็ก เยาวชน และกลุ่มวัยต่าง ๆ มีพื้นที่สาธารณะ/พื้นที่ทางสังคม เพื่อเอื้อต่อการส่งเสริมสุขภาพของคนในชุมชน มีการใช้ศาสนา/วัฒนธรรมเป็นฐานการพัฒนา',
			'environment.3' => '	เศรษฐกิจสร้างสรรค์สังคม /สร้างอาชีพ / เพิ่มรายได้',
			'environment.4' => '	มีการบริการสุขภาพทางเลือก และมีช่องทางการเข้าถึงระบบบริการสุขภาพ',
			'environment.5' => '	อื่นๆ',
		'publicpolicy' => 'การพัฒนานโยบายสาธารณะที่เอื้อต่อสุขภาวะ',
			'publicpolicy.1' => '	มีกฎ / กติกา ของกลุ่ม ชุมชน',
			'publicpolicy.2' => '	มีมาตรการทางสังคมของกลุ่ม ชุมชน',
			'publicpolicy.3' => '	มีธรรมนูญของชุมชน',
			'publicpolicy.4' => '	อื่นๆ เช่น ออกเป็นข้อบัญญัติท้องถิ่น ฯลฯ',
		'social' => 'กระบวนการเคลื่อนไหวทางสังคมและกระบวนการในพื้นที่',
			'social.1' => '	เกิดการเชื่อมโยงประสานงานระหว่างกลุ่ม / เครือข่าย (ใน และหรือนอกชุมชน)',
			'social.2' => '	การเรียนรู้การแก้ปัญหาชุมชน (การประเมินปัญหา การวางแผน การปฏิบัติการ และการประเมิน)',
			'social.3' => '	การใช้ประโยชน์จากทุนในชุมชน เช่น การระดมทุน การใช้ทรัพยากรบุคคลในชุมชน',
			'social.4' => '	มีการขับเคลื่อนการดำเนินงานของกลุ่มและชุมชนที่เกิดจากโครงการอย่างต่อเนื่อง',
			'social.5' => '	เกิดกระบวนการจัดการความรู้ในชุมชน',
			'social.6' => '	เกิดทักษะในการจัดการโครงการ เช่น การใช้ข้อมูลในการตัดสินใจ การทำแผนปฏิบัติการ',
			'social.7' => '	อื่นๆ',
		'spirite' => 'มิติสุขภาวะปัญญา / สุขภาวะทางจิตวิญญาณ',
			'spirite.1' => '	ความรู้สึกภาคภูมิใจในตัวเอง / กลุ่ม / ชุมชน',
			'spirite.2' => '	การเห็นประโยชน์ส่วนรวมและส่วนตนอย่างสมดุล',
			'spirite.3' => '	การใช้ชีวิตอย่างเรียบง่าย และพอเพียง',
			'spirite.4' => '	ชุมชนมีความเอื้ออาทร',
			'spirite.5' => '	มีการตัดสินใจโดยใช้ฐานปัญญา',
			'spirite.6' => '	อื่นๆ',
	];

	$areaOptions = [];
	if (mydb::table_exists('%project_area%')) {
		foreach (mydb::select(
				'SELECT a.`areaid`, CONCAT("เขต ", a.`areaid`, " ",a.`areaname`) `areaname`, GROUP_CONCAT(DISTINCT f.`changwat` ORDER BY `changwat`) `changwat`
				FROM %project_area% a
					LEFT JOIN %project_fund% f ON f.`areaid` = a.`areaid`
				GROUP BY `areaid`
				ORDER BY CAST(a.`areaid` AS UNSIGNED)'
			)->items as $rs) {
		 	$areaOptions[$rs->areaid] = [
		 		'label' => $rs->areaname,
		 		'attr' => ['data-changwat' => $rs->changwat],
		 	];
		 }
	}



	// View Model
	R::View('project.toolbar', $self, 'การประเมินคุณค่า (แผนภูมิ)', 'report');

	$reportBar = new Report(url('project/api/valuations/summary',array('debug' => $isDebug ? 'yes' : NULL)), 'project-report-valuation');

	$reportBar->addId('project-report-valuation');
	$reportBar->data('callback', 'projectReportValuationCallback');

	$reportBar->config('showArrowLeft', true);
	$reportBar->config('showArrowRight', true);

	$reportBar->addConfig('filterPretext',
		($getPlanningId ? '<input type="hidden" name="for_plan[]" value="'.$getPlanningId.'" />' : '')
		. ($followCfg->follow->yearStart ? '<input type="hidden" name="yearStart" value="'.$followCfg->follow->yearStart.'" />' : '')
		. ($getArea ? '<input type="hidden" name="for_area[0]" value="'.$getArea.'" />' : '')
		. ($getYear ? '<input type="hidden" name="for_year[0]" value="'.$getYear.'" />' : '')
		. ($getValuation ? '<input type="hidden" name="for_value[0]" value="'.$getValuation.'" />' : '')
	);

	$reportBar->Filter(
		'valuation',
		[
			'text' => 'คุณค่า',
			'filter' => 'for_value',
			'select' => $parts,
			'type' => 'radio'
		]
	);

	if ($projectSupportType = R::Model('category.get','project:supporttype', 'catid')) {
		$reportBar->Filter(
			'supporttype',
			[
				'text' => 'ประเภท',
				'type' => 'checkbox',
				'filter' => 'for_type',
				'select' => $projectSupportType,
			]
		);
	}
	/*
	$reportBar->Filter(
		'year',
		[
			'text' => 'ปี พ.ศ.',
			'filter' => 'for_year',
			'type' => 'radio',
			'select' => mydb::select(
					'SELECT DISTINCT p.`pryear`, CONCAT("พ.ศ.", p.`pryear` + 543) `bcyear`
					FROM %project_tr% v
						LEFT JOIN %project% p USING(`tpid`)
					WHERE v.`formid` IN ("valuation","ประเมิน")
					ORDER BY p.`pryear` ASC;
					-- {key: "pryear", value: "bcyear"}'
				)->items,
		]
	);
	*/
	/*
	if (!$getPlanningId) {
		$reportBar->Filter(
			'plan',
			[
				'text' => 'แผนงาน',
				'type' => 'radio',
				'filter' => 'for_plan',
				'select' => R::Model('category.get','project:planning','catid'),
			]
		);
	}
	*/
	if ($areaOptions) {
		$reportBar->Filter(
			'area',
			[
				'text' => 'เขต',
				'type' => 'radio',
				'filter' => 'for_area',
				'select' => $areaOptions,
			]
		);
	}
	$reportBar->Filter(
		'changwat',
		[
			'text' => 'จังหวัด',
			'type' => 'radio',
			'filter' => 'for_changwat',
			'select' => mydb::select(
					'SELECT DISTINCT LEFT(`areacode`, 2) `changwat`, `provname`
					FROM %project_tr% v
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(t.`areacode`, 2)
					WHERE v.`formid` IN ("valuation","ประเมิน") AND cop.`provname`!=""
					ORDER BY CONVERT(`provname` USING tis620) ASC;
					-- {key: "changwat", value: "provname"}'
				)->items
		]
	);
	$reportBar->Filter(
		'ampur',
		[
			'text' => 'อำเภอ',
			'type' => 'radio',
			'filter' => 'for_ampur',
			'select' => []
		]
	);
	if (mydb::table_exists('%project_fund%')) {
		$reportBar->Filter(
			'fund',
			[
				'text' => 'กองทุน',
				'type' => 'radio',
				'filter' => 'for_fund',
				'select' => []
			]
		);
	}

	$reportBar->Output('chart', '<div class="loader -rotate" style="width: 64px; height: 64px; margin: 0 auto; display: block;"></div>');
	$reportBar->Output('summary', '');

	$reportBar->optionsUi = new Ui(
		[
			'class' => 'graph_type',
			'id' => 'graph_type',
			'children' => [
				['text' => '<a id="graph_total" class="btn -active" data-tooltip="แสดงกราฟจำนวนโครงการ"><i class="icon -material">stacked_line_chart</i><span>1</span></a>'],
				['text' => '<a id="graph_percent" class="btn" data-tooltip="แสดงกราฟเปอร์เซ็นต์โครงการ"><i class="icon -material">stacked_line_chart</i><span>%</span></a>'],
				['text' => '&nbsp;'],
			],
		]
	);

	$ret .= $reportBar->build();

	head('<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>');

	$ret .= '<style type="text/css">
	.report-output-chart {height: 400px;}
	.graph_type .ui-item {position: relative;}
	.graph_type .btn {border-radius: 50%; padding: 6px;}
	.graph_type .btn.-active {box-shadow: 0 0 0 1px green inset;}
	.graph_type .btn>span {position: absolute; width: 16px; height: 16px; background-color: grey; display: block; border-radius: 50%; top: 2px; line-height: 16px; padding: 0; right: 2px; color: white; font-size: 10px; border: 1px #fff solid; opacity: 0.9;}
	.graph_type .btn.-active>span {background-color: green;}
	.graph_type .btn>.icon {color: grey;}
	.graph_type .btn.-active>.icon {color: green;}
	</style>';

	head('<script type="text/javascript">
	google.charts.load("current", {packages:["corechart"]});
	$(document).ready(function() {
		var $sgDrawReport = $(".sg-drawreport button.btn.-primary.-submit").sgDrawReport().doAction()
	});

	var graphType = "graph_total"
	function projectReportValuationCallback($this, data) {
		//console.log(data)
		projectReportValuationChart($this, data)
		projectReportValuationSummary($this, data)

		function projectReportValuationChart($this, data) {
			let $graphOutput = $("#report-output-chart")
			let summary = data.summary
			let chartType = "Line"
			let chart = new google.visualization.LineChart(document.getElementById("report-output-chart"))

			let axis = ["ปี พ.ศ.","โครงการเกิดคุณค่า"]
			if ("totalArea" in summary[0]) axis.push("เขต")
			if ("totalChangwat" in summary[0]) axis.push("จังหวัด")
			if ("totalAmpur" in summary[0]) axis.push("อำเภอ")
			if ("totalFund" in summary[0]) axis.push("กองทุน")
			if ("totalByValuation" in summary[0]) axis.push("คุณค่า")

			let chartData = [axis]

			let $debugOutput = $("#report-output-debug").empty()

			if (data.debug && data.process) {
				data.process.forEach(function(item) {
					$debugOutput.append($("<div></div>").html(item))
				})
			}

			//console.log("AXIS", axis)
			summary.map((item) => {
				//console.log(item)
				if (graphType == "graph_total") {
					let row = [
						(item.year+543).toString(),
						item.totalValuation
					]
					if ("totalArea" in item) row.push(item.totalArea ? item.totalArea : 0)
					if ("totalChangwat" in item) row.push(item.totalChangwat ? item.totalChangwat : 0)
					if ("totalAmpur" in item) row.push(item.totalAmpur ? item.totalAmpur : 0)
					if ("totalFund" in item) row.push(item.totalFund ? item.totalFund : 0)
					if ("totalByValuation" in item) row.push(item.totalByValuation ? item.totalByValuation : 0)
					chartData.push(row)
				} else {
					let row = [
						(item.year+543).toString(),
						_percent(item.totalValuation, item.totalProject),
						]
					if ("totalArea" in item) row.push(_percent(item.totalArea, item.totalAreaFollow))
					if ("totalChangwat" in item) row.push(_percent(item.totalChangwat, item.totalChangwatFollow))
					if ("totalAmpur" in item) row.push(_percent(item.totalAmpur, item.totalAmpurFollow))
					if ("totalFund" in item) row.push(_percent(item.totalFund, item.totalFundFollow))
					if ("totalByValuation" in item) row.push(_percent(item.totalByValuation, item.totalByValuationFollow))
					chartData.push(row)
				}
			})
			let chartTable = google.visualization.arrayToDataTable(chartData)
			$graphOutput.empty()

			let vAxis = {
				logScale: false,
				title: "โครงการ",
				viewWindow: {
					min: 0,
				},
				gridlines: {
					//count: maxProject/20,
				}
			}
			if (graphType == "graph_percent") {
				vAxis.viewWindow.max = 100
				vAxis.title = "%"
				graphTitle = "จำนวน % ของโครงการที่เกิดคุณค่า"
			} else {
				graphTitle = "จำนวนโครงการที่เกิดคุณค่า"
			}

			let hAxis = {
				title: "พ.ศ.",
			}

			let options = {
				title: graphTitle,
				//colors: ["#058DC7", "#f60", "#C605BA"],
				tooltip: {isHtml: true},
				legend: { position: "bottom", alignment: "start" },
				pointSize: 4,
				hAxis: hAxis,
				vAxes: {
					0: vAxis,
					/*
					1: {
						logScale: false,
						viewWindow: {
							min: 0,
							max: 100
						},
						gridlines: {
							count: 100/20,
						},
					}
					*/
					},
				series: {
					0:{targetAxisIndex:0, pointShape: "circle"},
					1:{targetAxisIndex:0, pointShape: "circle"},
					//2:{targetAxisIndex:1}
				}
			};
			chart.draw(chartTable, options);
		}

		function _percent(value, total) {
			if (value == 0 || total == 0) return 0;
			return parseFloat((value*100/total).toFixed(2))
		}

		function projectReportValuationSummary($this, data) {
			//console.log($this)
			let summary = data.summary
			let $summaryOutput = $("#report-output-summary")
			$summaryOutput.empty()
			if (summary === undefined || $summaryOutput.length === 0) return

			var table = $("<table></table>").addClass("item -center")
			var thead = $("<thead></thead>")
			let row1 = $("<tr></tr>");
			let row2 = $("<tr></tr>");

			row1.append("<th rowspan=\"2\">ปี พ.ศ.</th>")
			row1.append("<th colspan=\"3\">โครงการ</th>")
			row2.append("<th>ทั้งหมด</th>").append("<th>คุณค่า</th>").append("<th>%</th>")

			if ("totalArea" in data.summaryFields) {
				row1.append("<th colspan=\"3\">"+data.summaryFields.totalArea+"</th>")
				row2.append("<th>ทั้งหมด</th>").append("<th>คุณค่า</th><th>%</th>")
			}
			if ("totalChangwat" in data.summaryFields) {
				row1.append("<th colspan=\"3\">"+data.summaryFields.totalChangwat+"</th>")
				row2.append("<th>ทั้งหมด</th>").append("<th>คุณค่า</th><th>%</th>")
			}
			if ("totalAmpur" in data.summaryFields) {
				row1.append("<th colspan=\"3\">"+data.summaryFields.totalAmpur+"</th>")
				row2.append("<th>ทั้งหมด</th>").append("<th>คุณค่า</th><th>%</th>")
			}
			if ("totalFund" in data.summaryFields) {
				row1.append("<th colspan=\"3\">"+data.summaryFields.totalFund+"</th>")
				row2.append("<th>ทั้งหมด</th>").append("<th>คุณค่า</th><th>%</th>")
			}
			if ("totalByValuation" in data.summaryFields) {
				row1.append("<th colspan=\"3\">"+data.summaryFields.totalByValuation+"</th>")
				row2.append("<th>ทั้งหมด</th>").append("<th>คุณค่า</th><th>%</th>")
			}

			thead.append(row1)
			thead.append(row2)


			table.append(thead)
			//

			//console.log(data.summaryFields)
			//console.log(data.parameter)

			summary.map((item) => {
				let baseParameter = {}
				if (data.parameter.value) baseParameter.value = data.parameter.value
				if (data.parameter.type) baseParameter.type = data.parameter.type
				let paraTotalValuation = JSON.parse(JSON.stringify(baseParameter))

				let paraArea = JSON.parse(JSON.stringify(baseParameter))
				if (data.parameter.area) paraArea.area = data.parameter.area

				let paraChangwat = JSON.parse(JSON.stringify(baseParameter))
				if (data.parameter.changwat) paraChangwat.changwat = data.parameter.changwat

				let paraAmpur = JSON.parse(JSON.stringify(baseParameter))
				if (data.parameter.ampur) paraAmpur.ampur = data.parameter.ampur

				let paraFund = JSON.parse(JSON.stringify(baseParameter))
				if (data.parameter.fund) paraFund.fund = data.parameter.fund

				//let paraChangwat = {value: data.parameter.value, changwat: data.parameter.changwat}
				//let paraAmpur = {value: data.parameter.value, ampur: data.parameter.ampur}
				//let paraFund = {value: data.parameter.value, fund: data.parameter.fund}
				//let paraByValuation = {value: data.parameter.value, area: data.parameter.area}

				let row = $("<tr></tr>").addClass("row")
				row.append($("<td>พ.ศ."+(item.year + 543)+"</td>"))
				.append(_addRow(item.totalProject))
				.append(_addRow(item.totalValuation, "project/api/valuations", item.year, paraTotalValuation))
				.append(
					_addRow(
						item.totalProject > 0 ? (item.totalValuation*100/item.totalProject).toFixed(2) : "-"
					)
				)

				if ("totalArea" in item) {
					row
					.append(_addRow(item.totalAreaFollow))
					.append(_addRow(item.totalArea, "project/api/valuations", item.year, paraArea))
					.append(
						_addRow(
							item.totalAreaFollow > 0 ? (item.totalArea*100/item.totalAreaFollow).toFixed(2) : "-"
						)
					)
				}
				if ("totalChangwat" in item) {
					row
					.append(_addRow(item.totalChangwatFollow))
					.append(_addRow(item.totalChangwat, "project/api/valuations", item.year, paraChangwat))
					.append(
						_addRow(
							item.totalChangwatFollow > 0 ? (item.totalChangwat*100/item.totalChangwatFollow).toFixed(2) : "-"
						)
					)
				}
				if ("totalAmpur" in item) {
					row
					.append(_addRow(item.totalAmpurFollow))
					.append(_addRow(item.totalAmpur, "project/api/valuations", item.year, paraAmpur))
					.append(
						_addRow(
							item.totalAmpurFollow > 0 ? (item.totalAmpur*100/item.totalAmpurFollow).toFixed(2) : "-"
						)
					)
				}
				if ("totalFund" in item) {
					row
					.append(_addRow(item.totalFundFollow))
					.append(_addRow(item.totalFund, "project/api/valuations", item.year, paraFund))
					.append(
						_addRow(
							item.totalFundFollow > 0 ? (item.totalFund*100/item.totalFundFollow).toFixed(2) : "-"
						)
					)
				}
				if ("totalByValuation" in item) {
					row.append(_addRow(item.totalByValuation, "project/api/valuations", item.year, paraByValuation))
				}
				table.append(row)
			})

			/*
			Object.keys(data.summaryFields).forEach( function(key) {
				//console.log(key,data.summaryFields[key])
				thead.append($("<th></th>").text(data.summaryFields[key]))
			});
			*/

			$summaryOutput.append(table)

			//$this.showSummary(data)
		}

		function _addRow(value, urlLink = null, year, para = {}) {
			let td = $("<td></td>")
			let link = ""
			//console.log(value)
			if (value <= 0) return td.text(0)
			if (urlLink) {
				let percent = ""
				let urlPara = "?year="+year
				//console.log(para)
				Object.keys(para).map((key, index) => {
					urlPara += "&"+key+"="+para[key]
				})
				//console.log(urlPara)

				link = $("<a></a>")
					.attr("href", url+urlLink+urlPara)
					.addClass("sg-action -load-project")
					.data("rel", "box")
					.data("height", "100%")
					.data("widtg", "480")
					.data("callback", "showProject")
					.text(value)
			} else {
				link = $("<span></span>").text(value)
			}
			td.append(link)
			return td
		}

		function showProject($this, data) {
			var table = $("<table></table>").addClass("item")
			var thead = $("<thead></thead>")

			data.items.map((item) => {
				//console.log(item)
				let row = $("<tr></tr>").addClass("row")
				let link = $("<a />").attr("href", url+"project/"+item.projectId+"/valuation").text(item.title).attr("target", "_blank")
				let td = $("<td></td>").append(link).append("<br /><em>"+item.orgName+"</em>")
				row.append(td)
				table.append(row)
			})
			$(".box-page").html(table)
		}

		$(document).on("click", "#graph_type a", function() {
			graphType = $(this).attr("id")
			$(this).closest("ul").find(".btn").removeClass("-active")
			$(this).addClass("-active")
			projectReportValuationChart(null, data)
		})

	}

	/*
	$(document).on("click", ".-load-project", function() {
		console.log("CLICK"+$(this).attr("href"))
	})
	*/


	$(document).on("change", ".-checkbox-for_area", function() {
		let $this = $(this)
		let changwatId = $this.data("changwat").split(",")
		let $changwatCheckBox = $(".-checkbox-for_changwat")

		// Clear changwat checked and hide changwat not in area
		$(".-group-for_ampur .-checkbox").empty()
		$(".-group-for_fund .-checkbox").empty()
		$changwatCheckBox
		.prop("checked", false)
		.each(function(i) {
			let $checkBox = $(this)
			if (changwatId.indexOf($checkBox.attr("value")) != -1) {
				$checkBox.closest("abbr").show()
			} else {
				$checkBox.closest("abbr").hide()
			}
		});
		$(this).sgDrawReport().makeFilterBtn()
		$(".-group-for_changwat").find(".-check-count").addClass("-hidden")
		$(".-group-for_ampur").find(".-check-count").addClass("-hidden")
		$(".-group-for_fund").find(".-check-count").addClass("-hidden")
	});

	$(document).on("change", ".-checkbox-for_changwat", function() {
		console.log("CHANGWAT CHANGE")
		let $this = $(this)
		let $ampurContainer = $(".-group-for_ampur")
		let $ampur = $(".-group-for_ampur .-checkbox")
		$ampur.empty()
		$(".-group-for_fund .-checkbox").empty()
		$.get(url+"api/ampur",{q:$(this).val()}, function(data) {
			//if (data.length) $ampur.show(); else $ampur.hide()
			for (var i = 0; i < data.length; i++) {
				let ampurData = data[i]
				let ampurCode = $this.attr("value") + ampurData.ampur
				//console.log(ampurData)
				$ampur.append(
					"<abbr><label><input id=\"for_ampur_"+ampurCode+"\" class=\"-checkbox-for_ampur -filter-checkbox\" type=\"radio\" name=\"for_ampur[]\" value=\""+ampurCode+"\" />"
					+ "<span>"+ampurData.label+"</span>"
					+ "</label></abbr>"
				);
			};
		},"json")
		$(this).sgDrawReport().makeFilterBtn()
		$(".-group-for_ampur").find(".-check-count").addClass("-hidden")
		$(".-group-for_fund").find(".-check-count").addClass("-hidden")
	})

	$(document).on("change", ".-checkbox-for_ampur", function() {
		//console.log("AMPUR CHANGE")
		let $this = $(this)
		let $fundContainer = $(".-group-for_fund")
		let $fund = $(".-group-for_fund .-checkbox")
		$fund.empty()
		$.get(url+"project/api/fund",{ampur:$(this).val()}, function(data) {
			//if (data.length) $ampur.show(); else $ampur.hide()
			for (var i = 0; i < data.length; i++) {
				let fundData = data[i]
				let fundCode = fundData.value
				//console.log(fundData)
				$fund.append(
					"<abbr><label><input id=\"for_fund_"+fundCode+"\" class=\"-checkbox-for_fund -filter-checkbox\" type=\"radio\" name=\"for_fund[]\" value=\""+fundCode+"\" />"
					+ "<span>"+fundData.label+"</span>"
					+ "</label></abbr>"
				);
			};
		},"json")
		$(this).sgDrawReport().makeFilterBtn()
		$(".-group-for_fund").find(".-check-count").addClass("-hidden")
	})

	</script>');

	return $ret;
}
?>