<?php
/**
* Green :: Plants API
* Created 2021-11-30
* Modify  2021-11-30
*
* @return Widget
*
* @usage green/api/plants
*/

class GreenApiPlants extends Page {
	var $lanId;
	var $orgId;
	var $userId;

	function __construct() {
		$this->landId = post('landId');
		$this->orgId = post('orgId');
		$this->userId = post('userId');
	}

	function build() {

		// if ($this->landId) mydb::where('p.`landId` = :landId', ':landId', $this->landId);
		// if ($this->orgId) mydb::where('p.`orgId` = :orgId', ':orgId', $this->orgId);
		// if ($this->userId) mydb::where('p.`uid` = :userId', ':userId', $this->userId);

		mydb::value('$LIMIT$', 'LIMIT 30');

		// $plantDbs = mydb::select('SELECT
		// 	p.`plantId`
		// 	, p.`productName`
		// 	, p.`landId`
		// 	, land.`landName`
		// 	, p.`startDate`
		// 	, p.`cropDate`
		// 	, SUM(p.`qty`) `qty`
		// 	, p.`uid` `userId`
		// 	, u.`name` `ownerName`
		// 	, p.`orgId`
		// 	, o.`name` `orgName`
		// 	, p.`detail` `productDetail`
		// 	, GROUP_CONCAT(photo.`file`) `photoList`
		// 	, FROM_UNIXTIME(p.`created`, "%Y-%m-%d %H:%i:%s") `createDate`
		// 	FROM %ibuy_farmplant% p
		// 		LEFT JOIN %db_org% o ON o.`orgid` = p.`orgid`
		// 		LEFT JOIN %ibuy_farmland% land ON land.`landId` = p.`landId`
		// 		LEFT JOIN %users% u ON u.`uid` = p.`uid`
		// 		LEFT JOIN %topic_files% photo ON photo.`refid` = p.`plantId` AND photo.`tagname` = p.`tagname`
		// 	%WHERE%
		// 	GROUP BY `plantId`
		// 	ORDER BY `plantId` DESC
		// 	$LIMIT$;
		// 	-- PROJECT API PLANNING SUMMARY : COUNT PLANNING
		// 	'
		// );

		mydb::where('m.`tagname` IN ("GREEN,ACTIVITY")');
		if ($this->landId) mydb::where('m.`landId` = :landId', ':landId', $this->landId);
		// if ($this->orgId) mydb::where('p.`orgId` = :orgId', ':orgId', $this->orgId);
		if ($this->userId) mydb::where('m.`uid` = :userId', ':userId', $this->userId);

		$plantDbs = mydb::select(
			'SELECT
				m.`msgId`
				, m.`uid` `userId`
				, u.`username`
				, u.`name` `ownerName`
				, m.`tagName`
				, m.`privacy`
				, m.`landId`
				, m.`plantId`
				, m.`stayTime`
				, m.`likeTimes` `likeCount`
				, m.`message`
				, FROM_UNIXTIME(m.`created`, "%Y-%m-%d") `createDate`
				, GROUP_CONCAT(photo.`file`) `photoList`
			FROM %msg% m
				LEFT JOIN %users% u ON u.`uid` = m.`uid`
				LEFT JOIN %topic_files% photo ON photo.`refId` = m.`msgId` AND photo.`tagname` = m.`tagname`
			%WHERE%
			GROUP BY m.`msgId`
			ORDER BY `msgId` DESC
			$LIMIT$
			'
		);
		// debugMsg($plantDbs, '$plantDbs');
		foreach ($plantDbs->items as $key => $value) {
			$photoList = [];
			if ($value->photoList) {
				foreach(explode(',', $value->photoList) as $photoFile) {
					$photoList[] = _DOMAIN.'/upload/pics/'.$photoFile;
				}
			}
			$plantDbs->items[$key]->photoList = $photoList;
			$plantDbs->items[$key]->ownerPhoto = _DOMAIN.model::user_photo($value->username);
		}
		// debugMsg(mydb()->_query);
		$result = (Object) [
			'count' => count($plantDbs->items),
			'items' => $plantDbs->items,
		];
		return $result;
	}
}
?>