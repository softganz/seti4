<?php
/**
 * Write a note
 *
 * @return String
 */
function imed_at_note($self) {
	$ret.='<textarea id="myWriteBox" class="writeBox" data-service="Take notes" placeholder="เขียนบันทึกของคุณ"></textarea>';
	$ret.='<div class="toolbar"><ul><li><a href="#friends">เพื่อน</a></li><li><a href="#time">เวลา</a></li><li><a href="#pin">ปักพิน</a></li></ul><ul class="post"><li><button>โพสท์</button></li></ul></div>';
	return $ret;
}
?>