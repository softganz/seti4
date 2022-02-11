<?php
function view_project_toolbar($self,$title=NULL,$nav='default',$info=NULL,$options='{}') {
	$defaults='{menu:"","showPrint":false}';
	$options=sg_json_decode($options,$defaults);
	if (is_string($info) AND substr($info,0,1)=='{') $info=sg_json_decode($info);
	if (empty($nav)) $nav='default';

	$ret='';

	//$isEdit=user_access('administer projects','edit own project content',$uid) || project_model::is_owner_of($tpid) || project_model::is_trainer_of($tpid);

	// Module navigator
	// $ui=new Ui();

	// $ui->add('<a href="'.url('project').' " title="">แผนที่ภาพรวม</a>');
	// $ui->add('<a href="'.url('project/list').'" title="">รายชื่อโครงการ</a>');
	// $ui->add('<a href="'.url('project/develop').'" title="">พัฒนาโครงการ</a>');
	// $ui->add('<a href="'.url('project/report').' " title="">วิเคราะห์ภาพรวม</a>');
	// if (user_access('administer projects')) {
	// 	$ui->add('<a href="'.url('project/admin').'" title="ผู้จัดการระบบ">จัดการระบบ</a>');
	// }
	// $self->theme->moduleNav = $ui->build('ul','navgroup -main');

	if ($nav=='default' || $nav=='project') {
		$ret.='<form id="search" class="search-box" method="get" action="'.url('project/search').'" name="memberlist" role="search"><input type="hidden" name="sid" id="sid" /><input id="search-box" class="sg-autocomplete" type="text" name="q" size="40" value="'.post('q').'" placeholder="ป้อนชื่อโครงการหรือเลขที่ข้อตกลง" data-query="'.url('project/get/title').'" data-callback="'.url('project/').'" data-altfld="sid"><button type="submit"><i class="icon -search"></i></button></form>'._NL;
	} else if ($nav=='fund') {
		$ret.='<form id="search" class="search-box" method="get" action="'.url('project/fund/search').'" name="memberlist" role="search"><input type="hidden" name="sid" id="sid" /><input id="search-box" class="sg-autocomplete" type="text" name="q" size="40" value="'.post('q').'" placeholder="ป้อนชื่อกองทุนหรือรหัสกองทุน" data-query="'.url('project/api/fund',array('r'=>'f')).'" data-callback="'.url('project/fund/').'" data-altfld="sid"><button class="btn" type="submit"><i class="icon -search"></i>ค้นหากองทุน</button></form>'._NL;
	} else if ($nav=='develop') {
		$ret.='<form id="search" class="search-box" method="get" action="'.url('project/develop/search').'" name="memberlist" role="search"><input type="hidden" name="sid" id="sid" /><input id="search-box" class="sg-autocomplete" type="text" name="q" size="40" value="'.post('q').'" placeholder="ป้อนชื่อพัฒนาโครงการ" data-query="'.url('project/get/develop').'" data-callback="'.url('project/develop/').'" data-altfld="sid"><button class="btn" type="submit"><i class="icon -search"></i><span class="-hidden">ค้นหาพัฒนาโครงการ</span></button></form>'._NL;
	}

	$subnav=R::View('project.nav.'.$nav,$info,$options);
	if ($subnav) {
		$ret.='<nav class="nav -submodule -'.($nav == 'default' ? 'project' : ($nav == 'org' ? ($info->fundid ? 'fund' : $nav) : $nav)).'"><!-- nav of project.'.$nav.'.nav -->';
		$ret.=$subnav;
		$ret.='</nav><!-- submodule -->';
	}
	//$ret .= $nav.print_o($info,'$info');
	if (!is_null($self)) {
		$self->theme->title = isset($title) ? $title : 'Proejct Management';
		$self->theme->toolbar=$ret;
	}
	return $ret;
}
?>