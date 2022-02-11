<?php
/**
* iMed :: Patient Psyc Information
* Created 2021-06-11
* Modify  2021-08-18
*
* @param Object $patientInfo
* @return Widget
*
* @usage imed/psyc/{id}/info.psyc
*/

$debug = true;

class ImedPsycInfoPsyc {
	var $psnId;
	var $patientInfo;

	var $isAccess = false;
	var $isEdit = false;

	function __construct($patientInfo) {
		$this->psnId = $patientInfo->psnId;
		$this->patientInfo = $patientInfo;
	}

	function build() {
		$psnInfo = $this->patientInfo;
		$psnId = $psnInfo->psnId;

		$this->isAccess = $psnInfo->RIGHT & _IS_ACCESS;
		$this->isEdit = $psnInfo->RIGHT & _IS_EDITABLE;

		if (empty($psnId)) return message('error','ไม่มีข้อมูล');
		if (!$this->isAccess) return message('error',$psnInfo->error);

		$isDisabled = $psnInfo->disabled->pid;
		$currentUrl = url('imed/psyc/'.$psnId.'/info.psyc');

		$this->icons = [
			'diagnosis' => 'medication',
			'illness' => 'sick',
			'relativeIllness' => 'groups',
			'admit' => 'local_hospital',
			'drug' => 'local_pharmacy',
		];

		$inlineAttr = array();

		if ($this->isEdit) {
			$inlineAttr['class'] = 'sg-inline-edit';
			$inlineAttr['data-update-url'] = url('imed/edit/patient');
			$inlineAttr['data-psnid'] = $psnId;
			if (debug('inline')) $inlineAttr['data-debug'] = 'inline';
		}

		$inlineAttr['data-url'] = $currentUrl;

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ข้อมูลสุขภาพจิต - '.$this->patientInfo->fullname,
				'removeOnApp' => true,
			]), // AppBar
			'body' => new Container([
				'id' => 'imed-care-psychealth',
				'class' => 'imed-care-psychealth'.($this->isEdit ? ' sg-inline-edit' : ''),
				'children' => [
					new ScrollView([
						'child' => new Row([
							'class' => '-sg-paddingmore -menu',
							'children' => [
								'<a class="btn -active" onClick=\'showId("diagnosis")\'><i class="icon -material">'.$this->icons['diagnosis'].'</i><span>การวินิจฉัยโรค</span></a>',
								'<a class="btn" onClick=\'showId("illness")\'><i class="icon -material">'.$this->icons['illness'].'</i><span>ความเจ็บป่วย</span></a>',
								'<a class="btn" onClick=\'showId("relativeIllness")\'><i class="icon -material">'.$this->icons['relativeIllness'].'</i><span>เครือญาติ</span></a>',
								'<a class="btn" onClick=\'showId("admit")\'><i class="icon -material">'.$this->icons['admit'].'</i><span>Admit</span></a>',
								'<a class="btn" onClick=\'showId("drug")\'><i class="icon -material">'.$this->icons['drug'].'</i><span>ยาที่ได้รับ</span></a>',
							] // children
						]), // Row
					]),
					$this->_addItem('', 'diagnosis', 'การวินิจฉัยโรค', array_reverse($this->patientInfo->diagnosis)),
					$this->_addItem('-hidden', 'illness', 'ประวัติความเจ็บป่วยทางวิตเวช', array_reverse($this->patientInfo->illness)),
					$this->_addItem('-hidden', 'relativeIllness', 'ประวัติความเจ็บป่วยทางวิตเวชของเครือญาติ', array_reverse($this->patientInfo->relativeIllness)),
					$this->_addItem('-hidden', 'admit', 'ประวัติการ Admit', array_reverse($this->patientInfo->admit)),
					$this->_addItem('-hidden', 'drug', 'ยาที่ได้รับ', array_reverse($this->patientInfo->drug)),
					$this->_script(),
					// print_o($this->patientInfo, '$this->patientInfo'),
				], // children
			]), // Container
		]);
	}

	function _addItem($class, $code, $label, $trans = []) {
		return new Container([
			'id' => $code,
			'class' => $class,
			'children' => [
				new Card([
					'children' => [
						new ListTile([
							'title' => $label,
							'leading' => '<i class="icon -material">'.$this->icons[$code].'</i>',
							'trailing' => $this->isEdit ? '<a class="sg-action btn -primary" href="'.url('imed/patient/'.$this->psnId.'/form.diagnosis',['code' => $code, 'title' => $label]).'" data-rel="box" data-width="480"><i class="icon -material">add_circle</i><span>เขียนบันทึก</span></a>' : '',
							'style' => 'align-items: center',
						]), // ListTile
					], // children
				]), // Card
				new Container([
					'children' => (function($trans) {
						$result = [];
						foreach ($trans as $item) {
							$result[] = $this->_itemWidget($item);
						}
						return $result;
					})($trans) // children
				]), // Container
			], // children
		]);
	}

	function _itemWidget($item) {
		return new Card([
			'class' => 'imed-tran',
			'children' => [
				new ListTile([
					'leading' => '<img class="profile-photo" src="'.($item ? model::user_photo($item->username) : '_profilePhoto').'" />',
					'title' => $item ? $item->ownerName : '_ownerName',
					'subtitle' => '@'.($item ? sg_date($item->created, 'ว ดด ปปปป') : '_createdDate'),
					'trailing' => empty($item) || ($item->uid == i()->uid || is_admin('imed')) ? '<a class="sg-action" href="'.url('imed/patient/'.$this->psnId.'/info/tran.remove/'.($item ? $item->tr_id : '_tr_id')).'" data-rel="none" data-done="remove:parent .imed-tran" data-title="ลบรายการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -material">cancel</i></a>' : '',
				]), // ListTile
				new Container([
					'class' => '-detail',
					'children' => [
						$item ? nl2br($item->detail1) : '_detail1',
					], // children
				]), // Container
			], // children
		]);
	}

	function _script() {
		head('
		<style type="text/css">
		.imed-care-psychealth .imed-tran .-detail {padding: 8px;}
		.-menu>.-item {margin-right: 16px;}
		.-menu>.-item>.btn.-active {background-color: #ffcdab;}
		</style>
		');

		return '<script type="text/javascript">
		let itemWidget = \''.preg_replace('/[\r\n]/', '', $this->_itemWidget(NULL)->show()).'\'
		$(document).on("click", ".-menu a", function() {
			$(".-menu a").removeClass("-active")
			$(this).addClass("-active")
		});
		function showId(id) {
			for (hideId of ["diagnosis", "illness", "relativeIllness", "admit", "drug"]) {
				$("#"+hideId).hide()
			}
			$("#"+id).show()
		}
		function addDone($this, data) {
			console.log(data)
			let newWidget = itemWidget
			const mapObj = {
				_tr_id: data.tr_id,
				_ownerName : data.ownerName,
				_createdDate: data.createdDate,
				_detail1: data.detail1.replace(/(?:\r\n|\r|\n)/g, "<br>"),
				_profilePhoto: "'.model::user_photo(i()->username).'"
			}
			newWidget = newWidget.replace(/\b(?:_tr_id|_ownerName|_createdDate|_profilePhoto|_detail1)\b/gi, matched => mapObj[matched]);
			$("#"+data.tr_code+">.widget-container").prepend(newWidget)
		}

		// let newWidget = itemWidget
		// const mapObj = {_tr_id: 1111, _ownerName : "Little Bear", _createdDate: "10 สค. 64", _detail1: "รายละเอียด", _profilePhoto: "'.model::user_photo(i()->username).'"}
		// // newWidget = newWidget.replace("$ownerName", "Little Bear")
		// newWidget = newWidget.replace(/\b(?:_tr_id|_ownerName|_createdDate|_profilePhoto)\b/gi, matched => mapObj[matched]);
		// console.log(newWidget)
		// $("#id1>.widget-container").prepend(newWidget)

		</script>
		';
	}
}
?>