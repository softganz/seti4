<?php
cfg('green.version','0.10');
cfg('green.release','20.9.20');

menu('green','Green Smile','green','__controller',1,'access greens','static');
menu('green/my','Green Smile','green','__controller',1,'create green content','static', '{signform: {showTime: false, time: -1, signret: "green/my", regRel: "#main"}}');
menu('green/rubber/my','Green Smile','green','__controller',1,'create green content','static', '{verify: "green.my.verify", signform: {showTime: false, time: -1, signret: "green/my", regRel: "#main"}}');
menu('green/organic/my','Green Smile','green','__controller',1,'create green content','static', '{verify: "green.my.verify", signform: {showTime: false, time: -1}}');

define('_QTGROUP_GOGREEN',5);
define('_GREEN_URL_MY','green/my');

head('green.js','<script type="text/javascript" src="green/js.green.js"></script>');

cfg('green.permission', 'administer greens,access greens,create green content');

?>