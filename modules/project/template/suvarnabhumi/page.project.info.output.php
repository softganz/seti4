<?php
/**
* Project :: Follow Output Information
* Created 2021-10-26
* Modify  2021-10-26
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/{id}/info.output
*/

import('model:bigdata.php');

class ProjectInfoOutput extends Page {
	var $projectId;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
	}

	function build() {
		$isEdit = $this->projectInfo->info->isEdit && post('mode') != 'view';
		$data = (Object) BigDataModel::items(
			[
				'key' => 'info',
				'name' => ['output','outcome','impact'],
			],
			'{debug: false, key: "name", value: "value"}'
		);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->projectInfo->title,
			]),
			'body' => new Container([
				'children' => [
					view::inlineedit(
						[
							'label' => 'ผลผลิต (Output)',
							'group' => 'bigdata:info:output',
							'fld' => 'output',
							// 'tr' => $basicInfo->trid,
							'ret' => 'html',
							'options' => [
								'class' => '-fill',
								'placeholder' => 'ระบุผลที่คาดว่าจะได้รับเป็นข้อๆ',
							],
						],
						nl2br($data->items['output']),
						$isEdit,
						'textarea'
					),

					view::inlineedit(
						[
							'label' => 'ผลผลิต (Output)ผลลัพธ์ (Outcome)',
							'group' => 'bigdata:info:outcome',
							'fld' => 'outcome',
							// 'tr' => $basicInfo->trid,
							'ret' => 'html',
							'options' => [
								'class' => '-fill',
								'placeholder' => 'ระบุผลที่คาดว่าจะได้รับเป็นข้อๆ',
							],
						],
						$data->items['outcome'],
						$isEdit,
						'textarea'
					),

					view::inlineedit(
						[
							'label' => 'ผลกระทบ (Impact)',
							'group' => 'bigdata:info:impact',
							'fld' => 'impact',
							// 'tr' => $basicInfo->trid,
							'ret' => 'html',
							'options' => [
								'class' => '-fill',
								'placeholder' => 'ระบุผลที่คาดว่าจะได้รับเป็นข้อๆ',
							],
						],
						$data->items['impact'],
						$isEdit,
						'textarea'
					),
					// new DebugMsg($data, '$data'),
					// new DebugMsg($this->projectInfo),
				], // children
			]), // Container
		]);
	}
}
?>