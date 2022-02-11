<?php
/**
* Project :: Create Follow
* Created 2018-09-09
* Modify  2021-10-17
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

$debug = true;

class ProjectCreate extends Page {
	var $arg1;

	function __construct($arg1 = NULL) {
		$this->arg1 = $arg1;
	}

	function build() {
		$post = (object) post('data');
		if ($projectSet) $post->projectset = $projectSet;
		$post->startyear = SG\getFirst(post('startyear'), date('Y')-1);

		$isCreate = user_access('create project content');

		if ($post->projectset && $post->projectset != 'top') {
			$projectSetInfo = R::Model('project.get', $post->projectset, '{initTemplate:true}');
			if (!$projectSetInfo) return $ret.message('error', 'INVALID PROJECT SET');

			$settings = sg_json_decode(property('project:SETTING:'.$post->projectset));

			if ($settings->isCreateChild) {
				$isCreate = false;
				if ($settings->isCreateChild == 'MEMBER' && i()->ok) {
					$isCreate = true;
				} else if (i()->ok && in_array($projectSetInfo->membership[i()->uid], explode(',',$settings->isCreateChild))) {
					$isCreate = true;
				}
			}
		}


		if (!$isCreate) {
			$options = NULL;
			$ret .= '<header class="header -box -hidden"><h3>@Secure signin</h3></header>';
			if (i()->ok) {
				$ret .= message('error', 'Access Denied');
			} else {
				$options->signret = SG\getFirst(post('signret'));
				$ret .= R::View('signform', $options);
			}
			return $ret;
		}



		if (empty($post->title)) {
			$ret .= '<header class="header -box -hidden"><h3>เพิ่ม{tr:โครงการ}</h3></header>';
			$ret .= R::View('project.create.form', $post);
		} else {
			$result = R::Model('project.create', $post);

			$ret.='Project Created with projectset '.$projectSet.print_o($result,'$result');
			$_SESSION['mode'] = 'edit';
			// location('project/'.$result->tpid);
		}

		return $ret;
		// return new Scaffold([
		// 	'appBar' => new AppBar([
		// 		'title' => 'Title',
		// 	]),
		// 	'body' => new Widget([
		// 		'children' => [],
		// 	]),
		// ]);
	}
}
?>
<?php
/**
* Create new project
* Created 2018-09-09
* Modify  2019-09-05
*
* @param Object $self
* @param Int $projectSet
* @return String
*/

$debug = true;

function project_create($self, $projectSet = NULL) {
	R::View('project.toolbar', $self, 'Create new project');

	$post = (object) post('data');
	if ($projectSet) $post->projectset = $projectSet;
	$post->startyear = SG\getFirst(post('startyear'), date('Y')-1);

	$isCreate = user_access('create project content');

	if ($post->projectset && $post->projectset != 'top') {
		$projectSetInfo = R::Model('project.get', $post->projectset, '{initTemplate:true}');
		if (!$projectSetInfo) return $ret.message('error', 'INVALID PROJECT SET');

		$settings = sg_json_decode(property('project:SETTING:'.$post->projectset));

		if ($settings->isCreateChild) {
			$isCreate = false;
			if ($settings->isCreateChild == 'MEMBER' && i()->ok) {
				$isCreate = true;
			} else if (i()->ok && in_array($projectSetInfo->membership[i()->uid], explode(',',$settings->isCreateChild))) {
				$isCreate = true;
			}
		}
	}


	if (!$isCreate) {
		$options = NULL;
		$ret .= '<header class="header -box -hidden"><h3>@Secure signin</h3></header>';
		if (i()->ok) {
			$ret .= message('error', 'Access Denied');
		} else {
			$options->signret = SG\getFirst(post('signret'));
			$ret .= R::View('signform', $options);
		}
		return $ret;
	}



	if (empty($post->title)) {
		$ret .= '<header class="header -box -hidden"><h3>เพิ่ม{tr:โครงการ}</h3></header>';
		$ret .= R::View('project.create.form', $post);
	} else {
		$result = R::Model('project.create', $post);

		//$ret.='Project Created with projectset '.$projectSet.print_o($result,'$result');
		$_SESSION['mode'] = 'edit';
		location('project/'.$result->tpid);
	}

	//$ret.=print_o($post,'$post');
	//$ret.=print_o($projectSetInfo,'$projectSetInfo');

	return $ret;
}
?>