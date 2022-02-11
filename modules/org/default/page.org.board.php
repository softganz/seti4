<?php
/**
* Org :: Board
* Created 2021-03-18
* Modify  2021-11-09
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

$debug = true;

import('widget:org.nav.php');

class OrgBoard extends Page {
	var $orgId;
	var $right;
	var $orgInfo;

	function __construct($orgInfo = NULL) {
		$this->orgId = $orgInfo->orgId;
		$this->orgInfo = $orgInfo;
		$this->right = (Object) [
			'edit' => $orgInfo->is->editable,
			'delete' => $orgInfo->is->admin,
		];
	}

	function build() {
		if (!($this->orgId)) return message('error', 'PROCESS ERROR:NO FUND');

		$stmt = 'SELECT
			b.*,
			bp.`name` `boardName`
			FROM %org_board% b
				LEFT JOIN %tag% bp ON bp.`catid` = b.`position` AND bp.`taggroup` = "board:position"
			WHERE b.`orgid` = :orgid AND `status` = 1
			ORDER BY bp.`weight`, b.`posno`';

		$boardDbs = mydb::select($stmt,':orgid', $this->orgId);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'กรรมการ : '.$this->orgInfo->name,
				'navigator' => new OrgNavWidget($this->orgInfo),
			]),
			'body' => new Widget([
				'children' => [
					new Table([
						'class' => 'item -board',
						'thead' => [
							'no' => '',
							'',
							'name -nowrap' => 'ชื่อ-นามสกุล',
							'position -nowrap' => 'ตำแหน่ง',
							// '',
							'datein -date' => 'เริ่มดำรงตำแหน่ง',
							'datedue -date -hover-parent' => 'ครบวาระ',
							// 'status -center -nowrap -hover-parent' => 'สถานะ',
						],
						'children' => (function($boardDbs){
							$widgets = [];
							$no = 0;
							foreach ($boardDbs->items as $rs) {
								$menu = new Ui();
								if ($this->right->edit) {
									$menu->addConfig('container', '{tag: "nav", class: "nav -icons -hover"}');
									$menu->add('<a class="sg-action" href="'.url('org/'.$this->orgId.'/board.form/'.$rs->brdid).'" data-rel="box" data-width="470px"><i class="icon -material">edit</i></a>');
									$menu->add(
										sg_dropbox(
											'<ul>'
											. '<li><a class="sg-action" href="'.url('org/'.$this->orgId.'/board.out/'.$rs->brdid).'" data-rel="box" data-width="480"><i class="icon -material">arrow_forward</i><span>บันทึกออกจากการเป็นกรรมการ</span></a></li>'
											. ($this->right->delete?'<li><a class="sg-action" href="'.url('org/info/api/'.$this->orgId.'/board.delete/'.$rs->brdid).'" data-rel="notify" data-title="ลบชื่อกรรมการ" data-confirm="ต้องการลบชื่อกรรมการนี้ กรุณายืนยัน?" data-done="remove:parent tr"><i class="icon -material">delete</i><span>ลบชื่อกรรมการ</span></a></li>':'')
											. '</ul>',
											'{type:"click"}'
										)
									);
								}

								$widgets[] = [
									++$no,
									'<a class="btn -link -circle32">'.($rs->appointed ? '<i class="icon -material -circle32 -green">how_to_reg</i>' : '<i class="icon -material -circle32 -gray">person</i>').'</a>',
									$rs->prename.$rs->name,
									$rs->boardName,
									// $rs->fromorg,
									($rs->datein?sg_date($rs->datein,'ว ดด ปปปป'):''),
									($rs->datedue?sg_date($rs->datedue,'ว ดด ปปปป'):'')
									// ($rs->appointed ? 'แต่งตั้ง' : 'ยังไม่แต่งตั้ง')
									.$menu->build(),
								];
							}
							return $widgets;
						})($boardDbs),
					]), // Table

					$this->right->edit ? new FloatingActionButton([
						'children' => ['<a class="sg-action btn -floating -circle48" href="'.url('org/'.$this->orgId.'/board.form').'" data-rel="box" data-width="470px"><i class="icon -material">person_add</i></a>'],
					]) : NULL,
				],
			]),
		]);
	}
}
?>