<?php

$qt['สภาพที่อยู่อาศัย']=array(
					'label'=>'สภาพที่อยู่อาศัย',
					'type'=>'radio',
					'group'=>'poor',
					'fld'=>'housingstatus',
					'option'=>'คงทนถาวร:คงทนถาวร, ชำรุด:ชำรุด',
					);

$qt['สถานะที่อยู่อาศัย']=array(
					'label'=>'สถานะที่อยู่อาศัย',
					'type'=>'radio',
					'group'=>'poor',
					'fld'=>'housingowner',
					'option'=>'บ้านของตนเอง:บ้านของตนเอง, เช่าบ้านผู้อื่น:เช่าบ้านผู้อื่น, อื่นๆ:อื่นๆ',
					);
$qt['สถานะที่อยู่อาศัย-อื่นๆ']=array(
					'label'=>'ระบุ',
					'type'=>'text',
					'group'=>'poor',
					'fld'=>'housingother',
					);
$qt['สภาพที่อยู่อาศัย-สภาพปัญหา']=array(
					'label'=>'สภาพปัญหา',
					'type'=>'textarea',
					'group'=>'poor',
					'fld'=>'housingproblem',
					'ret'=>'text',
					'desc'=>'** ให้ระบุบรรทัดละปัญหา **',
					);
$qt['สภาพที่อยู่อาศัย-ความต้องการ']=array(
					'label'=>'ความต้องการ',
					'type'=>'textarea',
					'group'=>'poor',
					'fld'=>'housingneed',
					'ret'=>'text',
					'desc'=>'** ให้ระบุบรรทัดละความต้องการ **',
					);
$qt['ต้องการเข้าร่วมโครงการบ้านมั่นคง']=array(
					'label'=>'ต้องการเข้าร่วมโครงการบ้านมั่นคง',
					'type'=>'radio',
					'group'=>'poor',
					'fld'=>'housingmankong',
					'option'=>'สนใจ:สนใจ, ไม่สนใจ:ไม่สนใจ',
					);




$qt['เครื่องนุ่งห่ม-สถานการณ์']=array(
					'label'=>'เครื่องนุ่งห่ม/ของใช้ในครัวเรือน',
					'type'=>'radio',
					'group'=>'poor',
					'fld'=>'clothesstatus',
					'option'=>'มีเพียงพอ:มีเพียงพอ, มีไม่เพียงพอ:มีไม่เพียงพอ',
					);
$qt['เครื่องนุ่งห่ม-สภาพปัญหา']=array(
					'label'=>'สภาพปัญหา',
					'type'=>'textarea',
					'group'=>'poor',
					'fld'=>'clothesproblem',
					'ret'=>'text',
					'desc'=>'** ให้ระบุบรรทัดละปัญหา **',
					);
$qt['เครื่องนุ่งห่ม-ความต้องการ']=array(
					'label'=>'ความต้องการ',
					'type'=>'textarea',
					'group'=>'poor',
					'fld'=>'clothesneed',
					'ret'=>'text',
					'desc'=>'** ให้ระบุบรรทัดละความต้องการ **',
					);





$qt['อาหาร-สถานการณ์']=array(
					'label'=>'อาหาร',
					'type'=>'radio',
					'group'=>'poor',
					'fld'=>'foodstatus',
					'option'=>'มีเพียงพอบริโภคในแต่ละวัน:มีเพียงพอบริโภคในแต่ละวัน, มีไม่เพียงพอบริโภคในแต่ละวัน:มีไม่เพียงพอบริโภคในแต่ละวัน',
					);
$qt['อาหาร-สภาพปัญหา']=array(
					'label'=>'สภาพปัญหา',
					'type'=>'textarea',
					'group'=>'poor',
					'fld'=>'foodproblem',
					'ret'=>'text',
					'desc'=>'** ให้ระบุบรรทัดละปัญหา **',
					);
$qt['อาหาร-ความต้องการ']=array(
					'label'=>'ความต้องการ',
					'type'=>'textarea',
					'group'=>'poor',
					'fld'=>'foodneed',
					'ret'=>'text',
					'desc'=>'** ให้ระบุบรรทัดละความต้องการ **',
					);




$qt['สุขภาพ-สถานการณ์']=array(
					'label'=>'สุขภาพ',
					'type'=>'radio',
					'group'=>'poor',
					'fld'=>'healthstatus',
					'option'=>'ทุกคนมีสุขภาพดี:ทุกคนมีสุขภาพดีและสามารถเข้าถึงบริการสาธารณสุข, มีคนป่วย:มีคนป่วย',
					);
$qt['สุขภาพ-สภาพปัญหา']=array(
					'label'=>'สภาพปัญหา',
					'type'=>'textarea',
					'group'=>'poor',
					'fld'=>'healthproblem',
					'ret'=>'text',
					'desc'=>'** ให้ระบุบรรทัดละปัญหา **',
					);
$qt['สุขภาพ-ความต้องการ']=array(
					'label'=>'ความต้องการ',
					'type'=>'textarea',
					'group'=>'poor',
					'fld'=>'healthneed',
					'ret'=>'text',
					'desc'=>'** ให้ระบุบรรทัดละความต้องการ **',
					);
$qt['สุขภาพ-คนป่วย']=array(
					'label'=>'จำนวนคนป่วย',
					'type'=>'text',
					'group'=>'poor',
					'fld'=>'healthpatient',
					);
$qt['สุขภาพ-โรคเรื้อรัง']=array(
					'label'=>'โรคเรื้อรัง (ความดัน/เบาหวาน ฯลฯ)',
					'type'=>'checkbox',
					'group'=>'poor',
					'fld'=>'healthchronic',
					'option'=>'1:โรคเรื้อรัง (ความดัน/เบาหวาน ฯลฯ)',
					);
$qt['สุขภาพ-คนพิการ']=array(
					'label'=>'คนพิการ',
					'type'=>'checkbox',
					'group'=>'poor',
					'fld'=>'healthdisabled',
					'option'=>'1:คนพิการ',
					);







$qt['การออม-สถานการณ์']=array(
					'label'=>'การออม',
					'type'=>'radio',
					'group'=>'poor',
					'fld'=>'savingstatus',
					'option'=>'มีการออม:มีการออม, ไม่มีการออม:ไม่มีการออม',
					);
$qt['การออม-สภาพปัญหา']=array(
					'label'=>'สภาพปัญหา',
					'type'=>'textarea',
					'group'=>'poor',
					'fld'=>'savingproblem',
					'ret'=>'text',
					'desc'=>'** ให้ระบุบรรทัดละปัญหา **',
					);
$qt['การออม-ความต้องการ']=array(
					'label'=>'ความต้องการ',
					'type'=>'textarea',
					'group'=>'poor',
					'fld'=>'savingneed',
					'ret'=>'text',
					'desc'=>'** ให้ระบุบรรทัดละความต้องการ **',
					);
$qt['การออม-เงินฝากธนาคาร']=array(
					'label'=>'เงินฝากธนาคาร',
					'type'=>'checkbox',
					'group'=>'poor',
					'fld'=>'savingbank',
					'option'=>'1:เงินฝากธนาคาร',
					);
$qt['การออม-กลุ่มออมทรัพย์/สัจจะ']=array(
					'label'=>'กลุ่มออมทรัพย์/สัจจะ',
					'type'=>'checkbox',
					'group'=>'poor',
					'fld'=>'savinggroup',
					'option'=>'1:กลุ่มออมทรัพย์/สัจจะ',
					);
$qt['การออม-สหกรณ์']=array(
					'label'=>'สหกรณ์',
					'type'=>'checkbox',
					'group'=>'poor',
					'fld'=>'savingcoop',
					'option'=>'1:สหกรณ์',
					);
$qt['การออม-แชร์']=array(
					'label'=>'แชร์',
					'type'=>'checkbox',
					'group'=>'poor',
					'fld'=>'savingshare',
					'option'=>'1:แชร์',
					);
$qt['การออม-อื่นๆ']=array(
					'label'=>'อื่นๆ',
					'type'=>'checkbox',
					'group'=>'poor',
					'fld'=>'savingother',
					'option'=>'1:อื่นๆ',
					);
$qt['การออม-ระบุ']=array(
					'label'=>'ระบุ',
					'type'=>'text',
					'group'=>'poor',
					'fld'=>'savingspecify',
					);





$qt['อาชีพ-รายได้รวม']=array(
					'label'=>'ทุกคนในครัวเรือนมีรายได้รวม',
					'type'=>'money',
					'group'=>'poor',
					'fld'=>'joballincome',
					'posttext'=>'บาท/เดือน',
					);
$qt['อาชีพ-รายได้เฉลี่ย']=array(
					'label'=>'รายได้เฉลี่ย/คน/เดือน',
					'type'=>'money',
					'group'=>'poor',
					'fld'=>'jobaverage',
					'posttext'=>'บาท',
					);
$qt['อาชีพ-ไม่มีรายได้']=array(
					'label'=>'สมาชิกที่ไม่มีรายได้',
					'type'=>'text',
					'group'=>'poor',
					'fld'=>'jobnoincome',
					'posttext'=>'คน',
					);
$qt['อาชีพ-สภาพปัญหา']=array(
					'label'=>'สภาพปัญหา',
					'type'=>'textarea',
					'group'=>'poor',
					'fld'=>'jobproblem',
					'ret'=>'text',
					'desc'=>'** ให้ระบุบรรทัดละปัญหา **',
					);
$qt['อาชีพ-อาชีพเสริม']=array(
					'label'=>'อาชีพเสริมเพื่อเพิ่มรายได้',
					'type'=>'checkbox',
					'group'=>'poor',
					'fld'=>'jobaddoccupation',
					'option'=>'1:อาชีพเสริมเพื่อเพิ่มรายได้',
					);
$qt['อาชีพ-อาชีพเสริม-ระบุ']=array(
					'label'=>'ระบุ',
					'type'=>'text',
					'group'=>'poor',
					'fld'=>'jobaddoccuspecify',
					);
$qt['อาชีพ-รับงานมาทำที่บ้าน']=array(
					'label'=>'รับงานมาทำที่บ้าน',
					'type'=>'checkbox',
					'group'=>'poor',
					'fld'=>'jobathome',
					'option'=>'1:รับงานมาทำที่บ้าน',
					);
$qt['อาชีพ-รับงานมาทำที่บ้าน-ระบุ']=array(
					'label'=>'ระบุ',
					'type'=>'text',
					'group'=>'poor',
					'fld'=>'jobathomespecify',
					);
$qt['อาชีพ-ลดรายจ่ายในครัวเรือน']=array(
					'label'=>'ลดรายจ่ายในครัวเรือน',
					'type'=>'checkbox',
					'group'=>'poor',
					'fld'=>'jobdecexp',
					'option'=>'1:ลดรายจ่ายในครัวเรือน',
					);
$qt['อาชีพ-อื่นๆ']=array(
					'label'=>'อื่นๆ',
					'type'=>'checkbox',
					'group'=>'poor',
					'fld'=>'jobother',
					'option'=>'1:อื่นๆ',
					);
$qt['อาชีพ-อื่นๆ-ระบุ']=array(
					'label'=>'ระบุ',
					'type'=>'text',
					'group'=>'poor',
					'fld'=>'jobotherspec',
					);




$qt['เศรษฐกิจพอเพียง-สถานการณ์']=array(
					'label'=>'ความรู้ความเข้าใจในปรัชญาเศรษฐกิจพอเพียง',
					'type'=>'radio',
					'group'=>'poor',
					'fld'=>'porpiangstatus',
					'option'=>'มี:สมาชิกมีความรู้ ความเข้าใจในปรัชญาเศรษฐกิจพอเพียง, ไม่มี:สมาชิกไม่มีความรู้ ความเข้าใจในปรัชญาเศรษฐกิจพอเพียง',
					);
$qt['เศรษฐกิจพอเพียง-ไม่มีความรู้']=array(
					'label'=>'กรณีไม่มีความรู้',
					'type'=>'radio',
					'group'=>'poor',
					'fld'=>'porpianginterest',
					'option'=>'สนใจ:สนใจเรียนรู้, ไม่สนใจ:ไม่สนใจเรียนรู้',
					);
$qt['เศรษฐกิจพอเพียง-ข้อเสนอแนะ']=array(
					'label'=>'ข้อเสนอแนะ',
					'type'=>'textarea',
					'group'=>'poor',
					'fld'=>'porpiangsuggestion',
					'ret'=>'text',
					'desc'=>'** ให้ระบุบรรทัดละปัญหา **',
					);



$qt['ความช่วยเหลือ']=array(
					'label'=>'ความช่วยเหลือ',
					'type'=>'textarea',
					'group'=>'poor',
					'fld'=>'govhelp',
					'ret'=>'text',
					'desc'=>'** ให้ระบุบรรทัดละความช่วยเหลือ **',
					);

$qt['ข้อเสนอแนะ']=array(
					'label'=>'ข้อเสนอแนะ สภาพปัญหาอื่น ๆ หรือความต้องการช่วยเหลือนอกเหนอจากประเด็นที่ระบุในข้างต้น',
					'type'=>'textarea',
					'group'=>'poor',
					'fld'=>'otherproblem',
					'ret'=>'text',
					'desc'=>'** ให้ระบุบรรทัดละความช่วยเหลือ **',
					);

$qt['ลำดับความสำคัญของปัญหา']=array(
					'label'=>'ลำดับความสำคัญของปัญหาที่ต้องการให้แก้ไขเร่งด่วน',
					'type'=>'textarea',
					'group'=>'poor',
					'fld'=>'problempiority',
					'ret'=>'text',
					'desc'=>'** ให้ระบุบรรทัดละความช่วยเหลือ **',
					);




$qt['prename']=array(
					'label'=>'คำนำหน้าชื่อ',
					'type'=>'text',
					'group'=>'person',
					);

$qt['name']=array(
					'label'=>'ชื่อ - นามสกุล',
					'type'=>'text',
					'group'=>'person',
					'class'=>'w-10',
					);

$qt['nickname']=array(
					'label'=>'ชื่อเล่น',
					'type'=>'text',
					'group'=>'person',
					);

$qt['cid']=array(
					'label'=>'หมายเลขบัตรประชาชน',
					'type'=>'text',
					'group'=>'person',
					'fld'=>'cid',
					);

$qt['educate']=array(
					'label'=>'ระดับการศึกษา',
					'type'=>'select',
					'group'=>'person',
					'option'=>imed_model::get_category('education'),
					);

$qt['occupa']=array(
					'label'=>'อาชีพ',
					'type'=>'select',
					'group'=>'person',
					'option'=>imed_model::get_category('occupation'),
					);

$qt['religion']=array(
					'label'=>'ศาสนา',
					'type'=>'select',
					'group'=>'person',
					'option'=>imed_model::get_category('religion'),
					);

$qt['reltohouseholder']=array(
					'label'=>'ความเกี่ยวข้องกับหัวหน้าครัวเรือน',
					'type'=>'select',
					'group'=>'poormember',
					'option'=>array('หัวหน้าครัวเรือน'=>'หัวหน้าครัวเรือน', 'เจ้าของบ้าน'=>'เจ้าของบ้าน', 'สามี'=>'สามี', 'ภรรยา'=>'ภรรยา', 'บิดา'=>'บิดา', 'มารดา'=>'มารดา','บุตร'=>'บุตร', 'ญาติ'=>'ญาติ', 'ผู้อาศัย'=>'ผู้อาศัย'),
					);


?>