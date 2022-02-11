<?php
/**
* iMed :: Home Visit Depress Form
* Created 2019-06-25
* Modify  2021-05-28
*
* @param Object $patientInfo
* @param Object $visitInfo
* @return String
*
* @usage imed/visit/{psnId}/form.depress/{seqId}
*/

$debug = true;

class ImedVisitFormDepress {
	var $patientInfo;
	var $visitInfo;
	var $refApp;
	var $formDone;

	function __construct($patientInfo, $visitInfo) {
		$this->patientInfo = $patientInfo;
		$this->visitInfo = $visitInfo;
		$this->refApp = post('ref');
		$this->formDone = post('formDone');
	}

	function build() {
		$psnId = $this->patientInfo->psnId;
		$seqId = $this->visitInfo->seq;

		$isEdit = is_admin('imed') || $this->visitInfo->uid == i()->uid;
		$isApp = R()->appAgent;

		if (!($isEdit || $this->visitInfo->seqId == -1)) return message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'msg' => 'Access denied']);

		$data = mydb::select('SELECT * FROM %imed_2q9q% WHERE `seq` = :seq LIMIT 1',':seq',$seqId);

		//$ret .= print_o($data,'$data');

		$ui = new Ui();
		if ($isEdit && $data->seq) $ui->add('<a class="sg-action" href="'.url('imed/api/visit/'.$psnId.'/depress.delete/'.$seqId).'" data-rel="none" data-done="close | load:#imed-visit-'.$seqId.':'.url('imed/visit/'.$psnId.'/item/'.$seqId, ['ref' => $this->refApp]).'" data-title="ลบแบบประเมินภาวะซึมเศร้า" data-confirm="ต้องการลบแบบประเมินภาวะซึมเศร้า กรุณายืนยัน?"><i class="icon -material">delete</i></a>');

		$ret .= '<header class="header">'._HEADER_BACK.'<h3>แบบประเมินภาวะซึมเศร้า</h3><nav class="nav">'.$ui->build().'</nav></header>';


		//$ret .= '<h4>แบบประเมินภาวะซึมเศร้า</h4>';


		$form=new Form([
			'variable' => 'data',
			'action' => url('imed/api/visit/'.$psnId.'/depress.save/'.SG\getFirst($seqId,-1)),
			'class' => 'sg-form',
			'rel' => 'none',
			'done' => 'back'.($seqId ? ' | load:#imed-visit-'.$seqId.':'.url('imed/visit/'.$psnId.'/item/'.$seqId,['ref' => $this->refApp]) : '').($this->formDone ? ' | callback:'.$this->formDone : ''),
			'children' => [
				'seqId' => ['type' => 'hidden','value' => $seqId],
				'ref' => ['type'=>'hidden','name'=>'ref','value'=>$this->refApp],
				'<h3>แบบคัดกรองโรคซึมเศร้า 2 คำถาม (2Q)</h3>',
			],
		]);
		$form->addField(
			'q2_1',
			array(
				'label'=>'1. ใน 2 สัปดาห์ที่ผ่านมา รวมวันนี้ ท่านรู้สึก หดหู่ เศร้า หรือท้อแท้สิ้นหวัง หรือไม?',
				'type'=>'radio',
				'options'=>array(
					1=>'มี',
					0=>'ไม่มี',
				),
				'value' => $data->q2_1 === NULL ? -1 : $data->q2_1,
				'container' => '{class: "-q2choice"}',
			)
		);
		$form->addField(
			'q2_2',
			array(
				'label'=>'2. ใน 2 สัปดาห์ที่ผ่านมา รวมวันนี้ ท่านรู้สึก เบื่อ ทำอะไรก็ไม่เพลิดเพลิน หรือไม?',
				'type'=>'radio',
				'class' => '-q2choice',
				'options'=>array(
					1=>'มี',
					0=>'ไม่มี',
				),
				'value' => $data->q2_2 === NULL ? -1 : $data->q2_2,
				'container' => '{class: "-q2choice"}',
				)
			);

		$form->addText('การแปรผล<br /> - ถ้าคำตอบ "ไม่มี" ทั้ง 2 คำถาม ถือว่า ปกติ ไม่เป็นโรคซึมเศร้า<br />- ถ้าคำตอบ "มี" ข้อใดข้อหนึ่งหรือทั้ง 2 ข้อ (มีอาการใดๆ ในคำถามที่ 1 และ 2) หมายถึง "เป็นผู้มีความเสี่ยง" หรือ "มีแนวโน้มที่จะเป็นโรคซึมเศร้า" ให้ประเมินต่อด้วยแบบประเมิน โรคซึมเศร้า 9Q');

		$form->addText('<section id="q9" class="'.($data->q2_score>0 ? '' : '-hidden').'">');

		$form->addText('<h3>แบบคัดกรองโรคซึมเศร้า 9 คำถาม (9Q)</h3>');

		$q9List = array(
			1 => 'เบื่อ ไม่สนใจอยากทำอะไร',
			2 => 'ไม่สบายใจ ซึมเศร้า ท้อแท้',
			3 => 'หลับยากหรือหลับ ๆ ตื่น ๆ หรือหลับมากไป',
			4 => 'เหนื่อยง่ายหรือไม่ค่อยมีแรง',
			5 => 'เบื่ออาหารหรือกินมากเกินไป',
			6 => 'รู้สึกไม่ดีกับตัวเอง คิดว่าตัวเองล้มเหลวหรือครอบครัวผิดหวัง',
			7 => 'สมาธิไม่ดี เวลาทำอะไร เช่น ดูโทรทัศน์ ฟังวิทยุ หรือทำงานที่ต้องใช้ความตั้งใจ',
			8 => 'พูดช้า ทำอะไรช้าลงจนคนอื่นสังเกตเห็นได้ หรือกระสับกระส่ายไม่สามารถอยู่นิ่งได้เหมือนที่เคยเป็น',
			9 => 'คิดทำร้ายตนเอง หรือคิดว่าถ้าตายไปคงจะดี',
		);

		foreach ($q9List as $key => $value) {
			$q9key = 'q9_'.$key;
			$form->addField(
				$q9key,
				array(
					'label' => $key.'. '.$value,
					'type'=>'radio',
					'options' => [
						0 => '0 = ไม่มีเลย',
						1 => '1 = เป็นบางวัน 1-7 วัน',
						2 => '2 = เป็นบ่อย > 7 วัน',
						3 => '3 = เป็นทุกวัน',
					],
					'value' => $data->{$q9key} === NULL ? -1 : $data->{$q9key},
					)
				);
		}

		$form->addText('<table class="item"><thead><tr><th>คะแนนรวม</th><th>การแปรผล</th></tr></thead><tbody><tr><td>< 7</td><td>ไม่มีอาการของโรคซึมเศร้าหรือมีอาการโรคซึมเศร้าระดับน้อยมาก</td></tr><tr><td>7 - 12</td><td>มีอาการของโรคซึมเศร้า ระดับน้อย</td></tr><tr><td>13 - 18</td><td>มีอาการของโรคซึมเศร้า ระดับปานกลาง</td></tr><tr><td>>= 19</td><td>มีอาการของโรคซึมเศร้า ระดับรุนแรง</td></tr></tbody></table>');

		$form->addText('</section>');

		$form->addField(
			'save',
			array(
				'type'=>'button',
				'value'=>'<i class="icon -save -white"></i><span>บันทึก</span>',
				'pretext'=>'<a class="sg-action btn -link -cancel" href="javascript:void(0)" data-rel="back"><i class="icon -cancel -gray"></i><span>{tr:CANCEL}</span></a>',
				'container' => array('class'=>'-sg-text-right'),
			)
		);

		$ret .= $form->build();

		//$ret.=print_o($data,'$data');

		$ret.='<style type="text/css">
		label.option {padding-left:16px;}
		</style>';

		$ret .= '<script type="text/javascript">
		$(".-q2choice .form-radio").change(function() {
			console.log("Change "+$(this).attr("id"))
			var qt2c1 = parseInt($("input[name=\'data[q2_1]\']:checked").val())
			var qt2c2 = parseInt($("input[name=\'data[q2_2]\']:checked").val())
			//if (isNaN(qt2c1)) qt2c1 = 0
			//if (isNaN(qt2c2)) qt2c2 = 0
			console.log(qt2c1)
			console.log(qt2c2)
			if (qt2c1 == 1 || qt2c2 == 1) {
				$("#q9").show()
			} else {
				$("#q9").hide()
			}
		})
		</script>';

		return $ret;
	}
}
?>