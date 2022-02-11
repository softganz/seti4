<?php
/**
 * แบบประเมินการกินอาหารและการออกกำลังกายของนักเรียน
 *
 * @param Object $projectInfo
 * @param String $action
 * @param Int $tranId
 * @return String
 */
define(_KAMSAIINDICATOR,'schooleat');

function project_info_eat($self, $tpid, $action = NULL, $tranId = NULL) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get',$tpid, '{initTemplate: true}');
	$tpid = $projectInfo->tpid;

	R::View('project.toolbar', $self, $projectInfo->title, $projectInfo->submodule, $projectInfo,'{showPrint: true}');


	//$action=key((Array)$para);
	//if ($action=='_src') $action=null;
	//$tranId=$action?current((Array)$para):null;
	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;
	$isAdmin = $projectIn->RIGHT & _IS_ADMIN;

	$percentDigit=2;

	$ret = '';
	//$ret .= print_o($projectInfo,'projectInfo');
	//$ret.='trid='.$tranId.print_o($para,'$para');
	//$ret .= 'Action = '.$action;

	if ($tranId) $currentRs=__project_form_eat_gettitle($tranId);

	$navbar='<!--navbar start-->';
	$navbar.='<h3>สถานการณ์การกินอาหารและการออกกำลังกายของนักเรียน</h3>';

	$ui = new Ui();
	$subui=new ui();

	if ($isEdit && empty($action)) {
		$ret .= (new FloatingActionButton([
			'style' => 'width: 140px;',
			'children' => [
				'<a class="btn -floating -fill -hidden" href="'.url('project/'.$tpid.'/info.eat/create','area=โรงเรียน').'"><i class="icon -material">add</i><span>โรงเรียน</span></a>',
				'<a class="btn -floating -fill -hidden" href="'.url('project/'.$tpid.'/info.eat/create','area=บ้าน').'"><i class="icon -material">add</i><span>บ้าน</span></a>',
				'<a class="btn -floating"><i class="icon -material">add</i><span>เพิ่มสถานการณ์</span></a>',
			],
		]))->build();
	}
	$ui->add('<a class="btn -link" href="'.url('project/'.$tpid.'/info.eat').'"><i class="icon -list"></i><span>รายการบันทึก</span></a>');
	$ui->add('<a class="btn -link" href="'.url('project/'.$tpid.'/info.eat').'"><i class="icon -edit"></i><span>แก้ไขบันทึกสถานการณ์เดิม</span></a>');
	if ($tranId) $ui->add('<a class="btn -link" href="'.url('project/'.$tpid.'/info.eat/view/'.$tranId).'"><span>สถานการณ์ครั้งที่ '.$currentRs->sorder.'</span></a>');
	if ($tranId) {
		if ($isEdit) {
			$subui->add('<a class="" href="'.url('project/'.$tpid.'/info.eat/view/'.$tranId).'"><i class="icon -view"></i><span>ดูรายละเอียดบันทึกสถานการณ์</span></a>');
			$subui->add('<a class="" href="'.url('project/'.$tpid.'/info.eat/modify/'.$tranId).'"><i class="icon -edit"></i><span>แก้ไขบันทึกสถานการณ์เดิม</span></a>');
			$subui->add('<sep>');
			$subui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info.eat/remove/'.$tranId).'" data-title="ลบบันทึกสถานการณ์" data-confirm="ต้องการลบบันทึกนี้ กรุณายืนยัน"><i class="icon -delete"></i><span>ลบบันทึกสถานการณ์</span></a>');
		}
	}

	$subui->add('<sep>');
	$subui->add('<a class="" href="'.url('project/'.$tpid.'/info.eat/create','area=โรงเรียน').'"><i class="icon -add"></i><span>เพิ่มบันทึกสถานการณ์-โรงเรียน</span></a>');
	$subui->add('<a class="" href="'.url('project/'.$tpid.'/info.eat/create','area=บ้าน').'"><i class="icon -add"></i>เพิ่มบันทึกสถานการณ์-บ้าน</a>');

	$navbar .= '<nav class="nav -page -no-print">'
					.$ui->build('ul')
					.'</nav><!-- nav -->'._NL;
	$navbar .= ($subui->count() ? sg_dropbox($subui->build('ul'),'{class:"leftside -atright"}') : '');

	$self->theme->navbar=$navbar;

	switch ($action) {
		case 'create' :
			if ($isEdit) {
				if (post('checkdup')) {
					$r['isDup']=__project_form_eat_duplicate($tpid,NULL,post('area'),post('year'),post('termperiod'));
					$r['msg']='OK';
					$r['para']=print_o(post(),'post');
					die(json_encode($r));
				} else {
					$ret.=__project_form_eat_create($tpid,NULL,post('area'));
				}
			}
			return $ret;
			break;

		case 'modify' :
			if ($isEdit) {
				if (post('checkdup')) {
					$r['isDup']=__project_form_eat_duplicate($tpid,$tranId,post('area'),post('year'),post('termperiod'));
					$r['msg']='OK';
					$r['para']=print_o(post(),'post');
					$r['stmt']=mydb()->_query;
					die(json_encode($r));
				} else {
					$ret.=__project_form_eat_create($tpid,$tranId);
				}
			}
			return $ret;
			break;

		case 'view' :
			$ret.=__project_form_eat_view($tpid,$tranId,$isEdit);
			return $ret;
			break;

		case 'remove' :
			if ($isEdit && SG\confirm()) {
				$ret .= 'DELETE';
				$ret .= __project_form_eat_remove($tpid,$tranId);
				location('project/'.$tpid.'/info.eat');
			}
			return $ret;
			break;
	}



	// SHOW All transaction
	$stmt='SELECT
		tr.`trid`, tr.`tpid`, tr.`sorder`, tr.`detail1` `year`, tr.`detail2` `term`, tr.`period`,
		tr.`detail3` `area`, tr.`detail4` `postby`, tr.`date1` `dateinput`,
		SUM(q.`num5`) `bads`,
		SUM(q.`num6`) `fairs`,
		SUM(q.`num7`) `goods`,
		SUM(q.`num1`) total
		FROM %project_tr% tr
			LEFT JOIN %project_tr% q ON q.`parent`=tr.`trid`
		WHERE tr.`tpid`=:tpid AND tr.`formid`=:formid AND tr.`part`="title"
		GROUP BY tr.`sorder`
		ORDER BY `year` ASC,`term` ASC, `period` ASC';
	$dbs=mydb::select($stmt,':tpid',$tpid,':formid',_KAMSAIINDICATOR);
	//$ret.=print_o($dbs,'$dbs');

	$tables=new table('item -center -cols11');
	$tables->thead=array('ปีการศึกษา','ภาคการศึกษา','สถานที่','วันที่ชั่ง/วัด','ทำได้น้อย<br />(0-2 วันต่อสัปดาห์)<br />(%)','ทำได้ปานกลาง<br />(3-5 วันต่อสัปดาห์)<br />(%)','ทำได้ดี<br />(6-7 วันต่อสัปดาห์)<br />(%)','icons -c2'=>'');
	$no=0;
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			$rs->year+543,
			$rs->term.'/'.$rs->period,
			$rs->area,
			sg_date($rs->dateinput,'ว ดด ปป'),
			number_format($rs->bads*100/$rs->total,$percentDigit).'%',
			number_format($rs->fairs*100/$rs->total,$percentDigit).'%',
			number_format($rs->goods*100/$rs->total,$percentDigit).'%',
			'<nav class="nav -icons"><ul><li><a class="btn -link" href="'.url('project/'.$tpid.'/info.eat/view/'.$rs->trid).'"><i class="icon -view"></i></a></li>'
			.($isEdit?'<li><a class="btn -link" href="'.url('project/'.$tpid.'/info.eat/modify/'.$rs->trid).'"><i class="icon -edit"></i></a></li>':'')
			.'</ul></nav>'
		);
	}
	$ret.=$tables->build();

	//$ret.=print_o($dbs,'$dbs');
	//$ret.=print_o($topic,'$topic').print_o($para,'$para');

	return $ret;
}

function __project_form_eat_duplicate($tpid,$tranId,$area,$year,$termperiod) {
	list($term,$period)=explode(':',$termperiod);
	$stmt='SELECT * FROM %project_tr% WHERE `tpid`=:tpid AND `formid`=:formid AND `part`="title" AND `detail3`=:area AND `detail1`=:year AND `detail2`=:term AND `period`=:period '.($tranId ? 'AND `trid`!=:trid':'').' LIMIT 1';
	$rs=mydb::select($stmt,':tpid',$tpid, ':trid',$tranId, ':formid',_KAMSAIINDICATOR, ':area',$area, ':year',$year, ':term',$term, ':period',$period);
	$isDup=$rs->trid?$rs->trid:false;
	return $isDup;
}

function __project_form_eat_create($tpid,$tranId,$at='โรงเรียน') {
	$post = (object)post('eat');
	if ((array)$post) {
		if (!($tpid && $post->area && $post->year && $post->termperiod && $post->postby && $post->dateinput)) {
			return message('error','ข้อมูลไม่ครบถ้วน').print_o(post(),'post()');
		} else if (__project_form_eat_duplicate($tpid,$tranId,$post->area,$post->year,$post->termperiod)) {
			return message('error','ข้อมูลของปีการศึกษา '.($post->year+543)." ภาคการศึกษานี้ ได้มีการบันทึกข้อมูลไว้แล้ว ไม่สามารถบันทึกซ้ำได้!!!").print_o(post(),'post()');
		};
		$post->tpid=$tpid;
		$post->trid=$tranId;
		$post->formid=_KAMSAIINDICATOR;
		list($post->term,$post->period)=explode(':',$post->termperiod);
		$post->uid=i()->uid;
		$post->dateinput=sg_date($post->dateinput,'Y-m-d 00:00:00');
		$post->order=mydb::select('SELECT MAX(`sorder`) maxorder FROM %project_tr% WHERE `tpid`=:tpid AND `formid`=:formid AND `part`="title" LIMIT 1',':tpid',$tpid,':formid',_KAMSAIINDICATOR)->maxorder+1;
		//$ret.=mydb()->_query.'<br />';
		$post->created=date('U');
		$stmt='INSERT INTO %project_tr%
						(`trid`, `tpid`, `uid`, `formid`, `part`, `sorder`, `detail1`, `detail2`, `period`, `detail3`, `detail4` , `date1`, `created`)
						VALUES
						(:trid, :tpid, :uid, :formid, "title", :order, :year, :term, :period, :area, :postby, :dateinput, :created)
						ON DUPLICATE KEY UPDATE
						`detail1`=:year, `detail2`=:term, `period`=:period, `detail3`=:area, `detail4`=:postby, `date1`=:dateinput';
		mydb::query($stmt,$post);
		//$ret.=mydb()->_query.'<br />';
		if (!$tranId) $tranId=mydb()->insert_id;

		$qt=post('qt');
		$stmt='SELECT `trid`,`sorder`,`part`
						FROM %project_tr%
						WHERE `tpid`=:tpid AND `parent`=:trid
							AND `formid`=:formid AND `part`=:formid
						ORDER BY `sorder` ASC';
		foreach (mydb::select($stmt,':tpid',$tpid, ':trid',$tranId,':formid',_KAMSAIINDICATOR)->items as $item) {
			$qttrid[$item->sorder]=$item->trid;
		}
		//$ret.=mydb()->_query;
		//$ret.=print_o($qttrid,'$qttrid');
		foreach ($qt as $qtno => $qtarray) {
			unset($qtvalue);
			$qtvalue->trid=$qttrid[$qtno];
			$qtvalue->tpid=$tpid;
			$qtvalue->parent=$tranId;
			$qtvalue->uid=i()->uid;
			$qtvalue->sorder=$qtno;
			$qtvalue->formid=_KAMSAIINDICATOR;
			$qtvalue->part=_KAMSAIINDICATOR;
			$qtvalue->total=$qtarray['total'];
			$qtvalue->choice1=$qtarray['bad'];
			$qtvalue->choice2=$qtarray['fair'];
			$qtvalue->choice3=$qtarray['good'];
			$qtvalue->created=date('U');
			$stmt='INSERT INTO %project_tr%
						(
							`trid`, `tpid`, `parent`, `uid`, `sorder`, `formid`, `part`,
							`num1`, `num5`, `num6`, `num7`, `created`
						)
						VALUES
						(
							:trid, :tpid, :parent, :uid, :sorder, :formid, :part,
							:total, :choice1, :choice2, :choice3, :created
						)
						ON DUPLICATE KEY UPDATE
							`num1`=:total,
							`num5`=:choice1, `num6`=:choice2, `num7`=:choice3';
			mydb::query($stmt,$qtvalue);
			//$ret.=mydb()->_query.'<br />';
			//$ret.=$stmt.'<br />';
		}

		//$ret.=print_o($post,'$post');
		//$ret.=print_o($qt,'$qt');
		location('project/'.$tpid.'/info.eat'.($tranId?'/view/'.$tranId:''));
		//return $ret;
	} else if ($tranId) {
		$post=__project_form_eat_gettitle($tranId);
		$post->termperiod=$post->term.':'.$post->period;
	}

	$form = new Form([
		'variable' => 'eat',
		'action' => url('project/'.$tpid.'/info.eat/'.($tranId?'modify/'.$tranId:'create')),
		'id' => 'eat-add',
		'class' => 'container',
		'title' => '<h3>แบบประเมินการกินอาหารและการออกกำลังกายของนักเรียน - ที่'.$at.'</h3>',
		'children' => [
			'trid' => $tranId ? ['type' => 'hidden', 'value' => $tranId] : NULL,
			'<div class="row -flex">',
			'year' => [
				'type' => 'radio',
				'label' => 'ปีการศึกษา :',
				'require' => true,
				'options' => (function() {
					$options = [];
					for ($i = 2015; $i <= date('Y'); $i++) $options[$i] = $i+543;
					if (date('m') >= 10) $options[date('Y')] = date('Y')+543;
					return $options;
				})(),
				'value' => $post->year,
				'container' => '{class: "col -md-4"}',
			],
			'termperiod' => [
				'type' => 'radio',
				'label' => 'ภาคการศึกษา :',
				'require' => true,
				'options' => [
					'1:1' => 'ภาคการศึกษา 1 ต้นเทอม',
					'1:2' => 'ภาคการศึกษา 1 ปลายเทอม',
					'2:1' => 'ภาคการศึกษา 2 ต้นเทอม',
					'2:2' => 'ภาคการศึกษา 2 ปลายเทอม',
				],
				'value' => $post->termperiod,
				'container' => '{class: "col -md-4"}',
			],
			// 'period' => [
			// 	'type' => 'radio',
			// 	'label' => 'ช่วงเวลา :',
			// 	'require' => true,
			// 	'options' => ['1'=>'ก่อนทำโครงการ','2'=>'ระหว่างทำโครงการ','3'=>'หลังทำโครงการ'],
			// 	'value' => $post->period,
			// ],

			'area' => ['type' => 'hidden', 'value' => SG\getFirst(post('area'),$post->area)],
			'<div class="form-item col -md-4">',
			'postby' => [
				'type' => 'text',
				'label' => 'ผู้ประเมิน',
				'class' => '-fill',
				'require' => true,
				'value' => htmlspecialchars($post->postby),
			],
			'dateinput' => [
				'type' => 'text',
				'label' => 'วันที่ชั่ง/วัด',
				'class' => 'sg-datepicker -fill',
				'require' => true,
				'value' => htmlspecialchars($post->dateinput?sg_date($post->dateinput,'d/m/Y'):''),
				'placeholder' => '31/12/'.date('Y'),
			],
			'</div>',

			'</div>',

			(function($tpid, $tranId, $at) {
				$tables = new Table('item -std3');

				if ($at == 'โรงเรียน') {
					$tables->thead = ['no'=>'','พฤติกรรมการกินและการออกกำลังกายที่โรงเรียน (เฉพาะมื้อกลางวัน)','amt total'=>'จำนวนนักเรียน<br />(คน)','amt bad'=>'ทำได้น้อย<br />(0-1 วันต่อสัปดาห์)<br />(คน)','amt fair'=>'ทำได้ปานกลาง<br />(2-3 วันต่อสัปดาห์)<br />(คน)','amt good'=>'ทำได้ดี<br />(4-5 วันต่อสัปดาห์)<br />(คน)'];
				} else {
					$tables->thead = ['no'=>'','พฤติกรรมการกินและการออกกำลังกายที่บ้าน','amt total'=>'จำนวนนักเรียน<br />(คน)','amt bad'=>'ทำได้น้อย<br />(0-2 วันต่อสัปดาห์)<br />(คน)','amt fair'=>'ทำได้ปานกลาง<br />(3-5 วันต่อสัปดาห์)<br />(คน)','amt good'=>'ทำได้ดี<br />(6-7 วันต่อสัปดาห์)<br />(คน)'];
				}

				$stmt='SELECT
					  qt.`question`
					, qt.`qtgroup`
					, qt.`qtno`
					, tr.`parent`
					, tr.`part`
					, tr.`sorder`
					, tr.`num1` total
					, tr.`num5` bad
					, tr.`num6` fair
					, tr.`num7` good
					, qt.`description`
					FROM %qt% qt
						LEFT JOIN %project_tr% tr
							ON tr.`tpid` = :tpid AND tr.`parent` = :trid AND tr.`formid` = :formid AND tr.`part` = :formid
								AND tr.`part` = qt.`qtgroup` AND tr.`sorder` = qt.`qtno`
					WHERE `qtgroup` = :formid
					ORDER BY `qtgroup` ASC, `qtno` ASC';

				$qtResultDbs = mydb::select($stmt, ':trid', $tranId, ':tpid', $tpid, ':formid', _KAMSAIINDICATOR);

				$tables->rows[] = '<tr><td colspan="8"><h4>'.$stdName.'</h4></td></tr>';
				foreach ($qtResultDbs->items as $rs) {
					$radioName = 'qt['.$stdKey.']['.$rs->qtno.'][2]';
					$tables->rows[] = [
						$rs->qtno,
						$rs->question,
						'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][total]" value="'.number_format($rs->total,0,'.','').'" />',
						'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][bad]" value="'.number_format($rs->bad,0,'.','').'" />',
						'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][fair]" value="'.number_format($rs->fair,0,'.','').'" />',
						'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][good]" value="'.number_format($rs->good,0,'.','').'" />',
					];
					$subtotal += $rs->answer;
				}
				return $tables->build();
			})($tpid, $tranId, $at),

			'submit' => [
				'type' => 'button',
				'value' => '<i class="icon -save -white"></i><span>{tr:SAVE}</span>',
				'pretext' => '<a class="btn -link -cancel" href="'.url('project/'.$tpid.'/info.eat').'"><i class="icon -cancel -gray"></i><span>{tr:CANCEL}</span></a>',
				'container' => '{class: "-sg-text-right"}',
			],
		], // children
	]);

	$ret .= $form->build();

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
	$ret.='<script type="text/javascript">
	var i=0;
	var formSubmit=false;
	$("#eat-add").submit(function() {
		if (formSubmit) return true;
		var $form=$(this);
		var errorField;
		notify();
		if (!$("input[name=\'eat[year]\']:checked").val()) errorField="edit-eat-year";
		else if (!$("input[name=\'eat[termperiod]\']:checked").val()) errorField="edit-eat-termperiod";
		else if ($("#edit-eat-postby").val().trim()=="") errorField="edit-eat-postby";
		else if ($("#edit-eat-dateinput").val().trim()=="") errorField="edit-eat-dateinput";
		if (errorField) {
			var errorFieldLabel=$("#form-item-"+errorField+">label").text();
			notify("กรุณาป้อนข้อมูล :: "+errorFieldLabel,30000);
			$("#"+errorField).focus();
		} else {
			// Check year/termperiod is duplicate
			var para={}
			para.checkdup="yes";
			para.trid=$("#edit-eat-trid").val();
			para.year=$("input[name=\'eat[year]\']:checked").val();
			para.termperiod=$("input[name=\'eat[termperiod]\']:checked").val();
			para.area=$("#edit-eat-area").val();
			var url=$(this).attr("action");
			//notify("Check duplicate "+(++i)+url+para.year+para.termperiod);
			$.ajax({
				url: url,
				type: "POST",
				data: para,
				dataType: "json",
				success: function(data) {
						//notify("Result = "+data.isDup+"<br />"+data.para+data.stmt);
						if (data.isDup) {
							notify("ข้อมูลของปีการศึกษา "+(parseInt(para.year)+543)+" ภาคการศึกษานี้ ได้มีการบันทึกข้อมูลไว้แล้ว ไม่สามารถบันทึกซ้ำได้!!!",30000);
						} else {
							notify("กำลังบันทึกข้อมูล...");
							formSubmit=true;
							$form.submit();
						}
					},
			})
		}
		return false;
	});
	</script>';

	//$ret.=print_o($qtResultDbs,'$qtResultDbs');
	return $ret;
}

function __project_form_eat_view($tpid,$tranId,$isEdit) {
	$formid=_KAMSAIINDICATOR;

	$percentDigit=2;

	$title=__project_form_eat_gettitle($tranId);

	$stmt='SELECT
					  qt.`question`
					, qt.`qtgroup`
					, qt.`qtno`
					, tr.`trid`
					, tr.`part`
					, tr.`sorder`
					, tr.`num1` `total`
					, tr.`num5` bad
					, tr.`num6` fair
					, tr.`num7` good
				FROM %qt% qt
					LEFT JOIN %project_tr% tr
						ON tr.`tpid`=:tpid AND tr.`parent`=:trid AND tr.`formid`=:formid
							AND tr.`part`=qt.`qtgroup` AND tr.`sorder`=qt.`qtno`
				WHERE `qtgroup`=:formid
				ORDER BY `qtgroup` ASC, `qtno` ASC';
	$dbs=mydb::select($stmt,':trid',$tranId,':tpid',$tpid,':formid',_KAMSAIINDICATOR);

	$ret.='<div class="eat-header">ครั้งที่ : <strong>'.$title->sorder.'</strong> ปีการศึกษา : <strong>'.($title->year+543).'</strong> ภาคการศึกษา : <strong>'.$title->term.'</strong> ช่วงเวลา : <strong>'.$title->period.'</strong> พื้นที่ : <strong>'.$title->area.'</strong>  ผู้ประเมิน : <strong>'.$title->postby.'</strong> วันที่ชั่ง/วัด : <strong>'.sg_date($title->dateinput,'ว ดด ปป').'</strong>';

	$ret.='</div>';
	$tables=new table('item -eatform');
	$tables->colgroup=array('no'=>'','','amt student'=>'','amt bad'=>'','amt badpercent'=>'','amt fair'=>'','amt fairpercent'=>'','amt good'=>'','amt goodpercent'=>'');
	$tables->thead=array('no'=>'','พฤติกรรมการกินและการออกกำลังกาย','amt'=>'จำนวนนักเรียน<br />(คน)','<th colspan="2">ทำได้น้อย<br />(0-2 วันต่อสัปดาห์)<br />(คน)</th>','<th colspan="2">ทำได้ปานกลาง<br />(3-5 วันต่อสัปดาห์)<br />(คน)</th>','<th colspan="2">ทำได้ดี<br />(6-7 วันต่อสัปดาห์)<br />(คน)</th>');
	$rs->amt=rand(50,100);
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
											$rs->qtno,
											$rs->question,
											number_format($rs->total),
											number_format($rs->bad),
											$rs->total>0 ? number_format($rs->bad*100/$rs->total,$percentDigit).'%' : '-',
											number_format($rs->fair),
											$rs->total>0 ? number_format($rs->fair*100/$rs->total,$percentDigit).'%' : '-',
											number_format($rs->good),
											$rs->total>0 ? number_format($rs->good*100/$rs->total,$percentDigit).'%' : '-',
											);
	}
	$ret.=$tables->build();

	$ret.='<style type="text/css">
	.eat-header {margin: 8px 0;border: 2px #ccc solid; padding: 16px; background: #eee;}
	.badpercent, .fairpercent, .goodpercent {background:#efefef;color:#999;}
	.item.-eatform td:nth-child(n+3) {width:8%;}
	.item .student {font-weight:bold;}
	</style>';

	//$ret.=print_o($dbs,'$dbs');

	return $ret;
}

function __project_form_eat_gettitle($tranId) {
	$stmt='SELECT `trid`, `tpid`, `sorder`, `detail1` `year`, `detail2` `term`, `period`, `detail3` `area`, `detail4` `postby`, `date1` `dateinput` FROM %project_tr% WHERE `trid`=:trid LIMIT 1';
	$rs=mydb::select($stmt,':trid',$tranId);
	return $rs;
}

function __project_form_eat_remove($tpid,$tranId) {
	$stmt = 'DELETE FROM %project_tr%
					WHERE `tpid` = :tpid AND `formid` = "schooleat"
						AND (`trid` = :trid OR `parent` = :trid)';
	mydb::query($stmt, ':trid', $tranId, ':tpid', $tpid);
	$ret.=mydb()->_query.'<br />';

	//$stmt = 'DELETE FROM %project_tr% WHERE `tpid` = :tpid AND `parent` = :trid AND `formid` = "schooleat" AND `part` = "schooleat"';
	//mydb::query($stmt,':trid',$tranId,':tpid',$tpid);
	//$ret.=mydb()->_query.'<br />';

	return $ret;
}
?>