<?php
/**
* Project Follow Mapping
* @param Object $self
* @return String
*/
function project_present_situation($self) {
	$goodGroup=post('g');
	$getSector = post('s');
	$getPlan = post('p');
	$getYear = post('yy');

	//R::View('project.toolbar', $self, 'แผนที่ติดตามโครงการ', 'map');
	cfg('web.fullpage', true);

	$ui = new Ui();
	$ui->add('<a class="btn -primary" href="'.url('project/present/situation').'"><i class="icon -material">trending_up</i><span>สถานการณ์</span></a>');
	$ui->add('<a class="btn -link" href="'.url('project/present/planning').'"><i class="icon -material">dashboard</i><span>แผนงาน</span></a>');
	$ui->add('<a class="btn -link" href="'.url('project/present/follow').'"><i class="icon -material">directions_run</i><span>ติดตามประเมินผล</span></a>');
	$ui->add('<a class="btn -link" href="'.url('project/present/valuation').'"><i class="icon -material">how_to_reg</i><span>ประเมินคุณค่า</span></a>');

	$ret .= '<nav class="nav -page">'.$ui->build().'</nav>';


	$sourceList = array(
		'https://dekthaikamsai.com/project/situation/weight?situation=1&hide=yes' => 'ภาวะเตี้ย',
		'https://dekthaikamsai.com/project/situation/weight?situation=2&hide=yes' => 'ภาวะผอม',
		'https://dekthaikamsai.com/project/situation/weight?situation=3&hide=yes' => 'ภาวะอ้วน',
		'https://dekthaikamsai.com/project/situation/weight?situation=4&hide=yes' => 'ภาวะอ้วน+เริ่มอ้วน',

		'https://localfund.happynetwork.org/project/planning/situation?plan=5&hide=yes' => 'อาหารและโภชนาการ',
		'https://localfund.happynetwork.org/project/planning/situation?plan=7&hide=yes' => 'กิจกรรมทางกาย',
		'https://localfund.happynetwork.org/project/planning/situation?plan=1&hide=yes' => 'เหล้า',
		'https://localfund.happynetwork.org/project/planning/situation?plan=2&hide=yes' => 'บุหรี่',
		'https://localfund.happynetwork.org/project/planning/situation?plan=3&hide=yes' => 'สารเสพติด',
		'https://localfund.happynetwork.org/project/planning/situation?plan=4&hide=yes' => 'โรคเรื้อรัง',
		'https://localfund.happynetwork.org/project/planning/situation?plan=8&hide=yes' => 'อุบัติเหตุ',
		'https://localfund.happynetwork.org/project/planning/situation?plan=9&hide=yes' => 'อนามัยแม่และเด็ก',
		'https://localfund.happynetwork.org/project/planning/situation?plan=10&hide=yes' => 'เด็ก เยาวชน ครอบครัว',
		'https://localfund.happynetwork.org/project/planning/situation?plan=11&hide=yes' => 'ผู้สูงอายุ',
		'https://localfund.happynetwork.org/project/planning/situation?plan=12&hide=yes' => 'สิ่งแวดล้อม',
		'https://localfund.happynetwork.org/project/planning/situation?plan=13&hide=yes' => 'เผชิญภัยพิบัติและโรคระบาด',
		'https://localfund.happynetwork.org/project/planning/situation?plan=14&hide=yes' => 'กลุ่มประชาชนทั่วไปที่มีความเสี่ยง',
		'https://localfund.happynetwork.org/project/planning/situation?plan=15&hide=yes' => 'การบริหารจัดการกองทุนสุขภาพตำบล',
		'https://localfund.happynetwork.org/project/planning/situation?plan=16&hide=yes' => 'คนพิการ',
		'https://localfund.happynetwork.org/project/planning/situation?plan=17&hide=yes' => 'แรงงานนอกระบบ',
	);


	$form = new Form(NULL, url('project/present/situation'), 'map-form');
	$form->addConfig('method', 'GET');

	$form->addField(
		'host',
		array(
			'type' => 'select',
			'class' => '-fill',
			'options' => $sourceList,
		)
	);

	/*
	$form->addField(
					'submit',
					array('type' => 'button','value'=>'<i class="icon -search -white"></i><span>GO</span>','container'=>array('class'=>'-sg-text-right'))
				);
	*/

	$menuUi = new Ui(NULL, 'ui-menu situation-menu');
	foreach ($sourceList as $key => $value) {
		$menuUi->add('<a href="'.$key.'"><i class="icon -material">trending_up</i><span>'.$value.'</span></a>');
	}


	$ret .= '<div class="mapping -sg-flex -project-present">';

	$ret .= '<div class="map-nav">'
		//. $form->build()
		. $menuUi->build()
		. '</div><!-- map-nav -->';


	$ret.='<div id="map" class="app-output"><iframe src="https://dekthaikamsai.com/project/situation/weight&hide=yes"></iframe></div>'._NL;

	$ret.='</div><!-- mapping -->';


	$ret.='<style type="text/css">
	html, body, .page.-main {height: 100%;}
	.mapping.-project-present {height: calc( 100% - 40px);}
	.package-footer {display: none;}
	.nav.-page {margin: 0; padding: 2px; background-color: #65ccff;}

	.mapping {position:relative;}
	.mapping .map-nav {margin: 0; padding: 0; flex: 0 0 280px; padding:8px; z-index:1; border-radius:2px; background-color:#fff; opacity:0.9;}
	.module-project .app-output {height: 100%; flex: 1; float: none; margin: 0; padding: 0;}

	iframe {margin: 0; width: 100%; height: 100%; margin: 0; padding: 0;}

	.infowindow {width: 240px;}
	.infowindow h3 {font-family: sans-serif; font-weight: bold; font-size: 1em;}
	.btn.-active {}
	.notify-main {top: 40px;}
	.form-item {margin: 0;}
	</style>';



	$ret .= '<script type="text/javascript">
	var host = "https://dekthaikamsai.com/"
	var queryUrl = "project/api/marker/follow"
	var projectSet
	var goodType
	var markerUrl = host+queryUrl
	var infoWindow = null
	var activeInfoWindow = null
	var mapType = "Pin Map"
	var map
	var markerCluster
	var markers
	var pinMarkers = {}

	$(".situation-menu a").click(function() {
		$this = $(this)
		//notify("กำลังโหลด...", 10000)
		console.log($this.attr("href"))
		$("#map>iframe").attr("src",$this.attr("href"))
		return false
	});

	$("#edit-host").change(function() {
		//var res = $("#edit-host").val().split(":",10)
		//console.log(res)
		//host = res[0]+":"+res[1]
		//para.set = res[2]

		notify("กำลังโหลด...", 10000)
		$("#map>iframe").attr("src",$("#edit-host").val())
		return false
	})

	</script>';
	return $ret;
}
?>