<?php
/**
* Project :: Co-Organization
* Created 2021-09-27
* Modify  2021-09-27
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/{id}/info.org.co
*/

$debug = true;

import('model:project.follow.php');

class ProjectInfoOrgCo extends Page {
	var $projectId;
	var $showShortName;
	var $right;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
		$this->right = $projectInfo->right;
		$this->showShortName = post('shortname');
	}

	function build() {
		if (!$this->projectId) return message('error', 'PROCESS ERROR');

		$orgCo = ProjectFollowModel::getOrgCo(['projectId' => $this->projectId]);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'องค์กรร่วม '.$orgCo->count.' องค์กร',
				'boxHeader' => true,
				'leading' => _HEADER_BACK,
			]),
			'body' => new Widget([
				'children' => [
					new Table([
						'thead' => ['no' => '', 'องค์กร', 'จังหวัด', 'วันที่เข้าร่วม', 'by -hover-parent' => 'โดย'],
						'children' => array_map(function($item) {
							static $no = 0;
							return [
								++$no,
								'<a href="'.url('org/'.$item->orgId).'">'.$item->orgName.($this->showShortName && $item->shortname ? ' ('.$item->shortname.')' : '').'</a>',
								$item->changwatName,
								sg_date($item->created,'ว ดด ปปปป'),
								$item->ownerName
								. ($this->right->isEdit ? '<nav class="nav -icons -hover"><a class="sg-action" href="'.url('project/'.$this->projectId.'/info/org.co.remove/'.$item->orgId).'" data-rel="none" data-done="remove:parent tr" data-title="ลบออกจากองค์กรร่วม" data-confirm="ต้องการลบออกจากองค์กรร่วม กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a></nav>' : NULL),
							];
						}, $orgCo->items),
					]),
					$this->right->isEdit ? new Form([
						'action' => url('project/'.$this->projectId.'/info/org.co.add'),
						'class' => 'sg-form -sg-flex',
						'rel' => 'none',
						'done' => 'load:parent',
						'children' => [
							'orgId' => ['type' => 'hidden'],
							'orgName' => [
								'type' => 'text',
								'name' => false,
								'class' => 'sg-autocomplete -fill',
								'attr' => ['data-query' => url('api/org'), 'data-altfld' => 'edit-orgid'],
								'placeholder' => 'ค้นหาชื่อองค์กร',
								'container' => '{style: "flex:1;"}',
							],
							'save' => [
								'type' => 'button',
								'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
								'container' => '{class: "-sg-text-right"}',
							],
						], // children
					]) : NULL, // Form
				], // children
			]), // Widget
		]);
	}
}
?>