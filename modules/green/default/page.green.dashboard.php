<?php
/**
* Green : Dashboard
* Created 2020-10-03
* Modify  2020-10-03
*
* @param Object $self
* @return String
*
* @usage green/dashboard
*/

$debug = true;

function green_dashboard($self) {
	$ret = '';


	$mainUi = new Ui();
	$mainUi->addConfig('nav', '{class: "nav -app-menu"}');

	$mainUi->header('<h3>Dashboard</h3>');
	$mainUi->add('<a class="sg-action" href="'.url('green/organic').'" data-webview="เกษตรอินทรย์"><i class="icon -material">account_circle</i><span>เกษตรอินทรีย์</span></a>');
	$mainUi->add('<a class="sg-action" href="'.url('green/rubber').'" data-webview="สวนยางยั่งยืน"><i class="icon -material">account_circle</i><span>สวนยางยั่งยืน</span></a>');

	$ret .= $mainUi->build();


	if ($isAdmin) {
		$adminUi = new Ui();
		$adminUi->addConfig('nav', '{class: "nav -app-menu"}');
		$adminUi->header('<h3>ผู้จัดการระบบ</h3>');

		if (i()->username == 'softganz') {
			if (_DOMAIN_SHORT == 'localhost') {
				if (R()->appAgent) {
					$adminUi->add('<a href="'.url('green',array('setting:app' => '')).'"><i class="icon -material">web</i><span>www</span></a>');
				} else {
					$adminUi->add('<a href="'.url('green',array('setting:app' => '{OS:%22Android%22,ver:%220.20.0%22,type:%22App%22,dev:%22Softganz%22}')).'"><i class="icon -material">android</i><span>App</span></a>');
				}
			}

			$adminUi->add('<a href="'.url('imed/app').'"><i class="icon"><img src="//communeinfo.com/themes/default/logo-homemed.png" width="24" height="24" /></i><span>iMed@Home</span></a>');
			$adminUi->add('<a href="https://communeinfo.com"><i class="icon"><img src="//communeinfo.com/themes/default/logo-homemed.png" width="24" height="24" /></i><span>CommuneInfo</span></a>');


			if (R()->appAgent->OS == 'Android') {
				$host = preg_match('/^([a-z]+)/', _DOMAIN_SHORT, $out) ? $out[1] : _DOMAIN_SHORT;
				$isProduction = $host == 'communeinfo';
				$adminUi->add('<a class="sg-action" data-rel="none" data-webview="server" data-server="'.($isProduction ? 'DEV' : 'PRODUCTION').'" data-done="load:#main"><i class="icon -material">android</i><span>'.strtoupper($host).'</span></a>');
			}
		}


		$ret .= $adminUi->build();
	}

	return $ret;
}
?>