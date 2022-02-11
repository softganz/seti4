<?php
/**
* แบบฟอร์มการเก็บข้อมูลเพื่อการประเมินแบบมีโครงสร้าง
*
* @param Object $self
* @param Int $tpid
* @param String $action
* @param Int $actionId
* @return String
*/
function project_eval_valuation_v1($self, $tpid, $action = NULL, $actionId = NULL) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get',$tpid);
	$tpid = $projectInfo->tpid;

	$getShowId = post('id');

	if (!$tpid) return message('error', 'ไม่มีข้อมูลโครงการที่ระบุ');

	$formid = 'valuation';

	$valuationTr = project_model::get_tr($tpid,'valuation,ประเมิน');
	//$finalReportTitle = project_model::get_tr($tpid,'finalreport:title');
	//$ret .= print_o($valuationTr, '$valuationTr');

	/*
	$stmt = 'SELECT
			*
			FROM %project_tr%
			WHERE `tpid` = :tpid AND `formid` IN ("valuation", "ประเมิน")
			-- {key: "part"}';
	$valuationTr = mydb::select($stmt, ':tpid', $tpid);
	*/

	//$ret .= print_o($valuationTr, '$valuationTr');

	$url = q();

	R::View('project.toolbar', $self, $projectInfo->title, $projectInfo->submodule, $projectInfo);

	$titleRs = end($valuationTr->items['title']);

	$locked = $titleRs->flag;


	$isViewOnly = $action == 'view';
	$isAdmin = $projectInfo->info->isAdmin;
	$isEditable = $projectInfo->info->isRight;
	$isEdit = $projectInfo->info->isRight && $action == 'edit' && !$locked;

	$ret.='<header class="header -box"><nav class="nav -back -no-print"><a class="sg-action" href="javascript:void(0)" data-rel="back"><i class="icon -material">arrow_back</i></a></nav><h3>แบบประเมินคุณค่าของโครงการที่เกี่ยวข้องกับการสร้างเสริมสุขภาพ</h3></header>'._NL;


	if (post('lock') && $isAdmin && $titleRs->trid) {
		$locked = $titleRs->flag == _PROJECT_LOCKREPORT ? NULL : _PROJECT_LOCKREPORT;
		$stmt = 'UPDATE %project_tr% SET `flag` = :flag WHERE `trid` = :trid LIMIT 1';
		mydb::query($stmt, ':trid', $titleRs->trid, ':flag', $locked);
		$ret .= mydb()->_query;
		//location($url);
	}

	//$ret .= print_o($titleRs,'$titleRs');

	$ui = new Ui();
	$ui->add('<a href="'.url($url).'">รายงานแบบประเมิน</a>');
	$ui->add('<a href="'.($isAdmin ? url($url,array('lock'=>$locked?'no':'yes')) : 'javascript:void(0)').'" title="'.($isAdmin?'คลิกเพื่อเปลี่ยนสถานะรายงาน':'').'">สถานะรายงาน : '.($locked?'Lock':'UnLock').'</a>');
	$ret.='<nav class="nav -page">'.$ui->build().'</nav>';



	if ($isEdit) {
		$inlineAttr['class'] = 'sg-inline-edit ';
		$inlineAttr['data-tpid'] = $tpid;
		$inlineAttr['data-update-url'] = url('project/edit/tr');
		if (post('debug')) $inlineAttr['data-debug'] = 'yes';
	}
	$inlineAttr['class'] .= 'project-result';

	$ret.='<div id="project-valuation" '.sg_implode_attr($inlineAttr).'>'._NL;


	$section='title';
	$irs=end($valuationTr->items[$section]);

	$ret.='<p>ชื่อโครงการ <strong>'.$projectInfo->title.'</strong></p>'._NL;
	$ret.='<p>'
			. ($projectInfo->info->prid ? 'รหัสโครงการ <strong>'.$projectInfo->info->prid.'</strong> ' : '')
			. ($projectInfo->info->agrno ? 'รหัสสัญญา <strong>'.$projectInfo->info->agrno.'</strong> ' : '')
			. ($projectInfo->info->date_from ? 'ระยะเวลาโครงการ <strong>'.sg_date($projectInfo->info->date_from,'ว ดดด ปปปป').' - '.sg_date($projectInfo->info->date_end,'ว ดดด ปปปป').'</strong>' : '')
			. '</p>'._NL;

	// Section Activity
	$ret.='<p><em>แบบประเมินคุณค่าของโครงการที่เกี่ยวข้องกับการสร้างเสริมสุขภาพ เป็นการคุณค่าที่เกิดจากโครงการในมิติต่อไปนี้</p><ul><li>ความรู้ด้านการสร้างเสริมสุขภาพและนวัตกรรมเชิงระบบสุขภาพชุมชน</li><li>การปรับเปลี่ยนพฤติกรรมที่มีผลต่อสุขภาวะ</li><li>การปรับเปลี่ยนสิ่งแวดล้อมที่เอื้อต่อสุขภาวะ</li><li>ผลกระทบเชิงบวกและนโยบายสาธารณะที่เอื้อต่อการสร้างสุขภาวะชุมชน</li><li>กระบวนการชุมชน</li><li>มิติสุขภาวะปัญญา / สุขภาวะทางจิตวิญญาณ</li></ul></em></p>';

	/*
	$outputList['5.1']=array(
		'title'=>'1. เกิดความรู้ หรือ นวัตกรรมชุมชน',
		'items'=>array(
			array('section'=>'1','title'=>'ความรู้ใหม่ / องค์ความรู้ใหม่'),
			array('section'=>'2','title'=>'สิ่งประดิษฐ์ / ผลผลิตใหม่'),
			array('section'=>'3','title'=>'กระบวนการใหม่'),
			array('section'=>'4','title'=>'วิธีการทำงาน / การจัดการใหม่'),
			array('section'=>'5','title'=>'การเกิดกลุ่ม / โครงสร้างในชุมชนใหม่'),
			array('section'=>'6','title'=>'แหล่งเรียนรู้ใหม่'),
			array('section'=>'99','title'=>'อื่นๆ'),
		)
	);
	$outputList['5.2']=array(
		'title'=>'2. เกิดการปรับเปลี่ยนพฤติกรรมที่เอื้อต่อสุขภาพ',
		'items'=>array(
			array('section'=>'1','title'=>'การดูแลสุขอนามัยส่วนบุคคล'),
			array('section'=>'2','title'=>'การบริโภค'),
			array('section'=>'3','title'=>'การออกกำลังกาย'),
			array('section'=>'4','title'=>'การลด ละ เลิก อบายมุข เช่น การพนัน เหล้า บุหรี่'),
			array('section'=>'5','title'=>'การลดพฤติกรรมเสี่ยง เช่น พฤติกรรมเสี่ยงทางเพศ การขับรถโดยประมาท'),
			array('section'=>'6','title'=>'การจัดการอารมณ์ / ความเครียด'),
			array('section'=>'7','title'=>'การดำรงชีวิต / วิถีชีวิต เช่น การใช้ภูมิปัญญาท้องถิ่น / สมุนไพรในการดูแลสุขภาพตนเอง'),
			array('section'=>'8','title'=>'พฤติกรรมการจัดการตนเอง ครอบครัว ชุมชน'),
			array('section'=>'9','title'=>'อื่นๆ'),
		)
	);
	$outputList['5.3']=array(
		'title'=>'3. การสร้างสภาพแวดล้อมที่เอื้อต่อสุขภาพ (กายภาพ สังคม และเศรษฐกิจ)',
		'items'=>array(
			array('section'=>'1','title'=>'กายภาพ  เช่น  มีการจัดการขยะ  ป่า  น้ำ  การใช้สารเคมีเกษตร  และการสร้างสิ่งแวดล้อมในครัวเรือนที่ถูกสุขลักษณะ'),
			array('section'=>'2','title'=>'สังคม เช่น มีความปลอดภัยในชีวิตและทรัพย์สิน ลดการเกิดอุบัติเหตุ ครอบครัวอบอุ่น การจัดสภาพแวดล้อมที่เอื้อต่อเด็ก เยาวชน และกลุ่มวัยต่าง ๆ มีพื้นที่สาธารณะ/พื้นที่ทางสังคม เพื่อเอื้อต่อการส่งเสริมสุขภาพของคนในชุมชน มีการใช้ศาสนา/วัฒนธรรมเป็นฐานการพัฒนา'),
			array('section'=>'3','title'=>'เศรษฐกิจสร้างสรรค์สังคม /สร้างอาชีพ / เพิ่มรายได้'),
			array('section'=>'4','title'=>'มีการบริการสุขภาพทางเลือก และมีช่องทางการเข้าถึงระบบบริการสุขภาพ'),
			array('section'=>'5','title'=>'อื่นๆ'),
		)
	);
	$outputList['5.4']=array(
		'title'=>'4. การพัฒนานโยบายสาธารณะที่เอื้อต่อสุขภาวะ',
		'items'=>array(
			array('section'=>'1','title'=>'มีกฎ / กติกา ของกลุ่ม ชุมชน'),
			array('section'=>'2','title'=>'มีมาตรการทางสังคมของกลุ่ม ชุมชน'),
			array('section'=>'3','title'=>'มีธรรมนูญของชุมชน'),
			array('section'=>'4','title'=>'อื่นๆ เช่น ออกเป็นข้อบัญญัติท้องถิ่น ฯลฯ'),
		)
	);
	$outputList['5.5']=array(
		'title'=>'5. เกิดกระบวนการชุมชน',
		'items'=>array(
			array('section'=>'1','title'=>'เกิดการเชื่อมโยงประสานงานระหว่างกลุ่ม / เครือข่าย (ใน และหรือนอกชุมชน)'),
			array('section'=>'2','title'=>'การเรียนรู้การแก้ปัญหาชุมชน (การประเมินปัญหา การวางแผน การปฏิบัติการ และการประเมิน)'),
			array('section'=>'3','title'=>'การใช้ประโยชน์จากทุนในชุมชน เช่น การระดมทุน การใช้ทรัพยากรบุคคลในชุมชน'),
			array('section'=>'4','title'=>'มีการขับเคลื่อนการดำเนินงานของกลุ่มและชุมชนที่เกิดจากโครงการอย่างต่อเนื่อง'),
			array('section'=>'5','title'=>'เกิดกระบวนการจัดการความรู้ในชุมชน'),
			array('section'=>'6','title'=>'เกิดทักษะในการจัดการโครงการ เช่น การใช้ข้อมูลในการตัดสินใจ การทำแผนปฏิบัติการ'),
			array('section'=>'7','title'=>'อื่นๆ'),
		)
	);
	$outputList['5.6']=array(
		'title'=>'6. มิติสุขภาวะปัญญา / สุขภาวะทางจิตวิญญาณ',
		'items'=>array(
			array('section'=>'1','title'=>'ความรู้สึกภาคภูมิใจในตัวเอง / กลุ่ม / ชุมชน'),
			array('section'=>'2','title'=>'การเห็นประโยชน์ส่วนรวมและส่วนตนอย่างสมดุล'),
			array('section'=>'3','title'=>'การใช้ชีวิตอย่างเรียบง่าย และพอเพียง'),
			array('section'=>'4','title'=>'ชุมชนมีความเอื้ออาทร'),
			array('section'=>'5','title'=>'มีการตัดสินใจโดยใช้ฐานปัญญา'),
			array('section'=>'6','title'=>'อื่นๆ'),
		)
	);
	*/

	$outputList = [
		'inno' => [
			'title' => '1. เกิดความรู้ หรือ นวัตกรรมชุมชน',
			'items' => [
					['section' => '1','title' => 'ความรู้ใหม่ / องค์ความรู้ใหม่'],
					['section' => '2','title' => 'สิ่งประดิษฐ์ / ผลผลิตใหม่'],
					['section' => '3','title' => 'กระบวนการใหม่'],
					['section' => '4','title' => 'วิธีการทำงาน / การจัดการใหม่'],
					['section' => '5','title' => 'การเกิดกลุ่ม / โครงสร้างในชุมชนใหม่'],
					['section' => '6','title' => 'แหล่งเรียนรู้ใหม่'],
					['section' => '99','title' => 'อื่นๆ'],
			],
		],
		'behavior' => [
			'title' => '2. เกิดการปรับเปลี่ยนพฤติกรรมที่เอื้อต่อสุขภาพ',
			'items' => [
				['section' => '1','title' => 'การดูแลสุขอนามัยส่วนบุคคล'],
				['section' => '2','title' => 'การบริโภค'],
				['section' => '3','title' => 'การออกกำลังกาย'],
				['section' => '4','title' => 'การลด ละ เลิก อบายมุข เช่น การพนัน เหล้า บุหรี่'],
				['section' => '5','title' => 'การลดพฤติกรรมเสี่ยง เช่น พฤติกรรมเสี่ยงทางเพศ การขับรถโดยประมาท'],
				['section' => '6','title' => 'การจัดการอารมณ์ / ความเครียด'],
				['section' => '7','title' => 'การดำรงชีวิต / วิถีชีวิต เช่น การใช้ภูมิปัญญาท้องถิ่น / สมุนไพรในการดูแลสุขภาพตนเอง'],
				['section' => '8','title' => 'พฤติกรรมการจัดการตนเอง ครอบครัว ชุมชน'],
				['section' => '9','title' => 'อื่นๆ'],
			],
		],
		'environment' => [
			'title' => '3. การสร้างสภาพแวดล้อมที่เอื้อต่อสุขภาพ (กายภาพ สังคม และเศรษฐกิจ)',
			'items' => [
				['section' => '1','title' => 'กายภาพ  เช่น  มีการจัดการขยะ  ป่า  น้ำ  การใช้สารเคมีเกษตร  และการสร้างสิ่งแวดล้อมในครัวเรือนที่ถูกสุขลักษณะ'],
				['section' => '2','title' => 'สังคม เช่น มีความปลอดภัยในชีวิตและทรัพย์สิน ลดการเกิดอุบัติเหตุ ครอบครัวอบอุ่น การจัดสภาพแวดล้อมที่เอื้อต่อเด็ก เยาวชน และกลุ่มวัยต่าง ๆ มีพื้นที่สาธารณะ/พื้นที่ทางสังคม เพื่อเอื้อต่อการส่งเสริมสุขภาพของคนในชุมชน มีการใช้ศาสนา/วัฒนธรรมเป็นฐานการพัฒนา'],
				['section' => '3','title' => 'เศรษฐกิจสร้างสรรค์สังคม /สร้างอาชีพ / เพิ่มรายได้'],
				['section' => '4','title' => 'มีการบริการสุขภาพทางเลือก และมีช่องทางการเข้าถึงระบบบริการสุขภาพ'],
				['section' => '5','title' => 'อื่นๆ'],
			],
		],
		'publicpolicy' => [
			'title' => '4. การพัฒนานโยบายสาธารณะที่เอื้อต่อสุขภาวะ',
			'items' => [
				['section' => '1','title' => 'มีกฎ / กติกา ของกลุ่ม ชุมชน'],
				['section' => '2','title' => 'มีมาตรการทางสังคมของกลุ่ม ชุมชน'],
				['section' => '3','title' => 'มีธรรมนูญของชุมชน'],
				['section' => '4','title' => 'อื่นๆ เช่น ออกเป็นข้อบัญญัติท้องถิ่น ฯลฯ'],
			],
		],
		'social' => [
			'title' => '5. เกิดกระบวนการชุมชน',
			'items' => [
				['section' => '1','title' => 'เกิดการเชื่อมโยงประสานงานระหว่างกลุ่ม / เครือข่าย (ใน และหรือนอกชุมชน)'],
				['section' => '2','title' => 'การเรียนรู้การแก้ปัญหาชุมชน (การประเมินปัญหา การวางแผน การปฏิบัติการ และการประเมิน)'],
				['section' => '3','title' => 'การใช้ประโยชน์จากทุนในชุมชน เช่น การระดมทุน การใช้ทรัพยากรบุคคลในชุมชน'],
				['section' => '4','title' => 'มีการขับเคลื่อนการดำเนินงานของกลุ่มและชุมชนที่เกิดจากโครงการอย่างต่อเนื่อง'],
				['section' => '5','title' => 'เกิดกระบวนการจัดการความรู้ในชุมชน'],
				['section' => '6','title' => 'เกิดทักษะในการจัดการโครงการ เช่น การใช้ข้อมูลในการตัดสินใจ การทำแผนปฏิบัติการ'],
				['section' => '7','title' => 'อื่นๆ'],
			],
		],
		'spirite' => [
			'title' => '6. มิติสุขภาวะปัญญา / สุขภาวะทางจิตวิญญาณ',
			'items' => [
				['section' => '1','title' => 'ความรู้สึกภาคภูมิใจในตัวเอง / กลุ่ม / ชุมชน'],
				['section' => '2','title' => 'การเห็นประโยชน์ส่วนรวมและส่วนตนอย่างสมดุล'],
				['section' => '3','title' => 'การใช้ชีวิตอย่างเรียบง่าย และพอเพียง'],
				['section' => '4','title' => 'ชุมชนมีความเอื้ออาทร'],
				['section' => '5','title' => 'มีการตัดสินใจโดยใช้ฐานปัญญา'],
				['section' => '6','title' => 'อื่นๆ'],
			],
		],
	];


	$ret .= '<section class="section-5">';

	$tables = new Table();
	$tables->addClass('project-valuation-form -other');
	$tables->colgroup = array('width="20%"','width="5%"','width="5%"','width="25%"','width="25%"','width="25%"');
	$tables->thead = '<thead><tr><th rowspan="2">คุณค่าที่เกิดขึ้น<br />ประเด็น</th><th colspan="2">ผลที่เกิดขึ้น</th><th rowspan="2">รายละเอียด/การจัดการ</th><th rowspan="2">หลักฐาน/แหล่งอ้างอิง</th><th rowspan="2">แนวทางการพัฒนาต่อ</th></tr><tr><th style="width:30px;">ใช่</th><th style="width:30px;">ไม่ใช่</th></tr></thead>';

	foreach ($outputList as $mainKey=>$mainValue) {
		if ($getShowId && '5.'.$getShowId != $mainKey) continue;

		$tables->rows[] = array('<td colspan="6"><h3>'.$mainValue['title'].'</h3></td>');

		foreach ($mainValue['items'] as $k=>$v) {
			if (!empty($v['section'])) $tables->rows[] = '<header>';
			if (empty($v['section'])) {
				$tables->rows[] = array('<td colspan="6"><b>'.$v['title'].'</b></td>');
				continue;
			}

			$section = $mainKey.'.'.$v['section'];
			$irs = end($valuationTr->items[$section]);
			unset($row);
			$row[] = '<span>'.($v['section']).'. '.$v['title'].'</span>';
			$row[] = view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'rate1', 'name'=>'rate'.$section, 'tr'=>$irs->trid, 'value'=>$irs->rate1),'1:',$isEdit,'radio');
			$row[] = view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'rate1', 'name'=>'rate'.$section, 'tr'=>$irs->trid, 'value'=>$irs->rate1),'0:',$isEdit,'radio');
			$row[] = view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text1','tr'=>$irs->trid,'ret'=>'html', 'value'=>trim($irs->text1)),$irs->text1,$isEdit,'textarea');
			$row[] = view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text2','tr'=>$irs->trid, 'ret'=>'html', 'value'=>$irs->text2),$irs->text2,$isEdit,'textarea');
			$row[] = view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text3','tr'=>$irs->trid, 'ret'=>'html', 'value'=>$irs->text3),$irs->text3,$isEdit,'textarea');
			$tables->rows[] = $row;

			$tables->rows[] = array('','config'=>array('class'=>'empty'));
		}
	}
	$ret .= $tables->build();

	$ret .= '</section><!-- section-5 -->';


	// ดึงค่า default จากรายละเอียดโครงการ
	$preAbstract='โครงการนี้มีวัตถุประสงค์เพื่อ';
	if ($projectInfo->objective) {
		$oi = 0;
		foreach ($projectInfo->objective as $rs) {
			$preAbstract .= ' ('.(++$oi).') '.$rs->title;
		}
	} else {
		$ret .= $projectInfo->info->objective;
	}
	$preAbstract .= _NL._NL;

	$preAbstract .= 'ผลการดำเนินงานที่สำคัญ ได้แก่';

	$oi = 0;
	foreach ($projectInfo->activity as $rs) {
		$preAbstract.=' ('.(++$oi).') '.$rs->title;
	}

	$preAbstract .= _NL._NL;
	$preAbstract .= 'ข้อเสนอแนะ ได้แก่ (1) ...';


	/*
	$section='title';
	$irs=end($finalReportTitle->items[$section]);

	$ret .= '<section class="section-7 box">';
	$ret .= '<h3>7. สรุปผลการทำโครงการ (บทคัดย่อ)<sup>*</sup></h3>';
	$ret .= View::inlineedit(
						array(
							'group'=>'finalreport:title',
							'fld'=>'text2',
							'tr'=>$irs->trid,
							'ret'=>'html',
							'button'=>'yes',
							'value'=>trim(SG\getFirst($irs->text2,$preAbstract))
						),
						SG\getFirst($irs->text2,$preAbstract),
						$isEdit,
						'textarea'
					);


	$ret.='<p class="noprint">หมายเหตุ *<ul><li><strong>สรุปผลการทำโครงการ (บทคัดย่อ)</strong> จะนำไปใส่ในบทคัดย่อของรายงานฉบับสมบูรณ์</li><li>หากต้องการใช้ค่าเริ่มต้นของสรุปผลการทำโครงการ (บทคัดย่อ) ให้ลบข้อความในช่องสรุปผลการทำโครงการ (บทคัดย่อ) ทั้งหมด แล้วกดปุ่ม Refresh</li></ul></p>';
	$ret .= '</section><!-- section-7 -->';
*/

	if ($isViewOnly) {
		// Do nothing
	} else if ($isEdit) {
		$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -primary -circle48" href="'.url('project/'.$tpid.'/eval.valuation.v1',array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -save -white"></i></a></div>';
	} else if ($isEditable) {
		$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/'.$tpid.'/eval.valuation.v1/edit',array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -edit -white"></i></a></div>';
	}

	$ret.='</div>';



	$ret.='<style>
	.project-valuation-form td:nth-child(2), .project-valuation-form td:nth-child(3) {text-align:center;}
	.project-valuation-form thead {display:none;}
	.project-valuation-form .header th {font-weight:normal;}
	.project-valuation-form td:first-child span {background:#eee; display: block; padding: 8px; border-radius:4px; border: #ccc 1px solid;}
	.project-valuation-form td {border-bottom:none;}
	.project-valuation-form tr.empty td:first-child {background:transparent;}

	</style>';


	$ret .= '<script type="text/javascript">
	// Other radio group
	$(".project-valuation-form.-other input.inline-edit-field.-radio").each(function() {
		var $radioBtn = $(this).closest("tr").find(".inline-edit-field.-radio:checked")
		var radioValue = $radioBtn.val();
		//console.log("Tr = "+$radioBtn.data("tr")+" - radioValue="+radioValue);
		if (!(radioValue==0 || radioValue==1)) {
			$(this).closest("tr").find("span.inline-edit-field").hide();
		}
	});

	$(".project-valuation-form.-other input[type=\'radio\']").change(function() {
		var rate = $(this).val()
		var $inlineInput = $(this).closest("tr").find("td>span>span")
		//console.log("radio change "+$(this).val())
		$inlineInput.show()
	});

	</script>';

	//$ret.=print_o($valuationTr,'$valuationTr');
	return $ret;
}


?>