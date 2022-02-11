<?php
function green_app_supplier($self,$qtref=NULL,$action=NULL,$trid=NULL) {
	R::View('org.toolbar',$self,'เครือข่ายผู้ผลิต','supplier.app');
	//$self->theme->title='เครือข่ายผู้ผลิต';
	$formGroup=_QTGROUP_GOGREEN; // เครือข่าย Go Green

	unset($self->theme->toolbar, $self->theme->title);

	$isAdmin=user_access('access administrator pages,administer ibuys,administrator orgs');
	if (!is_numeric($qtref)) {$action=$qtref;unset($qtref);}

	if ($qtref) {
		$qtInfo=R::Model('green.qt.get',$qtref);
		$supplierInfo=R::Model('org.supplier.get',$qtInfo->orgid);
	}

	switch ($action) {
		case 'view':
			if ($qtInfo->orgid) {
				$ret.=R::Page('green.supplier',NULL,$qtInfo->orgid);
			} else {
				$ret.='<h2>'.$qtInfo->tr['ORG.NAME']->value.'</h2>';
				$ret.='<p class="notify">เครือข่ายนี้ยังไม่ได้รับการตรวจสอบจากผู้ดูแล กรุณารอการตรวจสอบจากผู้ดูแลระบบ</p>';
			}
			break;

		default:
			// If already has register
			$regDbs=mydb::select('SELECT q.*,o.*,tr.`value` `qtname` FROM %qtmast% q LEFT JOIN %db_org% o USING(`orgid`) LEFT JOIN %qttran% tr ON tr.`qtref`=q.`qtref` AND tr.`part`="ORG.NAME" WHERE q.`qtgroup`=:qtgroup AND q.`uid`=:uid',':qtgroup',$formGroup,':uid',i()->uid);
			if ($regDbs->count()) {
				$ret.='<h3 class="title">เครือข่ายในความดูแล</h3>';
				$ui=new Ui(NULL,'ui-card -gogreen');
				foreach ($regDbs->items as $rs) {
					$orgName = SG\getFirst($rs->name,$rs->qtname);
					if ($rs->orgid) {
						$url=url('green/app/supplier/'.$rs->qtref.'/view');
					} else {
						$url=url('green/app/supplier/form/'.$rs->qtref);
					}
					$ui->add('<h4><a class="sg-action" data-webview="'.$orgName.'" href="'.$url.'"><img src="https://softganz.com/img/img/shop-01.png" width="96" /><br />'.SG\getFirst($rs->name,$rs->qtname).'</a></h4>','{class:"-sg-text-center"}');
				}
				$ret.=$ui->build();
			}

			$stmt='SELECT
						q.`qtref`
						, q.`orgid`
						, q.`uid`
						, tr.`value` `name`
						FROM %qtmast% q
							LEFT JOIN %db_org% o USING(`orgid`)
							LEFT JOIN %qttran% tr ON tr.`qtref`=q.`qtref` AND tr.`part`="ORG.NAME"
						WHERE `qtgroup`=:qtgroup AND `qtstatus`>=0';
			$dbs=mydb::select($stmt,':qtgroup',_QTGROUP_GOGREEN);

			$ret.='<h3 class="title">รายชื่อเครือข่าย</h3>';

			$ui=new Ui(NULL,'ui-card -gogreen');
			foreach ($dbs->items as $rs) {
				$orgName = SG\getFirst($rs->name,$rs->qtname);
				$url=url('green/app/supplier/'.$rs->qtref.'/view');
				$menu='';
				if ($rs->uid==i()->uid || $isAdmin) {
					$menu.='<a class="btn -primary -circle" href="'.url('green/app/supplier/form/'.$rs->qtref).'"><i class="icon -edit -white"></i></a>';
				}
				$ui->add('<a class="sg-action" href="'.$url.'" data-webview="'.$orgName.'"><img src="https://softganz.com/img/img/shop-01.png" width="96" /></a><h4><a href="'.$url.'">'.$orgName.'</a></h4>'.$menu,'{class:"-sg-text-center"}');
			}
			$ret.=$ui->build();

			/*
			$tables = new Table();
			$tables->thead=array('ชื่อเครือข่าย','icons -c1'=>'');
			foreach ($dbs->items as $rs) {
				$menu='';
				if ($rs->uid==i()->uid || $isAdmin) {
					$menu.='<a href="'.url('green/app/supplier/form/'.$rs->qtref).'"><i class="icon -edit"></i></a>';
				}
				$tables->rows[]=array(
													'<a href="'.url('green/app/supplier/'.$rs->qtref.'/view').'">'.$rs->name.'</a>',
													$menu
													);
			}
			$ret.=$tables->build();
			*/

			break;
	}
	//$ret.=print_o($qtInfo,'$qtInfo');
	//$ret.=print_o($supplierInfo,'$supplierInfo');
	head(
		'<style type="text/css">
		.title {padding:16px; text-align:center; background-color:#ccc;}
		.ui-card.-gogreen {text-align:center; background-color:#ddd;}
		.ui-card.-gogreen li {margin:32px 0; padding:16px; background-color:#fff;}
		</style>'
		);
	return $ret;
}
?>