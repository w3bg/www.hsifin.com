<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * File Celltype Class for EE2
 * 
 * @package   Matrix
 * @author    Brandon Kelly <brandon@pixelandtonic.com>
 * @copyright Copyright (c) 2010 Pixel & Tonic, LLC
 */
class Matrix_file_ft {

	var $info = array(
		'name' => 'File'
	);

	/**
	 * Constructor
	 */
	function Matrix_file_ft()
	{
		$this->EE =& get_instance();

		// -------------------------------------------
		//  Prepare Cache
		// -------------------------------------------

		if (! isset($this->EE->session->cache['matrix']['celltypes']['file']))
		{
			$this->EE->session->cache['matrix']['celltypes']['file'] = array();
		}
		$this->cache =& $this->EE->session->cache['matrix']['celltypes']['file'];
	}

	// --------------------------------------------------------------------

	/**
	 * Display Cell Settings
	 */
	function display_cell_settings($data)
	{
		$content_type = isset($data['content_type']) ? $data['content_type'] : 'any';

		return array(
			array(
				str_replace(' ', '&nbsp;', lang('field_content_file')),
				form_dropdown('content_type', $data['field_content_options_file'], $content_type)
			)
		);
	}

	// --------------------------------------------------------------------

	/**
	 * Display Cell
	 */
	function display_cell($data)
	{
		if (! isset($this->cache['displayed']))
		{
			// include matrix_text.js
			$theme_url = $this->EE->session->cache['matrix']['theme_url'];
			$this->EE->cp->add_to_foot('<script type="text/javascript" src="'.$theme_url.'scripts/matrix_file.js"></script>');

			$this->EE->lang->loadfile('matrix');

			$this->cache['displayed'] = TRUE;
		}

		$r['class'] = 'matrix-file';

		// -------------------------------------------
		//  Get the upload directories
		// -------------------------------------------

		$upload_dirs = array();

		$upload_prefs = $this->EE->tools_model->get_upload_preferences($this->EE->session->userdata('group_id'));

		foreach($upload_prefs->result() as $row)
		{
			$upload_dirs[$row->id] = $row->name;
		}

		// -------------------------------------------
		//  Existing file?
		// -------------------------------------------

		if ($data)
		{
			if (is_array($data))
			{
				$filedir = $data['filedir'];
				$filename = $data['filename'];
			}
			else if (preg_match('/{filedir_([0-9]+)}/', $data, $matches))
			{
				$filedir  = $matches[1];
				$filename = str_replace($matches[0], '', $data);
			}
		}

		if (isset($filedir))
		{
			$filedir_info = $this->EE->tools_model->get_upload_preferences($this->EE->session->userdata('group_id'), $filedir);
			$thumb_filename = $filedir_info->row('server_path').'_thumbs/thumb_'.$filename;

			if (file_exists($thumb_filename))
			{
				$thumb_url = $filedir_info->row('url').'_thumbs/thumb_'.$filename;
				$thumb_size = getimagesize($thumb_filename);
			}
			else
			{
				$thumb_url = PATH_CP_GBL_IMG.'default.png';
				$thumb_size = array(64, 64);
			}

			$r['data'] = '<div class="matrix-thumb" style="width: '.$thumb_size[0].'px;">'
			           .   '<a title="'.lang('remove_file').'"></a>'
			           .   '<img src="'.$thumb_url.'" width="'.$thumb_size[0].'" height="'.$thumb_size[1].'" />'
			           . '</div>'
			           . '<div class="matrix-filename">'.$filename.'</div>';

			$add_style = ' style="display: none;"';
		}
		else
		{
			$filedir = '';
			$filename = '';
			$r['data'] = '';
			$add_style = '';
		}

		$add_line = (! isset($this->settings['content_type']) || $this->settings['content_type'] != 'image') ? 'add_file' : 'add_image';

		$r['data'] .= '<input type="hidden" name="'.$this->cell_name.'[filedir]"  value="'.$filedir .'" class="filedir" />'
		            . '<input type="hidden" name="'.$this->cell_name.'[filename]" value="'.$filename.'" class="filename" />'
		            . '<input type="file" name="'.$this->cell_name.'[file]" class="file" />'
		            . '<a class="matrix-btn matrix-add"'.$add_style.'>'.$this->EE->lang->line($add_line).'</a>';

		return $r;
	}

	// --------------------------------------------------------------------

	/**
	 * Save Cell
	 */
	function save_cell($data)
	{
		if (isset($data['filename']) && $data['filename'])
		{
			return '{filedir_'.$data['filedir'].'}'.$data['filename'];
		}

		return '';
	}

	// --------------------------------------------------------------------

	/**
	 * Replace Tag
	 */
	function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
		if (! $data) return '';

		// -------------------------------------------
		//  Get the file info
		//   - Down the road Matrix should support a
		//     pre_process_cell() method that this could go in
		// -------------------------------------------

		$file_info['path'] = '';

		if (preg_match('/^{filedir_(\d+)}/', $data, $matches))
		{
			// only replace it once
			$path = substr($data, 0, 10 + strlen($matches[1]));

			$file_dirs = $this->EE->functions->fetch_file_paths();

			$file_info['path'] = str_replace($matches[0], $file_dirs[$matches[1]], $path);
			$data = str_replace($matches[0], '', $data);
		}

		$file_info['extension'] = substr(strrchr($data, '.'), 1);
		$file_info['filename'] = basename($data, '.'.$file_info['extension']);

		// -------------------------------------------
		//  Tagdata
		// -------------------------------------------

		if ($tagdata)
		{
			$tagdata = $this->EE->functions->prep_conditionals($tagdata, $file_info);
			$tagdata = $this->EE->functions->var_swap($tagdata, $file_info);

			return $tagdata;
		}

		$full_path = $file_info['path'].$file_info['filename'].'.'.$file_info['extension'];

		if (isset($params['wrap']))
		{
			if ($params['wrap'] == 'link')
			{
				return '<a href="'.$full_path.'">'.$file_info['filename'].'</a>';
			}

			if ($params['wrap'] == 'image')
			{
				return '<img src="'.$full_path.'" alt="'.$file_info['filename'].'" />';
			}
		}

		return $full_path;
	}

}
