<?php
cfg('ibuy.version','3.10');
cfg('ibuy.release','20.6.30');

menu('ibuy/resaler/register','Resaler register','ibuy','__controller',1,user_access('create new resaler'),'static');
menu('ibuy/cart/items','ibuy cart','ibuy','__controller',1,true,'static');
//menu('ibuy/*0','Product View Detail','paper','view',1,'access ibuys','static');
menu('ibuy','ibuy main page','ibuy','__controller',1,'access ibuys','static');

tr('load','ibuy');

define('__IBUY_TYPE_ORDER',0);
define('__IBUY_TYPE_FRANCHISE',1);
define('__IBUY_STATUS_TRANSFER',20);
define('_QTGROUP_GOGREEN',5);

cfg('member.reserved',array('list','myshop','register'));

include_once('class.paper.init.php');
require_once('class.ibuy.model.php');

head('ibuy.js','<script type="text/javascript" src="ibuy/js.ibuy.js"></script>');
if (user_access('administer ibuys')) head('ibuy.admin.js','<script type="text/javascript" src="ibuy/js.ibuy.admin.js"></script>');


cfg('ibuy.permission', 'administer ibuys,access ibuys,access ibuys customer,access ibuys report,access ibuys admin report,create ibuy paper,create franchise paper,create new franchise,create new resaler,edit own product content,buy ibuy product,ibuy franchise price,ibuy resaler price,create own shop');

?>