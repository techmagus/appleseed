<?php
/**
 * @version      $Id$
 * @package      Appleseed.Components
 * @subpackage   System
 * @copyright    Copyright (C) 2004 - 2010 Michael Chisari. All rights reserved.
 * @link         http://opensource.appleseedproject.org
 * @license      GNU General Public License version 2.0 (See LICENSE.txt)
 */

// Restrict direct access
defined( 'APPLESEED' ) or die( 'Direct Access Denied' );

/** System Component Controller
 * 
 * System Component Admin Controller Class
 * 
 * @package     Appleseed.Components
 * @subpackage  System
 */
class cSystemAdminUpdateController extends cController {
	
	/**
	 * Constructor
	 *
	 * @access  public
	 */
	public function __construct ( ) {       
		parent::__construct( );
	}
	
	public function Display ( $pView = null, $pData = array ( ) ) {
		
		$this->Form = $this->GetView ( "admin.update" );
		
		$defaults = array ( "BackupDirectory" => ASD_PATH . "_backup" );
		
		$this->Form->Synchronize( $defaults );
		
		$this->Form->Display();
		
		return ( true );
	}
	
}