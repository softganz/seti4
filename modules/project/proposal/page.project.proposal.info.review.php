<?php
/**
* Project :: Proposal Review Information
* Created 2021-11-06
* Modify  2021-11-06
*
* @param Object $proposalInfo
* @return Widget
*
* @usage project/proposal/{id}/info.review
*/

$debug = true;

class ProjectProposalInfoReview extends Page {
	var $projectId;
	var $section;
	var $editMode;
	var $right;
	var $proposalInfo;

	function __construct($proposalInfo, $section) {
		$this->projectId = $proposalInfo->projectId;
		$this->proposalInfo = $proposalInfo;
		$this->editMode = $this->proposalInfo->editMode;
		$this->section = $section;
		$this->right = (Object) [
			'review' => is_admin('project'),
		];
	}

	function build() {
		static $reviewList;
		static $reviewScore;
		if (!isset($reviewList)) {
			$reviewList = mydb::select(
				'SELECT
					b.`bigId`, b.`fldRef` `section`, b.`fldData` `msg`, b.`created`
				, u.`username`, u.`name` `ownerName`
				FROM %bigdata% b
					LEFT JOIN %users% u ON u.`uid` = b.`ucreated`
				WHERE b.`keyid` = :projectId AND b.`keyname` = "project.develop" AND b.`fldname` = "review"
				ORDER BY `bigId` ASC',
				[':projectId' => $this->projectId]
			)->items;
			// debugMsg(mydb()->_query);
			// debugMsg($reviewList, '$reviewList');
			$reviewScore = mydb::select(
				'SELECT `bigId`, `fldRef` `section`, `fldData` `value` FROM %bigdata% b WHERE b.`keyid` = :projectId AND b.`keyname` = "project.develop" AND b.`fldname` = "rating";
				-- {key: "section"}',
				[':projectId' => $this->projectId]
			)->items;
			// debugMsg($reviewScore, '$reviewScore');
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->proposalInfo->title,
			]),
			'body' => new Container([
				'class' => '-review-a',
				'children' => [
					'คะแนน:',
					new Row([
						'class' => '-sg-text-center'.($this->right->review ? ' sg-inline-edit' : ''),
						'mainAxisAlignment' => 'spacearound',
						'attribute' => $this->right->review ? [
							'data-update-url' => url('project/develop/update/'.$this->projectId),
							'data-tpid' => $this->projectId,
							// 'data-debug' => 'yes',
						] : NULL,
						'children' => (function($reviewScore){
							$widgets = [];
							foreach (range(1,5) as $rateValue) {
								$widgets[] = view::inlineedit(
									[
										'group'=>'bigdata:project.develop:rating-'.$this->section,
										'tr' => $reviewScore[$this->section]->bigId,
										'fld' => 'rating',
										'name' => 'rating-'.$this->section,
										'fldref' => $this->section,
										'value' => $reviewScore[$this->section]->value,
									],
									$rateValue.':'.$rateValue,
									$this->right->review,
									'radio'
								);
							}
							// $widgets[] = new DebugMsg($reviewScore, '$reviewScore');
							// $widgets[] = $reviewScore[$this->section];
							return $widgets;
						})($reviewScore),
					]), // Row

					new Container([
						'children' => (function($reviewList) {
							$widgets = [];
							$inlineAttr = [
								'data-update-url' => url('project/develop/update/'.$this->projectId),
								'data-tpid' => $this->projectId,
							];
							foreach ($reviewList as $item) {
								if ($item->section != $this->section) continue;
								$rightToEdit = $this->right->review || $item->ucreated == i()->uid;
								$widgets[] = new Card([
									'children' => [
										new ListTile([
											'title' => $item->ownerName.' @'.sg_date($item->created, 'ว ดด ปปปป H:i'),
											'leading' => '<img class="profile-photo" src="'.model::user_photo($item->username).'" width="24" />',
										]),
										new Container([
											'class' => '-detail'.($rightToEdit ? ' sg-inline-edit' : ''),
											'attribute' => $rightToEdit ? $inlineAttr : NULL,
											'child' => view::inlineedit(
												[
													'group' => 'bigdata::'.$this->section.'-'.$item->bigId,
													'tr' => $item->bigId,
													'fld' => 'review',
													'fldref' => $this->section,
													'options' => '{class: "-fill",ret: "nl2br", placeholder: ""}',
													'value' => trim($item->msg),
												],
												nl2br($item->msg),
												$rightToEdit,
												'textarea'
											),

											// nl2br($item->msg),
										]),
									], // children
								]);
							}
							return $widgets;
						})($reviewList),
					]), // Container

					$this->right->review ? new Form([
						'action' => url('project/proposal/api/'.$this->projectId.'/review.save'),
						'class' => 'sg-form -no-print',
						'rel' => 'notify',
						'done' => 'load->replace:parent .-review-a:'.url('project/proposal/'.$this->projectId.'/info.review/'.$this->section),
						'children' => [
							'section' => ['type' => 'hidden', 'value' => $this->section],
							'msg' => [
								'type' => 'textarea',
								'class' => '-fill',
								'rows' => 5,
								'placeholder' => 'เขียนบันทึกการพิจารณาโครงการ',
							],
							'save' => [
								'type' => 'button',
								'class' => '-primary -fill',
								'value' => '<i class="icon -material">done</i><span>{tr:SAVE}</span>',
							],
						], // children
					]) : NULL, // Form
				], // children
			]), // Container,
		]);
	}
}
?>