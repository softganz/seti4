<?php
function org_seedfund($self) {
	$self->theme->title='กองทุนเมล็ดพันธุ์';
	R::Page('org.seedfund.toolbar',$self);

	$ret.='<div id="org-seedfund-main" class="org-seedfund-main">';
	$ret.='<div id="org-seedfund-info" class="org-seedfund -info">'.__org_seedfund_needcard().'</div>';
	$ret.='<div class="org-seedfund -search">Search</div>';
	$ret.='<div id="map_canvas" class="map -seedfund" data-center="13.710035342476681,100.5029296875" data-zoom="7">Map</div>';
	$ret.='</div><!-- org-seeffund-main -->';
	$ret.='<style tyle="text/css">
	.ui-card {margin:0;padding:0;list-style-type:none;}
	.ui-card>.ui-item {margin:0 0 16px 0; padding:0 16px; border-bottom:1px #ccc solid;}
	</style>';
	return $ret;
}

function __org_seedfund_needcard() {
	$stmt='SELECT * FROM %org_seedfundneed% ORDER BY `sfnid` DESC';
	$dbs=mydb::select($stmt);

	if ($dbs->_empty) return '<p>ยังไม่มีรายการแจ้งความต้องการ</p>';
	
	$ui=new Ui(NULL,'ui-card');
	foreach ($dbs->items as $rs) {
		$cardStr='<a class="sg-action" href="'.url('org/seedfund/need/view/'.$rs->sfnid).'" data-rel="box">';
		$cardStr.='<h3>'.$rs->who.'</h3>';
		$cardStr.='<p>'.nl2br($rs->need).'</p>';
		$cardStr.='</a>';
		$ui->add($cardStr);
	}
	$ret.=$ui->build();
	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>