<?php
/**
* Project :: Admin Repair
* Created 2021-07-28
* Modify  2021-07-28
*
* @return Widget
*
* @usage project/admin/repair
*/

$debug = true;

class ProjectAdminRepair extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Repair Project',
			]),
			'body' => new Ui([
				'type' => 'menu',
				'children' => [
					'<a href="'.url('project/admin/repair/areacode/from/org').'">Repair Area Code From Org Area Code</a>',
					'<a href="'.url('project/admin/repair/areacode').'">Repair Area Code From Changwat</a>',
					'<a href="'.url('project/admin/repair/jointarget').'">Repair Join Target</a>',
					'<a href="'.url('project/admin/repair/location').'">Repair Location GIS</a>',
					'<a href="'.url('project/admin/repair/parent').'">Repair Parent</a>',
					'<a href="'.url('project/admin/repair/person/areacode').'">Repair Person Areacode</a>',
					'<a href="'.url('project/admin/repair/proposal/status').'">Repair Proposal Status</a>',
					'<a href="'.url('project/admin/repair/space').'">Repair Space In Title</a>',
					'<a href="'.url('project/admin/repair/valuation').'">Repair Valuation</a>',

					'<sep>',
					'<a href="'.url('project/admin/repair/fund/address').'">Repair Local Fund Address</a>',
				],
			]),
		]);
	}
}
?>