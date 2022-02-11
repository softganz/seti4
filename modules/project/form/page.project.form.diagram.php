<?php
/**
* Project diagram chart
*
* @param Object $self
* @param Object $topic
* @param Object $para
* @param Object $body
* @return String
*/
function project_form_diagram($self,$topic,$para,$body) {
	$tpid=$topic->tpid;
	$formid='property:project';
	$property=property('project::'.$tpid);
	$diagramsKey='SITUATION,PEOPLE,ENVIRONMENT,MECHANISM,OBJECTIVE,INDICATOR,METHOD,CAPTITAL,BUDGET,PERSONNEL,PERSONNEL,OTHERRESOURCE,PROCESS,OUTPUT,OUTCOME,IMPACT,TRACKING,EVALUATION';
	foreach (explode(',', $diagramsKey) as $k) {
		$diagrams[$k]=array('group'=>$formid,'fld'=>$k,'button'=>'yes','ret'=>'html');
	}

	$is_edit=($topic->project->project_statuscode==1) && (user_access('administer projects','edit own project content',$topic->uid) || project_model::is_owner_of($topic->tpid) || project_model::is_trainer_of($topic->tpid));
	$liketitle=$is_edit?'คลิกเพื่อแก้ไข':'';
	$editclass=$is_edit?'editable':'';
	$emptytext=$is_edit?'<span style="color:#999;">แก้ไข</span>':'';

	// $ret.=print_o($property,'$property');

	$inlinePara['class']='project-diagram';
	if ($is_edit) {
		$inlinePara['class'].=' inline-edit';
		$inlinePara['data-update-url']=url('project/edit/tr');
		if (post('debug')) $inlinePara['debug']='yes';
		foreach ($inlinePara as $k => $v) {
			$inlineStr.=$k.'="'.$v.'" ';
		}

		$emptyKey=array();
		foreach (explode(',', $diagramsKey) as $key) {
			if (trim($property[$key])!='') continue;
			$emptyKey[]=$key;
		}
		if ($emptyKey) {
			$ret.='<p class="notify">สร้างค่าเริ่มต้น "แผนภาพเชิงระบบของโครงการ" จากรายละเอียดของการพัฒนาโครงการ เรียบร้อย</p>';
			$property=__project_form_diagram_makedefault($tpid,$property,$emptyKey);
			foreach ($emptyKey as $key) {
				property('project:'.$key.':'.$tpid,$property[$key]);
			}
		}
	}

	$ret.='<div id="project-diagram" '.$inlineStr.'>'._NL;
	$ret.='<h3>แผนภาพเชิงระบบของโครงการ</h3>';

	$ret.='<div>';
	$ret.='<h4>สถานการณ์</h4>';
	$ret.='<h5>สถานการณ์สุขภาวะ</h5>'.view::inlineedit($diagrams['SITUATION'],$property['SITUATION'],$is_edit,'textarea');
	$ret.='<h5>ปัจจัยที่เป็นสาเหตุที่เกี่ยวข้องกับ</h5>';
	$ret.='<h5>คน :</h5>'.view::inlineedit($diagrams['PEOPLE'],$property['PEOPLE'],$is_edit,'textarea');
	$ret.='<h5>สภาพแวดล้อม :</h5>'.view::inlineedit($diagrams['ENVIRONMENT'],$property['ENVIRONMENT'],$is_edit,'textarea');
	$ret.='<h5>กลไก :'.view::inlineedit($diagrams['MECHANISM'],$property['MECHANISM'],$is_edit,'textarea');
	$ret.='</div>';

	$ret.='<div>';
	$ret.='<h4>จุดหมาย/วัตถุประสงค์/เป้าหมาย</h4>'.view::inlineedit($diagrams['OBJECTIVE'],$property['OBJECTIVE'],$is_edit,'textarea');
	$ret.='<h4>ปัจจัยสำคัญที่เอื้อต่อความสำเร็จ/ตัวชี้วัด</h4>'.view::inlineedit($diagrams['INDICATOR'],$property['INDICATOR'],$is_edit,'textarea');
	$ret.='<h4>วิธีการสำคัญ</h4>'.view::inlineedit($diagrams['METHOD'], $property['METHOD'],$is_edit,'textarea');
	$ret.='</div>';

	$ret.='<div>';
	$ret.='<h4>ปัจจัยนำเข้า</h4>
		<h5>ทุนของชุมชน</h5>'.view::inlineedit($diagrams['CAPTITAL'],$property['CAPTITAL'],$is_edit,'textarea').'
		<h5>งบประมาณ</h5>'.view::inlineedit($diagrams['BUDGET'],$property['BUDGET'],$is_edit,'text').' บาท
		<h5>บุคลากร</h5>'.view::inlineedit($diagrams['PERSONNEL'],$property['PERSONNEL'],$is_edit,'textarea').'
		<h5>ทรัพยากรอื่น</h5>'.view::inlineedit($diagrams['OTHERRESOURCE'],$property['OTHERRESOURCE'],$is_edit,'textarea');
	$ret.='</div>';

	$ret.='<div>';
	$ret.='<h4>ขั้นตอนทำงาน</h4>'.view::inlineedit($diagrams['PROCESS'],$property['PROCESS'],$is_edit,'textarea').'</div>';

	$ret.='<div>';
	$ret.='<h4>ผลผลิต</h4>'.view::inlineedit($diagrams['OUTPUT'],$property['OUTPUT'],$is_edit,'textarea');
	$ret.='<h4>ผลลัพธ์</h4>'.view::inlineedit($diagrams['OUTCOME'],$property['OUTCOME'],$is_edit,'textarea');
	$ret.='<h4>ผลกระทบ</h4>'.view::inlineedit($diagrams['IMPACT'],$property['IMPACT'],$is_edit,'textarea');
	$ret.='</div>';

	$ret.='<div>';
	$ret.='<h4>กลไกและวิธีการติดตามของชุมชน</h4>'.view::inlineedit($diagrams['TRACKING'],$property['TRACKING'],$is_edit,'textarea');
	$ret.='</div>';

	$ret.='<div>';
	$ret.='<h4>กลไกและวิธีการประเมินผลของชุมชน</h4>'.view::inlineedit($diagrams['EVALUATION'],$property['EVALUATION'],$is_edit,'textarea');
	$ret.='</div>';

	$ret.='</div><!-- project-diagram -->';

	unset($body->comment,$body->comment_form,$body->docs);
	return $ret;
}

function __project_form_diagram_makedefault($tpid,$property,$emptyKey) {
	$info=project_model::get_info($tpid);
	//print_o($info,'$info',1);

	$bigdata=new bigdata('project.develop',$tpid);
	foreach ($bigdata->getField('*','project.develop',$tpid) as $rs) $data[$rs->fldname]=$rs->flddata;

	//print_o($data,'$data',1);

	// สถานการณ์สุขภาวะ
	if (in_array('SITUATION', $emptyKey)) $property['SITUATION']=$data['project-problem'];

	// ปัจจัยที่เป็นสาเหตุที่เกี่ยวข้องกับ - คน
	if (in_array('PEOPLE', $emptyKey)) $property['PEOPLE']=$data['factor-human'];

	// ปัจจัยที่เป็นสาเหตุที่เกี่ยวข้องกับ - สภาพแวดล้อม
	if (in_array('ENVIRONMENT', $emptyKey)) $property['ENVIRONMENT']=$data['factor-environment'];

	// ปัจจัยที่เป็นสาเหตุที่เกี่ยวข้องกับ - กลไก
	if (in_array('MECHANISM', $emptyKey)) $property['MECHANISM']=$data['factor-mechanism'];

	// จุดหมาย/วัตถุประสงค์/เป้าหมาย
	if (in_array('OBJECTIVE', $emptyKey)) {
		$i=0;
		foreach ($info->objective as $rs) {
			$property['OBJECTIVE'].=++$i.'. '.$rs->title._NL;
		}
	}

	// ปัจจัยสำคัญที่เอื้อต่อความสำเร็จ/ตัวชี้วัด:
	if (in_array('INDICATOR', $emptyKey)) {
		$i=0;
		foreach ($info->objective as $rs) {
			$property['INDICATOR'].=$rs->indicator._NL;
		}
	}
	//if (in_array('INDICATOR', $emptyKey)) $property['INDICATOR']=$data['factor-human'];

	// วิธีการสำคัญ
	if (in_array('METHOD', $emptyKey)) $property['METHOD']='กลวิธีที่เกี่ยวข้องกับคน กลุ่มคน'._NL._NL.$data['strategies-human']._NL._NL.'กลวิธีที่เกี่ยวข้องกับการปรับสภาพแวดล้อม'._NL._NL.$data['strategies-environment']._NL._NL.'กลวิธีที่เกี่ยวข้องกับการสร้างและปรับปรุงกลไก'._NL._NL.$data['strategies-mechanism'];

	// ปัจจัยนำเข้า - ทุนของชุมชน
	if (in_array('CAPTITAL', $emptyKey)) $property['CAPTITAL']='คน'._NL._NL.$data['commune-leader']._NL._NL.'กลุ่ม องค์กร หน่วยงานและเครือข่าย'._NL._NL.$data['commune-org']._NL._NL.'วัฒนธรรม'._NL._NL.$data['commune-tradition']._NL._NL.'วิถีชีวิต ภูมิปัญญาและเศรษฐกิจชุมชน'._NL._NL.$data['commune-knowledge'];

	// งบประมาณ
	if (in_array('BUDGET', $emptyKey)) $property['BUDGET']=$info->project->budget;

	// บุคลากร
	if (in_array('PERSONNEL', $emptyKey)) {
		$property['PERSONNEL']='1. '.$data['owner-prename'].' '.$data['owner-name'].' '.$data['owner-lastname']._NL;
		for ($i=1;$i<=5;$i++) {
			if ($data['coowner-'.$i.'-name']) $property['PERSONNEL'].=($i+1).'. '.$data['coowner-'.$i.'-prename'].' '.$data['coowner-'.$i.'-name'].' '.$data['coowner-'.$i.'-lastname']._NL;
		}
		$property['PERSONNEL'].=_NL._NL.'แกนนำในชุมขน'._NL._NL.$data['name-mainstay'];
	}

	// ทรัพยากรอื่น

	if (in_array('OTHERRESOURCE', $emptyKey)) $property['OTHERRESOURCE']='ศูนย์เรียนรู้ หรือกระบวนการเรียนรู้ หรือการจัดการความรู้ ในชุมชน'._NL._NL.$data['commune-learningcenter']._NL._NL.'การทำงานร่วมกัน หรือกระบวนการมีส่วนร่วมของชุมชน'._NL._NL.$data['commune-participation']._NL._NL.'เครือข่ายเศรษฐกิจชุมชน'._NL._NL.$data['commune-cconomic'];

	// ขั้นตอนการทำงาน
	if (in_array('PROCESS', $emptyKey)) {
		$i=0;
		foreach ($info->mainact as $rs) {
			$property['PROCESS'].=++$i.'. '.$rs->title._NL;
		}
	}

	// ผลผลิต
	if (in_array('OUTPUT', $emptyKey)) {
		$i=0;
		foreach ($info->mainact as $rs) {
			if (trim($rs->output)!='') $property['OUTPUT'].=++$i.'. '.$rs->output._NL;
		}
	}

	// ผลลัพธ์
	if (in_array('OUTCOME', $emptyKey)) {
		$i=0;
		$mainact=project_model::get_main_activity($tpid);
		foreach ($mainact->info as $rs) {
			if (trim($rs->output)!='') $property['OUTCOME'].=++$i.'. '.$rs->output._NL;
		}
	}

	// ผลกระทบ
	if (in_array('IMPACT', $emptyKey)) $property['IMPACT']='การเปลี่ยนของคนและกลุ่มคนในชุมชน'._NL._NL.$data['conversion-human']._NL._NL.'การเปลี่ยนแปลงสภาพแวดล้อมในชุมชนที่เอื้อต่อชุมชนน่าอยู่'._NL._NL.$data['conversion-environment']._NL._NL.'การเปลี่ยนแปลงของกลไกในชุมชน'._NL._NL.$data['conversion-mechanism'];

	// กลไกและวิธีการติดตามของชุมชน
	if (in_array('TRACKING', $emptyKey)) $property['TRACKING']=$data['project-evaluation'];

	// กลไกและวิธีการประเมินผลของชุมชน
	if (in_array('EVALUATION', $emptyKey)) $property['EVALUATION']=$data['project-evaluation'];

/*
กลไก=MECHANISM=factor-mechanism
จุดหมาย/วัตถุประสงค์/เป้าหมาย=OBJECTIVE=project_tr:info:objective
ปัจจัยสำคัญที่เอื้อต่อความสำเร็จ/ตัวชี้วัด=INDICATOR=project_tr:info:objective
วิธีการสำคัญ=METHOD=กลวิธีที่เกี่ยวข้องกับคน กลุ่มคน:strategies-human / กลวิธีที่เกี่ยวข้องกับการปรับสภาพแวดล้อม:strategies-environment / กลวิธีที่เกี่ยวข้องกับการสร้างและปรับปรุงกลไก:strategies-mechanism

ปัจจัยนำเข้า
ทุนของชุมชน=CAPTITAL=คน:commune-leader / กลุ่ม องค์กร หน่วยงานและเครือข่าย:commune-org / วัฒนธรรม:commune-tradition / วิถีชีวิต ภูมิปัญญาและเศรษฐกิจชุมชน:commune-knowledge
งบประมาณ=BUDGET=
บุคลากร=PERSONNEL=owner-prename+owner-name+owner-lastname / coowner-1-prename+coowner-1-name+coowner-1-lastname ... name-mainstay
ทรัพยากรอื่น=OTHERRESOURCE=commune-learningcenter / commune-participation / commune-cconomic

ขั้นตอนทำงาน=PROCESS=project_tr:mainact:title

ผลผลิต=OUTPUT=project_tr:mainact:
ผลลัพธ์=OUTCOME=project_tr:mainact:
ผลกระทบ=IMPACT=conversion-human / conversion-environment / conversion-mechanism

กลไกและวิธีการติดตามของชุมชน=TRACKING=project-evaluation
กลไกและวิธีการประเมินผลของชุมชน=EVALUATION=project-evaluation
*/
	return $property;
}
?>