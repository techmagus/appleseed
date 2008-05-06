<?php
  // +-------------------------------------------------------------------+
  // | Appleseed Web Community Management Software                       |
  // | http://appleseed.sourceforge.net                                  |
  // +-------------------------------------------------------------------+
  // | FILE: main.php                                CREATED: 02-11-2005 + 
  // | LOCATION: /code/admin/                       MODIFIED: 04-11-2007 +
  // +-------------------------------------------------------------------+
  // | Copyright (c) 2004-2008 Appleseed Project                         |
  // +-------------------------------------------------------------------+
  // | This program is free software; you can redistribute it and/or     |
  // | modify it under the terms of the GNU General Public License       |
  // | as published by the Free Software Foundation; either version 2    |
  // | of the License, or (at your option) any later version.            |
  // |                                                                   |
  // | This program is distributed in the hope that it will be useful,   |
  // | but WITHOUT ANY WARRANTY; without even the implied warranty of    |
  // | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the     |
  // | GNU General Public License for more details.                      |	
  // |                                                                   |
  // | You should have received a copy of the GNU General Public License |
  // | along with this program; if not, write to:                        |
  // |                                                                   |
  // |   The Free Software Foundation, Inc.                              |
  // |   59 Temple Place - Suite 330,                                    | 
  // |   Boston, MA  02111-1307, USA.                                    |
  // |                                                                   |
  // |   http://www.gnu.org/copyleft/gpl.html                            |
  // +-------------------------------------------------------------------+
  // | AUTHORS: Michael Chisari <michael.chisari@gmail.com>              |
  // +-------------------------------------------------------------------+
  // | VERSION:      0.7.3                                               |
  // | DESCRIPTION:  Main administration page.                           |
  // +-------------------------------------------------------------------+

  // Change to document root directory.
  chdir ($_SERVER['DOCUMENT_ROOT']);

  // Include BASE API classes.
  require_once ('code/include/classes/BASE/application.php'); 
  require_once ('code/include/classes/BASE/debug.php'); 
  require_once ('code/include/classes/base.php'); 
  require_once ('code/include/classes/system.php'); 
  require_once ('code/include/classes/BASE/remote.php'); 
  require_once ('code/include/classes/BASE/tags.php'); 
  require_once ('code/include/classes/BASE/xml.php'); 

  // Include Appleseed classes.
  require_once ('code/include/classes/appleseed.php'); 
  require_once ('code/include/classes/privacy.php'); 
  require_once ('code/include/classes/messages.php'); 
  require_once ('code/include/classes/friends.php'); 
  require_once ('code/include/classes/users.php'); 
  require_once ('code/include/classes/auth.php'); 
  require_once ('code/include/classes/search.php'); 

  // Create the Application class.
  $zAPPLE = new cAPPLESEED ();
  
  // Set Global Variables (Put this at the top of wrapper scripts)
  $zAPPLE->SetGlobals ();

  // Initialize Appleseed.
  $zAPPLE->Initialize("admin.control.update", TRUE);

  // Add javascript to top of page.
  $zHTML->AddScript ("admin/system/update.js");
  
  // Set which tab to highlight.
  $gADMINCONTROLUPDATETAB = 'on';

  // Set which switch to highlight.
  $gADMINCONTROLSWITCH = '';
  
  // Load security settings for the current page.
  $zLOCALUSER->Access (FALSE, FALSE, FALSE);
  
  // Create the update class.
  $zUPDATE = new cSYSTEMUPDATE ();

  // Check to see if user has read access for this area.
  if ($zLOCALUSER->userAccess->r == FALSE) {
    $zAPPLE->IncludeFile ('code/site/error/403.php', INCLUDE_SECURITY_NONE);
    $zAPPLE->End();
  } // if
  
  // Set the page title.
  $gPAGESUBTITLE = ' - Admin';
  
  // Set default server to update.appleseedproject.org
  if (!$gSERVER) $gSERVER = 'update.appleseedproject.org';
  
  // Load list of servers.
  $gSERVERLISTING = $zUPDATE->GetServerListing();
  
  // Load list of versions
  global $gVERSIONLISTING;
  $gVERSIONLISTING = $zUPDATE->GetVersionListing($gSERVER);
  
  $gOFFICIALLATEST = $zAPPLE->GetNodeVersion ($gSERVER);
  
  // Temporary
  $gOFFICIALLATEST = '0.7.4';
  
  // If we're not specifying the version, select the latest.
  if (!$gVERSION) $gVERSION = $gOFFICIALLATEST;
  
  if ($zAPPLE->CheckVersion ($gAPPLESEEDVERSION, $gOFFICIALLATEST)) {
  	$zAPPLE->SetTag ('NEWVERSION', $gOFFICIALLATEST);
  	$zAPPLE->SetTag ('OLDVERSION', $gAPPLESEEDVERSION);
    $zSTRINGS->Lookup ('MESSAGE.AVAILABLE', $zAPPLE->Context);
    $zUPDATE->Message = $zSTRINGS->Output;
  }
 
  // Choose which action to take.
  switch ($gACTION) {
  	case 'CHOOSE':
  	  if ($zUPDATE->AddServer ($gADDSERVER)) {
  	    $gSERVER = $gADDSERVER;
  	    $gADDSERVER = NULL;
  	  } // if
      // Reload list of servers.
      $gSERVERLISTING = $zUPDATE->GetServerListing();
      $gVERSIONLISTING = $zUPDATE->GetVersionListing($gSERVER);
      $gOFFICIALLATEST = $zAPPLE->GetNodeVersion ($gSERVER);
      $gVERSION = $gOFFICIALLATEST;
  	break;
  	case 'ADD':
  	break;
  	case 'REMOVE':
  	  if ($gSERVER == 'update.appleseedproject.org') {
        $zSTRINGS->Lookup ('ERROR.CANTDELETE', $zAPPLE->Context);
        $zUPDATE->Message = $zSTRINGS->Output;
        $zUPDATE->Error = -1;
  	  } else {
  	    $zUPDATE->RemoveServer ($gSERVER);
  	  } // if
      // Reload list of servers.
      $gSERVERLISTING = $zUPDATE->GetServerListing();
      $gSERVER = 'update.appleseedproject.org';
      $gVERSIONLISTING = $zUPDATE->GetVersionListing($gSERVER);
      $gOFFICIALLATEST = $zAPPLE->GetNodeVersion ($gSERVER);
  	break;
  	case 'CANCEL':
  	  $gADDSERVER = NULL;
  	break;
  	case 'CONTINUE':
  	  // If the version we're selecting is greater than the latest available.
      if ($zAPPLE->CheckVersion ($gOFFICIALLATEST, $gVERSION)) {
      	$zAPPLE->SetTag ('VERSION', $gVERSION);
      	$zAPPLE->SetTag ('SERVER', $gSERVER);
        $zSTRINGS->Lookup ('ERROR.INVALIDVERSION', $zAPPLE->Context);
        $zUPDATE->Message = $zSTRINGS->Output;
        $zUPDATE->Error = -1;
        break;
      } // if
      global $bRESULTS;
      $bRESULTS =  $zAPPLE->IncludeFile ("$gFRAMELOCATION/objects/admin/control/update.results.aobj", INCLUDE_SECURITY_NONE, OUTPUT_BUFFER);
  	break;
  	default:
  		$files = $zUPDATE->NodeFileListing($gSERVER);
  	break;
  } // switch
  
  // Include the outline frame.
  $zAPPLE->IncludeFile ("$gFRAMELOCATION/frames/admin/control/update.afrw", INCLUDE_SECURITY_NONE);
  
  // End the application.
  $zAPPLE->End ();

?> 