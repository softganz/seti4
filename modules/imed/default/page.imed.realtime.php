<?php
/**
* Home page of package imed
*
* @return String
*/
function imed_realtime($self) {
	$ret='<div id="imed-welcome"><h2 class="welcome">'.SG\getFirst(cfg('imed.welcome'),tr('Welcome to Online Clinic.','ยินดีต้อนรับสู่คลินิกรักษาโรคทางไกล')).'</h2></div>';
	$ret.='<div id="imed-intro">
	<h3>โปรดอ่าน</h3>
	<p>คลินิกรักษาโรคทางไกลเปิดบริการสำหรับสถานีอนามัยหรือสถานพยาบาล ที่ไม่มีแพทย์ประจำการ ได้มีโอกาสขอคำปรึกษาจากแพทย์ประจำศูนย์</p>
	<p><strong>บริการที่มีคือ</strong></p>
	<ul>
	<li>การให้คำปรึกษาแบบเรียลไทม์ (Realtime) : เป็นบริการให้คำปรึกษาด้วยภาพและเสียงผ่านทางโปรแกรมสไกป์ (Skype) ซึ่งบริการสำหรับสมาชิกเท่านั้น ท่านสามารถป้อน<strong>ชื่อสมาชิก/รหัสผ่าน</strong> เพื่อเริ่มใช้บริการ</li>
	<li><a href="'.url('forum/7').'">ทิ้งคำถาม-รอคำตอบ</a> : ท่านสามารถตั้งคำถาม เรามีทีมแพทย์ที่จะคอยตอบคำถามของท่าน ท่านสามารถ <a href="'.url('paper/post/forum/7').'">คลิกเพื่อทิ้งคำถาม</a> ไว้ได้เลย</li>
	<li><a href="'.url('forum/7/order/reply').'">คำถามตอบที่น่าสนใจ</a> : รวบรวมคำถาม-คำตอบที่น่าสนใจเพื่อศึกษาแนวทางในการรักษาอาการของโรคต่าง ๆ</li>
	</ul>
	<p><strong>ข้อจำกัดของระบบและผู้มีสิทธิ์สมัครสมาชิก</strong></p>
	</div>'._NL;

	$ret.='<div id="imed-memberzone">'._NL.'<h2>'.tr('Realtime Consulting','การปรึกษาเรียลไทม์').'</h2>'._NL.'<div id="imed-signform">'._NL.'<h3>เข้าสู่ระบบปรึกษาแบบเรียลไทม์</h3>'._NL;
	if (!i()->ok) {
		$ret .= R::View('signform','{action:"'.url('imed/realtime').'",cookielength:60}');
	} else if (!imed_model::is_member_of(NULL)) {
		$ret.='<p class="messages">ขออภัย - ท่านไม่ได้รับสิทธิ์ในการใช้งานระบบการรักษาทางไกล. กรุณาติดต่อผู้ดูแลระบบเพื่อกำหนดสิทธิ์ในการใช้งาน</p>';
	} else {
		$ret.='<p>หากมีปัญหาออนไลน์</p><p>เบอร์คลินิก 0883878083 เบอร์แฟกซ์ 074235494</p><p>สามารถโทรหรือแฟกซ์สอบถามมายังคลินิก</p><a class="go-imed" href="'.url('imed/realtime').'">'.tr('Enter Realtime Consulting','เข้าสู่การปรึกษาเรียลไทม์').'</a>';
	}
	$ret.='</div><!--imed-signform-->'._NL;
	$ret.='<div id="imed-member-notice"><p>สมาชิกที่ต้องการใช้บริการปรึกษาแบบเรียลไทม์ กรุณาลงทะเบียนก่อนเข้าสู่ระบบ หากท่านยังไม่ได้เป็นสมาชิก กรุณา <a href="'.url('imed/register').'">สมัครสมาชิก</a> ก่อน หรือ ติดต่อเบอร์คลินิก <strong>0883878083</strong></p><p><em>คลินิกการปรึกษาเรียลไทม์จะเปิดให้บริการเวลา 9.00-12.00 น. ทุกวันราชการ หากต้องการรักษาในเวลาอื่น ๆ ท่านสามารถติดต่อศูนย์ประสานงาน (center) ได้เป็นกรณีพิเศษ.</em></p></div>'._NL;
	$ret.='</div><!--imed-memberzone-->'._NL;

	$ret.='<p align="center">'.tr('If you install <img src="http://c.skype.com/i/images/logos/skype_logo.png" alt="Skype" />. You can call us <a href="skype:dlfpcenter1?call" title="Call with Skype"><img src="http://mystatus.skype.com/bigclassic/dlfpcenter1" style="border: medium none;" alt="DLFP Doctor status"></a> now.','หากท่านติดตั้ง<strong>โปรแกรมสไกป์ <img src="http://c.skype.com/i/images/logos/skype_logo.png" alt="Skype" /></strong> สามารถติดต่อศูนย์ให้คำปรึกษา <a href="skype:dlfpcenter1?call" title="Call with Skype"><img src="http://mystatus.skype.com/bigclassic/dlfpcenter1" style="border: medium none;" alt="DLFP Doctor status"></a> ได้เลย').'</p>';
	$ret.='<div id="imed-webboard">
	<h2>การปรึกษาผ่านกระดานสนทนา</h2>
	<p class="description">'.tr('You can ask question.','หากท่านมีปัญหาในการดูแลรักษาผู้ป่วย แต่ไม่มีอุปกรณ์สื่อสารแบบเรียลไทม์ สามารถทิ้งคำถามในกระดานสนทนา โดยจะมีผู้เชี่ยวชาญหรือสมาชิกท่านอื่นมาให้คำแนะนำ').'</p>';

	$form = new Form([
		'variable' => 'topic',
		'action' => url('paper/post/forum/7'),
		'id' => 'imed-ask-form',
		'children' => [
			'daykey' => ['name' => 'daykey', 'type' => 'hidden'],
			'tag' => [
				'name' => 'topic[taxonomy][3]',
				'type' => 'hidden',
				'value' => 7,
			],
			'title' => [
				'type' => 'text',
				'label' => tr('Question','หัวข้อคำถาม'),
				'class' => '-fill',
				'require' => true,
			],
			'body' => [
				'type' => 'textarea',
				'label' => tr('Detail','รายละเอียดคำถาม'),
				'class' => '-fill',
				'rows' => 4,
				'require' => true,
			],
			'poster' => [
				'type' => 'text',
				'label' => tr('Question By','ผู้ตั้งคำถาม'),
				'class' => '-fill',
				'value' => SG\getFirst(i()->name,tr('Anonymous','ไม่ระบุ')),
			],
			'email' => [
				'type' => 'text',
				'label' => tr('E-Mail'),
				'class' => '-fill',
			],
			'save' => [
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>{tr:Send Question,ส่งคำถาม}</span>',
				'container' => '{class: "-sg-text-right"}',
			],
		],
	]);

	$ret .= $form->build();


	$ret.='<script type="text/javascript">
	$(document).ready(function() {
	var d="'.poison::get_daykey(5,true).'";
	$("#imed-ask-form").submit(function() {
	$("#edit-daykey").val(d);
	});
	});
	</script>';

	$topics=model::get_paper('tag=7','order=view','sort=DESC','limit=10');

	$tables = new Table();
	$tables->thead=array('date'=>tr('Date','วันที่'),'title'=>tr('Question','คำถาม'),'amt reply'=>tr('Answers','ตอบ'),'amt view'=>tr('Views','อ่าน'));
	foreach ($topics->items as $rs) {
		$tables->rows[]=array(
			sg_date($rs->created,'ว-m-ปป H:i'),
			'<a href="'.url('paper/'.$rs->tpid).'">'.$rs->title.'</a>',
			$rs->reply?$rs->reply:'-',
			$rs->view,
		);
	}
	$ret.='<div id="imed-ans" class="topic-list"><h3 class="header">'.tr('Hot Questions','คำถามที่น่าสนใจ').'</h3>';

	$ret .= $tables->build();

	$ret.='<p class="readall"><a href="#">10 คำถามที่พบบ่อย</a> | <a href="#">คำถามที่รอแพทย์ตอบ</a> | <a href="'.url('forum/7').'">อ่านทั้งหมด &raquo;</a></p>';
	$ret.='</div>';

	$ret.='<div class="clear"><a href="'.url('forum/7').'">คำถามล่าสุด</a> | <a href="'.url('forum/7/order/last_reply').'">คำตอบล่าสุด</a> | <a href="'.url('forum/7/order/view').'">คำถามที่ดูกันมาก</a> | <a href="'.url('forum/7/order/reply').'">คำถามที่น่าสนใจ</a> | <a href="'.url('forum/7/order/reply/sort/asc').'">คำถามที่ยังไม่มีคำตอบ</a></div>';

	$stmt='SELECT t.tpid, t.title, t.poster,u.name, t.reply, NULL reply_by, t.view,
						t.created posted,
						t.created, t.last_reply
					FROM %topic% t
						LEFT JOIN %users% u ON u.uid=t.uid
					UNION
						SELECT tr.tpid, tr.title, tr.poster, tu.name, tr.reply,
							(SELECT c.name FROM %topic_comments% c WHERE c.tpid=tr.tpid AND c.timestamp=tr.last_reply LIMIT 1) reply_by,
							tr.view, tr.last_reply posted,tr.created,tr.last_reply
							FROM %topic% tr
								LEFT JOIN %users% tu ON tu.uid=tr.uid
					ORDER BY posted DESC
					LIMIT 40';
	$stmt='SELECT t.tpid, t.title, t.poster,u.name, t.reply, t.view,
						(SELECT IFNULL(c.name,cu.name)
							FROM %topic_comments% c
								LEFT JOIN %users% cu ON cu.uid=c.uid AND c.uid>0
							WHERE c.tpid=t.tpid AND c.timestamp=t.last_reply
							LIMIT 1) reply_by,
						IF(t.created>IFNULL(t.last_reply,0),t.created,t.last_reply) posted,
						t.created, t.last_reply
					FROM %topic% t
						LEFT JOIN %users% u ON u.uid=t.uid
					ORDER BY posted DESC
					LIMIT 40';

	$posted=mydb::select($stmt);
	unset($tables);

	$tables = new Table();
	$tables->thead=array('title'=>tr('Question','คำถาม'),'amt view'=>tr('Views','อ่าน'),'amt reply'=>tr('Answers','ตอบ'),'date posted'=>tr('Last Post','กระทู้ล่าสุด'));
	foreach ($posted->items as $rs) {
		$posttime=sg_remain2day(time()-sg_date($rs->posted,'U'));
		$tables->rows[]=array(
			'<a href="'.url('paper/'.$rs->tpid).'">'.$rs->title.'</a><span class="profile">'.tr('By ','โดย ').SG\getFirst($rs->poster,$rs->name,'ไม่ระบุ').'</span>',
			$rs->view,
			$rs->reply?$rs->reply:'-',
			$posttime.tr(' ago.',' ที่ผ่านมา ').($rs->reply?'<span class="profile">'.tr('By ','โดย ').SG\getFirst($rs->reply_by,'ไม่ระบุ').'</span>':''),
			'config'=>array('class'=>$rs->sticky==255?'sticky':''),
		);
	}
	$ret.='<div id="imed-ask" class="topic-list"><h3 class="header">'.tr('Left Question - Wait for an answer.','ทิ้งคำถาม - รอคำตอบ').'</h3>';
	$ret.=$tables->build();
	$ret.='</div>';

	$ret.='</div><!--imed-webboard-->'._NL;

	$ret.='<div id="recomment">หากต้องการใช้งานเว็บไซท์นี้ให้ดูดีที่สุด กรุณาใช้เบราส์เซอร์รุ่นใหม่ เช่น <a href="http://getfirefox.com/">ไฟร์ฟอกซ์ - Firefox</a> , <a href="http://www.microsoft.com/windows/internet-explorer/worldwide-sites.aspx">อินเตอร์เน็ตเอ็กโพลเรอร์ 8 - IE8</a> , <a href="http://www.google.com/chrome/?hl=th">กูเกิ้ลโครม - Google Chrome</a> หรือ <a href="http://www.apple.com/safari/download/">ซาฟารี - Safari</a> สืบเนื่องจากความสามารถบางอย่างที่ใช้งานไม่สามารถทำงานได้บนเบราส์เซอร์รุ่นเก่าเช่น IE6 , IE7 หากท่านมีปัญหาในการใช้งาน กรุณาเปลี่ยนไปใช้เบราส์เซอร์รุ่นใหม่ตามที่ระบุไว้</div>';
	head('<style>div.messages {display:none;}</style>');
	return $ret;
}
?>