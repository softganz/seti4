<?php
function view_school_toolbar($self,$title=NULL,$nav='default',$psn=NULL,$options='{}') {
	$defaults='{menu:""}';
	$options=sg_json_decode($options);
	if (empty($nav)) $nav='default';

	cfg('social.googleplus',false);
	cfg('social.facebook',false);
	
	head('js.school.js','<script type="text/javascript" src="school/js.school.js"></script>');

	$ret='';


	// Module navigator
	/*
	$ui=new Ui();
	$ui->add('<a href="'.url('project').' " title="">แผนที่ภาพรวม</a>');
	$ui->add('<a href="'.url('project/list').'" title="">รายชื่อโครงการ</a>');
	$ui->add('<a href="'.url('project/develop').'" title="">พัฒนาโครงการ</a>');
	$ui->add('<a href="'.url('project/report').' " title="">วิเคราะห์ภาพรวม</a>');
	if (user_access('administer projects')) {
		$ui->add('<a href="'.url('project/admin').'" title="ผู้จัดการระบบ">จัดการระบบ</a>');
	}
	$self->theme->moduleNav=$ui->show('ul','navgroup -main');
	*/

	$self->theme->title=isset($title)?$title:'School Kids';

/*
	if ($nav=='default' || $nav=='project') {
		$ret.='<form id="search" class="search-box" method="get" action="'.url('project/search').'" name="memberlist" role="search"><input type="hidden" name="sid" id="sid" /><input id="search-box" class="sg-autocomplete" type="text" name="q" size="40" value="'.post('q').'" placeholder="ป้อนชื่อโครงการหรือเลขที่ข้อตกลง" data-query="'.url('project/get/title').'" data-callback="'.url('paper/').'" data-altfld="sid"><input type="submit" class="button" value="ค้นหาโครงการ"></form>'._NL;
	} else if ($nav=='idea') {
		$ret.='<form id="search" class="search-box" method="get" action="'.url('project/idea/search').'" name="memberlist" role="search"><input type="hidden" name="sid" id="sid" /><input id="search-box" class="sg-autocomplete" type="text" name="q" size="40" value="'.post('q').'" placeholder="ป้อนชื่อแนวคิด" data-query="'.url('project/get/idea').'" data-callback="'.url('project/idea/view/').'" data-altfld="sid"><input type="submit" class="button" value="ค้นหาแนวคิด"></form>'._NL;
	} else if ($nav=='develop') {
		$ret.='<form id="search" class="search-box" method="get" action="'.url('project/develop/search').'" name="memberlist" role="search"><input type="hidden" name="sid" id="sid" /><input id="search-box" class="sg-autocomplete" type="text" name="q" size="40" value="'.post('q').'" placeholder="ป้อนชื่อพัฒนาโครงการ" data-query="'.url('project/get/develop').'" data-callback="'.url('project/idea/view/').'" data-altfld="sid"><input type="submit" class="button" value="ค้นหาพัฒนาโครงการ"></form>'._NL;
	}
	*/
	$subnav=R::View('school.'.$nav.'.nav',$psn,$options);
	if ($subnav) {
		$ret.='<nav class="nav -submodule -school"><!-- nav of school.'.$nav.'.nav -->';
		$ret.=$subnav;
		$ret.='</nav><!-- submodule -->';
	}
	$self->theme->toolbar=$ret;
	return $ret;
}
?>