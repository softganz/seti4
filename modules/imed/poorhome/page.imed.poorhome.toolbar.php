<?php
/**
* Poor Toolbar
*
* @param Object $self
* @return String
*/
function imed_poorhome_toolbar($self) {
	$tpid=$rs->tpid;
	$self->theme->option->header=false;
	$self->theme->option->package=false;

	$isEdit=user_access('administrator poorhomes','edit own poorhome content',$rs->uid);

	//$nav.='<nav class="nav -submodule">';
	$ui = new Ui(NULL,'ui-nav -sg-text-center');
	$ui->add('<a href="'.url('imed/poorhome').'" title="หน้าหลัก"><i class="icon -home"></i><span>หน้าหลัก</span></a>');
	$ui->add('<a href="'.url('imed/poorhome/report').'" title="รายงาน"><i class="icon -report"></i><span>รายงาน</span></a>');
	if (i()->ok) $ui->add('<a href="'.url('imed/poorhome/add').'" title="เพิ่มแบบสำรวจ" class="sg-action" data-confirm="ต้องการเพิ่มแบบสำรวจ กรุณายืนยัน?"><i class="icon -addbig"></i><span>บันทึกแบบสำรวจใหม่</span></a>');
	//$nav.='</nav>';

	$ret .= '<nav class="nav -submodule -'.($nav=='default'?'imed':$nav).'"><!-- nav of project.'.$nav.'.nav -->';
	$ret .= $ui->build();
	$ret .= '</nav><!-- submodule -->';

	$self->theme->toolbar = $ret;
	//$self->theme->submodule = $nav;

	head('js.imed.js','<script type="text/javascript" src="imed/js.imed.js"></script>');

	return $ret;
}
?>