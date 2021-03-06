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
class cSystemAdminNodesController extends cController {
	
	/**
	 * Constructor
	 *
	 * @access  public
	 */
	public function __construct ( ) {       
		parent::__construct( );
	}
	
	public function Display ( $pView = null, $pData = array ( ) ) {
		
		$request = $this->GetSys ( "Request" )->Get();
		
		$task = $this->GetSys ( "Request" )->Get ( "Task" );
		
		$this->List = $this->GetView ( "admin.nodes" );
		
		$this->Model = $this->GetModel ( "Nodes" );
		
		$session = $this->GetSys ( "Session" );
		$session->Context ( $this->Get ( "Context" ) );
		
		$saved = $session->Get();
		
		list ( $start, $step, $page ) = $this->_PageCalc();
		
		// Retrieve from the db, using no criteria except for the pagination settings.
		$this->Model->Retrieve( null, null, array ( "start" => $start, "step" => $step ) );
		
		$tbody = $this->List->Find ( "[id=customer-table-body] tbody tr", 0);
		
		$baseURL = $this->GetSys ( "Router" )->Get ( "Base" );
		$this->List->Find ( "form", 0 )->action = $this->GetSys ( "Router" )->Get ( "Base" );
		
		$this->List->Find( "input[name=Context]", 0 )->value = $this->_Context;
		
		$row = $this->List->Copy ( "[id=customer-table-body] tbody tr" )->Find ( "tr", 0 );
		
		$tbody->innertext = " " ;
		
		$cellNode_PK = $row->Find( "[class=Node_PK]", 0 );
		$cellDomain = $row->Find( "[class=Domain]", 0 );
		$cellTrust = $row->Find( "[class=Trust]", 0 );
		$cellSource = $row->Find( "[class=Source]", 0 );
		$cellExpires = $row->Find( "[class=Expires]", 0 );
		$cellAdmin = $row->Find( "[class=Admin]", 0 );
		$cellLocation = $row->Find( "[class=Location]", 0 );
		$cellInherit = $row->Find( "[class=Inherit]", 0 );
		$cellMasslist = $row->Find( "[class=Masslist] input[type=checkbox]", 0 );
		
		while ( $this->Model->Fetch() ) {
			
		    $oddEven = empty($oddEven) || $oddEven == 'even' ? 'odd' : 'even';
			
			$row->class = $oddEven;
			
			$id = $this->Model->Get ( 'Node_PK' );
			
			$url = $baseURL . "edit" . DS . $id . DS;
			
			$domain = $this->Model->Get ( 'Domain' );
			$trust = $this->Model->Get ( 'Trust' );
			$expires = $this->Model->Get ( 'Expires' );
			$source = $this->Model->Get ( 'Source' );
			$admin = $this->Model->Get ( 'Admin' );
			$location = $this->Model->Get ( 'Location' );
			$inheritance = $this->Model->Get ( 'Inherit' );
			
			if ( $expires == '0000-00-00 00:00:00' ) $expires = __( "Never Expires" );
			
			$context = $this->_Component . '.' . strtolower ( __FUNCTION__ );
			
			$cellNode_PK->innertext = $this->List->Link ( $id, $url );
			$cellDomain->innertext = $this->List->Link ( $domain, $url );
			$cellTrust->innertext = $this->List->Link ( $trust, $url );
			$cellExpires->innertext = $this->List->Link ( $expires, $url );
			$cellSource->innertext = $this->List->Link ( $source, $url );
			$cellMasslist->name = "Masslist[" . $id . "]";
			
		    $tbody->innertext .= $row->outertext;
		}
		
		$link = $this->GetSys ( "Router" )->Get ( "Base" ) . '(.*)';
		$total = $this->Model->Get ( "Total" );
		
		$pageData = array ( 'start' => $start, 'step'  => $step, 'total' => $total, 'link' => $link );
		$pageControls =  $this->List->Find ("nav[class=pagination]");
		foreach ( $pageControls as $p => $pageControl ) {
			$pageControl->innertext = $this->GetSys ( "Components" )->Buffer ( "pagination", $pageData ); 
		}
		
		$pageData = array ( 'total' => $total, 'step' => $step, 'link' => $link );
		$pageControls =  $this->List->Find ("nav[class=pagination-amount]");
		foreach ( $pageControls as $p => $pageControl ) {
			$pageControl->innertext = $this->GetSys ( "Components" )->Buffer ( "pagination", "pagination", "amount", $pageData ); 
		}
		
		$this->List->SynchronizeInputs();
		
		$this->_PrepareMessage();
		
		$this->List->Display();
		
		$this->List->Clear();
		unset ( $this->List );
		
		return ( true );
	}
	
	private function _PageCalc ( ) {
		
		$session = $this->GetSys ( "Session" );
		$session->Context ( $this->Get ( "Context" ) );
		
		$page = (int) $this->GetSys ( "Request" )->Get ( "Page");
		
		if ( $step = $this->GetSys ( "Request" )->Get ( "PaginationStep" ) ) {
			$page = 1;
			$session->Set ( "PaginationStep", $step );
		} else {
			$step = $session->Get ( "PaginationStep", 10 );
		}
		
		if ( !$page ) {
			// Get which page was stored, defaulting to page 1
			$page = (int) $session->Get ( "Page", 1 );
		} else {
			// Store the current page for retrieval
			$session->Set ( "Page", $page );
		}
		
		if ( !$page ) $page = 1;
		
		// Calculate the starting point in the list.
		$start = ( $page - 1 ) * $step;
		
		$return = array ( $start, $step, $page );
		
		return ( $return );
	}
	
	public function Edit ( ) {
		
		$this->Model = $this->GetModel ( "Nodes" );
		
		$this->_PrepareForm();
		
		$this->Form->Display();
		
		unset ( $this->Form );
		
		return ( true );
	}
	
	public function Add ( ) {
		
		$this->Model = $this->GetModel ( "Nodes" );
		
		$this->_PrepareForm();
		
		$this->Form->Display();
		
		unset ( $this->Form );
		
		return ( true );
	}
	
	public function _PrepareForm() {
		
		$Node_PK = $this->GetSys ( "Request" )->Get ( 'Node_PK', $this->Model->Get ( "Node_PK" ) );
		
		$this->Form = $this->GetView ( "admin.nodes.form" );
		
		$this->Form->Find ( "form", 0 )->action = $this->GetSys ( "Router" )->Get ( "Base" );
		$this->Form->Find( "input[name=Context]", 0 )->value = $this->_Context;
		
		$this->_PrepareMessage();
		
		if ( $Node_PK ) {
			$this->_PrepareEditForm ( );
		} else {
			$this->_PrepareAddForm ( );
		}
		
		return ( true );
	}
	
	function Apply ( ) {
		
		if ( !$this->_Save() ) {
			$this->Go ( "Edit" );
			return ( false );
		}
		
		$this->GetSys ( "Request" )->Set ( "Node_PK", $this->Model->Get ( "Node_PK" ) );
		
		$message = __( "Record Applied", array ( "id" => $this->Model->Get ( "Node_PK" ) ) ); 
		$this->GetSys ( "Session" )->Set ( "Message", $message );
		
		$this->Go ( "Edit" );
		 
		return ( true );
	}
	
	function Save ( ) {
		
		if ( !$this->_Save() ) {
			$this->Go ( "Edit" );
			return ( false );
		}
		
		$message = __( "Record Saved", array ( "id" => $this->Model->Get ( "Node_PK" ) ) ); 
		$this->GetSys ( "Session" )->Set ( "Message", $message );
		
		$this->Go ( "Display" );
		
		return ( true );
	}
	
	/**
	 * Internal function to save the data.
	 * 
	 * @access  public
	 */
	function _Save ( ) {
		
		$this->Model = $this->GetModel ( "Nodes" );
		$this->Model->Synchronize();
		
		if ( !$this->Model->Get ( 'Source' ) ) $this->Model->Set ( 'Source', ASD_DOMAIN );
		if ( $this->Model->Get ( 'Inherit' ) == 'on' ) $this->Model->Set ( 'Inherit', true ); else $this->Model->Set ( 'Inherit', false );
		
		$this->Model->Set ( 'Updated', NOW() );
		$this->Model->Set ( 'Status', true );
		
		$validate = $this->GetSys ( 'Validation' );
		
		$fields = $this->Model->Get ( 'Fields' );
		$data = $this->GetSys ( 'Request' )->Get ();
		
		if ( !$validate->Validate ( $fields, $data ) ) {
			return ( false );
		}
		
		$this->Model->Save();
		
		return ( true );
	}
	
	function Cancel ( ) {
		
		$this->GetSys ( 'Session' )->Set ( 'Message', 'Edit Cancelled' );
		
		$this->Go ( "Display" );
		
		return ( true );
	}
	
	function Delete_All ( ) {
		$selected = $this->GetSys ( "Request" )->Get ( "Masslist" );
		
		if ( !$selected ) {
			$this->GetSys ( "Session" )->Set ( "Message", "None Selected" );
			$this->GetSys ( "Session" )->Set ( "Error", TRUE );
			
			$this->Go ( "Display" );
			
			return ( false );
		}
		
		$criteria['Node_PK'] = $selected;
		
		$this->Model = $this->GetModel( "Nodes" );
		
		$this->Model->Delete ( $criteria );
		
		$count = count ( $selected );
		
		$this->GetSys ( "Session" )->Set ( "Message", __ ("Selected Items Deleted", array ( "count" => $count ) ) );
		$this->GetSys ( "Session" )->Set ( "Error", false );
		
		$this->Go ( "Display" );
		
		return ( true );
	}
	
	private function _PrepareEditForm ( ) {
		
		$Node_PK = $this->GetSys ( "Request" )->Get ( 'Node_PK', $this->Model->Get ( "Node_PK" ) );
		
		$this->Model->Retrieve ( $Node_PK );
		
		$this->Model->Fetch();
		
		$never = false;
		if ( $this->Model->Get ( "Expires" ) == '0000-00-00 00:00:00' ) $never = true;
		
		$defaults = (array) $this->Model->Get ( "Data" );
		$defaults = array_merge ( $defaults, array ( "Never" => $never) );
		$this->Form->SynchronizeInputs ( $defaults );
		
	}
	
	private function _PrepareAddForm ( ) {
		
		$this->Form->Find ( "[id=edit-subtitle]", 0)->innertext = "New Node Subtitle";
		$this->Form->Find ( "form[id=system-nodes-edit] fieldset p", 0)->innertext = "New Node Description";
		$this->Form->Find ( "[name=Source]", 0)->value = ASD_DOMAIN;
		
		return ( true );
	}
	
	
	private function _PrepareMessage ( ) {
		
		if ( $this->Form ) {
			$markup = & $this->Form;
		} else if ( $this->List ) {
			$markup = & $this->List;
		} else {
			return ( false );
		}
		
		if ( $message =  $this->GetSys ( "Session" )->Get ( "Message" ) ) {
			$markup->Find ( "[id=system-nodes-message]", 0 )->innertext = $message;
			if ( $error =  $this->GetSys ( "Session" )->Get ( "Error" ) ) {
				$markup->Find ( "[id=system-nodes-message]", 0 )->class = "error";
			} else {
				$markup->Find ( "[id=system-nodes-message]", 0 )->class = "message";
			}
			$this->GetSys ( "Session" )->Destroy ( "Message ");
			$this->GetSys ( "Session" )->Destroy ( "Error ");
		}
		
		return ( true );
	}
}
