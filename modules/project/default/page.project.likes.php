<?php
/**
* Project :: Likes
* Created 2021-05-31
* Modify  2021-10-01
*
* @param Array $_REQUEST
* @return Widget
*
* @usage project/likes
*/

$debug = true;

class ProjectLikes extends Page {
	var $reactionType;

	function __construct() {
		$this->reactionType = post('re');
	}

	function build() {


		if ($this->reactionType == 'plan') {
			mydb::where('r.`action` LIKE "PROJ.LIKE" AND p.`prtype` = "แผนงาน"');
		} else if ($this->reactionType == 'dev') {
			mydb::where('r.`action` LIKE "PDEV.LIKE"');
		} else {
			mydb::where('r.`action` LIKE "PROJ.LIKE" AND p.`prtype` = "โครงการ"');
		}
		$stmt = 'SELECT r.`refid` `tpid`,r.`action`,t.`title`
			, t.`approve`, t.`rating`, COUNT(*) `likes`
			FROM %reaction% r
				LEFT JOIN %topic% t ON t.`tpid` = r.`refid`
				LEFT JOIN %project% p ON p.`tpid` = t.`tpid`
			%WHERE%
			GROUP BY `refid`,`action`
			ORDER BY `likes` DESC';

		$dbs = mydb::select($stmt);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Project Likes',
				'navigator' => [
					'<a class="btn" href="'.url('project/likes', ['re'=>'plan']).'"><i class="icon -material">device_hub</i><span>แผนงาน</span></a>',
					'<a class="btn" href="'.url('project/likes', ['re'=>'dev']).'"><i class="icon -material">nature_people</i><span>พัฒนาโครงการ</span></a>',
					'<a class="btn" href="'.url('project/likes').'"><i class="icon -material">directions_run</i><span>ติดตามโครงการ</span></a>',
				], // Navigator
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new Table([
						'thead' => [
							'โครงการ',
							'approve -center'=>'<i class="icon -material">verified</i>',
							'star -center'=>'<i class="icon -material">stars</i>',
							'amt -amt'=>'<i class="icon -material">thumb_up<i>',
						],
						'children' => array_map(function($item) {
							if ($this->reactionType == 'dev') $url = url('project/develop/'.$item->tpid);
							else $url = url('project/'.$item->tpid);

							return [
								'<a class="" href="'.$url.'" target="_blank">'.$item->title.'</a>',
								'<i class="icon -material -'.['MASTER' => 'green', 'USE' => 'yellow', 'LEARN' => 'gray'][$item->approve].'">'.['MASTER' => 'verified', 'USE' => 'recommend', 'LEARN' => 'flaky'][$item->approve].'</i>',
								'<i class="icon -material rating-star '.($item->rating != '' ? '-rate-'.round($item->rating) : '').'">star</i>',
								$item->likes
							];
						}, $dbs->items),
					]), // Table
				],
			]),
		]);
	}
}
?>