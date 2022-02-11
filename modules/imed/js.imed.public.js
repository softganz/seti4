var toolbarIndex=0

$(document).on('click', ".report-form .toolbar>ul>li>a", function() {
	var $this=$(this)
	$(".toolbar>ul>li").removeClass("active")
	$this.parent().addClass("active")
	$("#reporttype").val($this.attr("href").slice(1))
	$this.closest('form').submit()
	return false
});

function imedReportDraw($this, data) {
	var $tableElement = $($this.data('reportTable'))
	var $chartElement = $($this.data('reportChart'))
	var $detailElement = $($this.data('reportDetail'))
	var chartType = $this.find('#graphtype').val()
	var isDebug = $this.find('input[name="debug"]:checked').length > 0
	var isShowDetail = $this.find('input[name="detail"]:checked').length > 0

	//console.log(data)

	$detailElement.empty()
	$tableElement.empty()

	if (isDebug) {
		data.process.forEach(function(item) {
			$('#report-output-detail').append($('<div></div>').html(item))
		})
	}

	if (data.total == 0) {
		$chartElement.html('ไม่มีข้อมูล')
		return
	}

	var table = $('<table></table>').addClass('item')
	var thead = $('<thead></thead>')
		.append($('<th></th>').text(data.fields[0]))
		.append($('<th></th>').text(data.fields[1]))
		.append($('<th></th>').text(data.fields[2]))
	table.append(thead)

	$tableElement.empty()

	var graphData = []
	graphData = [['รายการ','จำนวน']]

	data.items.forEach(function(item, index) {
		var row = $('<tr></tr>').addClass('row')
		row.append($('<td></td>').text(item.label))
		.append($('<td></td>').addClass('col -center').text(item.value))
		.append($('<td></td>').addClass('col -center').text(item.percent+'%'))
		table.append(row)

		graphData.push([item.label, item.value])
	})

	var tfoot = $('<tfoot></tfoot')
		.append(
			$('<tr></tr>')
			.append('<td>รวมทั้งสิ้น</td>')
			.append($('<td></td>').addClass('col -center').text(data.total))
			.append($('<td></td>').addClass('col -center').text('100%'))
		)

	table.append(tfoot)

	$tableElement.append(table)
	//console.log(graphData)


	var dataForGraph = google.visualization.arrayToDataTable(graphData)

	var options = {
		title: data.title,
		hAxis: {title: "H Axis", titleTextStyle: {color: "black"}},
		vAxis: {title: "Y Axis", minValue: 0},
		isStacked: false
	}

	var chart

	if (chartType == 'Bar') {
		chart = new google.visualization.BarChart(document.getElementById("report-output-chart"));
	} else if (chartType == 'Col') {
		chart = new google.visualization.ColumnChart(document.getElementById("report-output-chart"));
	} else if (chartType == 'Line') {
		chart = new google.visualization.LineChart(document.getElementById("report-output-chart"));
	} else {
		chart = new google.visualization.PieChart(document.getElementById("report-output-chart"));
	}

	chart.draw(dataForGraph, options);

	if (isShowDetail) {
		var exportBtn = $('<a/>')
		exportBtn
		.addClass('btn')
		.html('<i class="icon -material">cloud_download</i><span>EXPORT</span>')
		.attr('href', 'javascript:void(0)')
		.attr('onClick', 'export2excel("trans")')
		$detailElement.append($('<nav></nav>').addClass('nav -page -sg-text-right').append(exportBtn))

		var table = $('<table></table>').addClass('item').attr('id','imed-detail-list')
		var thead = $('<thead></thead>')
			.append(
				$('<tr></tr>')
				.append($('<th></th>').text('ลำดับ'))
				.append($('<th></th>').text('ชื่อ-สกุล'))
				.append($('<th></th>').text('ที่อยู่'))
				.append($('<th></th>').text('อายุ(ปี)'))
				.append($('<th></th>').text(data.fields[0]))
				.append($('<th></th>').text('วันที่เพิ่มข้อมูล'))
			)
		table.append(thead)
		var tbody = $('<tbody></tbody>')


		data.name.forEach(function(item, index) {
			var row = $('<tr></tr>').addClass('row')
			var personLink = $('<a/>')
			personLink
			.attr('href', item.href ? item.href : url+'imed/patient/view/'+item.psnid)
			.text(item.fullname)
			.addClass('sg-action')
			.attr('target', '_blank')
			.data('rel', 'box')
			.data('width', 640)
			.data('webview', item.fullname)

			row
			.append($('<td></td>').addClass('col -no').text(index+1))
			.append($('<td></td>').html(personLink))
			.append($('<td></td>').html(item.address))
			.append($('<td></td>').addClass('col -center').text(item.age))
			.append($('<td></td>').addClass('col').text(item.label))
			.append($('<td></td>').addClass('col').text(item.created))
			tbody.append(row)
		})
		table.append(tbody)

		$detailElement.append(table)
	}
}

$(document).on('click','a.right', function() {
	toolbarIndex++
	var containerWidth=$('.report-form .toolbar').width()
	var itemWidth=$('.report-form .toolbar>ul>li').width()
	var itemInToolbar=Math.floor(containerWidth/itemWidth)
	var w=toolbarIndex*itemWidth*itemInToolbar+(itemInToolbar*toolbarIndex+1)-1
	$(".report-form .toolbar>ul>li").animate({"left":-w+"px"}, "fast")
});

$(document).on('click','a.left',function() {
	toolbarIndex--
	if (toolbarIndex<0) toolbarIndex=0
	var containerWidth=$('.report-form .toolbar').width()
	var itemWidth=$('.report-form .toolbar>ul>li').width()
	var itemInToolbar=Math.floor(containerWidth/itemWidth)
	var w=toolbarIndex*itemWidth*itemInToolbar+(itemInToolbar*toolbarIndex+1)-1
	$(".report-form .toolbar>ul>li").animate({"left":-w+"px"}, "fast")
});

// Graph Chart Type change
$(document).on('click', '.report-form input[type="submit"]', function() {
	var $this=$(this)
	if ($this.val()!="ดูรายงาน") {
		$("#graphtype").val($this.val())
		$this.closest('ul').find('input').removeClass("active")
		$this.addClass("active")
	}
})

$(document).on('change','.report-form #area',function() {
	var $this=$(this)
	var provIdList = $(this).find(':selected').data('prov')

	$("#prov").val("")
	$("#ampur").val("").hide()
	$("#tambon").val("").hide()
	$("#village").val("").hide()
	$('#prov')[0].options.length = 1
	$('#ampur')[0].options.length = 1
	$('#tambon')[0].options.length = 1
	$('#village')[0].options.length = 1

	//console.log(provIdList)
	$.map(allProvince ,function(option) {
		//console.log(option)
		//console.log(provIdList.indexOf(option.id))
		if (option.id != ""
			&& ($this.val()=="" || provIdList.indexOf(option.id)>=0)) {
			$('#prov').append(
				$("<option></option>")
				.text(option.name)
				.val(option.id)
			);
		}
		return true;
	});
	$this.closest('form').submit()
});

// Province change
$(document).on('change','.report-form #prov',function() {
	var $this=$(this)
	if ($this.val()=='') $("#ampur").val("").hide(); else $("#ampur").val("").show()
	$("#tambon").val("").hide()
	$("#village").val("").hide()
	$('#ampur')[0].options.length = 1;
	$('#tambon')[0].options.length = 1;
	$('#village')[0].options.length = 1;
	$.get(url+'api/ampur',{q:$('#prov').val()}, function(data) {
		for (var i = 0; i < data.length; i++) {
			$('#ampur').append(
				$("<option></option>")
				.text(data[i].label)
				.val(data[i].ampur)
			);
		};
	},'json')
	$this.closest('form').submit()
});

// Ampur change
$(document).on('change','.report-form #ampur', function() {
	var $this=$(this)
	if ($this.val()=='') $("#tambon").val("").hide(); else $("#tambon").val("").show()
	$("#village").val("").hide()
	$('#tambon')[0].options.length = 1;
	$('#village')[0].options.length = 1;
	$.get(url+'api/tambon',{q:$('#prov').val()+$('#ampur').val()}, function(data) {
		for (var i = 0; i < data.length; i++) {
			$('#tambon').append(
				$("<option></option>")
				.text(data[i].label)
				.val(data[i].tambon)
			);
		};
	},'json')
	$this.closest('form').submit()
});

// Tambon change
$(document).on('change','.report-form #tambon', function() {
	var $this=$(this)
	if ($this.val()=='') $("#village").val("").hide(); else $("#village").val("").show()
	$('#village')[0].options.length = 1;
	$.get(url+'api/village',{q:$('#prov').val()+$('#ampur').val()+$('#tambon').val()}, function(data) {
		for (var i = 0; i < data.length; i++) {
			$('#village').append(
				$("<option></option>")
				.text(data[i].label)
				.val(data[i].village)
			);
		};
	},'json')
	$this.closest('form').submit()
});

// Village cgange
$(document).on('change','.report-form #village', function() {
	var $this=$(this)
	$this.closest('form').submit()
});