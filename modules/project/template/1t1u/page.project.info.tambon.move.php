<?php
/**
* Module :: Description
* Created 2021-12-18
* Modify  2021-12-18
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

$debug = true;

function project_info_tambon_move($self, $projectInfo) {
	if (!is_admin()) return message('error', 'Access Denied');

	$projectId = $projectInfo->projectId;

	if ($targetProjectId = post('moveto')) {
		$targetInfo = R::Model('project.get', $targetProjectId);
		if (!$targetInfo->projectId) return message('error', 'ไม่มีตำบลปลายทาง');
		mydb::query(
			'UPDATE `sgz_topic` t
				LEFT JOIN `sgz_project` p ON p.`tpid` = t.`tpid`
				LEFT JOIN `sgz_topic` tp ON tp.`tpid` = :targetProjectId
				LEFT JOIN `sgz_project` pp ON pp.`tpid` = tp.`tpid`
			SET
			t.`parent` = tp.`tpid`
			, t.`areacode` = tp.`areacode`
			, t.`changwat` = LEFT(tp.`areacode`,2)
			, p.`projectset` = tp.`tpid`
			, p.`changwat` = SUBSTR(tp.`areacode`,1,2)
			, p.`ampur` = SUBSTR(tp.`areacode`,3,2)
			, p.`tambon` = SUBSTR(tp.`areacode`,5,2)
			, p.`area` = pp.`area`
			, p.`location` = pp.`location`
			WHERE t.`tpid` = :srcProjectId;',
			':srcProjectId' , $projectId,
			':targetProjectId' , $targetProjectId
		);
		$ret .= 'ย้ายเรียบร้อย';

		model::watch_log(
			'project',
			'Move Tambon',
			'ย้ายตำบล "'.$projectInfo->title.'" ('.$projectId.') จาก "'.$projectInfo->info->parentTitle.'" ('.$projectInfo->info->parent.') => "'.$targetInfo->title.'" ('.$targetProjectId.')',
			NULL,
			$projectId,
			$targetProjectId
		);
		return $ret;
	}

	$targetUniversity = mydb::select(
		'SELECT `tpid` `projectId`, `title`
		FROM %topic%
		WHERE `parent` = (SELECT tambon.`parent` `universityId`
		FROM %topic% t
			LEFT JOIN %topic% tambon ON tambon.`tpid` = t.`parent`
		WHERE t.`tpid` = :srcProjectId)
		ORDER BY CONVERT(`title` USING tis620) ASC;
		-- {key: "projectId", value: "title"}',
		':srcProjectId', $projectId
	)->items;
	// debugMsg($targetUniversity, '$targetUniversity');

	// $targetTambonList = mydb::select()

	$form = new Form([
		'action' => url('project/'.$projectId.'/info.tambon.move'),
		'class' => 'sg-form',
		'rel' => 'notify',
		'done' => 'back | reload',
		'checkValid' => true,
		'children' => [
			'moveto' => [
				'type' => 'select',
				'label' => 'ย้ายไปตำบล:',
				'class' => '-fill',
				'require' => true,
				'options' => ['' => '== เลือกตำบลปลายทาง =='] + $targetUniversity,
				'placeholder' => 'หมายเลขโครงการตำบล เช่น 1',
			],
			'save' => [
				'type' => 'button',
				'value' => '<i class="icon -material">done</i><span>ย้ายตำบล</span>',
				'container' => '{class: "-sg-text-right"}',
			],
		], // children
	]);
	$ret .= $form->build();
	// debugMsg($projectInfo, '$projectInfo');
	return $ret;
}

class XProjectInfoTambonMove extends Page {
	var $projectId;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
	}

	function build() {
		$ret = (new Form([
			'action' => url(),
			'class' => 'sg-form',
			'rel' => 'notify',
			'children' => [
				'moveto' => [
					'type' => 'text',
					'label' => 'ย้ายไปตำบล',
					'class' => '-fill',
					'require' => true,
					'placeholder' => 'หมายเลขโครงการตำบล เช่น 1',
				],
				'save' => [
					'type' => 'button',
					'value' => '<i class="icon -material">done</i><span>ย้ายตำบล</span>',
					'container' => '{class: "-sg-text-right"}',
				],
			], // children
		]))->build;

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ย้ายตำบล',
				'boxHeader' => true,
				'leading' => _HEADER_BACK,
			]),
			'body' => new Container([
				'children' => [
					new Form([
						'action' => url(),
						'class' => 'sg-form',
						'rel' => 'notify',
						'children' => [
							'moveto' => [
								'type' => 'text',
								'label' => 'ย้ายไปตำบล',
								'class' => '-fill',
								'require' => true,
								'placeholder' => 'หมายเลขโครงการตำบล เช่น 1',
							],
							'save' => [
								'type' => 'button',
								'value' => '<i class="icon -material">done</i><span>ย้ายตำบล</span>',
								'container' => '{class: "-sg-text-right"}',
							],
						], // children
					]),// Form
				],
			]),
		]);
	}
}
?>