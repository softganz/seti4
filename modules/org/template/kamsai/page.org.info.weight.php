<?php
/**
* Org :: Weight Information
* Created 2021-12-06
* Modify  2021-12-06
*
* @param Object $orgInfo
* @return Widget
*
* @usage org/{id}/info.weight
*/

class OrgInfoWeight extends Page {
	var $orgId;
	var $orgInfo;

	function __construct($orgInfo) {
		$this->orgId = $orgInfo->orgId;
		$this->orgInfo = $orgInfo;
		$this->right = (Object) [
			'edit' => $this->orgInfo->RIGHT & _IS_EDITABLE,
		];
	}

	function build() {
		$stmt = 'SELECT w.`trid`, w.`tpid`, w.`detail1` `year`, w.`detail2` `term`, w.`period` `time` FROM %project_tr% w LEFT JOIN %topic% t USING(`tpid`) WHERE w.`formid` = "weight" AND w.`part` = "title" AND (t.`orgid` = :orgid OR w.`orgid` = :orgid) ORDER BY `year` ASC,`term` ASC,`time` ASC';
		$dbs = mydb::select($stmt, ':orgid', $this->orgId);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ข้อมูลโภชนาการ',
			]),
			'body' => new Card([
				'class' => 'project-knet -weight',
				'children' => [
					new Table([
						'thead' => ['weight -nowrap -hover-parent'=>''],
						'showHeader' => false,
						'children' => array_map(
							function($rs) {
								$ui = new Ui();
								if ($this->right->edit) {
									$ui->add('<a class="sg-action" href="'.url('project/knet/'.$this->orgId.'/weight.edit/'.$rs->trid).'" data-rel="box"><i class="icon -material -gray">edit</i></a>');
									$menu = '<nav class="nav -icons -hover">'.$ui->build().'</nav>';
								}
								return [
									'<a class="sg-action" href="'.url('project/knet/'.$this->orgId.'/weight.view/'.$rs->trid).'" data-rel="box" data-width="full">ปีการศึกษา '.($rs->year+543).' เทอม '.$rs->term.'/'.$rs->time.'</a>'
									. $menu
								];
							},
							$dbs->items
						),
					]), // Table

					$this->right->edit ? '<nav class="nav -sg-text-right -sg-paddingnorm"><a class="sg-action btn -primary" href="'.url('project/knet/'.$this->orgId.'/weight.add').'" data-rel="box"><i class="icon -material">add_circle</i><span>เพิ่มข้อมูลภาวะโภชนาการ</span></a></nav>' : NULL,
				],
			]),
		]);
	}
}
?>