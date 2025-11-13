<?php
/**
 * System class for system management
 *
 * @package system
 * @version 4.00
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * Created :: 2007-12-17
 * Modify  :: 2025-11-13
 * Version :: 2
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

cfg('api.version', '1.00.00');
cfg('api.release', '18.2.13');

cfg('comment.version', '4.00.00');
cfg('comment.release', '17.7.29');

cfg('contents.version', '1.00.00');
cfg('contents.release', '18.2.13');

cfg('my.version', '4.00.00');
cfg('my.release', '18.9.19');

cfg('stats.version', '4.0.01');
cfg('stats.release', '2010-05-21');

cfg('system.version', '4.00.00');
cfg('system.release', '17.7.29');

cfg('tags.version', '4.00.00');
cfg('tags.release', '18.9.21');

cfg('user.version', '4.00.00');
cfg('user.release', '18.8.3');

cfg('watchdog.version', '4.00.00');
cfg('watchdog.release', '17.7.29');


menu('api', 'API', 'api', '__controller', 1, true, 'static');
menu('comment', 'Comment management', 'comment', '__controller', 1, 'access comments', 'static');
menu('contents', 'Contents', 'contents', '__controller', 1, true, 'static');
menu('cookies', 'Cookies', 'cookies', '__controller', 1, true, 'static');
menu('my/account/delete', 'My Account Delete Request', 'my.account.delete', '__controller', 1, true, 'static');
menu('my', 'My Account Management', 'my', '__controller', 1, i()->ok, 'static');
menu('node', 'Node', 'node', '__controller', 1, true, 'static');
menu('profile', 'System', 'profile', '__controller', 1, true, 'static');
menu('qrcode', 'QR Code', 'qrcode', '__controller', 1, true, 'static');
menu('rss', 'RSS', 'rss', '__controller', 1, true, 'static');
menu('signin', 'Sign In', 'signin', '__controller', 1, true, 'static');
menu('signout', 'Sign Out', 'signout', '__controller', 1, true, 'static');
menu('stats/report', 'Web Statistic Report', 'stats', '__controller', 1, 'access statistic report', 'static');
menu('stats', 'Web Statistic', 'stats', '__controller', 1, 'access statistic', 'static');
menu('system', 'System', 'system', '__controller', 1, true, 'static');
menu('tags', 'Tags', 'tags', '__controller', 1, true, 'static');
menu('underconstruction', 'Under Construction', 'underconstruction', '__controller', 1, true, 'static');
menu('user/register', 'Member register', 'user', '__controller', 1, 'register new member' , 'static');
menu('user', 'User Management', 'user', '__controller', 1, true, 'static');
menu('watchdog', 'Watchdog', 'watchdog', '__controller', 1, true, 'static');

menu('ampur', 'Ampur API', 'ampur', '__controller', 1, true, 'static');
menu('address', 'API', 'address', '__controller', 1, true, 'static');
menu('changwat', 'API', 'changwat', '__controller', 1, true, 'static');
menu('commune', 'API', 'commune', '__controller', 1, true, 'static');
menu('hospital', 'API', 'hospital', '__controller', 1, true, 'static');
menu('image', 'API', 'image', '__controller', 1, true, 'static');
menu('my', 'API', 'my', '__controller', 1, true, 'static');
menu('node', 'API', 'node', '__controller', 1, true, 'static');
menu('person', 'API', 'person', '__controller', 1, true, 'static');
menu('tag', 'API', 'tag', '__controller', 1, true, 'static');
menu('tambon', 'API', 'tambon', '__controller', 1, true, 'static');
menu('village', 'API', 'village', '__controller', 1, true, 'static');


cfg('comment.permission','access comments,administer comments,hide comments,post comments,post comments without approval,edit own comment');

cfg('system.permission','access debugging program,access administrator pages,administer access control,administer contents,upload document,upload photo,upload video');

cfg('user.permission','access user profiles,administer users,change own username,change own profile,register new member');

cfg('watchdog.permission','access statistic,access statistic report,access logs,administer watchdogs');
?>