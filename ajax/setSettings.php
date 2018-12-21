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

$config = \OC::$server->getConfig();


/*
Store HiOrg ov
*/
$config->setAppValue ( 'user_hiorg', 'ov', $_POST['ov'] );

/*
Store quota for users
*/
$config->setAppValue ( 'user_hiorg', 'quota', $_POST['quota'] );
/*
Store group_id_0 as basic group for all HiOrg Users
*/
$num = strval(0);
$group_str = "group_id_".$num;

$config->setAppValue('user_hiorg',$group_str, $_POST[$group_str]);

/*
Store all other groups
*/
for($i = 0; $i < 11; $i++)
{
 $num = strval(2**$i);
 $group_str = "group_id_".$num;

 $config->setAppValue('user_hiorg',$group_str, $_POST[$group_str]);
}

echo 'true';
