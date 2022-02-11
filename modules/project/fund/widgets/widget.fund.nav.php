<?php
/**
* Module :: Description
* Created 2021-09-11
* Modify 	2021-09-11
*
* @param Array $args
* @return Widget
*
* @usage new FundNavWidget([])
*/

$debug = true;

class FundNavWidget extends Widget {
	var $fundInfo;

	function __construct($fundInfo = NULL) {
		$this->fundInfo = $fundInfo;
	}

	function build() {
		$orgId = $this->fundInfo->orgId;

		if ($orgId) {
			return new Row([
				'class' => '-fund',
				'children' => [
					'<a href="'.url('project/fund/'.$orgId).'"><i class="icon -material">home</i><span>หน้าหลัก</span></a>',
					'<a href="'.url('project/fund/'.$orgId.'/financial').'"><i class="icon -material">attach_money</i><span>การเงิน</span></a>',
					'<a href="'.url('project/fund/'.$orgId.'/planning').'"><i class="icon -material">device_hub</i><span>แผนงาน</span></a>',
					'<a href="'.url('project/fund/'.$orgId.'/proposal').'"><i class="icon -material">nature_people</i><span>พัฒนาโครงการ</span></a>',
					'<a href="'.url('project/fund/'.$orgId.'/follow').'"><i class="icon -material">directions_run</i><span>ติดตามโครงการ</span></a>',
					'<a href="'.url('project/fund/'.$orgId.'/eval').'" title="แบบประเมินตนเองของกองทุน"><i class="icon -material">assessment</i><span>แบบประเมิน</span></a>',
					'<a href="javascript:window.print()" title="พิมพ์"><i class="icon -material">print</i><span>พิมพ์</span></a>',
				],
			]);
		} else {
			return new Row([
				'class' => '-fund',
				'children' => [
					'<a href="'.url('project/fund/my').'"><i class="icon -material"><img src="//softganz.com/img/img/localfund-home.png" width="120" height="120"></i><span>กองทุน</span></a>',
					'<a href="'.url('project/report').'"><i class="icon -material">insights</i><span>วิเคราะห์</span></a>',
				],
			]);
		}
	}
}
?>