<?php
/******************************************************************************
 * Termine anlegen und bearbeiten
 *
 * Copyright    : (c) 2004 - 2009 The Admidio Team
 * Homepage     : http://www.admidio.org
 * Module-Owner : Markus Fassbender
 * License      : GNU Public License 2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Uebergaben:
 *
 * dat_id   - ID des Termins, der bearbeitet werden soll
 * headline - Ueberschrift, die ueber den Terminen steht
 *            (Default) Termine
 *
 *****************************************************************************/

require_once('../../system/common.php');
require_once('../../system/login_valid.php');
require_once('../../system/classes/date.php');
require_once('../../system/classes/table_roles.php');
require_once('../../system/classes/table_rooms.php');
if ($g_preferences['enable_bbcode'] == 1)
{
    require_once('../../system/bbcode.php');
}

// pruefen ob das Modul ueberhaupt aktiviert ist
if ($g_preferences['enable_dates_module'] == 0)
{
    // das Modul ist deaktiviert
    $g_message->show('module_disabled');
}

if(!$g_current_user->editDates())
{
    $g_message->show('norights');
}

// lokale Variablen der Uebergabevariablen initialisieren
$req_dat_id   = 0;

// Uebergabevariablen pruefen

if(isset($_GET['dat_id']))
{
    if(is_numeric($_GET['dat_id']) == false)
    {
        $g_message->show('invalid');
    }
    $req_dat_id = $_GET['dat_id'];
}

if(!isset($_GET['headline']))
{
    $_GET['headline'] = 'Termine';
}

$_SESSION['navigation']->addUrl(CURRENT_URL);

// Terminobjekt anlegen
$date = new Date($g_db);

if($req_dat_id > 0)
{
    $date->readData($req_dat_id);

    // Pruefung, ob der Termin zur aktuellen Organisation gehoert bzw. global ist
    if($date->editRight() == false)
    {
        $g_message->show('norights');
    }
}
else
{
    // bei neuem Termin Datum mit aktuellen Daten vorbelegen
    $date->setValue('dat_begin', date('Y-m-d H:00:00', time()));
    $date->setValue('dat_end', date('Y-m-d H:00:00', time()+3600));
}

if(isset($_SESSION['dates_request']))
{
    // durch fehlerhafte Eingabe ist der User zu diesem Formular zurueckgekehrt
    // nun die vorher eingegebenen Inhalte auslesen
    foreach($_SESSION['dates_request'] as $key => $value)
    {
        if(strpos($key, 'dat_') == 0)
        {
            $date->setValue($key, stripslashes($value));
        }
    }
    $date_from = $_SESSION['dates_request']['date_from'];
    $time_from = $_SESSION['dates_request']['time_from'];
    $date_to   = $_SESSION['dates_request']['date_to'];
    $time_to   = $_SESSION['dates_request']['time_to'];
    
    // Folgende Felder auch vorbelegen wenn Formular nicht korrekt ausgefüllt wurde (rn)
    // Da diese Felder nicht in der Datenbanktabelle vorkommen und somit in Instanz $date nicht enthalten sind, müssen sie hier gesetzt werden
    $visible_for = $_SESSION['dates_request']['dat_visible_for'];
    unset($_SESSION['dates_request']);
}
else
{
    // Zeitangaben von/bis aus Datetime-Feld aufsplitten
    $date_from = mysqldatetime('d.m.y', $date->getValue('dat_begin'));
    $time_from = mysqldatetime('h:i',   $date->getValue('dat_begin'));

    // Datum-Bis nur anzeigen, wenn es sich von Datum-Von unterscheidet
    $date_to = mysqldatetime('d.m.y', $date->getValue('dat_end'));
    $time_to = mysqldatetime('h:i',   $date->getValue('dat_end'));
    if($date->getValue('dat_rol_id') != '')
    {
        $_SESSION['dates_request']['keep_rol_id'] = 1;
    }
}

// Html-Kopf ausgeben
if($req_dat_id > 0)
{
    $g_layout['title'] = $_GET['headline']. ' bearbeiten';
}
else
{
    $g_layout['title'] = $_GET['headline']. ' anlegen';
}
$dat_rol = 0;
if($date->getValue('dat_rol_id')!= '')
{
    $dat_rol=$date->getValue('dat_rol_id');
}

$g_layout['header'] = '
<script type="text/javascript" src="'.$g_root_path.'/adm_program/libs/calendar/calendar-popup.js"></script>
<link rel="stylesheet" href="'.THEME_PATH. '/css/calendar.css" type="text/css" />
<script type="text/javascript"><!--
    // Funktion blendet Zeitfelder ein/aus
    function setAllDay()
    {
        if(document.getElementById("dat_all_day").checked == true)
        {
            document.getElementById("time_from").style.visibility = "hidden";
            document.getElementById("time_from").style.display    = "none";
            document.getElementById("time_to").style.visibility = "hidden";
            document.getElementById("time_to").style.display    = "none";
        }
        else
        {
            document.getElementById("time_from").style.visibility = "visible";
            document.getElementById("time_from").style.display    = "";
            document.getElementById("time_to").style.visibility = "visible";
            document.getElementById("time_to").style.display    = "";
        }
    }

    // Funktion belegt das Datum-bis entsprechend dem Datum-Von
    function setDateTo()
    {
        if(document.getElementById("date_from").value > document.getElementById("date_to").value)
        {
            document.getElementById("date_to").value = document.getElementById("date_from").value;
        }
    }

    var calPopup = new CalendarPopup("calendardiv");
    calPopup.setCssPrefix("calendar");

    $(document).ready(function() 
    {
        setAllDay();
        $("#dat_headline").focus();
    }); 
    var loginChecked = '.$dat_rol.';
    //öffnet Messagefenster
    function popupMessage()
    {
        if(loginChecked >0 && !document.getElementById("dat_rol_id").checked)
        {
            var msg_result = confirm("Wollen Sie die Anmeldung für den Termin wirklich entfernen? Hierbei würden alle bisherigen Teilnehmer gelöscht werden.");
            if(msg_result)
            {
                $("#formDate").submit();
            }
        }
        else
        {
            $("#formDate").submit();
        }
    }
    
    function markVisibilities()
    {
        var visibilities = $("input[name=\'dat_visible_for[]\']");
        jQuery.each(visibilities, function(index, value) {
            value.checked = true;
        });
    }
    
    function unmarkVisibilities()
    {
        var visibilities = $("input[name=\'dat_visible_for[]\']");
        jQuery.each(visibilities, function(index, value) {
            value.checked = false;
        });
    }
//--></script>';

//Script für BBCode laden
$javascript = "";
if ($g_preferences['enable_bbcode'] == 1)
{
    $javascript = getBBcodeJS('dat_description');
}
$g_layout['header'] .= $javascript;
require(THEME_SERVER_PATH. '/overall_header.php');
 
// Html des Modules ausgeben
echo '
<form method="post" id="formDate" action="'.$g_root_path.'/adm_program/modules/dates/dates_function.php?dat_id='.$req_dat_id.'&amp;mode=1">
<div class="formLayout" id="edit_dates_form">
    <div class="formHead">'. $g_layout['title']. '</div>
    <div class="formBody">
        <ul class="formFieldList">
            <li>
                <dl>
                    <dt><label for="dat_headline">Überschrift:</label></dt>
                    <dd>
                        <input type="text" id="dat_headline" name="dat_headline" style="width: 345px;" maxlength="100" value="'. $date->getValue('dat_headline'). '" />
                        <span class="mandatoryFieldMarker" title="Pflichtfeld">*</span>
                    </dd>
                </dl>
            </li>';

            // besitzt die Organisation eine Elternorga oder hat selber Kinder, so kann die Ankuendigung auf "global" gesetzt werden
            if($g_current_organization->getValue('org_org_id_parent') > 0
            || $g_current_organization->hasChildOrganizations())
            {
                echo '
                <li>
                    <dl>
                        <dt>&nbsp;</dt>
                        <dd>
                            <input type="checkbox" id="dat_global" name="dat_global" ';
                            if($date->getValue('dat_global') == 1)
                            {
                                echo ' checked="checked" ';
                            }
                            echo ' value="1" />
                            <label for="dat_global">'. $_GET['headline']. ' für mehrere Organisationen sichtbar</label>
                            <a class="thickbox" href="'. $g_root_path. '/adm_program/system/msg_window.php?err_code=date_global&amp;window=true&amp;KeepThis=true&amp;TB_iframe=true&amp;height=320&amp;width=580"><img 
                                onmouseover="ajax_showTooltip(event,\''.$g_root_path.'/adm_program/system/msg_window.php?err_code=date_global\',this)" onmouseout="ajax_hideTooltip()"
                                class="iconHelpLink" src="'. THEME_PATH. '/icons/help.png" alt="Hilfe" title="" /></a>
                        </dd>
                    </dl>
                </li>';
            }

          echo '<li><hr /></li>

            <li>
                <dl>
                    <dt><label for="date_from">Beginn:</label></dt>
                    <dd>
                        <span>
                            <input type="text" id="date_from" name="date_from" onchange="javascript:setDateTo();" size="10" maxlength="10" value="'.$date_from.'" />
                            <a class="iconLink" id="anchor_date_from" href="javascript:calPopup.select(document.getElementById(\'date_from\'),\'anchor_date_from\',\'dd.MM.yyyy\',\'date_from\',\'date_to\',\'time_from\',\'time_to\');"><img 
                            	src="'.THEME_PATH.'/icons/calendar.png" alt="Kalender anzeigen" title="Kalender anzeigen" /></a>
                            <span id="calendardiv" style="position: absolute; visibility: hidden; "></span>
                        </span>
                        <span style="margin-left: 10px;">
                            <input type="text" id="time_from" name="time_from" size="5" maxlength="5" value="'.$time_from.'" />
                            <span class="mandatoryFieldMarker" title="Pflichtfeld">*</span>
                        </span>
                        <span style="margin-left: 15px;">
                            <input type="checkbox" id="dat_all_day" name="dat_all_day" ';
                            if($date->getValue("dat_all_day") == 1)
                            {
                                echo ' checked="checked" ';
                            }
                            echo ' onclick="setAllDay()" value="1" />
                            <label for="dat_all_day">Ganztägig</label>
                        </span>
                    </dd>
                </dl>
            </li>
            <li>
                <dl>
                    <dt><label for="date_to">Ende:</label></dt>
                    <dd>
                        <span>
                            <input type="text" id="date_to" name="date_to" size="10" maxlength="10" value="'.$date_to.'" />
                            <a class="iconLink" id="anchor_date_to" href="javascript:calPopup.select(document.getElementById(\'date_to\'),\'anchor_date_to\',\'dd.MM.yyyy\',\'date_from\',\'date_to\',\'time_from\',\'time_to\');"><img 
                            	src="'.THEME_PATH.'/icons/calendar.png" alt="Kalender anzeigen" title="Kalender anzeigen" /></a>
                        </span>
                        <span style="margin-left: 10px;">
                            <input type="text" id="time_to" name="time_to" size="5" maxlength="5" value="'.$time_to.'" />
                            <span class="mandatoryFieldMarker" title="Pflichtfeld">*</span>
                        </span>
                    </dd>
                </dl>
            </li>
            <li>
                <dl>
                    <dt><label for="dat_cat_id">Kalender:</label></dt>
                    <dd>
                        <select id="dat_cat_id" name="dat_cat_id" size="1" tabindex="3">
                            <option value=" "';
                           	if($date->getValue("dat_cat_id") == 0)
                           	{
                              	echo ' selected="selected" ';
                           	}
                          	echo '>- Bitte wählen -</option>';

                            $sql = 'SELECT * FROM '. TBL_CATEGORIES. '
                                     WHERE cat_org_id = '. $g_current_organization->getValue('org_id'). '
                                       AND cat_type   = "DAT"
                                     ORDER BY cat_sequence ASC ';
                            $result = $g_db->query($sql);

                            while($row = $g_db->fetch_object($result))
                            {
                                echo '<option value="'.$row->cat_id.'"';
                                    if($date->getValue("dat_cat_id") == $row->cat_id)
                                    {
                                        echo ' selected="selected" ';
                                    }
                                echo '>'.$row->cat_name.'</option>';
                            }
                        echo '</select>
                        <span class="mandatoryFieldMarker" title="Pflichtfeld">*</span>
                    </dd>
                </dl>
            </li>
            <li>
                <dl>
                    <dt>Anmeldung möglich:</dt>
                    <dd>';
                    if($req_dat_id==0)
                    {
                        echo '<input type="checkbox" id="dat_rol_id" name="dat_rol_id" value="0"'.( ($date->getValue('dat_rol_id') != '') ? ' checked="checked"': '').'/>';
                    }
                    else
                    {
                        echo '<input type="checkbox" name="dat_rol_id" id="dat_rol_id" value="'.$date->getValue('dat_rol_id').'"'.( ($_SESSION['dates_request']['keep_rol_id']) ? ' checked="checked"' : '').'/>';
                    }
                    echo' <a class="thickbox" href="'. $g_root_path. '/adm_program/system/msg_window.php?err_code=date_login_possible&amp;window=true&amp;KeepThis=true&amp;TB_iframe=true&amp;height=200&amp;width=580"><img onmouseover="ajax_showTooltip(event,\''.$g_root_path.'/adm_program/system/msg_window.php?err_code=date_login_possible\',this)" onmouseout="ajax_hideTooltip()" class="iconHelpLink" src="'. THEME_PATH. '/icons/help.png" alt="Hilfe" title="" /></a>
                    </dd>
                </dl>
            </li>';
            if($g_preferences['dates_show_rooms']==1 || $date->getValue('dat_room_id')>0) //nur wenn Raumauswahl aktiviert ist
            {
                echo'
                    <li>
                        <dl>
                            <dt>Raum:</dt>
                            <dd>
                                <select id="dat_room_id" name="dat_room_id" size="1" tabindex="6">';
                                $room = new TableRooms($g_db);
                                $room_choice = $room->getRoomsArray();
                                foreach($room_choice as $key => $value)
                                {
                                    $selected = '';
                                    if($key == $date->getValue('dat_room_id'))
                                    {
                                        $selected = ' selected="selected"';
                                    }
                                    
                                    echo '<option value="'.$key.'"'.$selected.'>'.$value.'</option>';
                                }
                                echo '</select>
                            </dd>
                        </dl>
                    </li>';
            }
          
            echo'
                <li>
                    <dl>
                        <dt>Teilnehmerbegrenzung:</dt>
                        <dd>
                            <input type="text" id="dat_max_members" name="dat_max_members" style="width: 50px;" maxlength="5" value="'.($date->getValue('dat_max_members') ? $date->getValue('dat_max_members') : '').'" />
                            <a class="thickbox" href="'. $g_root_path. '/adm_program/system/msg_window.php?err_code=date_max_members&amp;window=true&amp;KeepThis=true&amp;TB_iframe=true&amp;height=200&amp;width=580"><img onmouseover="ajax_showTooltip(event,\''.$g_root_path.'/adm_program/system/msg_window.php?err_code=date_max_members\',this)" onmouseout="ajax_hideTooltip()" class="iconHelpLink" src="'. THEME_PATH. '/icons/help.png" alt="Hilfe" title="" /></a>
                        </dd>
                    </dl>
                </li>
                <li>
                    <dl>
                    <dt>Sichtbarkeit:</dt>
                    <dd>';

                        $visibility_modes = $date->getVisibilityArray();
                        foreach($visibility_modes as $value => $label)
                        {
                            $identity = 'dat_visible_for_'.$value;
                            echo '<input type="checkbox" name="dat_visible_for[]" value="'.$value.'" id="'.$identity.'"';
                            if($date->isVisibleFor($value) || (isset($visible_for) && in_array($value,$visible_for)) || $req_dat_id == 0)
                            {
                                echo ' checked="checked"';
                            }
                            
                            echo ' />&nbsp;<label for="'.$identity.'">'.$label.'</label>&nbsp;<br/>';
                        }
                    
                echo  '<a href="javascript:markVisibilities();">alle</a>
                        <a href="javascript:unmarkVisibilities();">keine</a>
                    </dd>
                </dl>
            </li>
            <li>
                <dl>
                    <dt><label for="dat_location">Ort:</label></dt>
                    <dd>
                        <input type="text" id="dat_location" name="dat_location" style="width: 345px;" maxlength="50" value="'. $date->getValue('dat_location'). '" />';
                        if($g_preferences['dates_show_map_link'])
                        {
                            echo '<a class="thickbox" href="'. $g_root_path. '/adm_program/system/msg_window.php?err_code=date_location_link&amp;window=true&amp;KeepThis=true&amp;TB_iframe=true&amp;height=200&amp;width=580"><img 
                                onmouseover="ajax_showTooltip(event,\''.$g_root_path.'/adm_program/system/msg_window.php?err_code=date_location_link\',this)" onmouseout="ajax_hideTooltip()"
                                class="iconHelpLink" src="'. THEME_PATH. '/icons/help.png" alt="Hilfe" title="" /></a>';
                        }
                    echo '</dd>
                </dl>
            </li>';
            if($g_preferences['dates_show_map_link'])
            {
                if(strlen($date->getValue("dat_country")) == 0)
                {
                    $date->setValue("dat_country", $g_preferences['default_country']);
                }
                echo '<li>
                    <dl>
                        <dt>&nbsp;</dt>
                        <dd>
                            <select size="1" id="dat_country" name="dat_country">';
                                // Datei mit Laenderliste oeffnen und alle Laender einlesen
                                $country_list = fopen("../../system/staaten.txt", "r");
                                $country = trim(fgets($country_list));
                                while (!feof($country_list))
                                {
                                    echo '<option value="'.$country.'"';
                                    if($country == $date->getValue("dat_country"))
                                    {
                                        echo ' selected="selected" ';
                                    }
                                    echo '>'.$country.'</option>';
                                    $country = trim(fgets($country_list));
                                }
                                fclose($country_list);
                            echo '</select>
                        </dd>
                    </dl>
                </li>';
            }

            if ($g_preferences['enable_bbcode'] == 1)
            {
               printBBcodeIcons();
            }
           echo '
            <li>
                <dl>
                    <dt><label for="dat_description">Text:</label>';
                        if($g_preferences['enable_bbcode'] == 1)
                        {
                            printEmoticons();
                        }
                    echo '</dt>
                    <dd>
                        <textarea id="dat_description" name="dat_description" style="width: 345px;" rows="10" cols="40">'. $date->getValue('dat_description'). '</textarea>
                        <span class="mandatoryFieldMarker" title="Pflichtfeld">*</span>
                    </dd>
                </dl>
            </li>
        </ul>

        <hr />

        <div class="formSubmit">
            <button name="speichern" type="button" onclick="javascript:popupMessage();" value="speichern"><img src="'. THEME_PATH. '/icons/disk.png" alt="Speichern" />&nbsp;Speichern</button>
        </div>
    </div>
</div>
</form>

<ul class="iconTextLinkList">
    <li>
        <span class="iconTextLink">
            <a href="'.$g_root_path.'/adm_program/system/back.php"><img
            src="'. THEME_PATH. '/icons/back.png" alt="Zurück" title="Zurück"/></a>
            <a href="'.$g_root_path.'/adm_program/system/back.php">Zurück</a>
        </span>
    </li>
</ul>';

require(THEME_SERVER_PATH. '/overall_footer.php');
?>