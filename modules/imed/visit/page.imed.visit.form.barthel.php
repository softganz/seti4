<?php
/**
* iMed :: Bartel Index Survey Form
* Created 2019-03-05
* Modify  2021-05-28
*
* @param Object $patientInfo
* @param Object $visitInfo
* @return String
*
* @usage imed/visit/{psnId}/form.barthel/{seqId}
*/

$debug = true;

class ImedVisitFormBarthel {
	var $refApp;
	var $formDone;
	var $patientInfo;
	var $visitInfo;

	function __construct($patientInfo, $visitInfo) {
		$this->patientInfo = $patientInfo;
		$this->visitInfo = $visitInfo;
		$this->refAdd = post('ref');
		$this->formDone = post('formDone');
	}

	function build() {
		$psnId = $this->patientInfo->psnId;
		$seqId = $this->visitInfo->seq;

		$isEdit = is_admin('imed') || $this->visitInfo->uid == i()->uid;
		$isApp = R()->appAgent;

		if (!($isEdit || $this->visitInfo->seqId == -1)) return message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'msg' => 'Access denied']);

		$data = mydb::select('SELECT * FROM %imed_barthel% WHERE `seq` = :seq LIMIT 1',':seq',$seqId);

		$ret = '';

		$ui = new Ui();
		if ($isEdit && $data->seq) $ui->add('<a class="sg-action" href="'.url('imed/api/visit/'.$psnId.'/barthel.delete/'.$seqId).'" data-rel="none" data-done="load:#imed-visit-'.$seqId.':'.url('imed/visit/'.$psnId.'/item/'.$seqId, ['ref' => $this->refApp]).' | close" data-title="ลบดัชนีบาร์เธล" data-confirm="ต้องการลบดัชนีบาร์เธล กรุณายืนยัน?"><i class="icon -material">delete</i></a>');

		$ret .= '<header class="header">'._HEADER_BACK.'<h3>ดัชนีบาร์เธล (Barthel ADL index)</h3><nav class="nav">'.$ui->build().'</nav></header>';


		$ret .= '<h4>แบบประเมินความสามารถในการทำกิจวัตรประจำวันของผู้สูงอายุ ดัชนีบาร์เธล (Barthel ADL index)</h4>';
		$form = new Form([
			'variable' => 'data',
			'action' => url('imed/api/visit/'.$psnId.'/barthel.save/'.SG\getFirst($seqId,-1)),
			'class' => 'sg-form',
			'rel' => 'none',
			'done' => 'back'.($seqId ? ' | load:#imed-visit-'.$seqId.':'.url('imed/visit/'.$psnId.'/item/'.$seqId,['ref' => $this->refApp]) : '').($this->formDone ? ' | callback:'.$this->formDone : ''),
			'children' => [
				'seqId' => ['type' => 'hidden', 'value' => $seqId],
				// 'ref' => ['type'=>'hidden','name'=>'ref','value'=>$this->refApp],
				'qt01' => [
					'label'=>'1. การรับประทานอาหารเมื่อเตรียมสำรับไว้ให้เรียบร้อยต่อหน้า',
					'type'=>'radio',
					'options'=>array(
						0=>'0 = ไม่สามารถตักอาหารเข้าปากได้',
						1=>'1 = ตักอาหารเองได้ แต่ต้องมีคนช่วย เช่น ช่วยใช้ช้อนตักเตรียมไว้ให้ หรือตัดเป็นชิ้นเล็ก ๆ ไว้ล่วงหน้า',
						2=>'2 = ตักอาหารและช่วยตัวเองได้เป็นปกติ'
					),
					'value'=>$data->qt01===NULL?-1:$data->qt01,
				],
				'qt02' => [
					'label'=>'2. การล้างหน้าหวีผม โกนหนวด ในระยะ 24-48 ชั่วโมงที่ผ่านมา',
					'type'=>'radio',
					'options'=>array(
						0=>'0 = ต้องการความช่วยเหลือ',
						1=>'1 = ทำได้เอง (รวมทั้งที่ทำได้เองถ้าเตรียมอุปกรณ์ไว้ให้'
					),
					'value'=>$data->qt02===NULL?-1:$data->qt02,
				],
				'qt03' => [
					'label'=>'3. การขึ้น/ลงเตียงหรือลุกนั่งจากที่นอนไปยังเก้าอี้ได้',
					'type'=>'radio',
					'options'=>array(
						0=>'0 = ไม่สามารถนั่งได้ (นั่งแล้วจะล้มลงเสมอ) หรือต้องใช้คนสองคนช่วยกันยกขึ้น',
						1=>'1 = ต้องการความช่วยเหลืออย่างมากจึงจะนั่งได้ เช่น ต้องใช้คนที่แข็งแรงหรือมีทักษะ 1 คน หรือใช้คนทั่วไปช่วยพยุงหรือดันขึ้นมาจึงจะนั่งอยู่ได้',
						2=>'2 = ต้องการความช่วยเหลือบ้าง เช่น บอกให้ทำตามหรือช่วยพยุงเล็กน้อย หรือต้องมีคนดูแลเพื่อความปลอดภัย',
						3=>'3 = ทำได้เอง'
					),
					'value'=>$data->qt03===NULL?-1:$data->qt03,
				],
				'qt04' => [
					'label'=>'4. การใช้ห้องน้ำสุขา',
					'type'=>'radio',
					'options'=>array(
						0=>'0 = ช่วยตัวเองไม่ได้',
						1=>'1 = ทำเองได้บ้าง (อย่างน้อยทำความสะอาดตัวเองได้หลังจากเสร็จธุระ) แต่ต้องการความช่วยเหลือในบางสิ่ง',
						2=>'2 = ช่วยตนเองได้ดี (ขึ้นนั่งและลงจากโถส้วมเอง ทำความสะอาดตัวเองได้เรียบร้อยหลังจากเสร็จธุระ ถอดใส่เสื้อผ้าได้เรียบร้อย)'
					),
					'value'=>$data->qt04===NULL?-1:$data->qt04,
				],
				'qt05' => [
					'label'=>'5. การเคลื่อนที่ภายในห้องหรือบ้าน',
					'type'=>'radio',
					'options'=>array(
						0=>'0 = เคลื่อนที่ไหนไม่ได้',
						1=>'1 = ต้องใช้รถเข็นช่วยตัวเองให้เคลื่อนที่ได้ และจะต้องเข้าออกมุมห้องหรือประตูได้ (ไม่ต้องมีคนเข็น)',
						2=>'2 = เดินหรือเคลื่อนที่โดยมีคนช่วย เช่น พยุง หรือบอกให้ทำตาม หรือต้องให้ความสนใจดูแลเพื่อความปลอดภัย',
						3=>'3 = เดินหรือเคลื่อนที่ได้เอง'
					),
					'value'=>$data->qt05===NULL?-1:$data->qt05,
				],
				'qt06' => [
					'label'=>'6. การสวมใส่/ถอดเสื้อผ้า',
					'type'=>'radio',
					'options'=>array(
						0=>'0 = ต้องมีคนส่วมใส่ให้ ช่วยตนเองไม่ได้',
						1=>'1 = ช่วยตัวเองได้รายร้อยละ 50 ที่เหลือต้องมีคนช่วย',
						2=>'2 = ช่วยตัวเองได้ดี (รวมทั้งการติดกระดุม รูดซิปหรือใช้เสื้อผ้าที่ดัดแปลงให้เหมาะสมก็ได้)'
					),
					'value'=>$data->qt06===NULL?-1:$data->qt06,
				],
				'qt07' => [
					'label'=>'7. การขึ้นลงบันได',
					'type'=>'radio',
						'options'=>array(
							0=>'0 = ไม่สามารถทำได้',
							1=>'1 = ต้องการคนช่วย',
							2=>'2 = ขึ้นลงได้เอง (ถ้าต้องใช้เครื่องช่วยเดิน เช่น Walker จะต้องเอาขึ้นลงได้ด้วย)'
						),
					'value'=>$data->qt07===NULL?-1:$data->qt07,
				],
				'qt08' => [
					'label'=>'8. การอาบน้ำ',
					'type'=>'radio',
					'options'=>array(
						0=>'0 = ต้องมีคนช่วยหรือทำให้',
						1=>'1 = อาบน้ำเองได้'
					),
					'value'=>$data->qt08===NULL?-1:$data->qt08,
				],
				'qt09' => [
					'label'=>'9. การกลั้นการถ่ายอุจจาระในระยะ 1 สัปดาห์ที่ผ่านมา',
					'type'=>'radio',
					'options'=>array(
						0=>'0 = กลั้นไม่ได้หรือต้องการการสวนอุจจาระอยู่เสมอ',
						1=>'1 = กลั้นไม่ได้เป็นครั้งคราว (เป็นน้อยกว่า 1 ครั้งต่อสัปดาห์)',
						2=>'2 = กลั้นได้เป็นปกติ'
					),
					'value'=>$data->qt09===NULL?-1:$data->qt09,
				],
				'qt10' => [
					'label'=>'10. การกลั้นปัสสาวะในระยะ 1 สัปดาห์ที่ผ่านมา',
					'type'=>'radio',
					'options'=>array(
						0=>'0 = กลั้นไม่ได้หรือต้องการการสวนปัสสาวะอยู่เสมอ',
						1=>'1 = กลั้นไม่ได้เป็นครั้งคราว (เป็นน้อยกว่าวันละ 1 ครั้ง)',
						2=>'2 = กลั้นได้เป็นปกติ'
					),
					'value'=>$data->qt10===NULL?-1:$data->qt10,
				],
				'save' => [
					'type'=>'button',
					'value'=>'<i class="icon -save -white"></i><span>บันทึก</span>',
					'pretext'=>'<a class="sg-action btn -link -cancel" href="javascript:void(0)" data-rel="back"><i class="icon -cancel -gray"></i><span>{tr:CANCEL}</span></a>',
					'container' => array('class'=>'-sg-text-right'),
				],
			], // children
		]);

		$ret.=$form->build();

		//$ret.=print_o($data,'$data');

		$ret.='<style type="text/css">
		label.option {padding-left:16px;}
		</style>';


		return $ret;
	}
}
?>