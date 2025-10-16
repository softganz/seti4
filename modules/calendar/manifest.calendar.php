<?php
/**
 * calendar class for calendar and room management
 *
 * @package calendar
 * @version 0.12
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * Created :: 2007-03-06
 * Modify  :: 2025-07-22
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

cfg('calendar.version','1.00.0');
cfg('calendar.release','2025-07-22');

menu('calendar/room','Calendar room management','calendar.room','__controller',2,true,'static');
menu('calendar/car','Car Reservation main page','calendar.car','__controller',2,'access calendar cars','static');

menu('calendar','Calendar','calendar','__controller',1,true,'static');

cfg('calendar.permission', 'access calendars, administer calendars,create calendar content,edit own calendar content, access calendar rooms, administer calendar rooms, create calendar room content,edit own calendar room content');
?>