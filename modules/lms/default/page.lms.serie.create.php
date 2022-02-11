<?php
/**
* LMS :: Create New Serie
* Created 2021-12-05
* Modify  2021-12-05
*
* @return Widget
*
* @usage lms/{id}/serie.create
*/

import('model:org.php');
import('model:lms.php');

class LmsSerieCreate extends Page {
	function __construct() {}
	function build() {
		$post = (Object) post();

		if (!$post->orgId) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลหน่วยงาน']);

		$data = (Object) [
			'serieId' => SG\getFirst($post->serieId),
			'orgId' => $post->orgId,
			'serieNo' => $post->serieNo,
			'dateStart' => $post->dateStart,
		];
		// debugMsg($data,'$data');

		$result = LmsModel::createSerie($data);
		// debugMsg($result, '$result');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Title',
			]),
			'body' => new Widget([
				'children' => [],
			]),
		]);
	}
}
?>