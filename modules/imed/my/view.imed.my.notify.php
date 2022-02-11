<?php
/**
* Module :: Description
* Created 2021-01-01
* Modify  2021-01-01
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage R::View('imed.my.notify')
*/

$debug = true;

class ViewImedMyNotify extends Widget {
	function __construct($args = []) {
		parent::__construct($args);
	}

	function build() {
		return new Ui([
			'class' => 'ui-card imed-notify',
			'children' => (function() {
				// Check Waiting group invite
				$stmt = 'SELECT
					b.`fldref` `orgid`, b.`keyid` `uid`
					, u.`username`, u.`name`
					, o.`name` `orgName`
					, b.`flddata` `data`
					FROM %bigdata% b
						LEFT JOIN %users% u ON u.`uid` = b.`keyid`
						LEFT JOIN %db_org% o ON o.`orgid` = b.`fldref`
					WHERE b.`keyname` = "imed" AND b.`fldname` = "group.invite" AND b.`keyid` = :uid';
				$watingInvite = mydb::select($stmt, ':uid', i()->uid);

				$result = [];
				if ($watingInvite->count()) {
					$result[] = [
						'text' => '<div class="detail">'
							. '<b>มีคำเชิญให้เข้าร่วมกลุ่ม</b>'
							. '</div>'
							. '<nav class="nav -card -sg-text-right"><a class="sg-action btn -primary" href="'.url('imed/social/my/invite').'" data-rel="box" data-width="480"><span>รายละเอียด</span></a></nav>',
						'options' => [
							'class' => 'sg-action ui-item',
							'href' => url('imed/social/my/invite'),
							'data-rel' => 'box',
							'data-width' => 480,
						],
					];
				}
				return $result;
			})(),
		]);
	}
}
?>