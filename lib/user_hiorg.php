<?php

/**
 * NextCloud -user_hiorg
 *
 * @author Klaus Herberth, Thomas Krause
 * @copyright 2015 Klaus Herberth <klaus@herberth.eu>
 * @copyright 2018 Thomas Krause <tom@krause-micro.de>
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

namespace OCA\user_hiorg;

use OCP\IUserBackend;
use OCP\UserInterface;

/**
 * This class implements a custom user backend which authenticates against @link.
 *
 * @link https://www.hiorg-server.de
 */
class User_HiOrg implements IUserBackend,UserInterface{

   /** @var array of group_IDs for HiOrg -> Nextcloud Group Mapping */
   private $group_id;

   /** @var IGroupManager */
   protected $groupManager;

   /** @var IUserManager */
   protected $userManager;

   private $_hiorg_group = null;


   private $_realBackend = null;
   const URL = 'https://www.hiorg-server.de/';
   const SSOURL = 'https://www.hiorg-server.de/logmein.php';

   public function __construct() {
      $this->_realBackend = new \OC\User\Database ();

      $this->userManager = \OC::$server->getUserManager();

      $this->groupManager = \OC::$server->getGroupManager();

      $hiorg_group_id = \OCP\Config::getAppValue('user_hiorg', "group_id_0", -1);

      if($this->groupManager->groupExists($hiorg_group_id))
      {
         $this->_hiorg_group =  $this->groupManager->get($hiorg_group_id);
      }
      
      $this->group_id = array();

      for($i = 0; $i < 11; $i++)
      {
         $num = strval(2**$i);
         $group_str = "group_id_".$num;

         $this->group_id[$num] = \OCP\Config::getAppValue('user_hiorg', $group_str, 0);
      }

   }

   /**
	 * Backend name to be shown in user management
	 * @return string the name of the backend to be shown
	 * @since 8.0.0
	 */
   public function getBackendName() {
      return 'user_hiorg';
   }


   /**
	 * Check if backend implements actions
	 * @param int $actions bitwise-or'ed actions
	 * @return boolean
	 *
	 * Returns the supported actions as int to be
	 * compared with \OC_User_Backend::CREATE_USER etc.
	 * @since 4.5.0
	 */
   public function implementsActions($actions) {
      return (bool)((\OC_User_Backend::CHECK_PASSWORD | \OC_User_Backend::GET_DISPLAYNAME | \OC_User_Backend::CREATE_USER)
                    & $actions);
   }
   /**
	 * Registers the used backends, used later to get the actual user backend
	 * of the user.
	 *
	 * @param \OCP\UserInterface[] $backends
	 */
   public function registerBackends(array $backends) {
      $this->backends = $backends;
   }

   public function getUsers($search = '', $limit = null, $offset = null) {
      return $this->_realBackend->getUsers ( $search, $limit, $offset );
   }

   public function userExists($uid) {
      return $this->_realBackend->userExists ( $uid );
   }

   public function getHome($uid) {
      return $this->_realBackend->getHome ( $uid );
   }

   public function getDisplayName($uid) {
      return $this->_realBackend->getDisplayName ( $uid );
   }

   public function getDisplayNames($search = '', $limit = null, $offset = null) {
      return $this->_realBackend->getDisplayNames ( $search, $limit, $offset );
   }

   public function checkPassword($username, $password) {
      /* 
      Check if user exists and try to authenticate against the normal backend
      */
      if ($this->userExists ( $username )) {
         \OCP\Util::writeLog('user_hiorg', 'use real backend. User already exists', \OCP\Util::INFO );

         $ret = $this->_realBackend->checkPassword ( $username, $password );
         $user = $this->userManager->get($username);

         if($this->_hiorg_group)
         {
            if($this->_hiorg_group->inGroup($user))
            {
               \OCP\Util::writeLog('user_hiorg', "User ($username) is member of HiOrg group -> authenticate against HiOrg-Server", \OCP\Util::INFO );
               $ret = false;
            }
         }
         else
         {
            \OCP\Util::writeLog('user_hiorg', "HiOrg group not assigned!", \OCP\Util::INFO );
         }

         if ($ret !== false)
            return $ret;

         \OCP\Util::writeLog('user_hiorg', 'real backend failed.', \OCP\Util::INFO );
      }

      /*
      When user does not exist or authentication against normal backend failed, proceed with authentification agianst HiOrg-Server
      */

      $reqUserinfo = array (
         'name',
         'vorname',
         'gruppe',
         'perms',
         'username',
         'email',
         'user_id' 
      );
      $reqParam = http_build_query ( array (
         'ov' => \OCP\Config::getAppValue ( 'user_hiorg', 'ov' ),
         'weiter' => self::SSOURL,
         'getuserinfo' => implode ( ',', $reqUserinfo ) 
      ) );

      $context = stream_context_create ( array (
         'http' => array (
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'content' => http_build_query ( array (
               'username' => $username,
               'password' => $password,
               'submit' => 'Login' 
            ), '', '&' ) 
         ) 
      ) );

      $result = file_get_contents ( self::SSOURL . '?' . $reqParam, false, $context );

      if (mb_substr ( $result, 0, 2 ) != 'OK') {
         \OCP\Util::writeLog('user_hiorg', 'Wrong password.', \OCP\Util::INFO );

         return false;
      }

      $token = null;
      foreach ( $http_response_header as $header ) {
         if (preg_match ( '/^([^:]+): *(.*)/', $header, $output )) {
            if ($output [1] == 'Location') {
               parse_str ( parse_url ( $output [2], PHP_URL_QUERY ), $query );

               if (isset ( $query ['token'] ) && preg_match ( '/[0-9a-z_\-]+/i', $query ['token'] )) {
                  $token = $query ['token'];
                  break;
               }
            }
         }
      }

      if ($token == null) {
         \OCP\Util::writeLog('user_hiorg', 'No token provided', OC_Log::WARN );

         return false;
      }

      $userinfo = unserialize ( base64_decode ( mb_substr ( $result, 3 ) ) );

      if ($userinfo ['ov'] !== \OCP\Config::getAppValue ( 'user_hiorg', 'ov' )) {
         \OCP\Util::writeLog('user_hiorg', 'Wrong ov', OC_Log::WARN );

         return false;
      }

      $uid = $userinfo ['user_id'];

      if (! $this->userExists ( $username )) {
         if ($this->_realBackend->createUser ( $username, $password )) {
            \OCP\Util::writeLog('user_hiorg', "New user ($username) created.", \OCP\Util::INFO );
            

            $user = $this->userManager->get($username);
            
            $accountManager = new \OC\Accounts\AccountManager (
               \OC::$server->getDatabaseConnection(),
               \OC::$server->getEventDispatcher(),
               \OC::$server->getJobList()
            );

            $display_name = $userinfo ['vorname'] . ' ' . $userinfo ['name'] ;
            $email = $userinfo['email'];
            $account = $accountManager->getUser($user);
            $account["displayname"]["value"] = $display_name;
            $account["displayname"]["email"] = $email;
            $accountManager->updateUser($user, $account);
            $user->setEmailAddress($email);

            \OCP\Util::writeLog('user_hiorg', "User ($username) display name set to ($display_name).", \OCP\Util::INFO );

            \OCP\Util::writeLog('user_hiorg', "User ($username) email set to ($email).", \OCP\Util::INFO );

            $this->_hiorg_group->addUser($user);
            $user->setQuota(\OCP\Config::getAppValue('user_hiorg', "quota", "0MB"));

         } else {
            \OCP\Util::writeLog('user_hiorg', "Could not create user ($username).", OC_Log::WARN );
            return false;
         }
      }

      $user = $this->userManager->get($username);

      \OCP\Util::writeLog('user_hiorg', "User ($username) is member of groups (".$userinfo['gruppe'].").", \OCP\Util::INFO );

      /* Check groups and maybe regroup */
      for($i = 0; $i < 11; $i++)
      {
         $num = strval(2**$i);

         \OCP\Util::writeLog('user_hiorg', "HiOrg-Group ($num) is assigned to (".strval($this->group_id[$num]).").", \OCP\Util::INFO );

         if($this->group_id[$num] != '')
         {
            if($this->groupManager->groupExists($this->group_id[$num]))
            {
               $group = $this->groupManager->get($this->group_id[$num]);
               if($userinfo['gruppe'] & 2**$i) /* 2^i */
               {
                  /* 
                  user has this HiOrg-Server group
                  check if user is already a member or add user to group
                  */
                  if(!$group->inGroup($user))
                  {
                     $group->addUser($user);
                     \OCP\Util::writeLog('user_hiorg', "Added user ($username) to group (".strval($this->group_id[$num]).").", \OCP\Util::INFO );
                  }
                  else
                  {
                     \OCP\Util::writeLog('user_hiorg', "User ($username) was already assigned to group (".strval($this->group_id[$num]).").", \OCP\Util::INFO );
                  }
               }
               else
               {
                  /*
                  user does NOT have this HiOrg-Server group
                  check if user is already a member and remove from group
                  */
                  if($group->inGroup($user))
                  {
                     $group->removeUser($user);
                     \OCP\Util::writeLog('user_hiorg', "Removed user ($username) from group (".$this->group_id[$num].").", \OCP\Util::INFO );
                  }
                  else
                  {
                     \OCP\Util::writeLog('user_hiorg', "User ($username) was not assigned to group (".$this->group_id[$num].").", \OCP\Util::INFO );
                  }
               }
            }
            else
            {
               \OCP\Util::writeLog('user_hiorg', "Group (".$this->group_id[$num].") does not exist!", \OCP\Util::WARNING );
            }
         }
      }
      /* END Check groups and maybe regroup */

      $updatePassword = $this->_realBackend->setPassword($username, $password);
      $updatePassword = json_encode($updatePassword);
      \OCP\Util::writeLog('user_hiorg', "Updated password for $username ($username): $updatePassword.", \OCP\Util::DEBUG );

      \OC::$server->getSession ()->set ( 'user_hiorg_token', $token );

      \OCP\Util::writeLog('user_hiorg', "Correct password for $username ($username).", \OCP\Util::INFO );

      return $username;
   }
   
   public function deleteUser($uid)
   {
      if ($this->userExists ( $uid )) {
         if ($this->_realBackend->deleteUser ( $uid)) {
            \OCP\Util::writeLog('user_hiorg', "User ($username) deleted.", \OCP\Util::INFO );
         }
      }
   }

   public function countUsers() {
      return $this->_realBackend->countUsers ();
   }
   
   public function createUser($uid, $password) {
		 \OCP\Util::writeLog('user_hiorg', 'Use the hiorg webinterface to create users',3);
		 return $this->_realBackend->createUser($uid, $password);   
	}
   
   public function hasUserListings()
   {
      return false;
   }
}

?>
