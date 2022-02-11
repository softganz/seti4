<?php
/**
* Transaction form
*
* @param Record Set/Integer $carInfo
* @return String
*/

$debug = true;

function icar_view_tran_form($self, $carId) {
	$carInfo = is_object($carId) ? $carId : R::Model('icar.get',$carId, '{initTemplate: true}');
	$carId = $carInfo->tpid;

	$isEdit = empty($carInfo->sold);

	$tables = new Table();
	$tables->thead = array('{tr:Date}','{tr:Detail}','{tr:Interest}(%)','{tr:Amount}({tr:THB})');

	if ($isEdit) {
		$costsoption = '';
		$costsoption .= '<optgroup label="ต้นทุน">';
		foreach (icar_model::category('icar:tr:cost',$carInfo->shopid) as $k=>$v) $costsoption .= '<option value="'.$k.'">'.$v.'</option>';
		$costsoption .= '</optgroup>';
		$costsoption .= '<optgroup label="ไม่คิดต้นทุน">';
		foreach (icar_model::category('icar:tr:notcost',$carInfo->shopid) as $k=>$v) $costsoption .= '<option value="'.$k.'">'.$v.'</option>';
		$costsoption .= '</optgroup>';
		$costsoption .= '<optgroup label="รายรับ">';
		foreach (icar_model::category('icar:tr:rcv',$carInfo->shopid) as $k=>$v) $costsoption .= '<option value="'.$k.'">'.$v.'</option>';
		$costsoption .= '</optgroup>';
		$costsoption .= '<optgroup label="รายจ่าย">';
		foreach (icar_model::category('icar:tr:exp',$carInfo->shopid) as $k=>$v) $costsoption .= '<option value="'.$k.'">'.$v.'</option>';
		$costsoption .= '</optgroup>';
		$costsoption .= '<optgroup label="รายการทั่วไป">';
		foreach (icar_model::category('icar:tr:finance',$carInfo->shopid) as $k=>$v) $costsoption .= '<option value="'.$k.'">'.$v.'</option>';
		foreach (icar_model::category('icar:tr:down',$carInfo->shopid) as $k=>$v) $costsoption .= '<option value="'.$k.'">'.$v.'</option>';
		foreach (icar_model::category('icar:tr:info',$carInfo->shopid) as $k=>$v) $costsoption .= '<option value="'.$k.'">'.$v.'</option>';
		$costsoption .= '</optgroup>';

		$tables->rows[] = array(
			'<input size="10" type="text" id="edit-icarcost-itemdate" name="cost[itemdate]" class="sg-datepicker form-text -require -date -fill" value="'.($post->itemdate?sg_date($post->itemdate,'d/m/Y'):sg_date('d/m/Y')).'" />',
			'<select id="edit-icarcost-costcode" name="cost[costcode]" class="form-select -require -fill"><option value="">==={tr:Select}===</option>'.$costsoption.'</select>',
			'<input size="4" type="text" id="edit-icarcost-interest" name="cost[interest]" class="form-text -money -fill" autocomplete="off" value="" placeholder="0" />',
			'<input size="10" type="text" id="edit-icarcost-amt" name="cost[amt]" class="form-text -require -money -fill" autocomplete="off" value="" placeholder="0.00" /><br />'
			.'<!-- <input name="save" class="btn -primary button-save -fill"  type="submit" value="บันทึก" />-->',
		);

		$tables->rows[] = array(
			'<td colspan="3">'
			.'<textarea id="edit-icarcost-detail" name="cost[detail]" class="form-textarea -fill" placeholder="'.tr('Additional Information').'" rows="1" style="margin: 0; padding: 6px;"></textarea>'
			.'<nav class="nav -sg-text-right"><a id="add-costcode" class="btn -link" href="javascript:void(0)"><i class="icon -add -gray"></i><span>{tr:Add New Item Name}</span></a></nav></td>',
			'<button class="btn -primary -fill" name="save" value="save"><i class="icon -save -white"></i><span>{tr:SAVE}</span></button>',
		);


		$form = new Form(NULL, url('icar/'.$carInfo->tpid), 'icar-cost-trform', 'sg-form icar-cost-trform');
		$form->addConfig('title', '{tr:Transaction}');
		$form->addData('checkValid', true);
		$form->addData('rel', '#main');
		$form->addText($tables->build());
		$ret .= $form->build();
	}

	return $ret;
}
?>