<?php
/**
* Org :: Health Information Dashboard
* Created 2021-10-17
* Modify  2021-12-05
*
* @param Object $orgInfo
* @return Widget
*
* @usage org/{id}/info.health
*/

import('widget:org.nav.php');

class OrgInfoHealth extends Page {
	var $orgId;
	var $orgInfo;

	function __construct($orgInfo) {
		$this->orgId = $orgInfo->orgId;
		$this->orgInfo = $orgInfo;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'สถานการณ์ภาวะโภชนาการ : '.$this->orgInfo->name,
				'navigator' => new OrgNavWidget($this->orgInfo),
			]),
			'body' => new Widget([
				'children' => [
					// '<p class="notify">อยู่ระหว่างดำเนินการ</p>',
					// new Row([
					// 	'class' => '-dashboard',
					// 	'style' => 'flex-wrap: wrap;',
					// 	'children' => [
					// 		$this->dashboard(1),
					// 		$this->dashboard(2),
					// 		$this->dashboard(3),
					// 		$this->dashboard(4),
					// 	], // children
					// ]), // Row
					new Card([
						'children' => [
							new ListTile([
								'class' => '-sg-paddingnorm',
								'title' => 'MENU',
								'leading' => '<i class="icon -material">stars</i>',
							]), // ListTile
							new Row([
								'class' => 'nav -app-menu',
								'style' => 'flex-wrap: wrap;',
								'children' => [
									'<a href="'.url('org/'.$this->orgId.'/info.student').'"><i class="icon -material">group</i><span>ข้อมูลนักเรียน</span></a>',
									'<a href="'.url('org/'.$this->orgId.'/info.student').'"><i class="icon -material">group</i><span>น้ำหนัก-ส่วนสูง</span></a>',
									// '<a><i class="icon -material">add</i><span>สมรรถภาพทางกาย</span></a>',
									// '<a><i class="icon -material">assessment</i><span>รายงานผล</span></a>',
								], // children
							]), // Row
						], // children
					]), // Card
					$this->script(),
				],
			]),
		]);
	}

	function dashboard($id) {
		$moneyIn = 10;
		$moneyOut = 4;
		return new Card([
			'children' => [
				'<h6>Widget '.$id.'</h6>',
				new Container([
					'id' => 'guage-remain-'.$id,
					'class' => 'sg-chart -guage',
					'attribute' => ['data-chart-type' => 'guage',],
					'child' => new Table([
						'class' => '-hidden',
						'children' => [
							[
								'string:Label' => 'Label '.round((($moneyOut)*100/$moneyIn)).'%',
								'Value' => $moneyOut,
								'max' => $moneyIn,
								'redFrom' => 0,
								'redTo' => $moneyIn*0.4,
								'yellowFrom' => $moneyIn*0.4,
								'yellowTo' => $moneyIn*0.7,
								'greenFrom' => $moneyIn*0.7,
								'greenTo' => $moneyIn,
							]
						], // children
					]), // Table
				]), // Container
			], // children
		]);
	}

	function script() {
		return '<style type="text/css">
		.widget-row.-dashboard>.-item {margin: 0 8px 8px 0;}
		.widget-row.-dashboard .widget-card {padding: 8px; border-radius: 8px;}
		.widget-row.-dashboard .widget-table {display: none;}
		.sg-chart {min-width: 100px; min-height: 100px;}
		</style>';
	}
}
?>