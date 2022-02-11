<?php
cfg('imed.version','2.1.0');
cfg('imed.release','5.3.19');


menu('imed/queue/add','iMed add queue','imed','queue_add',3,'method permission','static');
menu('imed/queue/get','iMed get queue','imed','queue_get',3,'method permission','static');
menu('imed/queue','iMed list queue','imed','queue_list',3,'method permission','static');

menu('imed/service/*/edit','iMed edit service','imed.service','edit',2,'method permission','static');
menu('imed/service/*/add2queue','iMed set status to queue','imed.service','add2queue',2,'method permission','static');
menu('imed/service/*/send2doctor','iMed set status to doctor','imed.service','send2doctor',2,'method permission','static');
menu('imed/service/*/complete','iMed set status to complete','imed.service','set_complete',2,'method permission','static');
menu('imed/service/*/cancel','iMed service cancel','imed.service','set_cancel',2,'method permission','static');
menu('imed/service/updatememo','iMed update service memo','imed.service','updatememo',3,'method permission','static');
menu('imed/service/*/get','iMed get status','imed.service','get_service_status',2,'method permission','static');
menu('imed/service/*','iMed service information','imed.service','view',2,'method permission','static');

menu('imed/people/add','iMed add people','imed','people_add',2,'method permission','static');
menu('imed/people/*','iMed view people','imed','people_view',2,'method permission','static');

menu('imed/rx','iMed view Rx','imed','rx',2,'method permission','static');
menu('imed/doctor','iMed doctor','imed','doctor',2,true,'static');

menu('imed/register','Pcu register','imed.pcu','register',3,true,'static');

menu('imed/manage','iMed management','imed.manage','__controller',2,'administrator imeds','static');

menu('imed/get/people','iMed get people information','imed','get_people',3,'method permission','static');

menu('imed/dashboard','iMed dashboard','imed.dashboard','__controller',2,true,'static');
menu('imed/realtime','iMed realtime page','imed','realtime',1,'method permission','static');

//menu('imed/patient','iMed@Home','imed.patient','__controller',2,true,'static');
menu('imed/qt','iMed@Home','imed.qt','__controller',2,true,'static');
menu('imed/edit','iMed@Home modify','imed.edit','__controller',2,true,'static');
menu('imed/report','iMed@Home report','imed','__controller',1,true,'static');
menu('imed/help','iMed@Home','imed.help','__controller',2,true,'static');
menu('imed/drugcontrol','iMed@Home drug control','imed.drugcontrol','__controller',2,'access imed drugcontrols','static');

menu('imed/api','iMed App','imed','__controller',1,true,'static');

menu('imed/m','Poor Man','imed','__controller',1,true,'static');
menu('imed/app','iMed App','imed','__controller',1,true,'static');
menu('imed/app/mobile','Poor Man','imed','__controller',1,true,'static');
menu('imed/poorhome','Poor Home','imed','__controller',1,true,'static');
menu('imed/app/poorman','Poor Man','imed','__controller',1,true,'static');
menu('imed/pocenter','Poor Man','imed','__controller',1,true,'static');

menu('imed/my','iMed My','imed','__controller',1,'create imed at home','static',
'{showTime: false, time: -1}');

menu('imed/care/our', 'iMedCare home', 'imed', '__controller',1, true,'static');
menu('imed/care/giver', 'iMedCare Giver', 'imed', '__controller',1,'access imed care giver','static', '{signform: {showTime: false, time: -1, showRegist: false}}');
menu('imed/care/taker', 'iMedCare Taker', 'imed', '__controller',1,'access imed care taker','static','{signform: {showTime: false, time: -1, showRegist: false}}');
menu('imed/care/my', 'iMedCare My', 'imed', '__controller',1,'access imed care taker','static','{signform: {showTime: false, time: -1, showRegist: false}}');
menu('imed/care/admin', 'iMedCare Taker', 'imed', '__controller',1,'administer imed cares','static','{signform: {showTime: false, time: -1}}');
menu('imed/care', 'iMedCare home', 'imed', '__controller',1, 'access imed cares','static');

menu('imed','imed main page','imed','__controller',1,'access imeds','static');

define('_IMED_RESULT', 'imed-app');

define('_IMED_CANCEL',-1);
define('_IMED_WAITING',0);
define('_IMED_CALLING',1);
define('_IMED_PROCESS',2);
define('_IMED_COMPLETE',127);

define('_IMED_CARE_DISABLED',1);
define('_IMED_CARE_ELDER',2);
define('_IMED_CARE_WAIT_REHAB',3);
define('_IMED_CARE_REHAB',4);

define('_IMED_CARE_SERVICE', 'Care Service');

cfg('imed.permission', 'administer imeds,access imeds,create imed pcu,create imed doctor,access imed at home,create imed at home,access imed poors,access imed cares,administer imed cares,access imed care giver,access imed care taker');

tr('load','imed');
require_once('class.imed.model.php');
?>