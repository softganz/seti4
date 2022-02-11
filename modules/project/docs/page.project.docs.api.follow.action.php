<?php
/**
* Project :: API Documents
* Created 2021-11-28
* Modify  2021-11-28
*
* @return Widget
*
* @usage project/docs/api
*/

class ProjectDocsAPIFollowAction extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'API : กิจกรรมติดตามโครงการ',
			]),
			'body' => new Column([
				'class' => 'docs',
				'children' => [
					'<h3>รายการบันทึกกิจกรรมของโครงการ</h3>',

					new Row([
						'class' => '-method',
						'children' => [
							'GET',
							'/project/api/actions',
						], // children
					]), // Row

					'<h4>Query parameters</h4>',

					new Column([
						'children' => [
							'<b>projectId</b> numeric',
							'หมายเลขโครงการ',
						], // children
					]), // Column

					new Column([
						'children' => [
							'<b>childOf</b> numeric',
							'หมายเลขโครงการหลัก',
						], // children
					]), // Column

					new Column([
						'children' => [
						'<b>dateFrom</b> string',
						'วันที่เริ่ม รูปแบบ YYYY-MM-DD',
						], // children
					]), // Column

					new Column([
						'children' => [
						'<b>dateEnd</b> string',
						'วันที่สิ้นสุด รูปแบบ YYYY-MM-DD',
						], // children
					]), // Column

					new Column([
						'children' => [
							'<b>page</b> numeric',
							'หมายเลขหน้า เริ่มจาก 1 เป็นต้นไป * สำหรับทั้งหมด',
						], // children
					]), // Column

					'<h3>Sample Request</h3>',
					new Column([
						'children' => [
							'/project/api/actions?childOf=1118&dateFrom=2021-10-01&dateEnd=2021-10-31&page=*',
						], // children
					]), // Column

					'<h3>Response</h3>',
					'A successful request returns the HTTP 201 Created status code and a JSON response body that shows plan details.',

					$this->_script(),
				], // children
			]), // Column
		]);
	}

	function _script() {
		return '<style type="text/css">
		.docs {padding: 16px;}
		.docs h3 {border-bottom: 1px #ccc solid; margin-bottom: 16px;}
		.docs>.-item {margin-bottom: 16px;}
		.docs .-method {border: 1px #eee solid; margin-bottom: 16px;}
		.docs .-method>.-item {padding: 8px;}
		.docs .-method>.-item:first-child {border-right: 1px #eee solid; background-color: #eee;}
		</style>';
	}
}
?>