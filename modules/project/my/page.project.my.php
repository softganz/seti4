<?php
/**
* Project :: My Main Page
* Created 2021-12-13
* Modify  2021-12-13
*
* @return Widget
*
* @usage project/my
*/

import('widget:appbar.nav.php');

class ProjectMy extends Page {
	function build() {
		if (!i()->ok) return R::View('signform');

		$myConfig = cfg('project')->my;

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => '@'.i()->name,
				'leading' => '<img class="profile-photo" src="'.model::user_photo(i()->username).'" width="32" height="32" />',
				'trailing' => new Row([
					'children' => [
						$myConfig->trailing->addFollowButton && user_access('create project content') ? '<a class="sg-action btn -primary" href="'.url('project/create', ['rel' => 'box']).'" data-rel="box" data-width="480" title="Create New Project"><i class="icon -material">add</i><span>สร้างโครงการ</span></a>' : NULL,
					]
				]),
				'navigator' => new AppBarNavWidget(['configName' => 'project.my', 'userSigned' => true]),
			]), // AppBar
			'body' => new Widget([
				'children' => (function($myConfig) {
					$childrens = [];
					$titleNo = 0;

					foreach (explode(',',$myConfig->infoUse) as $section) {
						$item = $myConfig->info->{$section};
						if (is_null($item) || is_null($widget = R::PageWidget($item->page, [$this->projectInfo]))) continue;

						$childrens[$section] = new Card([
							'tagName' => 'section',
							'id' => 'project-info-section-'.$section,
							'class' => 'project-info-section -'.$section,
							'children' => [
								new ListTile([
									'class' => '-sg-paddingnorm',
									'title' => ($myConfig->showInfoNumber ? ++$titleNo.'. ' : '').$item->title,
									'leading' => '<i class="icon -material">stars</i>',
									'trailing' => '<a class="sg-expand btn -link -no-print" href="javascript:void(0)"><icon class="icon -material">expand_less</i></a>',
								]), // ListTile
								$widget,
								// new DebugMsg($item,'$item'),
							], // children
						]);
					}

					// $childrens['script'] = $this->script();

					return $childrens;
				})($myConfig), // children
			]), // Widget
		]);
	}
}
?>
<?php
/**
 * My relate project such as Owner, Trainer
 *
 * @return String
 */
function project_my($self) {
	//R::View('project.toolbar',$self,'โครงการในความรับผิดชอบ');
	$title='@'.(i()->ok?i()->name:'Welcome');
	R::View('project.toolbar',$self,$title,'my',$projectInfo,'{modulenav:false}');

	if (!i()->ok) return '<p class="notify">กรุณาเข้าสู่ระบบสมาชิก</p>'.R::View('signform');

	$ret.=R::Page('project.my.action',NULL);
	return $ret;
}
?>