<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function org_ah_operation_add($self, $orgId = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('org.get',$orgId, '{initTemplate: true}');
	$orgId = $orgInfo->orgid;
	
	R::View('org.toolbar',$self,'เขตสุขภาพ', 'ah', $orgInfo);

	$ret = '';

	$ret .= '<h3>ปฏิบัติการที่ 1 ถอดบทเรียนเส้นทางการพัฒนาและสอบทานศักยภาพเครือข่าย</h3>';

	$form = new Form();

	$form->addField(
					'issue',
					array(
						'type' => 'select',
						'label' => 'ชื่อประเด็น',
						'class' => '-fill',
						'options' => array('ด้านการเกษตร'),
					)
				);

	$form->addField(
					'issuename',
					array(
						'type' => 'text',
						'class' => '-fill',
					)
				);

	$form->addField(
					'network',
					array(
						'type' => 'text',
						'label' => 'เครือข่าย',
						'class' => '-fill',
					)
				);

	$form->addField(
					'desc',
					array(
						'type' => 'textarea',
						'label' => 'คำอธิบาย',
						'class' => '-fill',
						'rows' => 2,
					)
				);

	$form->addField(
					'spec',
					array(
						'type' => 'textarea',
						'label' => 'คุณลักษณะ',
						'class' => '-fill',
						'rows' => 2,
					)
				);

	$form->addField(
					'target',
					array(
						'type' => 'textarea',
						'label' => 'เป้าหมายการทำงาน',
						'class' => '-fill',
						'rows' => 2,
					)
				);

	$form->addField(
					'address',
					array(
						'type' => 'text',
						'label' => 'ระบุตำแหน่งสถานที่',
						'class' => '-fill',
					)
				);

	$form->addField(
					'lat',
					array(
						'type' => 'text',
						'label' => 'Latitude',
						'class' => '-fill',
					)
				);

	$form->addField(
					'lng',
					array(
						'type' => 'text',
						'label' => 'Longitude',
						'class' => '-fill',
					)
				);



	$form->addText('<div id="map-canvas" class="map-canvas" style="width: 100%; height: 600px;"></div><div>* ขยับหมุดในแผนที่ไปยังตำแหน่งที่ถูกต้องก่อนการบันทึก</div>');

	$form->addField(
					'save',
					array(
						'type' => 'button',
						'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
						'container' => '{class: "-sg-text-right"}',
					)
				);
	$ret .= $form->build();
	return $ret;
}
?>