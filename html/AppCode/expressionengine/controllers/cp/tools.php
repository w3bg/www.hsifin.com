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
class Tools extends Controller {

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function Tools()
	{
		parent::Controller();
		
		if ( ! $this->cp->allowed_group('can_access_tools'))
		{
			show_error($this->lang->line('unauthorized_access'));
		}

		$this->lang->loadfile('tools');
	}
	
	// --------------------------------------------------------------------

	/**
	 * Index function
	 *
	 * @access	public
	 * @return	void
	 */	
	function index()
	{
		$this->cp->set_variable('cp_page_title', $this->lang->line('tools'));

		$this->javascript->compile();

		$this->load->vars(array('controller' => 'tools'));

		$this->load->view('_shared/overview');
	}
	
}

/* End of file tools.php */
/* Location: ./system/expressionengine/controllers/cp/tools.php */