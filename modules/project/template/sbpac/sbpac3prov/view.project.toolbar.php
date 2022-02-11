<?php
function view_project_toolbar($self,$title=NULL,$nav='default',$info=NULL,$options='{}') {
	$defaults='{menu: "", showPrint: false}';
	$options=sg_json_decode($options, $defaults);
	if (is_string($info) AND substr($info,0,1)=='{') $info=sg_json_decode($info);
	if (empty($nav)) $nav='default';


	$ret='';

	//$isEdit=user_access('administer projects','edit own project content',$uid) || project_model::is_owner_of($tpid) || project_model::is_trainer_of($tpid);

	// Module navigator
	$ui=new Ui();

	$ui->add('<a href="'.url('project/map',array('set'=>3205)).' " title="">แผนที่ภาพรวม</a>');
	$ui->add('<a href="'.url('project/list',array('set'=>3205)).'" title="">รายชื่อโครงการ</a>');
	$ui->add('<a href="'.url('project/report').' " title="">วิเคราะห์ภาพรวม</a>');
	if (user_access('administer projects')) {
		$ui->add('<a href="'.url('project/admin').'" title="ผู้จัดการระบบ">จัดการระบบ</a>');
	}
	$self->theme->moduleNav=$ui->build('ul','navgroup -main');

	$self->theme->title=isset($title)?$title:'Proejct Management';

	if ($nav=='default' || $nav=='project') {
		$ret.='<form id="search" class="search-box" method="get" action="'.url('project/search').'" name="memberlist" role="search"><input type="hidden" name="sid" id="sid" /><input id="search-box" class="sg-autocomplete" type="text" name="q" size="40" value="'.post('q').'" placeholder="ค้นชื่อโครงการหรือเลขที่ข้อตกลง" data-query="'.url('project/get/title').'" data-callback="'.url('paper/').'" data-altfld="sid"><button type="submit"><i class="icon -search"></i></button></form>'._NL;
	} else if ($nav=='idea') {
		$ret.='<form id="search" class="search-box" method="get" action="'.url('project/idea/search').'" name="memberlist" role="search"><input type="hidden" name="sid" id="sid" /><input id="search-box" class="sg-autocomplete" type="text" name="q" size="40" value="'.post('q').'" placeholder="ป้อนชื่อแนวคิด" data-query="'.url('project/get/idea').'" data-callback="'.url('project/idea/view/').'" data-altfld="sid"><input type="submit" class="button" value="ค้นหาแนวคิด"></form>'._NL;
	} else if ($nav=='develop') {
		$ret.='<form id="search" class="search-box" method="get" action="'.url('project/develop/search').'" name="memberlist" role="search"><input type="hidden" name="sid" id="sid" /><input id="search-box" class="sg-autocomplete" type="text" name="q" size="40" value="'.post('q').'" placeholder="ป้อนชื่อพัฒนาโครงการ" data-query="'.url('project/get/develop').'" data-callback="'.url('project/develop/search/').'" data-altfld="sid"><input type="submit" class="button" value="ค้นหาพัฒนาโครงการ"></form>'._NL;
	}

	$subnav=R::View('project.nav.'.$nav, $info, $options);
	if ($subnav) {
		$ret.='<nav class="nav -submodule -'.($nav=='default'?'project':$nav).'"><!-- nav of project.'.$nav.'.nav -->';
		$ret.=$subnav;
		$ret.='</nav><!-- submodule -->';
	}
	$self->theme->toolbar=$ret;
	return $ret;
}
?>