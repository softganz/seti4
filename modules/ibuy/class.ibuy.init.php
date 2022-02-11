<?php
cfg('ibuy.version','2.00');
cfg('ibuy.release','15.12.6');

//menu('ibuy/product/post','Product Post New','ibuy.product','post',3,'access ibuys','static');
//menu('ibuy/product/name','Product listing by name','ibuy.product','listing',2,'access ibuys','static');
//menu('ibuy/product/category','Product listing by category','ibuy.product','list_category',2,'access ibuys','static');
//menu('ibuy/product/search','Product Search','ibuy.product','search',3,'access ibuys','static');
//menu('ibuy/product','Product main page','ibuy.product','__controller',2,'access ibuys','static');

//menu('ibuy/category','Product List By Category','ibuy','category',1,'access ibuys','static');

//menu('ibuy/franchise/myshop','Franchise my shop','ibuy.franchise','myshop',3,'access ibuys','static');
//menu('ibuy/franchise/list','Franchise main page','ibuy.franchise','listing',0,'access ibuys','static');
//menu('ibuy/franchise/search','Franchise main page','ibuy.franchise','home',0,'access ibuys','static');
//menu('ibuy/franchise/register','Franchise register','ibuy.franchise','register',3,user_access('create new franchise'),'static');
//menu('ibuy/franchise/edit','Franchise shop edit','ibuy.franchise','shop_edit',3,'access ibuys','static');
//menu('ibuy/franchise/get/name','Franchise get name','ibuy.franchise','get_name',2,true,'static');
//menu('ibuy/franchise/*/edit','Franchise shop edit','ibuy.franchise','shop_edit',2,'access ibuys','static');
//menu('ibuy/franchise/*','Franchise main page','ibuy.franchise','view',2,'access ibuys','static');
//menu('ibuy/franchise','Franchise main page','ibuy.franchise','home',0,'access ibuys','static');

menu('ibuy/resaler/register','Resaler register','ibuy','__controller',1,user_access('create new resaler'),'static');
//menu('ibuy/resaler','Resaler main page','ibuy.resaler','home',0,'access ibuys','static');

//menu('ibuy/service','Service main page','ibuy.service','home',0,'access ibuys','static');
//menu('ibuy/service/report','iBuy report','ibuy.report','__controller',3,'administer ibuys','static');

//menu('ibuy/status/cart','Status of order','ibuy.status','cart',3,'access ibuys','static');
//menu('ibuy/status/order','Status of order process','ibuy.status','order',3,'access ibuys','static');
//menu('ibuy/status/claim','Status of claim process','ibuy.status','claim',3,'access ibuys','static');
//menu('ibuy/status/monitor','Status monitor','ibuy.status','monitor',3,'administer ibuys','static');

//menu('ibuy/status/order/*/remark','Order remark form','ibuy.status','order_remark_form',3,'administer ibuys','static');
//menu('ibuy/status/*/recieve','Set order status to recieved','ibuy.status','set_order_status_recieved',2,'access ibuys','static');
//menu('ibuy/status','Status main page','ibuy.status','__controller',2,'access ibuys','static');

//menu('ibuy/*/add2cart','Cart add item page','ibuy.cart','add2cart',1,'access ibuys','static');
//menu('ibuy/cart/proceed','Cart proceed','ibuy.cart','proceed',2,'access ibuys','static');
//menu('ibuy/cart/items','Cart proceed','ibuy.cart','items',2,'access ibuys','static');
//menu('ibuy/cart','Cart main page','ibuy.cart','home',2,'access ibuys','static');

menu('ibuy/cart/items','ibuy cart','ibuy','__controller',1,true,'static');
//menu('ibuy/cart','ibuy cart','page.ibuy','__controller',1,'access ibuys','static');
//menu('ibuy/report','iBuy report','ibuy.report','__controller',2,true,'static');
//menu('ibuy/edit','iBuy edit','ibuy.edit','__controller',2,true,'static');
//menu('ibuy/shop','ibuy main page','page.ibuy','__controller',1,true,'static');
//menu('ibuy/admin','ibuy main page','page.ibuy','__controller',1,'administer ibuys','static');
//menu('ibuy/member','ibuy main page','page.ibuy','__controller',1,'access ibuys','static');

//menu('ibuy/payment/form','Payment form','ibuy','payment_form',3,'access ibuys','static');
//menu('ibuy/payment','Payment process','ibuy','payment',1,'access ibuys','static');
//menu('ibuy/page','Product main other page','ibuy','listing',1,'access ibuys','static');
menu('ibuy/*0','Product View Detail','paper','view',1,'access ibuys','static');
menu('ibuy','ibuy main page','ibuy','__controller',1,'access ibuys','static');

//load_module('class.paper.init.php');

R::Manifest('paper');

tr('load','ibuy');

define('__IBUY_TYPE_ORDER',0);
define('__IBUY_TYPE_FRANCHISE',1);
define('__IBUY_STATUS_TRANSFER',20);
cfg('member.reserved',array('list','myshop','register'));

require_once('class.ibuy.model.php');
head('ibuy.js','<script type="text/javascript" src="ibuy/js.ibuy.js"></script>');
if (user_access('administer ibuys')) head('ibuy.js','<script type="text/javascript" src="ibuy/js.ibuy.admin.js"></script>');
?>
