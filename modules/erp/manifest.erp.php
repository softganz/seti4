<?php
cfg('erp.version','0.0.0');
cfg('erp.release','2021-12-01');

menu('erp','ERP','erp','__controller',1,'access erps','static');

head('erp.js','<script type="text/javascript" src="erp/js.erp.js"></script>');

cfg('erp.permission', 'administer erps,access erps,create erp content');

?>