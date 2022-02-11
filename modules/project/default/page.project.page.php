<?php
/**
* Project : Page
* Created 2020-01-01
* Modify  2020-06-04
*
* @param Object $self
* @param Object $projectInfo
* @return String
*/

$debug = true;

function project_page($self, $projectInfo = NULL) {
	$tpid = is_object($projectInfo) ? $projectInfo->tpid : $projectInfo;

	if (!$tpid) return message('error', 'PROCESS ERROR');

	$isAdmin = user_access('access administrator pages');

	$ret = '';

	if ($isAdmin) {
		$ui=new Ui();
		$ui->add('<a href="'.url('project/'.$tpid.'/page').'"><i class="icon -material">pageview</i><span>View</span></a>');
		$ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/page.init').'" data-rel="box" data-width="640"><i class="icon -material">edit</i><span>Init Command</span></a>');
		$ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/page.homepage').'" data-rel="box" data-width="640"><i class="icon -material">edit</i><span>Home Page Command</span></a>');
		$ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/page.setting').'" data-rel="box" data-width="640"><i class="icon -material">settings</i><span>Setting</span></a>');
		$ui->add('<a class="" href="'.url('paper/'.$tpid.'/edit.photo').'" target="_blank"><i class="icon -material">image</i><span>Upload Photo</span></a>');

		$ret .= '<nav class="nav -page">'.$ui->build().sg_dropbox($ui->build(),'{class:"leftside -atright"}').'</nav>';
	}

	$homepageCmd = property('project:HOMEPAGE:'.$tpid);
	$initCmd = property('project:INIT:'.$tpid);

	if ($homepageCmd) {
		$ret .= eval_php($homepageCmd, NULL, NULL, (object)array('tpid'=>$tpid));
		/*ob_start();
		$ret .= @eval('?>'.$homepageCmd);
		$ret .= ob_get_clean();
		*/
	} else {
		$ret .= R::Page('project.view', NULL, $projectInfo);
	}

	return $ret;
}