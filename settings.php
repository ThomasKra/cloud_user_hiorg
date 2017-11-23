<?php

/**
 * NextCloud -user_hiorg
 *
 * @author Klaus Herberth, Thomas Krause
 * @copyright 2015 Klaus Herberth <klaus@herberth.eu>
	* @copyright 2017 Thomas Krause <tom@krause-micro.de>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

OCP\User::checkAdminUser();

OCP\Util::addScript( 'user_hiorg', 'admin' );

$tmpl = new OCP\Template( 'user_hiorg', 'settings');

$tmpl->assign('ov', OCP\Config::getAppValue ( 'user_hiorg', 'ov' ));


$groupManager = \OC::$server->getGroupManager();
$groups = $groupManager->search('');
$group_array[''] = "[keine]";

foreach($groups as $group)
{
        $group_array[$group->getGID()] = $group->getGID();
}

$tmpl->assign('groups', $group_array);

$tmpl->assign('quota', OCP\Config::getAppValue('user_hiorg', 'quota'));

return $tmpl->fetchPage();
?>