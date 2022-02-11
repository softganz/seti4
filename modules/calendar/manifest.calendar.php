<?php
/**
 * calendar class for calendar and room management
 *
 * @package calendar
 * @version 0.12
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2007-03-06
 * @modify 2012-10-01
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

cfg('calendar.version','0.40.0');
cfg('calendar.release','20.6.24');

menu('calendar/room','Calendar room management','calendar.room','__controller',2,true,'static');
menu('calendar/car','Car Reservation main page','calendar.car','__controller',2,'access calendar cars','static');

menu('calendar','Calendar','calendar','__controller',1,true,'static');

cfg('calendar.permission', 'access calendars, administer calendars,create calendar content,edit own calendar content, access calendar rooms, administer calendar rooms, create calendar room content,edit own calendar room content');


head('calendar.js','<script type="text/javascript" src="/calendar/js.calendar.js"></script>');

?>