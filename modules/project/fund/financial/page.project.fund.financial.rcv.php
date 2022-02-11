<?php
/**
* Project :: Fund Financial Recieve
* Created 2020-01-01
* Modify  2020-01-01
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage project/fund/$orgId/financial.rcv
*/

$debug = true;

function project_fund_financial_rcv($self,$fundInfo = NULL) {
	$orgId = $fundInfo->orgid;

	$isAdmin = $fundInfo->right->admin;

	R::view('project.toolbar',$self,'ใบเสร็จรับเงิน - กองทุนตำบล','fund',$fundInfo);

	$myFund = R::Model('project.fund.get.my',i()->uid);

	mydb::where('`tagname` = "projectfundrcv"');

	if ($isAdmin) {
		//
	} else if (i()->ok && $myFund) {
		mydb::where('f.`orgid` = :orgid', ':orgid', $myFund->orgid);
	} else {
		return '<p class="notify">ขออภัย ไม่มีรายการ</p>';
	}
	//$ret.=print_o($myFund,'$myFund');


	// Get photo from database
	$stmt = 'SELECT
		  o.`orgid`, f.`fid`, f.`type`, f.`file`, f.`title`
		, o.`name`, o.`shortname`
		, g.`refcode`, g.`pglid`
		, f.`timestamp`
		FROM %topic_files% f
			LEFT JOIN %db_org% o USING(`orgid`)
			LEFT JOIN %project_gl% g ON g.`pglid`=f.`refid`
		%WHERE%
		ORDER BY `fid` DESC';

	$dbs= mydb::select($stmt);

	//$ret.=print_o($dbs,'$dbs');

	$tables = new Table();
	$tables->thead=array('กองทุนตำบล','เอกสารอ้างอิง','ใบเสร็จรับเงิน','date'=>'วันที่อัพโหลด');
	//$ret.='<ul id="loapp-photo" class="photocard -loapp">'._NL;
	// Show photos
	foreach ($dbs->items as $rs) {
		if ($rs->type=='photo') {
			$photo=model::get_photo_property($rs->file);
			$photo_alt=$rs->title;
			$uploadUrl=$photo->_src;
			$photoUrl=$photo->_src;
		} else {
			$uploadUrl=cfg('paper.upload.document.url').$rs->file;
			$photoUrl='//img.softganz.com/icon/pdf-icon.png';
		}
		$tables->rows[] = array(
			'<a href="'.url('project/fund/'.$rs->orgid).'">'.$rs->name.'</a>',
			'<a href="'.url('project/fund/'.$rs->orgid.'/financial.view/'.$rs->pglid).'">'.$rs->refcode.'</a>',
			'<a class="'.($rs->type=='photo'?'sg-action':'').'" href="'.$uploadUrl.'" data-rel="img"><img src="'.$photoUrl.'" height="100" /></a>',
			sg_date($rs->timestamp,'d-m-Y H:i:s'),
		);
	}
	//$ret.='</ul><!-- loapp-photo -->';
	$ret.=$tables->build();

	$ret.='<style type="text/css">
	.nav .sg-upload {display: block; float: left; height:21px; margin:0; }
	.nav .sg-upload .btn {margin:0; }
	.photocard {margin:0; padding:0; list-style-type:none;}
	.photocard>li {height:300px; margin:0 10px 10px 0; float:left; position;relative;}
	.photocard img {height:100%;}
	.photocard .iconset {right:10px; top:10px; z-index:1;}
	</style>';
	return $ret;
}
?>