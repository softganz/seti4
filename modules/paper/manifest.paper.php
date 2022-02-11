<?php
/**
 * Paper class for CMS management
 *
 * @package paper
 * @version 4.00.0
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2007-11-21
 * @modify 2018-02-02
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

cfg('paper.version','4.00.0');
cfg('paper.release','2.6.18');

menu('paper/*/comment/edit','Paper edit comment','paper.edit','_edit_comment_edit',3,'method permission','dynamic');
menu('paper/*/comment/delete','Paper edit comment','paper.edit','_edit_comment_delete',3,'method permission','dynamic');
menu('paper/*/comment/disable','Paper edit comment','paper.edit','_edit_comment_disable',3,'method permission','dynamic');

//menu('paper/post','Paper post new topic','paper','post',2,'method permission','static');
//menu('paper/*/edit/modify','Paper edit','paper.edit','__controller',3,true,'static');

//menu('paper/edit','Paper edit','paper','__controller',1,'method permission','dynamic');

menu('paper','Paper Home','paper','__controller',1,'access papers','static');

cfg('paper.permission', 'access papers,add bookmarks,edit own paper,administer papers,administer paper tags,post paper without approval,input format type php,input format type script');

include_once('class.paper.model.php');
?>