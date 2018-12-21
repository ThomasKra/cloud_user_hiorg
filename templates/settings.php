<div class="section">
	<h2>User Hiorg</h2>
	<form id="user_hiorg">
		<table style="width: 500px">
			<tr>
				<td><label for="ov">* Org.-Kürzel:</label></td>
				<td><input type="text" name="ov" id="ov" value="<?php p($_['ov']); ?>" /></td>
			</tr>
		</table>

		<h3>Standard HiOrg-User Quota</h3>
		<p>
			<label>Quota</label>
			<input type="text" name="quota" id="quota" value="<?php p($_['quota']); ?>" />
			<p>
				<em>
					Standard-Quota für Benutzer vom HiOrg-Server. z.B.: 10 MB (Standard-Abkürzungen wie MB, GB verwenden)
				</em>
		</p>
		</p>
		<h3><?php p($l->t('Group Names')) ?></h3>
		<?php 
	$num = 0;
			$value_name = 'group_id_'.$num;
		?>
		<p>
			<label>Allg. Hi-Org Gruppe</label>
			<select name="group_id_<?php p($num);?>">
				<?php 
				foreach($_['groups'] as $key => $group)
				{
				?>
				<option value="<?php p($key); ?>" <?php if($key === \OC::$server->getConfig()->getAppValue('user_hiorg', $value_name, '')){p('selected="selected"');}?>><?php p($group); ?></option>
				<?php
				}
				?>
			</select>
		</p>


		<?

		for($i=0; $i < 11; $i++)
		{
			$num = 2**$i;
			$value_name = 'group_id_'.$num;
		?>
		<p>
			<label>Group ID <?php p($num);?></label>
			<select name="group_id_<?php p($num);?>">
				<?php 
			foreach($_['groups'] as $key => $group)
			{
				?>
				<option value="<?php p($key); ?>" <?php if($key === \OC::$server->getConfig()->getAppValue('user_hiorg', $value_name, '')){p('selected="selected"');}?>><?php p($group); ?></option>
				<?php
			}
				?>
			</select>
		</p>
		<?php
		}
		?>


		<div class="msg"></div>

		<input type="submit" value="Save settings" />
	</form>
</div>
