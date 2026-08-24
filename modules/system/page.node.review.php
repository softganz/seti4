<?php
/**
 * Node     :: Review Form
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2021-09-30
 * Modified :: 2026-08-24
 * Version  :: 3
 *
 * @param Object $nodeInfo
 * @return Widget
 *
 * @uses node/{nodeId}/review
 */

use Softganz\DB;

class NodeReview extends Page {
	var $nodeId;
	var $nodeInfo;

	function __construct($nodeInfo = NULL) {
		$this->nodeId = $nodeInfo->nodeId;
		$this->nodeInfo = $nodeInfo;
	}

	/**
	 * Right to build
	 *
	 * @return object|boolean
	 */
	function rightToBuild(): object|bool {
		if (!i()->ok) return message('status', 'สำหรับสมาชิกเท่านั้น');

		return true;
	}

	/**
	 * Build page
	 *
	 * @return object
	 */
	#[\Override]
	function build(): object {
		$reviewInfo = DB::select([
			'SELECT `cid`, `giverating`, `comment`
			FROM %topic_comments% WHERE `tpid` = :tpid AND `uid` = :uid AND `subject` = "REVIEW" LIMIT 1',
			'var' => [
				':tpid' => $this->nodeId,
				':uid' => i()->uid
			]
		]);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'รีวิวโดย '.i()->name,
				'boxHeader' => true,
				'leading' => _HEADER_BACK,
			]),
			'body' => new Widget([
				'children' => [
					new Form([
						'action' => url('api/node/info/'.$this->nodeId.'/review.save'),
						'id' => 'node-review',
						'class' => 'sg-form -node-review',
						'checkValid' => true,
						'rel' => 'notify',
						'done' => 'load | close',
						'children' => [
							'cid' => ['type'=>'hidden','value'=>$reviewInfo->cid],
							'rate' => ['type'=>'hidden','label'=>'คะแนน','require'=>true,'value'=>$reviewInfo->giverating],
							new Table([
								'class' => 'item -center',
								'thead' => [1,2,3,4,5],
								'children' => (function($reviewInfo) {
									$rows = [];
									for ($i = 1; $i <= 5; $i++) {
										$rows[0][] = ['<a class="rating-star -r'.$i.($i<=$reviewInfo->giverating ? ' -active' : '').'" href="javascript:void(0)" data-rate="'.$i.'" title="ให้คะแนน '.$i.' ดาว"><i class="icon -material">star</i></a>'];
									}
									return $rows;
								})($reviewInfo),
							]), // Table
							new Container(['class' => '-sg-text-center', 'child' => 'คลิกบนดาวเพื่อให้คะแนน']),
							'msg' => [
								'type' => 'textarea',
								'class' => '-fill',
								'rows' => 3,
								'value' => $reviewInfo->comment,
								'placeholder' => 'บอกทุกคนว่าคุณคิดอย่างไรเกี่ยวกับเรื่องนี้ หรือให้คำแนะนำว่าอย่างไร?',
							],
							'submit' => [
								'type' => 'button',
								'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
								'container' => '{class: "-sg-text-right"}',
							]
						], // children
					]), // Form
					$this->script(),
				],
			]),
		]);
	}

	private function script() {
		return '<style type="text/css">
		.rating-star .icon.-material {width: 40px; height: 40px; color: #ccc; font-size: 40px; margin: 0;}
		.rating-star:hover .icon.-material {color: red;}
		.rating-star.-active .icon.-material {color: red;}
		</style>

		<script type="text/javascript">
		$("#node-review .rating-star").click(function() {
			var $this = $(this)
			$("#node-review .rating-star").removeClass("-active")
			$("#node-review .rating-star").each(function() {
				if ($(this).data("rate") <= $this.data("rate")) {
					$(this).addClass("-active")
				}
			})
			$("#node-review #edit-rate").val($this.data("rate"))
		})
		</script>';
	}
}
?>