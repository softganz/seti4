<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;


function org_mapping_mechanism($self, $orgId, $mapId = NULL, $action = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('org.get',$orgId, '{initTemplate: true}');
	$orgId = $orgInfo->orgid;

	$ret = '';

	$isEdit = $orgInfo->RIGHT & (_IS_ADMIN | _IS_OWNER | _IS_OFFICER) && $action == 'edit';

	$ret .= '<div id="org-mechanism" class="org-mechanism">';
	$stmt = 'SELECT b.*, t.`name`
					FROM %bigdata% b
						LEFT JOIN %tag% t ON t.`taggroup`="map:mechanism" AND t.`catid` = b.`flddata`
					WHERE b.`keyname` = "map" AND `keyid` = :tranId AND `fldname`  = "mechanism"
					ORDER BY `weight`';

	$dbs = mydb::select($stmt,':tranId',$mapId);
	//$ret .= print_o($dbs);

	$exceptMechanism = array(0);

	$ui = new Ui(NULL, 'ui-tag');
	foreach ($dbs->items as $rs) {
		$exceptMechanism[] = $rs->flddata;
		$menu = $isEdit ? '<a class="sg-action -hover" href="'.url('org/'.$orgId.'/mapping/mechanism.delete/'.$rs->bigid).'" data-rel="replace:#org-mechanism" data-ret="'.url('org/'.$orgId.'/mapping.mechanism/'.$mapId.'/edit').'" data-confirm="ลบรายการนี้ กรุณายืนยัน?"><i class="icon -remove -gray"></i></a>' : '';
		$ui->add($rs->name.$menu, '{class: "-hover-parent"}');
	}


	$ret .= $ui->build();

	if ($isEdit) {
		// Show mechanism form
		$orgMechanismList = R::Model('category.get','map:mechanism','catid', (object) array('result'=>'', 'condition'=>'tg.`catid` NOT IN ('.(implode(',',$exceptMechanism)).')','{debug:true}'));
		//$ret .= mydb()->_query.print_o('orgMechanismList');

		if ($orgMechanismList) {
			$form = new Form(NULL, url('org/'.$orgInfo->orgid.'/mapping/mechanism.add/'.$mapId),'org-mechanism-edit','sg-form -inlineitem');
			$form->addData('checkValid',true);
			$form->addData('rel','replace:#org-mechanism');
			$form->addData('ret',url('org/'.$orgInfo->orgid.'/mapping.mechanism/'.$mapId.'/edit'));

			$form->addField(
							'mechanism',
							array(
								//'label' => 'ประเด็นการทำงานขององค์กร:',
								'type' => 'select',
								'class' => '-fill',
								'options' => array('== เพิ่มกลไก ==')+$orgMechanismList,
								'attr' => array('onChange'=>'$(this).closest(\'form\').submit()'),
								'container' => array('class'=>'-inline'),
							)
						);
			$ret .= $form->build();
		}
	}
	//$ret.=print_o($orgInfo,'$orgInfo');
	//$ret.=print_o($dbs,'$dbs');
	$ret.='</div><!-- org-mechanism -->';
	return $ret;
}
?>