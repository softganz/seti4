<?php
/**
* Prosthesis and Orthosis Center
*
* @param Object $self
* @param Int $orgId
* @return String
*/

$debug = true;

function imed_patient_po($self, $psnId, $action = NULL, $tranId = NULL) {
	$psnId = SG\getFirst($psnId,post('id'));

	$psnInfo = is_object($psnId) ? $psnId : R::Model('imed.patient.get', $psnId, '{}');
	$psnId = $psnInfo->psnId;

	if (empty($psnInfo)) return message('error','ไม่มีข้อมูล');


	$ret = '';

	$isAccess = $psnInfo->RIGHT & _IS_ACCESS;
	$isEdit = $psnInfo->RIGHT & _IS_EDITABLE;


	R::View('imed.toolbar',$self,$psnInfo->fullname.' @กายอุปกรณ์','none');

	switch ($action) {
		case 'add':
			$data = new stdClass;
			$data->psnid = $psnId;
			$ret .= __imed_patient_po_form($data);
			break;

		case 'edit':
			// Only user who can edit or owner of tran
			$stmt = 'SELECT * FROM %po_stktr% tr WHERE `stktrid` = :stktrid AND `psnid` = :psnid AND `orgid` IS NULL LIMIT 1';
			$data = mydb::select($stmt, ':stktrid', $tranId, ':psnid', $psnId);
			if ($data->stktrid && ($isEdit || i()->uid == $data->uid)) {
				$ret .= __imed_patient_po_form($data);
			}
			break;

		case 'save':
			$data = (Object) post('stk');
			$canSave = true;
			if ($data->stktrid) {
				// Only user who can edit or owner of tran
				$stmt = 'SELECT * FROM %po_stktr% tr WHERE `stktrid` = :stktrid AND `psnid` = :psnid AND `orgid` IS NULL LIMIT 1';
				$rs = mydb::select($stmt, ':stktrid', $data->stktrid, ':psnid', $psnId);
				//$ret .= print_o($rs,'$rs');
				if ($rs->stktrid && ($isEdit || i()->uid == $rs->uid)) {
					$canSave = true;
				} else {
					$canSave = false;
				}
			}
			if ($canSave) {
				$result = R::Model('imed.po.tran.save', $data);
				$ret .= 'บันทึกการมีกายอุปกรณ์เรียบร้อย';
				//$ret .= print_o($result,'$result');
				//$ret .= print_o($data,'$data');
			}
			break;

		case 'delete':
			// Only user who can edit or owner of tran
			$stmt = 'SELECT * FROM %po_stktr% tr WHERE `stktrid` = :stktrid AND `psnid` = :psnid AND `orgid` IS NULL LIMIT 1';
			$rs = mydb::select($stmt, ':stktrid', $tranId, ':psnid', $psnId);
			//$ret .= print_o($rs,'$rs');
			if ($rs->stktrid && ($isEdit || i()->uid == $rs->uid)) {
				$stmt = 'DELETE FROM %po_stktr% WHERE `stktrid` = :stktrid LIMIT 1';
				mydb::query($stmt, ':stktrid', $tranId);
				//$ret .= mydb()->_query;
				$ret .= 'ลบรายการกายอุปกรณ์เรียบร้อย';
			}
			break;

		default:
			$ret .= '<section id="imed-patient-po">';

			$stmt = 'SELECT
					tr.*
					, c.`name` `stkName`
					, cat.`cat_name` `statusText`
					FROM %po_stktr% tr
						LEFT JOIN %imed_stkcode% c USING(`stkid`)
						LEFT JOIN %co_category% cat ON cat.`cat_id` = tr.`status`
					WHERE `psnid` = :psnid
					ORDER BY tr.`stkdate` ASC';
			$dbs = mydb::select($stmt, ':psnid', $psnId);


			$tables = new Table();
			$tables->id = 'imed-patient-prosthetic';
			$tables->caption = 'กายอุปกรณ์';
			$tables->thead = array('date'=>'วันที่ได้รับ', 'รายการ', 'amt'=>'จำนวน','status'=>'สถานะ', 'org -hover-parent' => 'หน่วยงาน');

			foreach ($dbs->items as $rs) {
				$menu = '';
				if (empty($rs->orgid) && ($isEdit || i()->uid == $rs->uid)) {
					$ui = new Ui();
					$ui->add('<a class="sg-action" href="'.url('imed/patient/'.$psnId.'/po/edit/'.$rs->stktrid).'" data-rel="box" data-width="480" data-height="80%"><i class="icon -material">edit</i></a>');
					$ui->add('<a class="sg-action" href="'.url('imed/patient/'.$psnId.'/po/delete/'.$rs->stktrid).'" data-rel="notify" data-title="ลบรายการกายอุปกรณ์" data-confirm="ต้องการลบรายการกายอุปกรณ์ กรุณายืนยัน?" data-done="remove:parent tr"><i class="icon -material -gray">cancel</i></a>');
					$menu = '<nav class="nav -icons -hover">'.$ui->build().'</nav>';
				}
				$tables->rows[] = array(
					$rs->stkdate ? sg_date($rs->stkdate,'ว ดด ปปปป') : '',
					$rs->stkName,
					number_format($rs->qty),
					$rs->statusText,
					$rs->refname
					. $menu,
				);
			}

			$ret .= $tables->build();

			//$ret .= print_o($dbs,'$dbs');

			$ret .= '<div class="btn-floating -right-bottom -po-add"><a class="sg-action btn -floating -circle48" href="'.url('imed/patient/po/'.$psnId.'/add').'" data-rel="box" data-width="480" data-max-height="80%"><i class="icon -material -white">add</i></a></div>';

			//$ret .= print_o($psnInfo,'$psnInfo');
			$ret .= '</section>';
			break;
	}


	//$ret .= print_o($orgInfo,'$orgInfo');

	return $ret;
}

function __imed_patient_po_form($data) {

	$ui = new Ui();
	//$ui->add('<a href=""><i class="icon -material">delete</i></a>');
	//$ui->add('<a href=""><i class="icon -material">close</i></a>');
	$ret .= '<header class="header -box"><nav class="nav -back"><a class="sg-action -back" href="javascript:void(0)" data-rel="close"><i class="icon -material">arrow_back</i></a></nav><h3>บันทึกการมีกายอุปกรณ์</h3><nav class="nav">'.$ui->build().'</nav></header>';

	$form = new Form('stk',url('imed/patient/'.$data->psnid.'/po/save'),NULL,'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel','notify');
	$form->addData('done', 'close | load->replace:#imed-patient-po:'.url('imed/patient/po/'.$data->psnid));

	$form->addField('stktrid',array('type'=>'hidden','value'=>$data->stktrid));
	$form->addField('trtype',array('type'=>'hidden','value'=>'OPEN'));
	$form->addField('psnid',array('type'=>'hidden','id'=>'psnid','value'=>$data->psnid));

	$stmt = 'SELECT `stkid`,`name` FROM %imed_stkcode% WHERE `parent` IN ("01", "03") ORDER BY `parent` ASC, CONVERT(`name` USING tis620) ASC';
	$dbs = mydb::select($stmt);

	$options = array(''=>'== เลือกกายอุปกรณ์ ==');
	foreach ($dbs->items as $rs) $options[$rs->stkid] = $rs->name;

	$form->addField(
					'stkid',
					array(
						'type' => 'select',
						'label' => 'กายอุปกรณ์ที่มี:',
						'class' => '-fill',
						'require' => true,
						'options' => $options,
						'value' => $data->stkid,
					)
				);

	$form->addField(
					'qty',
					array(
						'type' => 'text',
						'label' => 'จำนวน',
						'class' => '-numeric',
						'require' => true,
						'value' => $data->qty ? number_format($data->qty) : '',
					)
				);

	$form->addField(
					'status',
					array(
						'type' => 'select',
						'label' => 'สถานะ:',
						'class' => '-fill',
						'options' => array(''=>'== เลือกสถานะ ==')+imed_model::get_category('toolstate'),
						'value' => $data->status,
					)
				);

	$form->addField(
					'stkdate',
					array(
						'type' => 'text',
						'label' => 'วันที่ได้รับ',
						'class' => 'sg-datepicker -date',
						'value' => sg_date(SG\getFirst($data->stkdate, date('U')),'d/m/Y'),
					)
				);

	$form->addField(
					'refname',
					array(
						'type' => 'text',
						'label' => 'ได้รับจากหน่วยงาน',
						'class' => '-fill',
						'value' => htmlspecialchars($data->refname),
					)
				);

	$form->addField(
					'description',
					array(
						'type' => 'text',
						'label' => 'บันทึกช่วยจำ',
						'class' => '-fill',
						'value' => htmlspecialchars($data->description),
					)
				);

	$form->addField(
					'save',
					array(
						'type' => 'button',
						'value' => '<i class="icon -save -white"></i><span>บันทึก</span>',
						'pretext' => '<a class="sg-action btn -link -cancel" data-rel="close"><i class="icon -cancel -gray"></i><span>{tr:CANCEL}</span></a>',
						'container' => array('class'=>'-sg-text-right'),
					)
				);

	$ret .= $form->build();
	return $ret;
}
?>