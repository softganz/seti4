<?php
/**
* Project :: New Form For Proposal
* Created 2021-09-24
* Modify  2021-09-24
*
* @return Widget
*
* @usage project/nxt
*/

$debug = true;

import('model:org.php');
import('model:project.php');

class ProjectProposalNew extends Page {
	var $parentId;
	function __construct($parentId = NULL) {
		$this->parentId = $parentId;
	}

	function build() {
		$isCreateProposal = i()->ok;

		$parentInfo = ProjectModel::get($this->parentId);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'เสนอหลักสูตร : '.$parentInfo->title,
				'boxHeader' => true,
			]),
			'body' => new Widget([
				'children' => [
					new Form([
						'action' => url('project/proposal/create'),
						'variable' => 'topic',
						'class' => 'sg-form',
						// 'rel' => 'notify',
						'checkValid' => true,
						'children' => [
							'parent' => ['type' => 'hidden', 'value' => $this->parentId],
							'title' => [
								'type' => 'text',
								'label' => 'ชื่อหลักสูตร',
								'class' => '-fill',
								'require' => true,
							],
							'pryear' => [
								'type' => 'select',
								'label' => 'ปีงบประมาณ:',
								'class' => '-fill',
								'require' => true,
								'options' => (function() {
									$options = ['' => '==เลือกปีงบประมาณ=='];
									for($year = date('Y')+1; $year >= cfg('project')->nxt->startYear; $year--) {
										$options[$year] = 'ปีงบประมาณ '.($year + 543);
									}
									return $options;
								})(),
							],
							'orgId' => [
								'type' => 'radio',
								'label' => 'มหาวิทยาลัย/สถาบัน:',
								'class' => '-fill',
								'require' => true,
								'options' => (function() {
									$options = [];
									foreach (OrgModel::items(['userId' => 'memberShip']) as $item) {
										$options[$item->orgId] = $item->name;
										$childOrg = OrgModel::items(['childOf' => $item->orgId, 'sector' => 10]);
										if ($childOrg) {
											$options['child-'.$item->orgId] = '&nbsp;วิทยาเขต';
											foreach ($childOrg as $childItem) {
												// $options['วิทยาเขต'][$childItem->orgId] = $childItem->name;
												if (array_key_exists($childItem->orgId, $options)) continue;
												$options[$childItem->orgId] = '	'.$childItem->name;
											}
										}
										// debugMsg($childOrg, '$childOrg');
									}
									return $options;
								})(),
							],
							// 'orgId' => ['type' => 'hidden'],
							// 'orgName' => [
							// 	'type' => 'text',
							// 	'label' => 'มหาวิทยาลัย',
							// 	'class' => 'sg-autocomplete -fill',
							// 	'require' => true,
							// 	'attr' => [
							// 		'data-altfld' => 'edit-topic-orgid',
							// 		'data-query' => url('api/org'),
							// 	],
							// 	'placeholder' => 'ค้นชื่อหน่วยงานต้นสังกัด',
							// ],
							'save' => [
								'type' => 'button',
								'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
								'container' => '{class: "-sg-text-right"}',
							]
						], // children
					]), // Form
				],
			]),
		]);
	}
}
?>