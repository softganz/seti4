<?php
function view_project_objective_view($projectInfo) {
	$tpid=$projectInfo->tpid;

	$isEdit=$projectInfo->RIGHT & _IS_EDITABLE;
	$isAdmin=$projectInfo->RIGHT & _IS_ADMIN;

	$objTypeList=array();
	foreach(mydb::select('SELECT `catid`,`name` FROM %tag% WHERE `taggroup`="project:objtype" ORDER BY `catid` ASC')->items as $item) $objTypeList[$item->catid]=$item->name;

	$objectiveNo=0;
	$ret.='<div id="objective-tree" class="ui-tree -master -objective">';
	foreach ($objTypeList as $objTypeId => $objTypeName) {
		$ret.='<h4>'.$objTypeName.'</h4>';

		foreach ($projectInfo->objective as $objectiveItem) {
			if ($objectiveItem->objectiveType!=$objTypeId) continue;

			++$objectiveNo;

			// Show for not editable
			if (!$isEdit) {
				$ret.='<b>'.$objectiveNo.'. '.$objectiveItem->title.'</b>';
				if ($objectiveItem->indicator['quantitative']) {
					$ret.='<p><b>ตัวชี้วัดเชิงปริมาณ</b></p>'._NL;
					$ret.='<ol>'._NL;
					foreach ($objectiveItem->indicator['quantitative'] as $indicatorId) {
						$indicator=$projectInfo->indicator[$indicatorId];
						$ret.='<li>'.$indicator->indicatorName.' เป้าหมายจำนวน '.number_format($indicator->amount).' '.$indicator->unit.'</li>'._NL;
					}
					$ret.='</ol>'._NL;
				}
				if ($objectiveItem->indicator['qualitative']) {
					$ret.='<p><b>ตัวชี้วัดเชิงคุณภาพ</b></p>'._NL;
					$ret.='<ol>'._NL;
					foreach ($objectiveItem->indicator['qualitative'] as $indicatorId) {
						$indicator=$projectInfo->indicator[$indicatorId];
						$ret.='<li>'.$indicator->indicatorName.'</li>'._NL;
					}
					$ret.='</ol>'._NL;
				}
				continue;
			}

			// Show for editable

			// Show Tree Header
			$ret.='<div id="objective-header-'.$objectiveItem->trid.'" class="ui-item -header'.($isSubActivity?' -activity':'').'"><a class="title -showdetail" href="javascript:void(0)" data-rel="after">'
				.'<span class="-bullet">'.$pretext.($objectiveNo).'</span> '
				.'<span class="-title">'.$objectiveItem->title.'</span>'
				.'<i class="icon -up -gray"></i></a>'
				._NL;

			$ui=new Ui(NULL,'ui-menu -main -no-print');
			$dui=new Ui();
			if ($isEdit) {
				$dui->add('<a href="'.url('project/objective/'.$tpid.'/move/'.$objectiveItem->trid).'" class="sg-action" data-rel="box" title="ย้ายวัตถุประสงค์"><i class="icon -back"></i><span>ย้ายวัตถุประสงค์</span></a>');
				$dui->add('<a class="sg-action" href="'.url('project/objective/'.$tpid.'/remove/'.$objectiveItem->trid).'" data-confirm="คุณต้องการลบวัตถุประสงค์นี้ แน่ใจหรือไม่ กรุณายืนยัน?" data-rel="none" data-callback="treeRemove"><i class="icon -cancel"></i><span>ลบวัตถุประสงค์</span></a>');
				$dui->add('<a class="sg-action -no-print" href="'.url('project/history',array('tpid'=>$tpid,'k'=>'tr,info,objective,text1,'.$objectiveItem->trid)).'" data-rel="box">ประวัติการแก้ไข</a>');
			}
			if ($isAdmin) $dui->add('<a class="sg-action" href="'.url('project/objective/'.$tpid.'/info/'.$objectiveItem->trid).'" data-rel="box">ข้อมูลเฉพาะ</a>');
			$ui->add(sg_dropbox($dui->build()));
			$ret.=$ui->build();
			$ret.='</div>';


			/*
			$ui=new Ui();
			$menu=$ui->count()?sg_dropbox($ui->build(),'{type:"click",class:"leftside"}'):'';

			$ret.='<li class="ui-item">';
			$ret.='<big>'.view::inlineedit(array('group'=>'tr:info:objective', 'fld'=>'text1', 'tr'=>$objectiveItem->trid, 'class'=>'-fill -primary'), $objectiveItem->title, $isEdit, 'text').'</big>';
			$ret.=$menu;
			*/


			$ret.='<div class="ui-item -child -x-hidden">';
			$ret.='<div id="objective-detail-'.$objectiveItem->trid.'" class="ui-item -detail -init">';

			$ret.='<div class="-no-print"><b>วัตถุประสงค์</b><br />'
				.view::inlineedit(
					array(
						'group'=>'tr:info:objective',
						'fld'=>'text1',
						'tr'=>$objectiveItem->trid,
						'class'=>'-fill -primary',
						'callback'=>'projectObjectiveTitleUpdate'
						),
					$objectiveItem->title,
					$isEdit,
					'text'
					)
				.'</div>';

			$ret.='<form id="form-1-'.$objectiveItem->trid.'" class="sg-form" action="'.url('project/objective/'.$tpid.'/addindicator/'.$objectiveItem->trid).'" data-rel="replace:#objective-tree" data-checkvalid="true">';

			$i=$no=0;

			$tables = new Table();
			$tables->addClass('-indicator');
			$tables->addConfig('showHeader',false);
			$tables->thead=array('no'=>'ลำดับ','detail'=>'ชื่อตัวชี้วัด','amt -target'=>'เป้าหมาย/จำนวน','amt -unit'=>'หน่วยนับ<br />(เช่น คน,แห่ง)','icons'=>'');
			$tables->rows[]=array('<td colspan="5"><b>ตัวชี้วัดเชิงปริมาณ</b></td>');
			$tables->rows[]='<header>';

			foreach ($projectInfo->objective[$objectiveItem->trid]->indicator['quantitative'] as $indicatorId) {
				$value=$projectInfo->indicator[$indicatorId];
				$ui=new Ui();
				$ui->add('<a class="sg-action" href="'.url('project/objective/'.$tpid.'/removeindicator/'.$value->indicatorId).'" data-rel="none" data-confirm="ต้องการลบตัวชี้วัดนี้ กรุณายืนยัน?" data-removeparent="tr"><i class="icon -delete"></i><span>ลบตัวชี้วัด</span></a>');
				$ui->add('<a class="sg-action -no-print" href="'.url('project/history',array('tpid'=>$tpid,'k'=>'tr,info,indicator,detail1,'.$value->indicatorId)).'" data-rel="box"><i class="icon -list"></i><span>ประวัติการแก้ไข</span></a>');
				$menu=$ui->count()?sg_dropbox($ui->build(),'{type:"click",class:"leftside hover-icon -li"}'):'';

				$row=array(
							++$no,
							view::inlineedit(array('group'=>'tr:info:indicator','fld'=>'detail1','tr'=>$value->indicatorId, 'class'=>'-fill'),$value->indicatorName,$isEdit,'text'),
							view::inlineedit(array('group'=>'tr:info:indicator','fld'=>'num1','tr'=>$value->indicatorId, 'class'=>'-numeric -fill','ret'=>'numeric'),number_format($value->amount),$isEdit,'text'),
							view::inlineedit(array('group'=>'tr:info:indicator','fld'=>'detail2','tr'=>$value->indicatorId, 'class'=>'-fill'),$value->unit,$isEdit,'text'),
							$menu,
							);

				$tables->rows[]=$row;
				++$i;
			}


			$tables->rows[]=array(
												'<td></td>',
												'<div class="form-item"><input class="form-text -fill -showbtn" type="text" name="quantitative" placeholder="ระบุตัวชี้วัดเชิงปริมาณ ข้อที่ '.($i+1).'" /></div>',
												'<div class="form-item" style="display:none;"><input class="form-text -numeric -fill" type="text" name="amount" placeholder="0" /></div>',
												'<div class="form-item" style="display:none;"><input class="form-text -fill" type="text" name="unit" /></div>',
												'<div class="form-item" style="display:none;"><button class="btn"><i class="icon -add"></i><span>เพิ่มตัวชี้วัด</span></button></div>',
												'config'=>array('class'=>'-no-print')
												);



			$tables->rows[]=array('<td colspan="5"><b>ตัวชี้วัดเชิงคุณภาพ</b></td>');
			$tables->rows[]='<header>';
			$no=0;

			foreach ($projectInfo->objective[$objectiveItem->trid]->indicator['qualitative'] as $indicatorId) {
				$value=$projectInfo->indicator[$indicatorId];
				$ui=new Ui();
				$ui->add('<a class="sg-action" href="'.url('project/objective/'.$tpid.'/removeindicator/'.$value->indicatorId).'" data-rel="none" data-confirm="ต้องการลบตัวชี้วัดนี้ กรุณายืนยัน?" data-removeparent="tr"><i class="icon -delete"></i><span>ลบตัวชี้วัด</span></a>');
				$ui->add('<a class="sg-action -no-print" href="'.url('project/history',array('tpid'=>$tpid,'k'=>'tr,info,indicator,detail1,'.$value->indicatorId)).'" data-rel="box"><i class="icon -list"></i><span>ประวัติการแก้ไข</span></a>');
				$menu=$ui->count()?sg_dropbox($ui->build(),'{type:"click",class:"leftside hover-icon -li"}'):'';

				$row=array(
							++$no,
							'<td colspan="3">'.view::inlineedit(array('group'=>'tr:info:indicator','fld'=>'detail1','tr'=>$value->indicatorId, 'class'=>'-fill'),$value->indicatorName,$isEdit,'text').'</td>',
							$menu,
							);
				$tables->rows[]=$row;
				++$i;
			}


			$tables->rows[]=array(
												'<td></td>',
												'<td colspan="3"><input class="form-text -fill -showbtn" type="text" name="qualitative" placeholder="ระบุตัวชี้วัดเชิงคุณภาพ ข้อที่ '.($no+1).'" /></td>',
												'<div class="form-item" style="display:none;"><button class="btn"><i class="icon -add"></i><span>เพิ่มตัวชี้วัด</span></button></div>',
												'config'=>array('class'=>'-no-print')
												);

			$ret.=$tables->build();
			$ret.='</form>'._NL;

			$ret.='</div>'._NL;
			$ret.='</div>'._NL;
		}
	}

	if ($isEdit) {
		$ret.='<div id="project-objective-add" style="text-align:right;"><a class="sg-action btn -primary -no-print" data-rel="replace:#project-objective-add" href="'.url('project/objective/'.$tpid.'/form').'"><i class="icon -addbig -white"></i><span>เพิ่มวัตถุประสงค์ ข้อที่ '.($objectiveNo+1).'</span></a></div>';
	}

	$stmt='SELECT `trid`,`tpid`,`refid` `strategyId` FROM %project_tr% WHERE `tpid`=:tpid AND `parent` IS NULL AND `formid`="info" AND `part`="strategy"; -- {key:"strategyId"}';
	$projectStrategy=mydb::select($stmt,':tpid',$tpid)->items;

	//$ret.=print_o($projectStrategy,'$projectStrategy');
	$strategyList=array(
									1=>'การจัดการความรู้ นวัตกรรมและสื่อ',
									'พัฒนาขีดความสามารถของคนและเครือข่าย (Health Litercy - PA)',
									'สร้างพื้นที่ต้นแบบสุขภาวะ	- ที่บ้าน',
									'สร้างพื้นที่ต้นแบบสุขภาวะ	- ที่โรงเรียน',
									'สร้างพื้นที่ต้นแบบสุขภาวะ	- ที่องค์กร',
									'สร้างพื้นที่ต้นแบบสุขภาวะ	- ที่ชุมชน',
									'ขับเคลื่อนนโยบาย PA ทั้งระดับชาติและระดับพื้นที่',
									'องค์กรกีฬาเป็นกลไกในการขับเคลื่อนกิจกรรมทางกายและเป็นองค์กรที่มีนโยบายปลอดเหล้าบุหรี่',
									99=>'อื่น ๆ',
									);
	$ret.='<h4>วัตถุประสงค์ยุทธศาสตร์</h4>';
	$no=0;
	$tables = new Table();
	$tables->thead=array('ตัวชี้วัด','amt'=>'สอดคล้อง');
	foreach ($strategyList AS $strategyKey=>$strategyName) {
		$tables->rows[]=array(
											$strategyName,
											'<input type="checkbox" data-type="checkbox" '
												.'class="inline-edit-field '.($isEdit?'':'-disabled').'" '
												.'name="refid[]" '
												.'data-group="info:strategy:'.$strategyKey.'" data-fld="refid" '
												.'data-tr="'.$projectStrategy[$strategyKey]->trid.'" '
												.'value="'.$strategyKey.'" '
												.(array_key_exists($strategyKey, $projectStrategy)?'checked="checked"':'').' '
												.'data-removeempty="yes" '
												.'/>'
												);
	}
	$ret.=$tables->build();

	$ret.='</div><!-- project-objective -->';
	return $ret;
}