<?php
function view_ibuy_green_supplier_view($supplierInfo) {
	$ret.='<div class="container">';
	$ret.='<h2 class="title -gogreen">'.$supplierInfo->name.'</h2>';
	$ret.='<a href="https://communeinfo.com/paper/320"><img src="https://communeinfo.com/upload/pics/greenzone-01.jpg" width="100%" /></a>';

	$ret.='<h2 class="title -gogreen">'.$supplierInfo->name.'</h2>';
	$ret.='<h3>รายละเอียดเครือข่าย</h3>';
	$ret.='<div class="detail">';
	$ret.='ที่อยู่ '.$supplierInfo->info->address.'<br />';
	$ret.='โทรศัพท์ '.$supplierInfo->info->phone.'<br />';
	$ret.=$supplierInfo->qt->tr['ORG.DETAIL']->value;
	$ret.='</div>';

	//$ret.=print_o($supplierInfo);
	$ret.='<div class="row -footer">';
	$url=_DOMAIN.urlencode(url('ibuy/green/supplier/'.$supplierInfo->orgid));
	$ret.='<div class="col -md-6 -qrcode"><img class="qrcode" src="https://chart.googleapis.com/chart?cht=qr&chl='.$url.'&chs=160x160&choe=UTF-8&chld=L|2" alt="">';
	$ret.='WEBSITE<br />'.urldecode($url);
	$ret.='</div>';

	$ret.='<div class="col -md-6"><b>สนับสนุนโดย</b><br />www.ข้อมูลชุมชน.com<br />Hatyai Go Green<br />บริษัทประชารัฐฯ (สงขลา)<br />มูลนิธิชุมชนสงขลา</div>';
	$ret.='</div><!-- row -->';
	$ret.='</div><!-- container -->';

	//$ret.=print_o($supplierInfo,'$supplierInfo');
	head(
		'<style type="text/css">
		.qrcode {width:160px;height:160px;margin:0 auto;display:block;}
		.row.-footer {text-align:center;}
		.col {overflow:hidden;}
		.title.-gogreen {padding:16px 0; text-align: center;}
		@media print {
			.row.-footer {width:100%;position:fixed;bottom:0;}
		}
		</style>'
		);
	return $ret;
}
?>