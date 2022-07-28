<?php
/**
* Node :: Review Form
* Created 2021-09-30
* Modify  2021-09-30
*
* @param String $arg1
* @return Widget
*
* @usage node/{id}/review
*/

$debug = true;

class NodeReview extends Page {
	var $nodeId;
	var $nodeInfo;

	function __construct($nodeInfo = NULL) {
		$this->nodeId = $nodeInfo->nodeId;
		$this->nodeInfo = $nodeInfo;
	}

	function build() {
		if (!i()->ok) return message('status', 'สำหรับสมาชิกเท่านั้น');

		$stmt = 'SELECT * FROM %topic_comments% WHERE `tpid` = :tpid AND `uid` = :uid AND `subject` = "REVIEW" LIMIT 1';
		$reviewInfo = mydb::select($stmt, ':tpid', $this->nodeId, ':uid', i()->uid);
		// debugMsg($reviewInfo);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'รีวิวโดย '.i()->name,
				'boxHeader' => true,
				'leading' => _HEADER_BACK,
			]),
			'body' => new Widget([
				'children' => [
					new Form([
						'action' => url('node/api/'.$this->nodeId.'/review.save'),
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
					// new DebugMsg($nodeInfo, '$nodeInfo'),
					$this->_script(),
				],
			]),
		]);
	}

	function _script() {
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
<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function node_review($self, $tpid) {
	$getRating = post('rate');
	$getMsg = post('msg');

	$ret = '';


	if (!i()->ok) return message('status', 'สำหรับสมาชิกเท่านั้น');

	$stmt = 'SELECT * FROM %topic% WHERE `tpid` = :tpid LIMIT 1';
	$nodeInfo = mydb::select($stmt, ':tpid',$tpid);

	if ($nodeInfo->_empty) return message('error', 'ไม่มีข้อมูลตามที่ระบุ');

	$stmt = 'SELECT * FROM %topic_comments% WHERE `tpid` = :tpid AND `uid` = :uid AND `subject` = "REVIEW" LIMIT 1';
	$reviewInfo = mydb::select($stmt, ':tpid', $tpid, ':uid', i()->uid);

	$ret .= '<h3 class="title -box">Review by '.i()->name.'</h3>';


	if ($getRating) {
		if ($nodeInfo->ratetimes) {
			$currentRating = $nodeInfo->rating*$nodeInfo->ratetimes;
			$currentTimes = $nodeInfo->ratetimes;
			if ($reviewInfo->_num_rows) {
				$currentRating -= $reviewInfo->giverating;
				$currentTimes--;
			}
			$rateTimes = $currentTimes + 1;
			$newRating = round(($currentRating + $getRating) / $rateTimes,1);
		} else {
			$newRating = $getRating;
			$rateTimes = 1;
		}

		$stmt = 'UPDATE %topic% SET `rating` = :rating, `ratetimes` = :ratetimes WHERE `tpid` = :tpid LIMIT 1';
		mydb::query($stmt, ':tpid', $tpid, ':rating', $newRating, ':ratetimes', $rateTimes);
		//$ret .= mydb()->_query.'<br />';


		$data = null;
		$data->cid = post('cid') ? post('cid') : NULL;
		$data->tpid = $tpid;
		$data->uid = i()->uid;
		$data->giverating = $getRating;
		$data->status = _PUBLISH;
		$data->subject = 'REVIEW';
		$data->comment = $getMsg;
		$data->thread = $nodeInfo->title;
		$data->name = i()->name;
		$data->ip = ip2long(getenv('REMOTE_ADDR'));
		$stmt = 'INSERT INTO %topic_comments%
						(`cid`, `tpid`, `uid`, `status`, `giverating`, `subject`, `comment`, `thread`, `name`, `ip`)
						VALUES
						(:cid, :tpid, :uid, :status, :giverating, :subject, :comment, :thread, :name, :ip)
						ON DUPLICATE KEY UPDATE
						`giverating` = :giverating
						, `comment` = :comment';
		mydb::query($stmt, $data);
		//$ret .= mydb()->_query.'<br />';
		return $ret;
	}



	// Show rating form
	$form = new Form(NULL, url('node/review/'.$tpid), "node-review", 'sg-form -node-review');
	$form->addData('checkValid', true);
	$form->addData('complete','closebox');
	$form->addData('rel','reload');

	$form->addField('cid', array('type'=>'hidden','value'=>$reviewInfo->cid));
	$form->addField('rate',array('type'=>'hidden','label'=>'คะแนน','require'=>true,'value'=>$reviewInfo->giverating));

	$form->addField(
					'msg',
					array(
						'type' => 'textarea',
						'class' => '-fill',
						'rows' => 3,
						'value' => $reviewInfo->comment,
						'placeholder' => 'บอกทุกคนว่าคุณคิดอย่างไรเกี่ยวกับเรื่องนี้ ให้คำแนะนำว่าอย่างไร และ ทำไม?',
					)
				);
	$starText = 'ให้คะแนน :<br />';
	for ($i = 1; $i <= 5; $i++) {
		$starText .= '<a class="rating-star -r'.$i.($i<=$reviewInfo->giverating ? ' -active' : '').'" href="javascript:void(0)" data-rate="'.$i.'"><i class="icon -material">star</i></a>';
	}
	$form->addText($starText);

	$form->addField(
					'submit',
					array('type' => 'button', 'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>', 'container' => array('class' => '-sg-text-right'))
				);

	$ret .= $form->build();


	//$ret .= print_o($nodeInfo, '$nodeInfo');
	//$ret .= print_o($reviewInfo, '$reviewInfo');

	$ret .= '<style type="text/css">
	.rating-star .icon.-material {width: 48px; height: 48px; color: #ccc; font-size: 48px; margin: 8px;}
	.rating-star:hover .icon.-material {color: red;}
	.rating-star.-active .icon.-material {color: red;}
	</style>';

	$ret .= '<script type="text/javascript">
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
	return $ret;
}
?>