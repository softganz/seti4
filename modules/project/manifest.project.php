<?php
/**
 * Project class for project management
 *
 * @package project
 * @version 4.4.01
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2010-05-25
 * @modify  2022-02-13
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

cfg('project.version','4.4.01');
cfg('project.release','2022-02-13');

menu('project/get','Get project from query','project.get','__controller',2,true,'static');
menu('project/proposal','Access Project Proposal','project','__controller',1,'access project proposals','dynamic');

menu('project','Project Homepage','project','__controller',1,'access projects,access own projects','static');

$dict['th']['post new comment'] = cfg('project.msg.postnewcomment');
tr($dict);

cfg('project.permission', 'administer projects,access projects,access projects qt,access projects person,access own projects,access full expense,access activity expense,create project content,create project org,create project other, edit own project content,comment project,comment own project,create project planning,create project set,create project proposal,create project form,access project proposals');

define('_PROJECT_DRAFTREPORT', 0);
define('_PROJECT_COMPLETEPORT', 1);
define('_PROJECT_LOCKREPORT', 2);
define('_PROJECT_PASS_HSMI', 6);
define('_PROJECT_PASS_SSS', 9);

define('_PROJECT_OWNER_ACTIVITY', 1);
define('_PROJECT_TRAINER_ACTIVITY', 2);

define('_PROJECT_QTGROUP', 10);

define('_PROJECT_TAGNAME', 'info');
define('_PROPOSAL_TAGNAME', 'develop');

define('_INBOARD_CODE', 1);

define('_PROJECT_PERIOD_FLAG_CREATE', 0); // เริ่มทำรายงานการเงินประจำงวด
define('_PROJECT_PERIOD_FLAG_SEND', 1); // แจ้งรายงานเสร็จสมบูรณ์
define('_PROJECT_PERIOD_FLAG_TRAINER', 2); // ผ่านการตรวจสอบของพี่เลี้ยงโครงการ
define('_PROJECT_PERIOD_FLAG_MANAGER', 6); // ผ่านการตรวจสอบของผู้จัดการโครงการ
define('_PROJECT_PERIOD_FLAG_GRANT', 9); // ผ่านการตรวจสอบของผู้ให้ทุน

define('_PROJECT_OWNERTYPE_NETWORK', 'network');
define('_PROJECT_OWNERTYPE_UNIVERSITY', 'university');
define('_PROJECT_OWNERTYPE_TAMBON', 'tambon');
define('_PROJECT_OWNERTYPE_GRADUATE', 'graduate');
define('_PROJECT_OWNERTYPE_STUDENT', 'student');
define('_PROJECT_OWNERTYPE_PEOPLE', 'people');

head('js.project.js','<script type="text/javascript" src="/project/js.project.js"></script>');

include_once('class.project.model.php');
include_once('class.project.base.php');
include_once('class.project.cfg.php');

?>