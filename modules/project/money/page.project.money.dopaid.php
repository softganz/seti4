<?php
function project_money_dopaid($self,$projectInfo,$trid) {
	$tpid=$projectInfo->tpid;
	$doingInfo=R::Model('org.dopaid.get',$trid, '{data: "info,member"}');

	$tables = new Table();
	$tables->thead['no']='';
	$tables->thead['name -nowrap']='ชื่อ - นามสกุล';
	$tables->thead[]='ที่อยู่';
	$tables->thead['phone -hover']='โทรศัพท์';
	foreach ($doingInfo->members as $rs) {
		$menuUi = new Ui('span');
		if ($rs->dopid) {
			$menuUi->add('<a href="'.url('project/money/'.$tpid.'/dopaidview/'.$rs->dopid).'"><i class="icon -viewdoc"></i></a>');
		} else {
			$menuUi->add('<a class="sg-action" href="'.url('project/money/'.$tpid.'/createpaid/'.$trid,array('psnid'=>$rs->psnid)).'" data-confirm="ต้องการสร้างใบสำคัญรับเงิน กรุณายืนยัน?"><i class="icon -addbig -circle"></i></a>');
		}

		$menu = '<nav class="iconset -parent-hover">'.$menuUi->build().'</nav>'._NL;

		$class = '-parent-of-hover ';
		if ($rs->dopid) $class .= '-joined';

		$tables->rows[]=array(
											++$no,
											trim($rs->prename.' '.$rs->name.' '.$rs->lname),
											SG\implode_address($rs,'short'),
											$rs->phone
											.$menu,
											'config' => array('class' => $class),
										);
	}

	$ret.=$tables->build();
	$ret.=print_o($doingInfo,'$doingInfo');

	$ret .= '<style type="text/css">
	tr.-joined {color:green;}
	tr.-joined a {color: green;}
	tr.-joined>td:first-child {border-left: 2px green solid;}
	tr.-joined>td {background-color: #f3ffeb;}
	</style>';

	$ret .= '<script type="text/javascript">
	function projectJoinMakJoinCallback($this, ui) {
		console.log("Mark Join")
		var $parent = $this.closest("tr")
		$parent.toggleClass("-joined")
		$this.find("i").toggleClass("-circle -gray -green")
	}
	</script>';

	return $ret;
}
?>