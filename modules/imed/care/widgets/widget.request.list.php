<?php
/**
* Module :: Description
* Created 2021-08-21
* Modify 	2021-08-21
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

$debug = true;

class RequestListWidget extends Widget {
	var $takerId;
	var $giverId;
	var $title;
	var $leading;
	var $trailing;

	function __construct($args = []) {
		parent::__construct($args);
	}

	function build() {
		// debugMsg($this,'$this');
		return new Container([
			'children' => [
				$this->title ? new ListTile([
					'crossAxisAlignment' => 'center',
					'title' => $this->title,
					'leading' => $this->leading ? $this->leading : NULL,
					'trailing' => $this->trailing ? $this->trailing : NULL,
				]) : NULL,
				new Container([
					'children' => (function() {
						$result = [];
						foreach ($this->children as $item) {
							if ($this->takerId) {
								$profilePhoto = $item->giverUsername;
								$profileName = $item->giverName ? 'Service by <b>'.$item->giverRealName.'</b>' : '<span style="color:#ffa162;">ยังไม่กำหนดผู้ให้บริการ</span>';
							} else if ($this->giverId) {
								$profilePhoto = $item->takerUsername;
								$profileName = 'Request by '.$item->takerName;
							} else {
								$profilePhoto = $item->takerUsername;
								$profileName = 'Request by <b>'.$item->takerName.'</b> '.($item->giverName ? 'Service by <b>'.$item->giverRealName.'</b>' : '<span style="color:#ffa162;">ยังไม่กำหนดผู้ให้บริการ</span>');
							}

							$result[] = new Card([
								'class' => 'sg-action',
								'href' => url('imed/care/req/'.$item->keyId),
								'webview' => SG\getFirst($item->serviceName,'Package ??'),
								'data-options' => '{history: true, actionBar: false}',
								'children' => [
									new ListTile([
										'crossAxisAlignment' => 'start',
										'class' => '-pending-service',
										'leading' => '<img class="profile-photo" src="'.model::user_photo($profilePhoto).'" />',
										'title' => SG\getFirst($item->serviceName,'Package ??'),
										// 'subtitle' => $profileName,
										// 'subtitle' => 'By '.$item->takerName.' @'.sg_date($item->created, 'ว ดด ปปปป H:i').' น. Service Date Start '.sg_date($item->dateStart, 'ว ดด ปปปป'),
										'trailing' => new Row([
											'children' => [
												'<i class="icon -material'.($item->plan ? ' -complete' : '').'" title="แผนบริการ">list_alt</i>'.($item->plan ? '<span style="position: absolute; width: 1.2rem; height: 1.2rem; line-height: 1.2rem; font-size: 0.9rem; top: 0; right: 0; color: red; display: block; background-color: green; border-radius: 50%; text-align: center; color: #fff; opacity: 0.7;">'.$item->plan.'</span>' : ''),
												'<i class="icon -material" title="บันทึกบริการ">post_add</i>',
												'<i class="icon -material'.($item->done ? ' -complete' : '').'" title="บริการเรียบร้อย">'.($item->done ? 'done_all' : 'done').'</i>',
												'<i class="icon -material'.($item->paid ? ' -complete' : '').'" title="ชำระเงิน">attach_money</i>',
												'<i class="icon -material'.($item->eval ? ' -complete' : '').'" title="ประเมิน">rule</i>',
												'<i class="icon -material'.($item->closed? ' -complete' : '').'" title="ปิดคำขอบริการ">verified</i>',
												// '<i class="icon -material">hourglass_empty</i>',
												// '<i class="icon -material">navigate_next</i>',
											], // children
										]), // Row
									]), // ListTile
									new Container([
										'class' => '',
										'style' => 'padding: 0px 8px 8px; font-size: 0.9em;',
										'child' => $profileName.'<br />Request date : '.sg_date($item->created, 'ว ดด ปปปป H:i').' น. Service Date Start '.sg_date($item->dateStart, 'ว ดด ปปปป'),
									]), // Container
									// '<div class="detail">'.print_o($item,'$item').'</div>',
									// new DebugMsg($item,'$item'),
								], // children
							]);
						}
						return $result;
					})(),
				]),
			],
		]);
	}
}
?>