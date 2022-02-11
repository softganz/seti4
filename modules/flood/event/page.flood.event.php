<?php
function flood_event($self) {
	$ret.='<div id="flood-event" class="sg-load" data-url="flood/event/init">AAAA</div>';
	head('flood.event.js','<script type="text/javascript" src="/flood/js.flood.event.js"></script>');
	return $ret;
}
?>