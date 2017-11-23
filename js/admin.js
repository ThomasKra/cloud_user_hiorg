/**
 * NextCloud -user_hiorg
 *
 * @author Thomas Krause
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


$(document).ready(function() {

   $('#user_hiorg').submit(function(event) {
      event.preventDefault();

      var post = $("#user_hiorg").serialize();

      $.post(OC.filePath('user_hiorg', 'ajax', 'setSettings.php'), post, function(data) {
         if (data === 'true') {
            $('#user_hiorg .msg').text('Finished saving.');
         } else {
            $('#user_hiorg .msg').text('Error!');
         }
      });
   });

});
