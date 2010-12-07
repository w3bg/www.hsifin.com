<?php if ( ! $permissions['admin']) $this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure');?>
<?=form_open($action_url, $attributes)?>

<table class="mainTable" border="0" cellspacing="0" cellpadding="0">
	<thead>
		<tr class="odd">
			<th>Dashboard Preference</th>
			<th>Setting</th>
		</tr>
	</thead>
	<tbody>	
		<tr class="odd">
			<td><?=lang('settings_enable_picker'); ?></td>
			<td>
				<select name="show_picker">
					<option value="y"<?=set_select('yes', 'Yes',  ($settings['show_picker'] == '' || $settings['show_picker'] == 'y' ? 'y' : ''));?>>Yes</option>
					<option value="n"<?=set_select('no', 'No', ($settings['show_picker'] == '' || $settings['show_picker'] == 'n' ? 'y' : ''));?>>No</option>
				</select>
			</td>
		</tr>
		<tr class="even">
			<td>Show "View Page" links</td>
			<td>
				<select name="show_view_page">
					<option value="y"<?=set_select('yes', 'Yes',  ($settings['show_view_page'] == '' || $settings['show_view_page'] == 'y' ? 'y' : ''));?>>Yes</option>
					<option value="n"<?=set_select('no', 'No', ($settings['show_view_page'] == '' || $settings['show_view_page'] == 'n' ? 'y' : ''));?>>No</option>
				</select>
			</td>
		</tr>
		<tr class="odd">
			<td>Show Page Statuses</td>
			<td>
				<select name="show_status">
					<option value="y"<?=set_select('yes', 'Yes',  ($settings['show_status'] == '' || $settings['show_status'] == 'y' ? 'y' : ''));?>>Yes</option>
					<option value="n"<?=set_select('no', 'No', ($settings['show_status'] == '' || $settings['show_status'] == 'n' ? 'y' : ''));?>>No</option>
				</select>
			</td>
		</tr>
		<tr class="even">
			<td>Show Page Type</td>
			<td>
				<select name="show_page_type">
					<option value="y"<?=set_select('yes', 'Yes',  ($settings['show_page_type'] == '' || $settings['show_page_type'] == 'y' ? 'y' : ''));?>>Yes</option>
					<option value="n"<?=set_select('no', 'No', ($settings['show_page_type'] == '' || $settings['show_page_type'] == 'n' ? 'y' : ''));?>>No</option>
				</select>
			</td>
		</tr>
		<tr class="odd">
			<td>Show global add page button</td>
			<td>
				<select name="show_global_add_page">
					<option value="y"<?=set_select('yes', 'Yes',  ($settings['show_global_add_page'] == '' || $settings['show_global_add_page'] == 'y' ? 'y' : ''));?>>Yes</option>
					<option value="n"<?=set_select('no', 'No', ($settings['show_global_add_page'] == '' || $settings['show_global_add_page'] == 'n' ? 'y' : ''));?>>No</option>
				</select>
			</td>
		</tr>
	</tbody>
</table>

<table class="mainTable" border="0" cellspacing="0" cellpadding="0">
	<thead>
		<?php if ($groups) : ?>
			<tr class="even">
				<th>Member Group Permission</th>
				<?php foreach ($groups as $group): ?>
				<th><?=$group['title'];?></th>
				<?php endforeach; ?>
			</tr>
		<?php endif; ?>
	</thead>
	<tbody>
		<?php if ( ! $groups): ?>
			<tr class="box">
				<td>
					<p><strong>No Groups</strong></p>
					<ul>
						<li>Can access control panel</li>
						<li>Can access publish</li>
						<li>Can access edit</li>
						<li>Can access Structure</li>
					</ul>
				</td>
			</tr>
		<?php else: ?>
			<?php $i = 0; foreach ($perms as $perm_id => $perm): ?>
				<tr class="<?php echo ($i++ % 2) ? 'even' : 'odd'; ?>">
					<td><?=$perm?></td>
					<?php foreach ($groups as $group): $perm_key = $perm_id . '_' . $group['id']; ?>
					<td class="settingsPermBoxes">
						<input type="checkbox" name="<?=$perm_key; ?>" id="<?=$perm_key; ?>" class="<?=$perm_id . ' group' . $group['id']; ?>" value="<?=$group['id']; ?>"<?php if (isset($settings[$perm_key])) echo ' checked="checked"'; ?> />
					</td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>


<?=form_submit(array('name' => 'submit', 'value' => 'Save', 'class' => 'submit'))?>
<a href="<?=BASE.AMP;?>C=addons_modules&M=show_module_cp&module=structure" style="margin-left:10px;">Cancel</a>


<?=form_close()?>