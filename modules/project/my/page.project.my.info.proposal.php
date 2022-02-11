<?php
/**
* Project :: My Proposal Information
* Created 2021-12-13
* Modify  2021-12-13
*
* @return Widget
*
* @usage project/my/info/proposal
*/

import('model:project.proposal.php');

class ProjectMyInfoProposal extends Page {
	function build() {
		$proposals = ProjectProposalModel::items(['userId' => 'member']);
		if (empty($proposals->count)) return NULL;

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Proposal',
			]), // AppBar
			'body' => new Container([
				'children' => [
					new ScrollView([
						'child' => new Table([
							'thead' => [
								'',
								'title -fill' => 'ชื่อโครงการ',
								'budget -money -nowrap' => 'งบประมาณ',
							],
							'children' => (function($proposalsItems) {
								$rows = [];
								foreach ($proposalsItems as $item) {
									$rows[] = [
										'<img class="profile-photo -sg-24" src="'.model::user_photo($item->username).'" />',
										'<a href="'.url('project/proposal/'.$item->projectId).'">'.SG\getFirst($item->title, '???').'</a>',
										number_format($item->budget,2),
									];
								}
								return $rows;
							})($proposals->items),
						]), // Table
					]), // ScrollView
				], // children
			]), // Container
		]);
	}
}
?>