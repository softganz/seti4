<?php
function view_project_objective_view($projectInfo) {
	$tpid=$projectInfo->tpid;

	$isEdit=$projectInfo->RIGHT & _IS_EDITABLE;
	$isAdmin=$projectInfo->RIGHT & _IS_ADMIN;

	$objTypeList=array();
	foreach(mydb::select('SELECT `catid`,`name` FROM %tag% WHERE `taggroup`="project:objtype" ORDER BY `catid` ASC')->items as $item) $objTypeList[$item->catid]=$item->name;


	$objectiveNo=0;
	$ret.='<div id="project-objective">';
	foreach ($objTypeList as $objTypeId => $objTypeName) {
		if ($objTypeId==1) $ret.='<h3>วัตถุประสงค์โดยตรง</h3>';
		else if ($objTypeId==2) $ret.='<h3>วัตถุประสงค์โดยอ้อม</h3>';

		$ret.='<h4>'.$objTypeName.'</h4>';
		$ret.='<ol class="project-objective-list">';


		foreach ($projectInfo->objective as $objective) {
			if ($objective->objectiveType!=$objTypeId) continue;

			// Show for not editable
			if (!$isEdit) {
				$ret.='<li><b>'.$objective->title.'</b>';
				if ($objective->indicator['quantitative']) {
					$ret.='<p><b>ตัวชี้วัดเชิงปริมาณ</b></p>'._NL;
					$ret.='<ol>'._NL;
					foreach ($objective->indicator['quantitative'] as $indicatorId) {
						$indicator=$projectInfo->indicator[$indicatorId];
						$ret.='<li>'.$indicator->indicatorName.' เป้าหมายจำนวน '.number_format($indicator->amount).' '.$indicator->unit.'</li>'._NL;
					}
					$ret.='</ol>'._NL;
				}
				if ($objective->indicator['qualitative']) {
					$ret.='<p><b>ตัวชี้วัดเชิงคุณภาพ</b></p>'._NL;
					$ret.='<ol>'._NL;
					foreach ($objective->indicator['qualitative'] as $indicatorId) {
						$indicator=$projectInfo->indicator[$indicatorId];
						$ret.='<li>'.$indicator->indicatorName.'</li>'._NL;
					}
					$ret.='</ol>'._NL;
				}
				$ret.='</li>'._NL;
				continue;
			}

			// Show for editable
			++$objectiveNo;

			$ui=new Ui();
			$ui->add('<a href="'.url('project/objective/'.$tpid.'/move/'.$objective->trid).'" class="sg-action" title="ย้ายวัตถุประสงค์" data-rel="box">ย้ายวัตถุประสงค์</a>');
			$ui->add('<a class="sg-action" href="'.url('project/objective/'.$tpid.'/remove/'.$objective->trid).'" data-confirm="คุณต้องการลบวัตถุประสงค์นี้ แน่ใจหรือไม่ กรุณายืนยัน?" data-rel="none" data-removeparent=".project-objective-list>li">ลบวัตถุประสงค์</a>');
			$ui->add('<a class="sg-action -no-print" href="'.url('project/history',array('tpid'=>$tpid,'k'=>'tr,info,objective,text1,'.$objective->trid)).'" data-rel="box">ประวัติการแก้ไข</a>');

			if ($isAdmin) $ui->add('<a class="sg-action" href="'.url('project/objective/'.$tpid.'/info/'.$objective->trid).'" data-rel="box">ข้อมูลเฉพาะ</a>');
			$menu=$ui->count()?sg_dropbox($ui->build(),'{type:"click",class:"leftside hover-icon -li"}'):'';

			$ret.='<li>';
			$ret.='<big>'.view::inlineedit(array('group'=>'tr:info:objective', 'fld'=>'text1', 'tr'=>$objective->trid, 'class'=>'-fill -primary'), $objective->title, $isEdit, 'text').'</big>';
			$ret.=$menu;



			$ret.='<form id="form-1-'.$objective->trid.'" class="sg-form" action="'.url('project/objective/'.$tpid.'/addindicator/'.$objective->trid).'" data-rel="replace:#project-objective" data-checkvalid="true">';
			$i=$no=0;





			$tables = new Table();
			$tables->addClass('-indicator');
			$tables->addConfig('showHeader',false);
			$tables->thead=array('no'=>'ลำดับ','detail'=>'ชื่อตัวชี้วัด','amt -target'=>'เป้าหมาย/จำนวน','amt -unit'=>'หน่วยนับ<br />(เช่น คน,แห่ง)','icons'=>'');
			$tables->rows[]=array('<td colspan="5"><b>ตัวชี้วัดเชิงปริมาณ</b></td>');
			$tables->rows[]='<header>';
			foreach ($projectInfo->objective[$objective->trid]->indicator['quantitative'] as $indicatorId) {
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
			foreach ($projectInfo->objective[$objective->trid]->indicator['qualitative'] as $indicatorId) {
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
			$ret.='</li>'._NL;
		}
		$ret.='</ol>'._NL;
	}

	$ret.='<h3>วัตถุประสงค์ยุทธศาสตร์</h3>';
	$tables = new Table();
	$tables->thead=array('ตัวชี้วัด','amt'=>'สอดคล้อง');
	$tables->rows[]=array('การจัดการความรู้ นวัตกรรมและสื่อ','<input type="checkbox" />');
	$tables->rows[]=array('พัฒนาขีดความสามารถของคนและเครือข่าย (Health Litercy - PA)','<input type="checkbox" />');
	$tables->rows[]=array('สร้างพื้นที่ต้นแบบสุขภาวะ','<input type="checkbox" />');
	$tables->rows[]=array('ขับเคลื่อนนโยบาย PA ทั้งระดับชาติและระดับพื้นที่','<input type="checkbox" />');
	$tables->rows[]=array('องค์กรกีฬาเป็นกลไกในการขับเคลื่อนกิจกรรมทางกายและเป็นองค์กรที่มีนโยบายปลอดเหล้าบุหรี่','<input type="checkbox" />');
	$tables->rows[]=array('อื่น ๆ','<input type="checkbox" />');
	$ret.=$tables->build();

	if ($isEdit) {
		$ret.='<div id="project-objective-add" style="text-align:right;"><a class="sg-action btn -primary -no-print" data-rel="replace:#project-objective-add" href="'.url('project/objective/'.$tpid.'/form').'"><i class="icon -addbig -white"></i><span>เพิ่มวัตถุประสงค์ ข้อที่ '.($objectiveNo+1).'</span></a></div>';
	}
	$ret.='</div><!-- project-objective -->';
	return $ret;
}