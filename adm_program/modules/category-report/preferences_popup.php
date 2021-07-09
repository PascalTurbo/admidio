<?php
/**
 * Displays a pop-up window with instructions in the preferences module
 *
 * @copyright 2004-2021 The Admidio Team
 * @see https://www.admidio.org/
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0 only
 *
 * Parameters:	none
 *
 ***********************************************************************************************
 */

require_once(__DIR__ . '/../../system/common.php');

// only authorized user are allowed to start this module
if (!$gCurrentUser->isAdministrator())
{
	$gMessage->show($gL10n->get('SYS_NO_RIGHTS'));
}

// set headline of the script
$headline = $gL10n->get('SYS_CONFIGURATIONS');

header('Content-type: text/html; charset=utf-8');

echo '
<div class="modal-header">
    <h4 class="modal-title">'.$headline.'</h4>
</div>
<div class="modal-body">
	<strong>'.$gL10n->get('SYS_DESIGNATION').'</strong><br/>
    '.$gL10n->get('SYS_CAT_SELECTION_COL_DESC').'<br/><br/>
    <strong>'.$gL10n->get('SYS_COLUMN_SELECTION').'</strong><br/>
	'.$gL10n->get('SYS_COLUMN_SELECTION_DESC').'<br/><br/>		  
    <strong>'.$gL10n->get('SYS_ROLE_SELECTION').'</strong><br/>
	'.$gL10n->get('SYS_ROLE_SELECTION_CONF_DESC').'<br/><br/>
	<strong>'.$gL10n->get('SYS_CAT_SELECTION').'</strong><br/>
	'.$gL10n->get('SYS_CAT_SELECTION_CONF_DESC').'<br/><br/>	
    <strong>'.$gL10n->get('SYS_NUMBER_COL').'</strong><br/>
	'.$gL10n->get('SYS_NUMBER_COL_DESC').'
</div>';
