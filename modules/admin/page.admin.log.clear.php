<?php
function admin_log_clear($self) {
	set_time_limit(0);
	$itemToClear = \SG\getFirst(post('i'),100000);
	$deleteStep = \SG\getFirst(post('s'),10000);
	$waitTimeSec = \SG\getFirst(post('w'),20);

	$ret=_NL.'<h2>CLEAR COUNTER LOG. <span id="statustext"></span> <span id="waittime">0</span> sec.</h2>'._NL;
	$rs=mydb::select('SELECT MIN(`id`) firstID , MAX(`id`) lastID, MIN(`log_date`) firstLogDate FROM %counter_log% LIMIT 1');
	$maxId = intval($rs->lastID);
	$minId = intval($rs->firstID);
	$counterInfo = 'Counter <strong>'.number_format($maxId-$minId).'</strong> records range ID from <strong>'.number_format($minId) .'</strong> to <strong>'.number_format($maxId).'</strong> start date '.$rs->firstLogDate;

	$ret.='<p id="loginfo">'.$counterInfo.'</p>'._NL;
	$ret.='<form id="adminlogclear" method="get" action="'.url(q()).'">'._NL.'Delete <input id="itemtoclear" class="form-text -numeric" type="text" name="i" value="'.$itemToClear.'" size="8" /> logs step <input id="deleteStep" class="form-text -numeric" type="text" name="s" value="'.$deleteStep.'" size="8" /> items wait <input id="waittime-sec" class="form-text -numeric" type="text" name="w" value="'.$waitTimeSec.'" size="2" /> sec.'._NL.'<button class="btn -primary" type="submit" name="delete" value="Clear log"><span>CLEAR LOG</span></button> <a href="'.url(q(),array('i'=>$itemToClear)).'">Refresh</a>'._NL;
	$ret.='</form>'._NL;

	$ret.='<div id="result"></div>'._NL;

	if ($itemToClear && $_REQUEST['delete']) {
		$rowOnEach=$itemToClear<$deleteStep ? $itemToClear:$deleteStep;
		$startID=$minId;
		$result=array('info'=>$counterInfo,'html'=>'');
		do {
			$stmt='DELETE LOW_PRIORITY FROM %counter_log% WHERE `id` BETWEEN :clearid AND :clearid+'.($rowOnEach-1);
			$stmt='DELETE FROM %counter_log% WHERE `id` BETWEEN :clearid AND :clearid+'.($rowOnEach-1);
			//$stmt='DELETE LOW_PRIORITY FROM %counter_log% LIMIT '.$rowOnEach;
			mydb::query($stmt,':clearid',$startID);
			$result['html'].='<p>@'.date('H:i:s').' :: Delete id '.$startID.'-'.($startID+($rowOnEach-1)).' :: '.mydb()->_query.'</p>';
			$startID+=$rowOnEach;
			sleep(5);
		} while ($startID<$minId+$itemToClear);
		$rs=mydb::select('SELECT MIN(`id`) firstID , MAX(`id`) lastID, MIN(`log_date`) firstLogDate FROM %counter_log% LIMIT 1');
		$maxId=$rs->lastID;
		$minId=$rs->firstID;
		$counterInfo='Counter <strong>'.number_format($maxId-$minId).'</strong> records range ID from <strong>'.number_format($minId) .'</strong> to <strong>'.number_format($maxId).'</strong> start date '.$rs->firstLogDate;
		$result['info']=$counterInfo;
		//print_o($result,'$result',1);
		die(json_encode($result));
	}

	$ret.='<script type="text/javascript">
	$(document).ready(function() {
		$(document).on("submit", "#adminlogclear", function() {
			var url = $(this).attr("action")
			var itemToClear = parseInt($("#itemtoclear").val())
			var delayTime = $("#waittime-sec").val() * 1000
			var $statusText = $("#statustext")
			var $waitTime = $("#waittime")
			var i = 1
			var itemOnStep =  parseInt($("#deleteStep").val())
			var debug = ""
			var param = {}
			$("#result").html("Start process to clear " + itemToClear + " logs step " + itemOnStep + ".<br />")

			var tid = setInterval(function() {
				$waitTime.text(parseInt($waitTime.text()) + 1)
			}, 1000);

			function processClearLog() {
				var i = 0

				function clearLogItem() {
					$waitTime.text(0)
					$statusText.text("Sending clear request")
					param.i = itemOnStep
					param.s = itemOnStep
					param.delete = "Yes"
					$("#result").prepend("<p><b>Remaining " + (itemToClear - i) + " logs to process.</b></p>");
					$.ajax({
						url: url,
						method: "POST",
						async: true,
						data: param,
						dataType: "html",
						success: function(data) {
							var result = jQuery.parseJSON(data);
							$("#result").prepend(result.html);
							$("#loginfo").html(result.info);
							$statusText.text("Waiting for next request")
							$waitTime.text(0)
							i = i + itemOnStep;
							if (i < itemToClear) {
								setTimeout(clearLogItem, delayTime);
							} else {
								clearInterval(tid)
								$("#result").prepend("<p><b>Delete " + itemToClear + " logs process completed!!!!</b></p>");
							}
						}
					})
				}
				clearLogItem()
			}

			processClearLog()

			/*
			do {
				param.i=itemOnStep;
				param.s=itemOnStep;
				param.delete="Yes";
				$.ajax({
						type: "POST",
						url: url,
						data: param,
						async: false,
						timeout: 60*1000,
						dataType: "html",
						success: function(data) {
												var result=jQuery.parseJSON(data);
												$("#result").prepend(result.html);
												$("#loginfo").html(result.info);
											},
					});
				i=i+itemOnStep;
			} while (i<itemToClear);
			$("#result").prepend("<p><b>Completed!!!!</b></p>");
			*/

			return false
		});



// no while loop is needed
// just call getAllImages() and pass it the 
// position and the maxImages you want to retrieve

	});
	</script>';

	return $ret;




	/*		
	if ($itemToClear && $_REQUEST['delete']) {
		$loop=10;
		$itemPerLoop=round($itemToClear/$loop);
		for ($i=1;$i<=$loop;$i++) {
			$stmt='DELETE LOW_PRIORITY FROM %counter_log% WHERE `id`<:clearid LIMIT '.$itemPerLoop.';';
			mydb::query($stmt,':clearid',$minId+$itemToClear);
			if ($itemToClear) $ret.='<p>'.mydb()->_query.'</p>';
			flush();
		}
	}
	*/
	
	return $ret;
}
?>