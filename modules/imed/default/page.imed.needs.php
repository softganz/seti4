<?php
/**
* iMed : Need List
* Created 2020-10-24
* Modify  2021-06-02
*
* @return Widget
*
* @usage imed/needs
*/

$debug = true;

class ImedNeeds {
	var $ref;

	function __construct() {
		$this->ref = post('ref');
	}

	function build() {
		$showItems = 10;
		$start = sg\getFirst(post('start'),0);

		$getChangwat = post('p');
		$getAmpur = post('a');
		$getTambon = post('t');
		$getVillage = post('v');
		$getNeedType = post('nt');
		$getDone = post('done');
		$getMyInput = post('show');

		if ($getNeedType) mydb::where('n.`needtype` = :needtype', ':needtype', $getNeedType);
		if ($getMyInput == 'my') mydb::where('n.`uid` = :uid', ':uid', i()->uid);
		mydb::where('p.`changwat` != ""');
		if ($getChangwat) mydb::where('p.`changwat` = :changwat', ':changwat', $getChangwat);
		if ($getAmpur) mydb::where('p.`ampur` = :ampur', ':ampur', $getAmpur);
		if ($getTambon) mydb::where('p.`tambon` = :tambon', ':tambon', $getTambon);
		if ($getVillage) mydb::where('p.`village` = :village', ':village', intval($getVillage));
		if ($getDone == 'y') mydb::where('n.`status` = 1');
		else if ($getDone == 'n') mydb::where('n.`status` IS NULL');

		mydb::value('$LIMIT$', 'LIMIT '.$start.','.$showItems);

		$stmt = 'SELECT
				n.*
			, u.`username`, u.`name`
			, CONCAT(p.`prename`,p.`name`," ",p.`lname`) `patient_name`
			, nt.`name` `needTypeName`
			FROM %imed_need% n
				LEFT JOIN %users% u USING(`uid`)
				LEFT JOIN %db_person% p USING(`psnid`)
				LEFT JOIN %imed_stkcode% nt ON nt.`stkid` = n.`needtype`
			%WHERE%
			ORDER BY `needid` DESC
			$LIMIT$;';

		$needDbs = mydb::select($stmt);
		// debugMsg(mydb()->_query);

		return new Container([
			'id' => 'imed-needs',
			'children' => [
				$this->_needCard($needDbs->items),
				$needDbs->_num_rows && $needDbs->_num_rows == $showItems ?
					'<div id="more" class="imed-my-note-more" style="padding: 24px 16px 44px;">'
						. '<a class="sg-action btn -primary" '
						. 'href="'.url(
							'imed/needs',
							[
								'nt' => $getNeedType,
								'show' => $getMyInput,
								'p' => $getChangwat,
								'a' => $getAmpur,
								't' => $getTambon,
								'v' => $getVillage,
								'done' => $getDone,
								'start' => $start+$needDbs->_num_rows,
								'ref' => $this->ref,
							]
						).'" '
						. 'data-rel="replace:#more" style="margin:0 auto;display:block;text-align:center; padding: 16px 0;"><span>{tr:More}</span><i class="icon -forward -white"></i></a>'
						. '</p>'
				: NULL,
			], // children
		]);
	}

	function _needCard($needItems) {
		$needCard = new Ui('div','ui-card imed-my-note -need');
		$needCard->addId('imed-my-note');

		foreach ($needItems as $rs) {
			$needCard->add(
				R::View('imed.need.render',$rs, '{ref: "'.$this->ref.'"}'),
				'{class: "-urgency-'.$rs->urgency.'", id: "noteUnit-'.$rs->seq.'"}'
			);
		}
		return $needCard;
	}
}
?>