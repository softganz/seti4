<?php
/**
* Project Nxt :: Evaluate Main Page
* Created 2021-10-31
* Modify  2021-10-31
*
* @return Widget
*
* @usage project/nxt/eval
*/

$debug = true;

class ProjectNxtEval extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'การประเมินผล',
				'leading' => '<i class="icon -material">assessment</i>',
				'navigator' => new Row([
					'children' => [
						'<a href=""><i class="icon -material">assessment</i><span>ประเมินหลักสูตร</span></a>',
						'<a href=""><i class="icon -material">assessment</i><span>ประเมินผลสัมฤทธิ์</span></a>',
						'<a href="" title="Output/Outcome/Impact"><i class="icon -material">assessment</i><span>ประเมินผลลัพท์</span></a>'
					], // children
				]), // Row
			]),
			'body' => new Row([
				'class' => 'nav -app-menu',
				'children' => [
				], // children
			]), // Row
		]);
	}
}
?>