<?php
/**
* RSS :: Get Counter
* Created 2021-09-20
* Modify  2022-05-03
*
* @param String $arg1
* @return Widget
*
* @usage rss/counter
*/

class RssCounter extends Page {
	function build() {
		// sendheader($type = 'text/xml');
		$timer = new Timer();
		$timer->start(0);

		$day = SG\getFirst($para->day, 7);

		$counter = cfg('counter');

		mydb::value('$LIMIT$', $day);
		$result = mydb::select(
			'SELECT
				`log_date`, SUM(`hits`) `hits`, SUM(`users`) `users`
			FROM %counter_day%
			GROUP BY `log_date`
			ORDER BY `log_date` DESC
			LIMIT $LIMIT$'
		);

		$timer->stop(0);

		$channel = [
			'title' => 'Counter Statistics',
			'link' => cfg('domain').'/rss/counter/day/'.$day,
			'description' => strip_tags(cfg('web.slogan')),
			'language' => 'en-us',
			'pubDate' => date('Y-m-d H:i:s'),
			'lastBuildDate' => date('Y-m-d H:i:s'),
			'generator' => 'SoftGanz RSS',
			'managingEditor' => 'support@softganz.com',
			'webMaster' => 'webmaster@softganz.com',
			'responseTime' => $timer->get(0),
			'online' => [
				'date' => date('Y-m-d H:i:s'),
				'members' => intval($counter->online_members),
				'users' => intval($counter->online_count),
				'memberName' => htmlspecialchars($counter->online_name),
			],
			'items' => [],
		];

		foreach ( $result->items as $rs) {
			$channel['items'][] = [
				'title' => 'stat',
				'date' => $rs->log_date,
				'hits' => intval($rs->hits),
				'users' => intval($rs->users),
				'description' => $rs->log_date.'/'.$rs->hits.'/'.$rs->users
			];
		}

		return $channel;

		// $ret .= $this->_create_rss($channel);

		// return $ret;
	}

	/*
	 * Create rss feed from array
	 *
	 * @param Array $channel
	 * @return String
	 */
	function _create_rss($channel=array()) {
		$header='';
		$items='';
		foreach ($channel as $key=>$value) {
			if ($key=='item' && is_array($value)) {
				foreach ($value as $item) {
					$items.='<item>'._NL;
					foreach ($item as $ikey=>$ivalue) {
						if (is_array($ivalue)) { // This item is array
							$items.='	<'.$ikey.' ';
							$item_value=NULL;
							foreach ($ivalue as $k1=>$v1) {
								if (is_string($k1)) $items.=$k1.'="'.$v1.'" ';
								else $item_value=$v1;
							}
							$items.=isset($item_value) ? '>'.$item_value.'</'.$ikey.'>':'/>';
							$items.=_NL;
						} else if (is_string($ivalue) || is_numeric($ivalue)) { // This item is string
							$items.='	<'.$ikey.'>'.$ivalue.'</'.$ikey.'>'._NL;
						}
					}
					$items.='</item>'._NL;
				}
			} else if (is_string($value)) {	// This item is in header
				$header.='<'.$key.'>'.htmlspecialchars($value).'</'.$key.'>'._NL;
			}
		}
		$ret='<?xml version="1.0" encoding="'.cfg('client.characterset').'"?>
<rss version="2.0" xml:base="'.cfg('domain').'"  xmlns:dc="http://purl.org/dc/elements/1.1/">
<channel>
'.$header.$items.'</channel>
</rss>';
		return $ret;
	}
}
?>