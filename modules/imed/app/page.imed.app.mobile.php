<?php
/**
* Module :: Description
* Created 2018-07-17
* Modify 	2021-08-25
*
* @return Widget
*
* @usage imed/app/mobile
*/

$debug = true;

class ImedAppMobile extends Page {
	function build() {
		$qrCodeWebApp = SG\qrcode('imed/m','{width: 512, height: 512, domain: "https://communeinfo.com/", imgWidth: "200px", imgHeight: "200px"}');

		$qrCodePlayStore = SG\qrcode('store/apps/details?id=com.softganz.imedhome','{width: 512, height: 512, domain: "https://play.google.com/", imgWidth: "200px", imgHeight: "200px"}');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'iMed@Home',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new Container([
						'class' => 'nav -banner-menu',
						'tagName' => 'nav',
						'child' => new Ui([
							'children' => [
								'<a class="btn -primary -fill" href="'.url('imed/app').'"><i class="icon -doctor"></i><span>เยี่ยมบ้าน</span></a>',
								'<a class="btn -primary -fill" href="'.url('imed/poorman').'"><i class="icon -rehabilitation"></i><span>คนยากลำบาก</span></a>',
							], // children
						]), // Ui
					]), // Container
					new Card([
						'child' => '<p class="-sg-text-center">เว็บแอพพลิเคชั่น</p>'
							. '<div class="qrcode">'
							. $qrCodeWebApp.'<br />'
							. '</div>',
							'{style: "margin: 64px 0 128px 0;"}',
					]), // Card
					new Card([
						'child' => '<p class="-sg-text-center">ดาวน์โหลดแอพพลิเคชั่นจาก Google Play Store</p>'
							. '<div class="qrcode">'
							. $qrCodePlayStore.'<br />'
							. '</div>',
							'{style: "margin: 64px 0 128px 0;"}',
					]), // Card
				], // children
			]), // Widget
		]);
	}
}
?>
<?php
function imed_app_mobile($self) {
	R::View('imed.toolbar',$self,'iMed@Home','app');

	$ui = new Ui();
	$ui->addConfig('nav', '{class: "nav -banner-menu"}');

	$ui->add('<a class="btn -primary -fill" href="'.url('imed/app').'"><i class="icon -doctor"></i><span>เยี่ยมบ้าน</span></a>');
	$ui->add('<a class="btn -primary -fill" href="'.url('imed/poorman').'"><i class="icon -rehabilitation"></i><span>คนยากลำบาก</span></a>');
	$ret.=$ui->build();


	$qrCodeWebApp = SG\qrcode('imed/m','{width: 512, height: 512, domain: "https://communeinfo.com/", imgWidth: "200px", imgHeight: "200px"}');

	$qrCodePlayStore = SG\qrcode('store/apps/details?id=com.softganz.imedhome','{width: 512, height: 512, domain: "https://play.google.com/", imgWidth: "200px", imgHeight: "200px"}');

	$qrCard = new Ui(NULL, 'ui-card');
	$qrCard->add(
		'<p class="-sg-text-center">เว็บแอพพลิเคชั่น</p>'
		. '<div class="qrcode">'
		. $qrCodeWebApp.'<br />'
		. '</div>',
		'{style: "margin: 64px 0 128px 0;"}'
	);

	$qrCard->add('<p class="-sg-text-center">ดาวน์โหลดแอพพลิเคชั่นจาก Google Play Store</p>'
		. '<div class="qrcode">'
		. $qrCodePlayStore.'<br />'
		. '</div>',
		'{style: "margin: 64px 0 128px 0;"}'
	);

	$ret .= $qrCard->build();

	$ret.='<style type="text/css">
	.toolbar.-main .nav.-submodule {display:none;}
	</styel>';
	return $ret;
}
?>