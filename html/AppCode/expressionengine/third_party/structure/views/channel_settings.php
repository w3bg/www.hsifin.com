<?php if ( ! $permissions['admin']) $this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure');?>
<?=form_open($action_url, $attributes)?>

<table class="mainTable" border="0" cellspacing="0" cellpadding="0">
	<thead>
		<tr class="odd">
			<th>Channel</th>
			<th>Type</th>
			<th>Default Template</th>
		</tr>
	</thead>
	<tbody>	
		
		<?php if (isset($channel_data)):
			$i = 0;
			foreach ($channel_data as $channel_id => $value):
		?>
		
		
		<tr class="<?=($i++ % 2) ? 'even' : 'odd';?>">
			<td><?=$value['channel_title']?></td>
			<td>
				<select name="<?=$channel_id?>[]">
					<option value="unmanaged"<?=set_select($channel_id, 'unmanaged', (($channel_data[$channel_id]['type'] == 'unmanaged') ? TRUE : FALSE));?> >Unmanaged</option>
					<option value="page"<?=set_select($channel_id, 'page', (($channel_data[$channel_id]['type'] == 'page') ? TRUE : FALSE));?> >Page</option>
					<option value="listing"<?=set_select($channel_id, 'listing', (($channel_data[$channel_id]['type'] == 'listing') ? TRUE : FALSE));?> >Listing</option>
					<option value="asset"<?=set_select($channel_id, 'asset', (($channel_data[$channel_id]['type'] == 'asset') ? TRUE : FALSE));?> >Asset</option>
				</select>
			</td>
			
			<td>
				<select name="<?=$channel_id?>[]">
					<option value="none">None</option>
					<?php foreach ($template_data->result_array() as $template) 
					{
						$field  = '<option value="'.$template['template_id'].'"';
						$field .= set_select($template['template_id'], $template['group_name'].'/'.$template['template_name'], (($template['template_id'] == $channel_data[$channel_id]['template_id']) ? TRUE : FALSE)).">";
						$field .= $template['group_name'].'/'.$template['template_name']."</option>\n";
					
						echo $field;
					}
					?>
				</select>
				
			</td>
		</tr>
		
		<?php endforeach; ?>
		<?php endif; ?>
				
	</tbody>
</table>

<?=form_submit(array('name' => 'submit', 'value' => 'Save', 'class' => 'submit'))?>
<a href="<?=BASE.AMP;?>C=addons_modules&M=show_module_cp&module=structure" style="margin-left:10px;">Cancel</a>


<?=form_close()?>