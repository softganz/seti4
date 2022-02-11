<?php
/**
* Project :: My Fund
* Created 2021-06-08
* Modify 	2021-06-08
*
* @return Widget
*
* @usage module/{id}/method
*/

$debug = true;

class ProjectFundMy extends Page {
	function build() {
		if (!i()->ok) {
			return R::View('signform');
		}

		$stmt = 'SELECT of.`orgid`, o.*, f.`fundid`
			FROM %org_officer% of
				LEFT JOIN %db_org% o USING(`orgid`)
				LEFT JOIN %project_fund% f USING(`orgid`)
			WHERE of.`uid` = :uid
			ORDER BY CONVERT(`name` USING tis620) ASC';

		$orgMember = mydb::select($stmt,':uid',i()->uid);


		if ($orgMember->count() == 0) {
			location('project/fund');
		} if ($orgMember->count() == 1) {
			$orgInfo = $orgMember->items[0];
			if ($orgInfo->fundid)
				location('project/fund/'.$orgInfo->orgid);
			else
				location('project/org/'.$orgInfo->orgid);
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ระบบบริหารกองทุนสุขภาพตำบล',
			]),
			'body' => new Widget([
				'children' => (function($orgMember) {
					$result = [];
					foreach ($orgMember->items as $rs) {
						// if ($rs->fundid)
						$result[] = new Card([
							'class' => '-sg-text-center',
							'children' => [
								'<a href="'.($rs->fundid ? url('project/fund/'.$rs->orgid) : url('org/'.$rs->orgid)).'"><img src="//softganz.com/img/img/localfund-home.png" width="120" height="120" /><h3 class="card-title">'.$rs->name.'</h3></a><p class="card-detail"></p>',
							],
						]);
					}
					return $result;
				})($orgMember),
			]), // Widget
		]);
	}
}
?>