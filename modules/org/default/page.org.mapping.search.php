<?php
/**
* Organization Mapping
*
* @param Object $self
* @param Int $orgId
* @return String
*/

$debug = true;

function org_mapping_search($self, $orgId) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('org.get',$orgId, '{initTemplate: true}');
	$orgId = $orgInfo->orgid;

	R::View('org.toolbar',$self, 'ค้นหา', 'mapping', $orgInfo);

	$isEdit = $orgInfo->RIGHT & _IS_OFFICER;

	$ret = '';

	if (!mydb::table_exists('%map_networks%')) return message('notify', 'Mapping Network not exists');


	$form = new Form(NULL, url('org/'.$orgId.'/mapping.search'), NULL, 'sg-form -options');
	$form->addData('rel','replace:#search-result');
	$form->addField('start',array('type'=>'hidden','value'=>1));
	$form->addField('areacode',array('type'=>'hidden','value'=>post('areacode')));

	$orgnameOptions = array(
		1=>'สำนักงานสาธารณสุขจังหวัด',
		'สำนักงานสาธารณสุขอำเภอ',
		'โรงพยาบาลจังหวัด',
		'โรงพยาบาลอำเภอ',
		'โรงพยาบาลศูนย์',
		'โรงพยาบาลชุมชน',
		'คณะบุคคล',
		'องค์กรปกครองส่วนท้องถิ่น',
		'สมาคม',
		'ห้างหุ้นส่วน',
		'ศูนย์ประสานงานหลักประกันสุขภาพ',
		'ศูนย์อนามัย',
		'ศูนย์สุขภาพจิต',
		'สำนักงานป้องกันควบคุมโรคที่ 12',
		'สำนักงานสนับสนุนบริการสุขภาพเขต 1 (สงขลา)',
		'สำนักงานสนับสนุนบริการสุขภาพเขต 2  (ยะลา)',
		'สำนักงานหลักประกันสุขภาพแห่งชาติ',
	);
	$form->addField(
		'orgname',
		array(
			'type' => 'select',
			'label' => 'ชื่อองค์กร/หน่วยงาน',
			'class' => '-fill',
			'options' => array(''=>'=== ทุกองค์กร/หน่วยงาน ===')+$orgnameOptions,
			'value' => post('orgname'),
		)
	);
	$form->addField(
		'name',
		array(
			'type' => 'text',
			'label' => 'ชื่อบุคคล',
			'class' => '-fill',
			'value' => htmlspecialchars(post('name')),
		)
	);

	$provOptions=array(''=>'==เลือกจังหวัด==');
	foreach (mydb::select('SELECT `provid`,`provname` FROM %map_networks% n LEFT JOIN %co_province% cop ON cop.`provid` = n.`changwat` WHERE `provid` != "" ORDER BY CONVERT(`provname` USING tis620) ASC; -- {key:"provid"}')->items AS $item) $provOptions[$item->provid]=$item->provname;

	$form->addField(
		'changwat',
		array(
			'label'=>'พื้นที่ปฎิบัติงาน:',
			'name'=>'changwat',
			'type'=>'select',
			'class'=>'sg-changwat',
			'options'=>$provOptions,
			'posttext'=>'&nbsp;<select name="ampur" id="ampur" class="form-select sg-ampur -hidden">'
				.'<option value="">==เลือกอำเภอ==</option>'
				.'</select>&nbsp;'
				.'<select name="tambon" id="tambon" class="form-select sg-tambon -hidden" data-altfld="#edit-areacode">'
				.'<option value="">==เลือกตำบล==</option>'
				.'</select>&nbsp;'
				.'<select name="village" id="village" class="form-select sg-village -hidden">'
				.'<option value="">==เลือกหมู่บ้าน==</option>'
				.'</select>'
		)
	);


	$mechanismOptions = array(
		1 => 'คณะกรรมการพัฒนาคุณภาพชีวิตและระบบสุขภาพอำเภอ (DHB)',
		'การวิจัยเชิงปฏิบัติการแบบมีส่วนร่วม (PAR)',
		'การพัฒนาระบบข้อมูลสุขภาพชุมชน (DHIS)',
		'การประเมินเพื่อการพัฒนา (DE)',
		'DHML (เดิม)',
		'DHML (ใหม่)',
		'DHS Academy',
		'โครงการพัฒนาศักยภาพบุคลากรด้านการประเมินความจำเป็นด้านสุขภาพเพื่อรองรับการบริหารจัดการเขตสุขภาพที่ 12 (HNA)',
		'ธรรมนูญสุขภาพเขต12',
		'เครือข่าย 4PW เขต12',
		'กองทุนตำบล สปสช.',
		'คณะกรรมการเขตสุขภาพเพื่อประชาชน (กขป.)',
		'กองทุนสนับสนุนการสร้างเสริมสุขภาพขนาดเล็ก (สสส.)',
	);
	$form->addField(
		'mechanism',
		array(
			'type' => 'select',
			'label' => 'กลไก:',
			'class' => '-fill',
			'options' => array(''=>'=== ทุกกลไก ===')+$mechanismOptions,
			'value' => post('mechanism'),
		)
	);

	$form->addField(
		'issue',
		array(
			'type' => 'checkbox',
			'label' => 'ประเด็นที่เกี่ยวข้อง:',
			'class' => '-fill',
			'options' => array(
				1 => 'ผู้สูงอายุ',
				'ผู้พิการ',
				'ผู้ด้อยโอกาส',
				'อาหารปลอดภัย',
				'ความมั่นคงทางอาหาร (ปริมาณพอ)',
				'ขยะและสิ่งแวดล้อม',
				'การจัดการทรัพยากรธรรมชาติ',
				'อุบัติเหตุ',
				'เด็กและเยาวชน',
				'เกษตร',
				'เศรษฐกิจพอเพียง',
				'ยาเสพติด',
				'ภัยพิบัติ',
				'การส่งเสริมและพัฒนาคุณภาพชีวิต',
				'ความมั่นคงทางด้านสุขภาพ',
				'จิตอาสา',
				'โรคเรื้อรัง',
				'โรคติดต่อ',
				'โรคอ้วน',
				'ปัจจัยเสี่ยงด้านสุขภาพ',
				'สมุนไพร',
				'ไข้เลือดออก',
				'โรคพิษสุนัทบ้า',
				'ครัวเรือนน่าอยู่',
				'ท่องเที่ยว',
				'ความจำเป็นพื้นฐาน',
				'วัคซีน',
				'มาลาเรีย',
				'การตั้งครรภ์วัยรุ่น',
			),
		)
	);

	$form->addField(
		'search',
		array(
			'type' => 'button',
			'value' => '<i class="icon -search -white"></i><span>DONE</span>',
			'container' => array('class'=>'-sg-text-right'),
		)
	);


	$self->theme->navbar .= '<h3><a class="toggle-option" href="javascript:void(0)">ตัวเลือก <i class="icon -up -white"></i></a></h3><div class="form-content">'
			. $form->build().'</div>';


	$ret .= '<div id="search-result" class="search-result">';

	//$ret .= print_o(post(),'post');

	if (post('start')) {
		mydb::where('m.`orgid` = :orgid', ':orgid', $orgId);
		if (post('orgname')) mydb::where('(n.`who` LIKE :orgname)', ':orgname', '%'.$orgnameOptions[post('orgname')].'%');
		if (post('name')) mydb::where('(n.`who` LIKE :name)', ':name', '%'.post('name').'%');
		if (post('areacode')) mydb::where('n.`areacode` LIKE :areacode', ':areacode', post('areacode').'%');
		if (post('mechanism')) mydb::where('n.`dowhat` LIKE :dowhat', ':dowhat', '%'.$mechanismOptions[post('mechanism')].'%');


		$stmt = 'SELECT
			  m.*, n.*
			, covi.`villno` `village`, covi.`villname` `villageName`
			, cosub.`subdistname` `tambonName`
			, codist.`distname` `ampurName`
			, copv.`provname` `changwatName`
			FROM %map_name% m
				RIGHT JOIN %map_networks% n USING(`mapgroup`)
				LEFT JOIN %co_province% copv ON copv.`provid` = LEFT(n.`areacode`,2)
				LEFT JOIN %co_district% codist ON codist.`distid` = LEFT(n.`areacode`,4)
				LEFT JOIN %co_subdistrict% cosub ON cosub.`subdistid` = LEFT(n.`areacode`,6)
				LEFT JOIN %co_village% covi ON covi.`villid` = n.`areacode`
			%WHERE%
			ORDER BY CONVERT(`dowhat` USING tis620) ASC
			;';
		$dbs = mydb::select($stmt);
		//$ret .= mydb()->_query;

		$tables = new Table();
		$tables->thead = array('no'=>'', 'โครงการ','คน/องค์กร/หน่วยงาน','ที่อยู่', 'cdate -date -hover-parent'=>'สร้าง
			');
		foreach ($dbs->items as $rs) {
			$ui = new Ui('span');
			$ui->add('<a class="sg-action" href="'.url('org/'.$orgId.'/mapping.view/'.$rs->mapid).'" data-rel="box" data-width="600"><i class="icon -view"></i></a>');
			$menu = '<nav class="nav -icons -hover">'.$ui->build().'</nav>';
			$tables->rows[] = array(
				++$no,
				'<a class="sg-action" href="'.url('org/'.$orgId.'/mapping.view/'.$rs->mapid).'" data-rel="box" data-width="600">'.$rs->dowhat.'</a>',
				$rs->who,
				SG\implode_address($rs,'short'),
				($rs->created ? sg_date($rs->created,'ว ดด ปปปป') : '')
				.$menu
			);
		}

		if ($dbs->_empty) {
			$ret .= '<p class="notify">ไม่มีข้อมูลตามเงื่อนไขที่กำหนด</p>';
		} else {
			$ret .= $tables->build();
		}
	}

	$ret .= '</div><!-- search-result -->';

	//$ret .= print_o($dbs,'$dbs');

	head('<style type="text/css">
	.toggle-option {background: #0068A6; font-weight: bold; padding: 8px; display: inline-block; color:#fff; border-radius: 4px;}
	.toggle-option:hover {color:#eee; background-color: #0075ba;}
	.form-content {left: 10px; right: 10px; position: absolute; background: #fff; box-shadow: 5px 5px 10px 0 #999; z-index: 1; opacity: 0.95;}
	.sg-form.-options {padding: 16px;}

	.form-item.-edit-issue {display: flex; flex-wrap: wrap;}
	.form-item.-edit-issue label[for="edit-issue"] {width: 100%;}
	.form-item.-edit-issue .option {width: 300px;}
	</style>
	<script type="text/javascript">
	$(document).on("click",".sg-form.-options .btn.-primary", function(){
		console.log("Click")
		$(".toggle-option").trigger("click")
		return true;
	})

	$(document).on("click",".toggle-option",function() {
		var $this=$(this);
		var $icon=$this.children(".icon");
		//console.log("Click "+$icon.attr("class"))
		if ($icon.hasClass("-down")) {
			$icon.removeClass("-down").addClass("-up");
			$(".sg-form.-options").show();
		} else {
			$icon.removeClass("-up").addClass("-down");
			$(".sg-form.-options").hide();
		}
	})
	</script>
	'
	);
	return $ret;
}
?>