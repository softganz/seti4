<?php
/**
* Project :: Organization Admin Page
* Created 2019-10-07
* Modify  2021-09-26
*
* @return Widget
*
* @usage project/admin/org
*/

$debug = true;

class ProjectAdminOrgHome extends Page {
	function build() {
		$q = post('q');
		$orgSector = post('sector');
		$order = SG\getFirst(post('o'),'CONVERT(o.`name` USING tis620)');
		$sort = SG\getFirst(post('s'),'ASC');

		if ($q) mydb::where('o.`name` LIKE :q',':q','%'.$q.'%');
		if ($orgSector) mydb::where('o.`sector` = :sector', ':sector', $orgSector);

		$stmt = 'SELECT o.* , COUNT(f.`uid`) `officer`, po.`name` parentName
			FROM %db_org% o
				LEFT JOIN %org_officer% f USING(`orgid`)
				LEFT JOIN %db_org% po ON po.`orgid` = o.`parent`
			%WHERE%
			GROUP BY `orgid`
			ORDER BY '.$order.' '.$sort;

		$dbs = mydb::select($stmt);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Organization Management',
			]),
			'sideBar' => R::View('project.admin.menu'),
			'body' => new Widget([
				'children' => [
					new Form([
						'id' => 'search-org',
						'class' => 'sg-form form-report',
						'action' => url('project/admin/org'),
						'method' => 'get',
						'style' => 'margin: 8px; width: 320px;',
						'children' => [
							'sector' => [
								'type' => 'select',
								'options' => ['' => '==ทุกประเภท=='] + project_base::$orgTypeList,
								'value' => $orgSector,
							],
							'q' => [
								'type' => 'text',
								'class' => 'sg-autocomplete',
								'placeholder' => 'ค้นหาชื่อหน่วยงาน',
								'attr' => ['data-query' => url('api/org',['r'=>'id']), 'data-callback' => 'submit'],
							],
							'go' => ['type' => 'button', 'value' => '<i class="icon -material">search</i>'],
							'orgid' => ['type' => 'hidden'],
						], // children
					]), // Form
					$ret,
					new Table([
						'class' => 'item org-list',
						'caption' => 'รายชื่อหน่วยงาน',
						'thead' => [
							'name' => 'ชื่อหน่วยงาน',
							'pin -center' => '<i class="icon -material">room</i>',
							'sector -center' => 'Sector',
							'officer -amt -nowrap' => 'เจ้าหน้าที่',
							'created -date' => 'วันที่สร้าง',
						],
						'children' => array_map(function($item) {
							$sectorList = project_base::$orgTypeList;
							return [
								'<a class="sg-action" href="'.url('project/admin/org/'.$item->orgid).'" data-rel="box" data-width="full">'
								.$item->name
								.($item->shortname?' ('.$item->shortname.')':'')
								.'</a>'
								.'<br />'.$item->parentName,
								'<a class="sg-action" href="'.url('org/'.$item->orgid.'/info.map').'" data-rel="box"><i class="icon -material">'.($item->location ? 'room' : 'pin_drop').'</i></a>',
								$item->sector ? $sectorList[$item->sector].'('.$item->sector.')' : '',
								$item->officer?$item->officer:'-',
								sg_date($item->created,'d-m-Y G:i'),
							];
						}, $dbs->items),

						// 	if ($item->admin_remark) $tables->rows[]=array('','<td colspan="3"><p><font color="#f60">Admin remark : '.$item->admin_remark.'</font></p></td>');
						// }
					]), // Table
					new FloatingActionButton([
						'children' => [
							'<a class="sg-action btn -floating" href="'.url('project/admin/org/create').'" data-rel="box" data-width="full" title="สร้างหน่วยงานใหม่"><i class="icon -material">add</i><span>สร้างหน่วยงานใหม่</span></a>',
						], // children
					]), // FloatingActionButton
				],
			]),
		]);
	}
}
?>