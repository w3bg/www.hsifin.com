<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2010, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * ExpressionEngine CP Home Page Class
 *
 * @package		ExpressionEngine
 * @subpackage	Control Panel
 * @category	Control Panel
 * @author		ExpressionEngine Dev Team
 * @link		http://expressionengine.com
 */
class Homepage extends Controller {

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function Homepage()
	{
		parent::Controller();
	}

	// --------------------------------------------------------------------

	/**
	 * Index function
	 *
	 * @access	public
	 * @return	void
	 */	
	function index($message = '')
	{	
		$this->cp->get_installed_modules();
		$this->cp->set_variable('cp_page_title', $this->lang->line('main_menu'));

		$version			= FALSE;
		$show_notice		= $this->_checksum_bootstrap_files();
		$allowed_templates	= $this->session->userdata('assigned_template_groups');

		// Notices only show for super admins
		if ($this->session->userdata['group_id'] == 1 AND $this->config->item('new_version_check') == 'y')
		{
			$version		= $this->_version_check();
			$show_notice	= ($show_notice OR $version);
		}
		
		$vars = array(
			'version'			=> $version,
			'message'			=> $message,
			'instructions'		=> $this->lang->line('select_channel_to_post_in'),
			'show_page_option'	=> (isset($this->cp->installed_modules['pages'])) ? TRUE : FALSE,
			'info_message_open'	=> ($this->input->cookie('home_msg_state') != 'closed' && $show_notice) ? TRUE : FALSE,
			'no_templates'		=> sprintf($this->lang->line('no_templates_available'), BASE.AMP.'C=design'.AMP.'M=new_template_group'),
			
			'can_access_modify'		=> TRUE,
			'can_access_content'	=> TRUE,
			'can_access_templates'	=> (count($allowed_templates) > 0 && $this->cp->allowed_group('can_access_design')) ? TRUE : FALSE
		);
		
		
		// Pages module is installed, need to check perms
		// to see if the member group can access it.
		// Super admin sees all.
		
		if ($vars['show_page_option'] && $this->session->userdata('group_id') != 1)
		{
			$this->load->model('member_model');
			$vars['show_page_option'] = $this->member_model->can_access_module('pages');
		}
		
		$vars['recent_entries'] = $this->_recent_entries();

		// A few more permission checks
		
		if ( ! $this->cp->allowed_group('can_access_publish'))
		{
			$vars['show_page_option'] = FALSE;
			
			if ( ! $this->cp->allowed_group('can_access_edit') && ! $this->cp->allowed_group('can_admin_templates'))
			{
				$vars['can_access_modify'] = FALSE;
				
				if ( ! $this->cp->allowed_group('can_admin_channels')  && ! $this->cp->allowed_group('can_admin_sites'))
				{
					$vars['can_access_content'] = FALSE;
				}
			}
		}
		
		//  Comment blocks
		$vars['comments_installed']			= $this->db->table_exists('comments');
		$vars['can_moderate_comments']		= $this->cp->allowed_group('can_moderate_comments') ? TRUE : FALSE;
		$vars['comment_validation_count']	= ($vars['comments_installed']) ? $this->_total_validating_comments() : FALSE;	

		// Most recent comment and most recent entry
		$this->load->model('channel_model');

		$vars['cp_recent_ids'] = array(
			'entry'		=> $this->channel_model->get_most_recent_id('entry')
		);

		// Prep js
		
		$this->javascript->set_global('lang.close', $this->lang->line('close'));
		
		if ($show_notice)
		{
			$this->javascript->set_global('importantMessage.state', $vars['info_message_open']);
		}

		$this->cp->add_js_script('file', 'cp/homepage');
		$this->javascript->compile();
		
		$this->load->view('homepage', $vars);
	}


	// --------------------------------------------------------------------
	
	/**
	 *  Get Comments Awaiting Validation
	 *
	 * Gets total number of comments with 'pending' status
	 *
	 * @access	private
	 * @return	integer
	 */
	function _total_validating_comments()
	{  
		$this->db->where('status', 'p');
		$this->db->where('site_id', $this->config->item('site_id'));
		$this->db->from('comments');

		return $this->db->count_all_results();
  	}
  	/* END */


	// --------------------------------------------------------------------
	
	/**
	 *  Get Recent Entries
	 *
	 * Gets total number of comments with 'pending' status
	 *
	 * @access	private
	 * @return	array
	 */
	function _recent_entries()
	{
		$this->load->model('channel_entries_model');
		$entries = array();

		$query = $this->channel_entries_model->get_recent_entries(10);
		
		if ($query && $query->num_rows() > 0)
		{
			$result = $query->result();
			foreach($result as $row)
			{
				$link = '';
				
				if (($row->author_id == $this->session->userdata('member_id')) OR $this->cp->allowed_group('can_edit_other_entries'))
				{
					$link = BASE.AMP.'C=content_publish'.AMP.'M=entry_form'.AMP.'channel_id='.$row->channel_id.AMP.'entry_id='.$row->entry_id;
				}
				

				$link = ($link == '') ? $row->title: '<a href="'.$link.'">'.$row->title.'</a>';
				
				$entries[] = $link;
			}
		}
		
		return $entries;
	}



	// --------------------------------------------------------------------

	/**
	 * Accept Bootstrap Checksum Changes
	 * 
	 * Updates the bootstrap file checksums with the new versions.
	 *
	 * @access	public
	 */
	function accept_checksums()
	{
		if ($this->session->userdata('group_id') != 1)
		{
			show_error($this->lang->line('unauthorized_access'));
		}
		
		$this->load->library('file_integrity');
		$changed = $this->file_integrity->check_bootstrap_files(TRUE);

		if ($changed)
		{
			foreach($changed as $site_id => $paths)
			{
				foreach($paths as $path)
				{
					$this->file_integrity->create_bootstrap_checksum($path, $site_id);
				}
			}
		}
		
		$this->functions->redirect(BASE.AMP.'C=homepage');
	}

	// --------------------------------------------------------------------

	/**
	 * Bootstrap Checksum Validation
	 * 
	 * Creates a checksum for our bootstrap files and checks their
	 * validity with the database
	 *
	 * @access	private
	 */
	function _checksum_bootstrap_files()
	{
		$this->load->library('file_integrity');
		$changed = $this->file_integrity->check_bootstrap_files();

		if ($changed)
		{
			// Email the webmaster - if he isn't already looking at the message
			
			if ($this->session->userdata('email') != $this->config->item('webmaster_email'))
			{
				$this->file_integrity->send_site_admin_warning($changed);
			}
			
			if ($this->session->userdata('group_id') == 1)
			{
				$this->load->vars(array('new_checksums' => $changed));
				return TRUE;
			}
		}
		
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * EE Version Check function
	 * 
	 * Requests a file from ExpressionEngine.com that informs us what the current available version
	 * of ExpressionEngine.  In the future, we might put the build number in there as well.
	 *
	 * @access	private
	 * @return	bool|string
	 */
	function _version_check()
	{		
		// Attempt to grab the local cached file
		$cached = $this->_check_version_cache();

		$download_url = $this->cp->masked_url('https://secure.expressionengine.com/download.php');
		
		$data = '';
		
		if ( ! $cached)
		{
			$details['timestamp'] = time();
			
			$dl_page_url = 'http://expressionengine.com/version.txt';

			$target = parse_url($dl_page_url);

			$fp = @fsockopen($target['host'], 80, $errno, $errstr, 3);
			
			if (is_resource($fp))
			{
				fputs($fp,"GET ".$dl_page_url." HTTP/1.0\r\n" );
				fputs($fp,"Host: ".$target['host'] . "\r\n" );
				fputs($fp,"User-Agent: EE/EllisLab PHP/\r\n");
				fputs($fp,"If-Modified-Since: Fri, 01 Jan 2004 12:24:04\r\n\r\n");

				$headers = TRUE;

				while ( ! feof($fp))
				{
					$line = fgets($fp, 4096);

					if ($headers === FALSE)
					{
						$data .= $line;
					}
					elseif(trim($line) == '')
					{
						$headers = FALSE;
					}
				}

				fclose($fp);
				
				if ($data !== '')
				{
					// We have a file, now parse & make an array of arrays.
					$display_new_build = FALSE;
					
					$data = explode("\n", trim($data));
					
					$version_file = array();
					
					foreach ($data as $d)
					{
						$version_file[] = explode('|', $d);
					}

				  // 1 => 
				  //   array
				  //     0 => string '2.1.0' (length=5)
				  //     1 => string '20100805' (length=8)
				  //     2 => string 'normal' (length=6)
					
					if ($data === NULL)
					{
						// something's not right...
						unset($details['version']);
						$details['error'] = TRUE;
					}
					else
					{
						// We'll deal with the last piece of the array
						$cur_ver = end($version_file);
						
						// Extracting the date the build was released.  IF the build was 
						// released in the past 2 calendar days, we don't show anything
						// on the control panel home page unless it was a security release
						$date_threshold = mktime(0, 0, 0, 
												substr($cur_ver[1], 4, -2), // Month
												(substr($cur_ver[1], -2) + 2), // Day + 2 
												substr($cur_ver[1], 0, 4) // Year
								);

						$details['version'] = $cur_ver[0];
						$details['build'] = $cur_ver[1];
						$details['priority'] = $cur_ver[2];
						
						if (($this->localize->now < $date_threshold) && $details['priority'] != 'high')
						{
							$this->_write_version_cache($version_file);
							return FALSE;
						}
					}
				}
				else
				{
					$version_file['error'] = TRUE;
				}
				
				$this->_write_version_cache($version_file);
			}
			else
			{
				$version_file['error'] = TRUE;
				$this->_write_version_cache($version_file);				
			}
		}
		else
		{
			$version_file = $cached;
		}
		
		if (isset($version_file))
		{
			$new_release = FALSE;
			$high_priority = FALSE;
			
			$cur_ver = end($version_file);

			// Do we have a newer version out?
			foreach ($version_file as $app_data)
			{
				if ($app_data[0] > APP_VER && $app_data[2] == 'high')
				{
					$new_release = TRUE;
					$high_priority = TRUE;
					$high_priority_release = array(
							'version'		=> $app_data[0],
							'build'			=> $app_data[1]
						);

					continue;
				}
				elseif ($app_data[1] > APP_BUILD && $app_data[2] == 'high')
				{
					// A build could sometimes be a security release.  So we can plan for it here.
					$new_release = TRUE;
					$high_priority = TRUE;
					$high_priority_release = array(
							'version'		=> $app_data[0],
							'build'			=> $app_data[1]
						);

					continue;					
				}
			}
			
			if ($new_release)
			{
				if ($high_priority)
				{
					return sprintf($this->lang->line('new_version_notice_high_priority'),
								   $high_priority_release['version'],
								   $high_priority_release['build'],
								   $cur_ver[0],
								   $cur_ver[1],
								   $download_url,
								   $this->cp->masked_url($this->config->item('doc_url').'installation/update.html'));
				}
				else
				{
					return sprintf($this->lang->line('new_version_notice'),
								   $details['version'],
								   $download_url,
								   $this->cp->masked_url($this->config->item('doc_url').'installation/update.html'));					
				}
			}
		}
		else
		{
			return sprintf($this->lang->line('new_version_error'),
							$download_url);
		}
		
		return FALSE;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Check EE Version Cache.
	 *
	 * @access	private
	 * @return	bool|string
	 */
	function _check_version_cache()
	{
		// check cache first
		$cache_expire = 60 * 60 * 24;	// only do this once per day
		$this->load->helper('file');	
		$contents = read_file(APPPATH.'cache/ee_version/current_version');

		if ($contents !== FALSE)
		{
			$details = unserialize($contents);

			if (($details['timestamp'] + $cache_expire) > $this->localize->now)
			{
				return $details['data'];
			}
			else
			{
				return FALSE;
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Write EE Version Cache
	 *
	 * @param array - details of version needed to be cached.
	 * @return void
	 */
	function _write_version_cache($details)
	{
		$this->load->helper('file');
		
		if ( ! is_dir(APPPATH.'cache/ee_version'))
		{
			mkdir(APPPATH.'cache/ee_version', DIR_WRITE_MODE);
			@chmod(APPPATH.'cache/ee_version', DIR_WRITE_MODE);	
		}
		
		$data = array(
				'timestamp'	=> $this->localize->now,
				'data' 		=> $details
			);

		if (write_file(APPPATH.'cache/ee_version/current_version', serialize($data)))
		{
			@chmod(APPPATH.'cache/ee_version/current_version', FILE_WRITE_MODE);			
		}		
	}
}

/* End of file homepage.php */
/* Location: ./system/expressionengine/controllers/cp/homepage.php */