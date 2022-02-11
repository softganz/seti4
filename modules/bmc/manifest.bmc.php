<?php
cfg('bmc.version','0.0.0');
cfg('bmc.release','2020-12-07');

menu('bmc','BMC','bmc','__controller',1,'access bmcs','static');

head('bmc.js','<script type="text/javascript" src="bmc/js.bmc.js"></script>');

cfg('bmc.permission', 'administer bmcs,access bmcs,create bmc content');

?>