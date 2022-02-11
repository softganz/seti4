<?php
/**
 * แบบฟอร์มรายงานภาวะโภชนาการนักเรียน
 *
 * @param Object $projectInfo
 * @param String $action
 * @param Int $tranId
 * @return String
 */

define(_FORMID,'learn');
define(_PARTID,'title');

function project_info_learn($self, $projectInfo, $action = NULL, $tranId = NULL) {
	if (!$projectInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $projectInfo->tpid;

	R::View('project.toolbar', $self, $projectInfo->title, $projectInfo->submodule, $projectInfo,'{showPrint: true}');


	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;
	$isAdmin = $projectIn->RIGHT & _IS_ADMIN;

	$percentDigit=2;

	//$ret.='trid='.$tranId.print_o($para,'$para');

	if ($tranId) $currentRs=__project_form_learn_gettitle($tranId);

	$navbar='<!--navbar start-->';
	$navbar.='<h3>ผลสัมฤทธิ์ทางการเรียน</h3>';
	$ui=new ui();
	if ($isEdit) {
		$ui->add('<a href="'.url('project/'.$tpid.'/info.learn/create').'"><i class="icon -material">add_circle_outline</i><span>เพิ่มบันทึกสถานการณ์</span></a>');
		$ui->add('<a href="'.url('project/'.$tpid.'/info.learn').'"><i class="icon -material">edit</i><span>แก้ไขบันทึกสถานการณ์เดิม</span></a>');
	}
	$ui->add('<a href="'.url('project/'.$tpid.'/info.learn').'"><i class="icon -material">view_list</i><span>ดูรายการสถานการณ์</span></a>');
	if ($tranId) $ui->add('<a href="'.url('project/'.$tpid.'/info.learn/view/'.$tranId).'">สถานการณ์ครั้งที่ '.$currentRs->sorder.'</a>');
	$ui->add('<a href="javascript:window.print()"><i class="icon -material">print</i><span>พิมพ์</span></a>');

	if ($tranId) {
		$subui=new ui();
		if ($isEdit) {
			$subui->add('<a href="'.url('project/'.$tpid.'/info.learn/view/'.$tranId).'"><i class="icon -view"></i>ดูรายละเอียดบันทึกสถานการณ์</a>');
			$subui->add('<a href="'.url('project/'.$tpid.'/info.learn/modify/'.$tranId).'"><i class="icon -edit"></i>แก้ไขบันทึกสถานการณ์เดิม</a>');
			$subui->add('<hr />');
			$subui->add('<a href="'.url('project/'.$tpid.'/info.learn/create').'"><i class="icon -add"></i>เพิ่มบันทึกสถานการณ์</a>');
			$subui->add('<hr />');
			$subui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info.learn/remove/'.$tranId).'" data-confirm="ต้องการลบบันทึกนี้ กรุณายืนยัน"><i class="icon -delete"></i>ลบบันทึกสถานการณ์</a>');
		}
		if ($isAdmin) $subui->add('<a href="'.url('project/'.$tpid.'/info.learn/view/'.$tranId).'"><i class="icon -refresh"></i>รีเฟรช</a>');
		$ui->add(sg_dropbox($subui->build('ul')));
	}

	$navbar.='<nav class="nav -page -no-print">'.$ui->build('ul').'</nav><!--navbar end-->'._NL;


	$self->theme->navbar=$navbar;


	switch ($action) {
		case 'create' :
			if ($isEdit) $ret.=__project_form_learn_create($tpid);
			return $ret;
			break;

		case 'modify' :
			if ($isEdit) $ret.=__project_form_learn_create($tpid,$tranId);
			return $ret;
			break;

		case 'view' :
			$ret.=__project_form_learn_view($tpid,$tranId,$isEdit);
			return $ret;
			break;

		case 'remove' :
			if ($isEdit) $ret.=__project_form_learn_remove($tpid,$tranId);
			location('project/'.$tpid.'/info.learn');
			return $ret;
			break;

	}


	$qtvalue->getweight=$qtarray['thin']+$qtarray['ratherthin']+$qtarray['willowy']+$qtarray['plump']+$qtarray['gettingfat']+$qtarray['fat'];

	$stmt='SELECT
					tr.`trid`, tr.`tpid`, tr.`sorder`, tr.`detail1` `year`, tr.`detail2` `term`, tr.`period`,
					tr.`detail3` `area`, tr.`detail4` `postby`, tr.`date1` `dateinput`
					, SUM(q.`num2`) `subject1`
					, SUM(q.`num3`) `subject2`
					, SUM(q.`num4`) `subject3`
					, SUM(q.`num5`) `subject4`
					, SUM(q.`num6`) `subject5`
					, SUM(q.`num7`) `subject6`
					, SUM(q.`num8`) `subject7`
					, SUM(q.`num9`) `subject8`
					, SUM(q.`num10`) `subject9`
					, SUM(q.`num11`) `subject10`
					, SUM(q.`num12`) `subject11`
					, SUM(q.`num1`) `total`
					, COUNT(IF(q.`num2`>0,1,NULL)) `count1`
					, COUNT(IF(q.`num3`>0,1,NULL)) `count2`
					, COUNT(IF(q.`num4`>0,1,NULL)) `count3`
					, COUNT(IF(q.`num5`>0,1,NULL)) `count4`
					, COUNT(IF(q.`num6`>0,1,NULL)) `count5`
					, COUNT(IF(q.`num7`>0,1,NULL)) `count6`
					, COUNT(IF(q.`num8`>0,1,NULL)) `count7`
					, COUNT(IF(q.`num9`>0,1,NULL)) `count8`
					, COUNT(IF(q.`num10`>0,1,NULL)) `count9`
					, COUNT(IF(q.`num11`>0,1,NULL)) `count10`
					, COUNT(IF(q.`num12`>0,1,NULL)) `count11`
					, COUNT(IF(q.`num1`>0,1,NULL)) `countTotal`
					FROM %project_tr% tr
						LEFT JOIN %project_tr% q ON q.`parent`=tr.`trid` AND q.`part`=:formid
					WHERE tr.`tpid`=:tpid AND tr.`formid`=:formid AND tr.`part`="title"
					GROUP BY tr.`sorder`
					ORDER BY `year` ASC,`term` ASC, `period` ASC';
	$dbs=mydb::select($stmt,':tpid',$tpid,':formid',_FORMID);
	//$ret.=print_o($dbs,'$dbs');

	$tables=new table('item -center -weightform');
	$tables->caption='ผลสัมฤทธิ์ทางการเรียน';
	$tables->thead='<tr><th>ปีการศึกษา</th><th>ภาคการศึกษา</th><th>วันที่ประเมิน</th><th>ภาษาไทย</th><th>คณิตศาสตร์</th><th>วิทยาศาสตร์</th><th>สังคมฯ</th><th>สุขศึกษา</th><th>การงานฯ</th><th>ศิลปะ</th><th>ภาษาอังกฤษ</th><th>ประ
					ORDER BY `year` ASC,`term` ASC, `period` ASC';

	$tables=new table('item -center -weightform');
	$tables->caption='ผลสัมฤทธิ์ทางการเรียน';
	$tables->thead='<tr><th>ปีการศึกษา</th><th>ภาคการศึกษา</th><th>วันที่ประเมิน</th><th>ภาษาไทย</th><th>คณิตศาสตร์</th><th>วิทยาศาสตร์</th><th>สังคมฯ</th><th>สุขศึกษา</th><th>การงานฯ</th><th>ศิลปะ</th><th>ภาษาอังกฤษ</th><th>ประวัติศาสตร์</th><th>ภาษาไทยเพิ่มเติม</th></th><th>หน้าที่ฯ</th><th>รวม</th><th></th><th></th></tr>';

	$no=0;
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
											$rs->year+543,
											$rs->term,
											sg_date($rs->dateinput,'ว ดด ปป'),
											number_format($rs->subject1/$rs->count1,$percentDigit),
											number_format($rs->subject2/$rs->count2,$percentDigit),
											number_format($rs->subject3/$rs->count3,$percentDigit),
											number_format($rs->subject4/$rs->count4,$percentDigit),
											number_format($rs->subject5/$rs->count5,$percentDigit),
											number_format($rs->subject6/$rs->count6,$percentDigit),
											number_format($rs->subject7/$rs->count7,$percentDigit),
											number_format($rs->subject8/$rs->count8,$percentDigit),
											number_format($rs->subject9/$rs->count9,$percentDigit),
											number_format($rs->subject10/$rs->count10,$percentDigit),
											number_format($rs->subject11/$rs->count11,$percentDigit),
											number_format($rs->total/$rs->countTotal,$percentDigit),
											'<a class="sg-action" href="'.url('project/'.$tpid.'/info.learn/view/'.$rs->trid).'" data-rel="box"><i class="icon -material">search></i></a>',
											$isEdit?'<a href="'.url('project/'.$tpid.'/info.learn/modify/'.$rs->trid).'"><i class="icon -material">edit</i></a>':'',
											);
	}
	$ret.=$tables->build();

	$ret.='<p>หมายเหตุ : ในการรายงานผลสัมฤทธิ์ทางการเรียน<ul><li>นักเรียนระดับประถมศึกษา --- จะมีสรุปการรายงานปีละครั้ง</li><li>นักเรียนระดับมัธยมศึกษา --- จะมีการสรุปรายงานปีละ 2 ครั้ง</li></ul></p>';



	//$ret.=print_o($dbs,'$dbs');
	//$ret.=print_o($projectInfo,'$projectInfo');

	$ret.='<style type="text/css">
	.item.-weightform {margin-bottom:80px;}
	.item.-weightform caption {background:#FFAE00; color:#000; font-size:1.4em;}
	.item.-weightform td:nth-child(2n+1) {color:#999;}
	.item.-weightform td:nth-child(2n+2) {background:#efefef;}
	.item.-weightform td {width:50px;}
	.item.-weightform td:first-child, .item.-weightform td:nth-child(3) {color:#333;}
	.item td {text-align: center;}
	.item h3 {padding-left:10px;text-align:left; background:#9400FF; color:#fff;}
	.item .student {font-weight:bold;}
	.graph {width:150px;height:150px; margin:0 auto;}
	.toolbar.-graphtype {text-align: right; margin:0 0 10px 0;}
	.toolbar .active {background:#84CC00;}
	.item tr.subfooter.-sub2 td {background-color:#d0d0d0;}
	.item tr.subfooter.-sub3 td {background-color:#c0c0c0;}
	</style>';
	return $ret;
}

function __project_form_learn_create($tpid,$tranId = NULL) {
	$post=(object)post('title');

	$percentDigit=2;

	if ((Array)$post) {
		$post->tpid=$tpid;
		$post->trid=$tranId;
		$post->formid=_FORMID;
		list($post->term,$post->period)=explode(':',$post->termperiod);
		if (empty($post->period)) $post->period=1;
		$post->uid=i()->uid;
		$post->dateinput=sg_date($post->dateinput,'Y-m-d 00:00:00');
		$post->order=mydb::select('SELECT MAX(`sorder`) maxorder FROM %project_tr% WHERE `tpid`=:tpid AND `formid`=:formid AND `part`="title" LIMIT 1',':tpid',$tpid,':formid',_FORMID)->maxorder+1;
		//$ret.=mydb()->_query.'<br />';
		$post->created=date('U');
		$stmt='INSERT INTO %project_tr%
			(
			`trid`, `tpid`, `uid`, `formid`, `part`, `sorder`
			, `detail1`, `detail2`, `period`, `detail4` , `date1`
			, `created`
			)
			VALUES
			(
			:trid, :tpid, :uid, :formid, "title", :order
			, :year, :term, :period, :postby, :dateinput
			, :created
			)
			ON DUPLICATE KEY UPDATE
			`detail1`=:year
			, `detail2`=:term
			, `period`=:period
			, `detail4`=:postby
			, `date1`=:dateinput';
		mydb::query($stmt,$post);
		if (!$tranId) $tranId=mydb()->insert_id;
		//$ret.=mydb()->_query.'<br />';

		$qt=post('qt');
		$qttrid=array();
		$stmt='SELECT `trid`,`sorder`,`part`
						FROM %project_tr%
						WHERE `tpid`=:tpid AND `parent`=:trid
							AND `formid`=:formid AND `part`=:formid
						ORDER BY `sorder` ASC';
		foreach (mydb::select($stmt,':tpid',$tpid, ':trid',$tranId,':formid',_FORMID)->items as $item) {
			$qttrid[$item->sorder]=$item->trid;
		}
		//$ret.=mydb()->_query.'<br />';
		//$ret.=print_o($qttrid,'$qttrid');

		foreach ($qt as $qtno => $qtarray) {
			unset($qtvalue);
			$qtvalue->trid=$qttrid[$qtno];
			$qtvalue->tpid=$tpid;
			$qtvalue->parent=$tranId;
			$qtvalue->uid=i()->uid;
			$qtvalue->sorder=$qtno;
			$qtvalue->formid=_FORMID;
			$qtvalue->part=_FORMID;
			$qtvalue->choice01=$qtarray['subject1'];
			$qtvalue->choice02=$qtarray['subject2'];
			$qtvalue->choice03=$qtarray['subject3'];
			$qtvalue->choice04=$qtarray['subject4'];
			$qtvalue->choice05=$qtarray['subject5'];
			$qtvalue->choice06=$qtarray['subject6'];
			$qtvalue->choice07=$qtarray['subject7'];
			$qtvalue->choice08=$qtarray['subject8'];
			$qtvalue->choice09=$qtarray['subject9'];
			$qtvalue->choice10=$qtarray['subject10'];
			$qtvalue->choice11=$qtarray['subject11'];
			$qtTotal=$qtCount=0;
			foreach ($qtarray as $qtValue) {
				if (empty($qtValue)) continue;
				$qtTotal+=$qtValue;
				$qtCount++;
			}
			$qtvalue->total=empty($qtTotal)?0:$qtTotal/$qtCount;
			$qtvalue->created=date('U');
			$stmt='INSERT INTO %project_tr%
						(
							`trid`, `tpid`, `parent`, `uid`, `sorder`, `formid`, `part`,
							`num1`, `num2`, `num3`, `num4`, `num5`, `num6`, `num7`, `num8`, `num9`, `num10`, `num11`, `num12`, `created`
						)
						VALUES
						(
							:trid, :tpid, :parent, :uid, :sorder, :formid, :part,
							:total, :choice01, :choice02, :choice03, :choice04, :choice05, :choice06, :choice07, :choice08, :choice09, :choice10, :choice11, :created
						)
						ON DUPLICATE KEY UPDATE
							  `num1`=:total
							, `num2`=:choice01
							, `num3`=:choice02
							, `num4`=:choice03
							, `num5`=:choice04
							, `num6`=:choice05
							, `num7`=:choice06
							, `num8`=:choice07
							, `num9`=:choice08
							, `num10`=:choice09
							, `num11`=:choice10
							, `num12`=:choice11
							';
			mydb::query($stmt,$qtvalue);
			//$ret.=mydb()->_query.'<br />';
		}

		//$ret.=print_o($post,'$post');
		//$ret.=print_o($qt,'$qt');
		location('project/'.$tpid.'/info.learn'.($tranId?'/view/'.$tranId:''));
	} else if ($tranId) {
		$post=__project_form_learn_gettitle($tranId);
	}

	$form = new Form([
		'variable ' => 'title',
		'action' => url('project/'.$tpid.'/info.learn/'.($tranId?'modify/'.$tranId:'create')),
		'id' => 'activity-add',
		'title' => '<h3>บันทึกผลสัมฤทธิ์ทางการเรียน</h3>',
		'children' => [
			'<div class="row -sg-flex">',
			'year' => [
				'type' => 'radio',
				'label' => 'ปีการศึกษา :',
				'require' => true,
				'options' => (function() {
					$options = [];
					for ($i=2015;$i<=date('Y')+1;$i++) $options[$i]=$i+543;
					return $options;
				})(),
				'value' => SG\getFirst($post->year,date('Y')),
			],
			'termperiod' => [
				'type' => 'radio',
				'label' => 'ภาคการศึกษา :',
				'require' => true,
				'options' => [
					'1'=>'ภาคการศึกษา 1',
					'2'=>'ภาคการศึกษา 2',
				],
				'value' => SG\getFirst($post->termperiod,1),
			],
			'postby' => [
				'type' => 'text',
				'label' => 'ผู้ประเมิน',
				'require' => true,
				'value' => $post->postby,
			],
			'dateinput' => [
				'type' => 'text',
				'label' => 'วันที่ประเมิน',
				'class' => 'sg-datepicker',
				'require' => true,
				'value' => sg_date(SG\getFirst($post->dateinput,date('Y-m-d')),'d/m/Y'),
			],
			'</div>',

			(function($tpid, $tranId) {
				$tables = new Table('item -weight');
				$tables->thead='<tr><th>ชั้น</th><th>ภาษาไทย</th><th>คณิตศาสตร์</th><th>วิทยาศาสตร์</th><th>สังคมฯ</th><th>สุขศึกษา</th><th>การงานฯ</th><th>ศิลปะ</th><th>ภาษาอังกฤษ</th><th>ประวัติศาสตร์</th><th>ภาษาไทยเพิ่มเติม</th><th>หน้าที่ฯ</th><th>รวม</th></tr>';
				$stmt='SELECT
					  qt.`question`
					, qt.`qtgroup`
					, qt.`qtno`
					, tr.`parent`
					, tr.`part`
					, tr.`sorder`
					, tr.`num1` total
					, tr.`num2` subject1
					, tr.`num3` subject2
					, tr.`num4` subject3
					, tr.`num5` subject4
					, tr.`num6` subject5
					, tr.`num7` subject6
					, tr.`num8` subject7
					, tr.`num9` subject8
					, tr.`num10` subject9
					, tr.`num11` subject10
					, tr.`num12` subject11
					, qt.`description`
				FROM %qt% qt
					LEFT JOIN %project_tr% tr
						ON tr.`tpid`=:tpid AND tr.`parent`=:trid AND tr.`formid`=:formid AND tr.`part`=:formid
							AND qt.`qtgroup`="schoolclass" AND tr.`sorder`=qt.`qtno`
				WHERE `qtgroup`="schoolclass"
				ORDER BY `qtgroup` ASC, `qtno` ASC';
				$qtResultDbs=mydb::select($stmt,':trid',$tranId,':tpid',$tpid,':formid',_FORMID);
				//$ret.=print_o($qtResultDbs);

				$i=0;
				foreach ($qtResultDbs->items as $rs) {
					if ($rs->qtno<=20) continue;
					$i++;
					if (in_array($rs->qtno,array(11,21,31))) $tables->rows[]=array('<th colspan="13"><h3>'.rtrim(substr($rs->question,0,strpos($rs->question,' ')),'ปีที่').'</h3></th>');
					$tables->rows[]=array(
						$rs->question
						//.'<br />'.$stdKey.print_o($rs,'$rs')
						,
						'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][subject1]" value="'.number_format($rs->subject1,$percentDigit).'" />',
						'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][subject2]" value="'.number_format($rs->subject2,$percentDigit).'" />',
						'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][subject3]" value="'.number_format($rs->subject3,$percentDigit).'" />',
						'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][subject4]" value="'.number_format($rs->subject4,$percentDigit).'" />',
						'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][subject5]" value="'.number_format($rs->subject5,$percentDigit).'" />',
						'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][subject6]" value="'.number_format($rs->subject6,$percentDigit).'" />',
						'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][subject7]" value="'.number_format($rs->subject7,$percentDigit).'" />',
						'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][subject8]" value="'.number_format($rs->subject8,$percentDigit).'" />',
						'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][subject9]" value="'.number_format($rs->subject9,$percentDigit).'" />',
						'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][subject10]" value="'.number_format($rs->subject10,$percentDigit).'" />',
						'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][subject11]" value="'.number_format($rs->subject11,$percentDigit).'" />',
						'<span id="">'.number_format($rs->total,$percentDigit).'</span>',
					);
					$subtotal+=$rs->answer;
				}
				return $tables;
			})($tpid, $tranId),

			'submit' => [
				'type' => 'button',
				'value' => '<i class="icon -save -white"></i><span>{tr:SAVE}</span>',
				'pretext' => '<a class="btn -link -cancel" href="'.url('project/'.$tpid.'/info.learn').'"><i class="icon -cancel -gray"></i><span>{tr:CANCEL}</span></a>',
				'container' => array('class'=>'-sg-text-right'),
			],
		], // children
	]);

	$ret .= $form->build();

	//$ret.=print_o($qtResultDbs,'$qtResultDbs');
	$ret.='<style type="text/css">
	form>div>.form-item {margin: 0; padding:0;}
	form>div>.form-item:first-child {margin-left:0;}
	form>div>.form-item:last-child {margin-right:0;}
	form>#form-item-edit-eat-submit {display:block; border:none;}
	.container>.row.-flex>.col {float: none; padding: 8px 16px; margin: 16px 16px 16px 0;}
	@media (min-width:45em) { /* 720/16 */
	form>div>.form-item {margin: 16px; padding:0 16px; display: inline-block; border: 1px #ccc solid; vertical-align: top; border-radius:2px;}
	}
	</style>';
	return $ret;
}

function __project_form_learn_view($tpid,$tranId,$isEdit) {
	$formid=_FORMID;
	$percentDigit=2;

	$title=__project_form_learn_gettitle($tranId);

	$stmt='SELECT
					  qt.`question`
					, qt.`qtgroup`
					, qt.`qtno`
					, tr.`trid`
					, tr.`part`
					, tr.`sorder`
					, tr.`num1` `total`
					, tr.`num2` `subject1`
					, tr.`num3` `subject2`
					, tr.`num4` `subject3`
					, tr.`num5` `subject4`
					, tr.`num6` `subject5`
					, tr.`num7` `subject6`
					, tr.`num8` `subject7`
					, tr.`num9` `subject8`
					, tr.`num10` `subject9`
					, tr.`num11` `subject10`
					, tr.`num12` `subject11`
				FROM %qt% qt
					LEFT JOIN %project_tr% tr
						ON tr.`tpid`=:tpid AND tr.`parent`=:trid AND tr.`formid`=:formid
							AND qt.`qtgroup`="schoolclass" AND tr.`sorder`=qt.`qtno`
				WHERE `qtgroup`="schoolclass"
				ORDER BY `qtgroup` ASC, `qtno` ASC';
	$dbs=mydb::select($stmt,':trid',$tranId,':tpid',$tpid,':formid',_FORMID);
	//$ret.=print_o($dbs,'$dbs');

	$ret.='<header class="header -box"><h3>ครั้งที่ : <strong>'.$title->sorder.'</strong> ปีการศึกษา : <strong>'.($title->year+543).'</strong> ภาคการศึกษา : <strong>'.$title->term.'</strong> ครั้งที่ : <strong>'.$title->period.'</strong> ผู้ประเมิน : <strong>'.$title->postby.'</strong> วันที่ประเมิน : <strong>'.sg_date($title->dateinput,'ว ดด ปป').'</strong></header>';

	$ret.='</h3>';

	$weightTotal=$weightGetweight=$weightThin=$weightRatherthin=$weightWillowy=$weightPlump=$weightGettingfat=$weightFat=0;
	$tables=new table('item -weightform');
	$tables->thead='<tr><th>ชั้น</th><th>ภาษาไทย</th><th>คณิตศาสตร์</th><th>วิทยาศาสตร์</th><th>สังคมฯ</th><th>สุขศึกษา</th><th>การงานฯ</th><th>ศิลปะ</th><th>ภาษาอังกฤษ</th><th>ประวัติศาสตร์</th><th>ภาษาไทยเพิ่มเติม</th><th>หน้าที่ฯ</th><th>รวม</th></tr>';
	foreach ($dbs->items as $rs) {
		$totalError=$rs->total<$rs->getweight;
		if ($rs->qtno<20) continue;
		if (in_array($rs->qtno,array(11,21,31))) {
			$tables->rows[]=array('<th colspan="13"><h3>'.rtrim(substr($rs->question,0,strpos($rs->question,' ')),'ปีที่').'</h3></th>');
			$subWeightTotal=$subWeightGetweight=$subWeightThin=$subWeightRatherthin=$subWeightWillowy=$subWeightPlump=$subWeightGettingfat=$subWeightFat=0;
		}
		$tables->rows[]=array(
											$rs->question,
											$rs->subject1!=0?number_format($rs->subject1,$percentDigit):'',
											$rs->subject2!=0?number_format($rs->subject2,$percentDigit):'',
											$rs->subject3!=0?number_format($rs->subject3,$percentDigit):'',
											$rs->subject4!=0?number_format($rs->subject4,$percentDigit):'',
											$rs->subject5!=0?number_format($rs->subject5,$percentDigit):'',
											$rs->subject6!=0?number_format($rs->subject6,$percentDigit):'',
											$rs->subject7!=0?number_format($rs->subject7,$percentDigit):'',
											$rs->subject8!=0?number_format($rs->subject8,$percentDigit):'',
											$rs->subject9!=0?number_format($rs->subject9,$percentDigit):'',
											$rs->subject10!=0?number_format($rs->subject10,$percentDigit):'',
											$rs->subject11!=0?number_format($rs->subject11,$percentDigit):'',
											$rs->total!=0?number_format($rs->total,$percentDigit):'',
											'config'=>array('class'=>$totalError?'error -weight':''),
											);
		$subWeightTotal+=$rs->total;
		$subWeightGetweight+=$rs->getweight;
		$subWeightThin+=$rs->thin;
		$subWeightRatherthin+=$rs->ratherthin;
		$subWeightWillowy+=$rs->willowy;
		$subWeightPlump+=$rs->plump;
		$subWeightGettingfat+=$rs->gettingfat;
		$subWeightFat+=$rs->fat;
		/*
		if (in_array($rs->qtno,array(13,26,33))) {
			$tables->rows[]=array(
												'รวมช่วงชั้น',
												$subWeightTotal,
												$subWeightGetweight,
												$subWeightThin,number_format($subWeightThin*100/$subWeightTotal,$percentDigit),
												$subWeightRatherthin,number_format($subWeightRatherthin*100/$subWeightTotal,$percentDigit),
												$subWeightWillowy,number_format($subWeightWillowy*100/$subWeightTotal,$percentDigit),
												$subWeightPlump,number_format($subWeightPlump*100/$subWeightTotal,$percentDigit),
												$subWeightGettingfat,number_format($subWeightGettingfat*100/$subWeightTotal,$percentDigit),
												'config'=>array('class'=>'subfooter')
												);
		}
		*/

		$weightTotal+=$rs->total;
		$weightGetweight+=$rs->getweight;
		$weightThin+=$rs->thin;
		$weightRatherthin+=$rs->ratherthin;
		$weightWillowy+=$rs->willowy;
		$weightPlump+=$rs->plump;
		$weightGettingfat+=$rs->gettingfat;
		$weightFat+=$rs->fat;
	}

	$tables->tfoot[]=array(
										'ภาพรวมโรงเรียน',
										'','','','','','','','','','','',''
										);

	$ret.=$tables->build();



	$ret.='<style type="text/css">
	.item.-weightform caption {background:#FFAE00; color:#fff; font-size:1.4em;}
	.item.-weightform td:nth-child(2n+1) {}
	.item.-weightform td:nth-child(2n+2) {background:#efefef;}
	.item.-weightform td:nth-child(n+2) {width:60px;}
	.item.-weightform td:first-child, .item.-weightform td:nth-child(3) {color:#333;}
	.item td:nth-child(n+2) {text-align: center;}
	.item h3 {padding-left:10px;text-align:left; background:#9400FF; color:#fff;}
	.item .student {font-weight:bold;}
	.item .error td:nth-child(n+1) {background:red; color:#333;}
	.item .error td:nth-child(2),.item .error td:nth-child(3) {text-decoration:underline;}
	</style>';

	//$ret.=print_o($dbs,'$dbs');

	return $ret;
}

function __project_form_learn_gettitle($tranId) {
	$stmt='SELECT `trid`, `tpid`, `sorder`, `detail1` `year`, `detail2` `term`, `period`, `detail3` `area`, `detail4` `postby`, `date1` `dateinput` FROM %project_tr% WHERE `trid`=:trid LIMIT 1';
	$rs=mydb::select($stmt,':trid',$tranId);
	if ($rs->_num_rows) $rs->termperiod=$rs->term.':'.$rs->period;
	return $rs;
}


function __project_form_learn_remove($tpid,$tranId) {
	$stmt='DELETE FROM %project_tr% WHERE `trid`=:trid AND `tpid`=:tpid AND `formid`=:formid AND `part`="title"';
	mydb::query($stmt,':trid',$tranId,':tpid',$tpid, ':formid',_FORMID);
	$ret.=mydb()->_query.'<br />';

	$stmt='DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `parent`=:trid AND `formid`=:formid AND `part`=:formid';
	mydb::query($stmt,':trid',$tranId,':tpid',$tpid, ':formid',_FORMID);
	$ret.=mydb()->_query.'<br />';

	return $ret;
}

?>