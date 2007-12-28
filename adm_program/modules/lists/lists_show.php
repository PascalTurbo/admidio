<?php
/******************************************************************************
 * Listen anzeigen
 *
 * Copyright    : (c) 2004 - 2007 The Admidio Team
 * Homepage     : http://www.admidio.org
 * Module-Owner : Markus Fassbender
 * License      : GNU Public License 2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Uebergaben:
 *
 * type   : Listenselect (mylist, address, telephone, former)
 * mode   : Ausgabeart   (html, print, csv-ms, csv-ms-2k, csv-oo)
 * rol_id : Rolle, fuer die die Funktion dargestellt werden soll
 * start  : Angabe, ab welchem Datensatz Mitglieder angezeigt werden sollen 
 *
 *****************************************************************************/

require("../../system/common.php");
require("../../system/login_valid.php");
require("../../system/role_class.php");

// lokale Variablen der Uebergabevariablen initialisieren
$arr_mode   = array("csv-ms", "csv-ms-2k", "csv-oo", "html", "print");
$arr_type   = array("mylist", "address", "telephone", "former");
$req_rol_id = 0;
$req_start  = 0;

// Uebergabevariablen pruefen

$req_mode   = strStripTags($_GET["mode"]);
$req_type   = strStripTags($_GET["type"]);

if(in_array($req_mode, $arr_mode) == false)
{
    $g_message->show("invalid");
}

if(in_array($req_type, $arr_type) == false)
{
    $g_message->show("invalid");
}

if(isset($_GET["rol_id"]))
{
    if(is_numeric($_GET["rol_id"]) == false)
    {
        $g_message->show("invalid");
    }
    $req_rol_id = $_GET["rol_id"];
}

if(isset($_GET['start']))
{
    if(is_numeric($_GET["start"]) == false)
    {
        $g_message->show("invalid");
    }
    $req_start = $_GET['start'];
}

//SESSION array für bilder initialisieren
$_SESSION['profilphoto'] = array();

if($req_mode == "csv-ms")
{
    $separator    = ";"; // Microsoft XP und neuer braucht ein Semicolon
    $value_quotes = "\"";
    $req_mode     = "csv";
}
else if($req_mode == "csv-ms-2k")
{
    $separator    = ","; // Microsoft 2000 und aelter braucht ein Komma
    $value_quotes = "\"";
    $req_mode     = "csv";
}
else if($req_mode == "csv-oo")
{
    $separator    = ",";    // fuer CSV-Dateien
    $value_quotes = "\"";   // Werte muessen mit Anfuehrungszeichen eingeschlossen sein
    $req_mode     = "csv";
}
else
{
    $separator    = ",";    // fuer CSV-Dateien
    $value_quotes = "";
}

// Array um den Namen der Tabellen sinnvolle Texte zuzuweisen
$arr_col_name = array('usr_login_name' => 'Benutzername',
                      'usr_photo'      => 'Foto',
                      'mem_begin'      => 'Beginn',
                      'mem_end'        => 'Ende',
                      'mem_leader'     => 'Leiter'
                      );

if($req_mode == "html")
{
    $class_table           = "tableList";
    $class_sub_header      = "tableSubHeader";
    $class_sub_header_font = "tableSubHeaderFont";
}
else if($req_mode == "print")
{
    $class_table           = "tableListPrint";
    $class_sub_header      = "tableSubHeaderPrint";
    $class_sub_header_font = "tableSubHeaderFontPrint";
}

$main_sql  = "";   // enthaelt das Haupt-Sql-Statement fuer die Liste
$str_csv   = "";   // enthaelt die komplette CSV-Datei als String
$leiter    = 0;    // Gruppe besitzt Leiter
$arr_usf_types = array();    // speichert zu jeder usf_id den usf_type
$arr_usf_names = array();    // speichert zu jeder usf_id den usf_name

// Rollenobjekt erzeugen
$role = new Role($g_db, $req_rol_id);

// Nummer der Spalte, ab der die Anzeigefelder anfangen (beginnend mit 0)
$start_column = 2;

// das jeweilige Sql-Statement zusammenbauen
// !!!! Das 1. Feld muss immer mem_leader und das 2. usr_id sein !!!!

switch($req_type)
{
    case "mylist":
        $main_sql = $_SESSION['mylist_sql'];
        break;

    case "address":
        $usf_last_name  = $g_current_user->getProperty("Nachname", "usf_id");
        $usf_first_name = $g_current_user->getProperty("Vorname", "usf_id");
        $usf_birthday   = $g_current_user->getProperty("Geburtstag", "usf_id");
        $usf_address    = $g_current_user->getProperty("Adresse", "usf_id");
        $usf_zip_code   = $g_current_user->getProperty("PLZ", "usf_id");
        $usf_city       = $g_current_user->getProperty("Ort", "usf_id");
        
        $main_sql = "SELECT mem_leader, usr_id, f$usf_last_name.usd_value, f$usf_first_name.usd_value, 
                            f$usf_birthday.usd_value, f$usf_address.usd_value, 
                            f$usf_zip_code.usd_value, f$usf_city.usd_value
                     FROM ". TBL_ROLES. ", ". TBL_CATEGORIES. ", ". TBL_MEMBERS. ", ". TBL_USERS. "
                     LEFT JOIN ". TBL_USER_DATA ." f$usf_last_name
                       ON f$usf_last_name.usd_usr_id = usr_id
                      AND f$usf_last_name.usd_usf_id = $usf_last_name
                     LEFT JOIN ". TBL_USER_DATA ." f$usf_first_name
                       ON f$usf_first_name.usd_usr_id = usr_id
                      AND f$usf_first_name.usd_usf_id = $usf_first_name
                     LEFT JOIN ". TBL_USER_DATA ." f$usf_birthday
                       ON f$usf_birthday.usd_usr_id = usr_id
                      AND f$usf_birthday.usd_usf_id = $usf_birthday
                     LEFT JOIN ". TBL_USER_DATA ." f$usf_address
                       ON f$usf_address.usd_usr_id = usr_id
                      AND f$usf_address.usd_usf_id = $usf_address
                     LEFT JOIN ". TBL_USER_DATA ." f$usf_zip_code
                       ON f$usf_zip_code.usd_usr_id = usr_id
                      AND f$usf_zip_code.usd_usf_id = $usf_zip_code
                     LEFT JOIN ". TBL_USER_DATA ." f$usf_city
                       ON f$usf_city.usd_usr_id = usr_id
                      AND f$usf_city.usd_usf_id = $usf_city
                    WHERE rol_id     = $req_rol_id
                      AND rol_cat_id = cat_id
                      AND cat_org_id = ". $g_current_organization->getValue("org_id"). "
                      AND rol_id     = mem_rol_id
                      AND mem_valid  = ". $role->getValue("rol_valid"). "
                      AND mem_usr_id = usr_id
                      AND usr_valid  = 1
                    ORDER BY mem_leader DESC, f$usf_last_name.usd_value ASC, f$usf_first_name.usd_value ASC ";
        break;

    case "telephone":
        $usf_last_name  = $g_current_user->getProperty("Nachname", "usf_id");
        $usf_first_name = $g_current_user->getProperty("Vorname", "usf_id");
        $usf_phone      = $g_current_user->getProperty("Telefon", "usf_id");
        $usf_mobile     = $g_current_user->getProperty("Handy", "usf_id");
        $usf_email      = $g_current_user->getProperty("E-Mail", "usf_id");
        $usf_fax        = $g_current_user->getProperty("Fax", "usf_id");
        
        $main_sql = "SELECT mem_leader, usr_id, f$usf_last_name.usd_value, f$usf_first_name.usd_value, 
                            f$usf_phone.usd_value, f$usf_mobile.usd_value, f$usf_email.usd_value, f$usf_fax.usd_value
                     FROM ". TBL_ROLES. ", ". TBL_CATEGORIES. ", ". TBL_MEMBERS. ", ". TBL_USERS. "
                     LEFT JOIN ". TBL_USER_DATA ." f$usf_last_name
                       ON f$usf_last_name.usd_usr_id = usr_id
                      AND f$usf_last_name.usd_usf_id = $usf_last_name
                     LEFT JOIN ". TBL_USER_DATA ." f$usf_first_name
                       ON f$usf_first_name.usd_usr_id = usr_id
                      AND f$usf_first_name.usd_usf_id = $usf_first_name
                     LEFT JOIN ". TBL_USER_DATA ." f$usf_phone
                       ON f$usf_phone.usd_usr_id = usr_id
                      AND f$usf_phone.usd_usf_id = $usf_phone
                     LEFT JOIN ". TBL_USER_DATA ." f$usf_mobile
                       ON f$usf_mobile.usd_usr_id = usr_id
                      AND f$usf_mobile.usd_usf_id = $usf_mobile
                     LEFT JOIN ". TBL_USER_DATA ." f$usf_email
                       ON f$usf_email.usd_usr_id = usr_id
                      AND f$usf_email.usd_usf_id = $usf_email
                     LEFT JOIN ". TBL_USER_DATA ." f$usf_fax
                       ON f$usf_fax.usd_usr_id = usr_id
                      AND f$usf_fax.usd_usf_id = $usf_fax
                    WHERE rol_id     = $req_rol_id
                      AND rol_cat_id = cat_id
                      AND cat_org_id = ". $g_current_organization->getValue("org_id"). "
                      AND rol_id     = mem_rol_id
                      AND mem_valid  = ". $role->getValue("rol_valid"). "
                      AND mem_usr_id = usr_id
                      AND usr_valid  = 1
                    ORDER BY mem_leader DESC, f$usf_last_name.usd_value ASC, f$usf_first_name.usd_value ASC ";
        break;

    case "former":
        $usf_last_name  = $g_current_user->getProperty("Nachname", "usf_id");
        $usf_first_name = $g_current_user->getProperty("Vorname", "usf_id");
        $usf_birthday   = $g_current_user->getProperty("Geburtstag", "usf_id");
        
        $main_sql = "SELECT mem_leader, usr_id, f$usf_last_name.usd_value, f$usf_first_name.usd_value, 
                            f$usf_birthday.usd_value, mem_begin, mem_end
                     FROM ". TBL_ROLES. ", ". TBL_CATEGORIES. ", ". TBL_MEMBERS. ", ". TBL_USERS. "
                     LEFT JOIN ". TBL_USER_DATA ." f$usf_last_name
                       ON f$usf_last_name.usd_usr_id = usr_id
                      AND f$usf_last_name.usd_usf_id = $usf_last_name
                     LEFT JOIN ". TBL_USER_DATA ." f$usf_first_name
                       ON f$usf_first_name.usd_usr_id = usr_id
                      AND f$usf_first_name.usd_usf_id = $usf_first_name
                     LEFT JOIN ". TBL_USER_DATA ." f$usf_birthday
                       ON f$usf_birthday.usd_usr_id = usr_id
                      AND f$usf_birthday.usd_usf_id = $usf_birthday
                    WHERE rol_id     = $req_rol_id
                      AND rol_cat_id = cat_id
                      AND cat_org_id = ". $g_current_organization->getValue("org_id"). "
                      AND rol_id     = mem_rol_id
                      AND mem_valid  = 0
                      AND mem_usr_id = usr_id
                      AND usr_valid  = 1
                    ORDER BY mem_leader DESC, mem_end DESC, f$usf_last_name.usd_value ASC, f$usf_first_name.usd_value ASC ";
        break;
}

// pruefen, ob die Rolle Leiter hat, wenn nicht, dann Standardliste anzeigen

if(substr_count(str_replace(" ", "", $main_sql), "mem_valid=0") > 0)
{
    $former = 0;
}
else
{
    $former = 1;
}

// aus main_sql alle Felder ermitteln und in ein Array schreiben

// SELECT am Anfang entfernen
$str_fields = substr($main_sql, 7);
// ab dem FROM alles abschneiden
$str_fields = substr($str_fields, 0, strpos($str_fields, " FROM "));

$arr_fields = explode(",", $str_fields);

// Spaces entfernen
for($i = 0; $i < count($arr_fields); $i++)
{
    $arr_fields[$i] = trim($arr_fields[$i]);
}

// SQL-Statement der Liste ausfuehren und pruefen ob Daten vorhanden sind
$result_list = $g_db->query($main_sql);

$num_members = $g_db->num_rows($result_list);

if($num_members == 0)
{
    // Es sind keine Daten vorhanden !
    $g_message->show("nodata");
}

if($num_members < $req_start)
{
    $g_message->show("invalid");
}

if($req_mode == "html" && $req_start == 0)
{
    // Url fuer die Zuruecknavigation merken, aber nur in der Html-Ansicht
    $_SESSION['navigation']->addUrl(CURRENT_URL);
}

if($req_mode != "csv")
{
    // Html-Kopf wird geschrieben
    if($req_mode == "print")
    {
        echo '
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="de" xml:lang="de">
        <head>
            <!-- (c) 2004 - 2007 The Admidio Team - http://www.admidio.org -->
            
            <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        
            <title>'. $g_current_organization->getValue("org_longname"). ' - Liste - '. $role->getValue("rol_name"). '</title>
            
            <link rel="stylesheet" type="text/css" href="'. THEME_PATH. '/print.css" />
            <script type="text/javascript" src="'. $g_root_path. '/adm_program/system/common_functions.js"></script>

            <!--[if lt IE 7]>
    		<script type="text/javascript"><!--
				window.attachEvent("onload", correctPNG);
    		--></script>
    		<![endif]-->

            <style type="text/css">
                @page { size:landscape; }
            </style>
        </head>
        <body class="bodyPrint">';
    }
    else
    {
        $g_layout['title']  = "Liste - ". $role->getValue("rol_name");
        $g_layout['header'] = "
            <script type=\"text/javascript\"><!--
                function exportList(element)
                {
                    var sel_list = element.value;

                    if(sel_list.length > 1)
                    {
                        self.location.href = '$g_root_path/adm_program/modules/lists/lists_show.php?type=$req_type&rol_id=$req_rol_id&mode=' + sel_list;
                    }
                }
            //--></script>";
        require(THEME_SERVER_PATH. "/overall_header.php");
    }
    
    echo "<h1 class=\"moduleHeadline\">". $role->getValue("rol_name"). "&nbsp;&#40;".$role->getValue("cat_name")."&#41;</h1>";

    //Beschreibung der Rolle einblenden
    if(strlen($role->getValue("rol_description")) > 0)
    {
        echo "<h3>". $role->getValue("rol_description"). "</h3>";
    }

    if($req_mode == "html")
    {
        if($req_type == "mylist")
        {
            $image = "application_form.png";
            $text  = "Konfiguration Eigene Liste";
        }
        else
        {
            $image = "application_view_list.png";
            $text  = "Listen&uuml;bersicht";            
        }

        echo "
        <div class=\"navigationPath\">
            <a href=\"$g_root_path/adm_program/system/back.php\"><img
            src=\"". THEME_PATH. "/icons/$image\" alt=\"Zurück\" /></a>
            <a href=\"$g_root_path/adm_program/system/back.php\">$text</a>
        </div>
        
        <ul class=\"iconTextLinkList\">";
            if($role->getValue("rol_mail_login") == 1 && $g_preferences['enable_mail_module'] == 1)
            {
                echo "<li>
                    <span class=\"iconTextLink\">
                        <a href=\"$g_root_path/adm_program/modules/mail/mail.php?rol_id=$req_rol_id\"><img
                        src=\"". THEME_PATH. "/icons/email.png\" alt=\"E-Mail an Mitglieder\" /></a>
                        <a href=\"$g_root_path/adm_program/modules/mail/mail.php?rol_id=$req_rol_id\">E-Mail an Mitglieder</a>
                    </span>
                </li>";
            }

            echo "<li>
                <span class=\"iconTextLink\">
                    <a href=\"#\" onclick=\"window.open('$g_root_path/adm_program/modules/lists/lists_show.php?type=$req_type&amp;mode=print&amp;rol_id=$req_rol_id', '_blank')\"><img
                    src=\"". THEME_PATH. "/icons/print.png\" alt=\"Druckvorschau\" /></a>
                    <a href=\"#\" onclick=\"window.open('$g_root_path/adm_program/modules/lists/lists_show.php?type=$req_type&amp;mode=print&amp;rol_id=$req_rol_id', '_blank')\">Druckvorschau</a>
                </span>
            </li>
            <li>
                <span class=\"iconTextLink\">
                    <img src=\"". THEME_PATH. "/icons/database_out.png\" alt=\"Exportieren\" />
                    <select size=\"1\" name=\"list$i\" onchange=\"exportList(this)\">
                        <option value=\"\" selected=\"selected\">Exportieren nach ...</option>
                        <option value=\"csv-ms\">Microsoft Excel</option>
                        <option value=\"csv-ms-2k\">Microsoft Excel 97/2000</option>
                        <option value=\"csv-oo\">CSV-Datei (OpenOffice)</option>
                    </select>
                </span>
            </li>
        </ul>";
    }
}

if($req_mode != "csv")
{
    // Tabellenkopf schreiben
    echo "<table class=\"$class_table\" style=\"width: 95%;\" cellspacing=\"0\">
        <thead><tr>";
}

// Spalten-Ueberschriften
for($i = $start_column; $i < count($arr_fields); $i++)
{
    $align = "left";

    // den Namen des Feldes ermitteln
    if(strpos($arr_fields[$i], ".") > 0)
    {
        // benutzerdefiniertes Feld
        // die usf_id steht im Tabellen-Alias hinter dem f
        $usf_id = substr($arr_fields[$i], 1, strpos($arr_fields[$i], "."));
        $sql = "SELECT usf_name, usf_type FROM ". TBL_USER_FIELDS. "
                 WHERE usf_id = $usf_id ";
        $result_user_fields = $g_db->query($sql);

        $row = $g_db->fetch_object($result_user_fields);
        $col_name = $row->usf_name;
        $arr_usf_types[$usf_id] = $row->usf_type;
        $arr_usf_names[$usf_id] = $row->usf_name;

        if($arr_usf_types[$usf_id] == "CHECKBOX")
        {
            $align = "center";
        }
        elseif($arr_usf_types[$usf_id] == "NUMERIC")
        {
            $align = "right";
        }
    }
    else
    {
        $col_name = $arr_col_name[$arr_fields[$i]];

        if($arr_fields[$i] == "usr_gender")
        {
            // Icon des Geschlechts zentriert darstellen
            $align = "center";
        }
    }

    if($req_mode == "csv")
    {
        if($i == $start_column)
        {
            // die Laufende Nummer noch davorsetzen
            $str_csv = $str_csv. $value_quotes. "Nr.". $value_quotes;
        }
        $str_csv = $str_csv. $separator. $value_quotes. $col_name. $value_quotes;
    }
    else
    {                
        if($i == $start_column)
        {
            // die Laufende Nummer noch davorsetzen
            echo "<th style=\"text-align: $align;\">Nr.</th>";
        }
        echo "<th style=\"text-align: $align;\">$col_name</th>\n";
    }
}  // End-For

if($req_mode == "csv")
{
    $str_csv = $str_csv. "\n";
}
else
{
    echo "</tr></thead><tbody>\n";
}

$irow        = $req_start + 1;  // Zahler fuer die jeweilige Zeile
$leader_head = -1;              // Merker um Wechsel zwischen Leiter und Mitglieder zu merken
if($req_mode == "html" && $g_preferences['lists_members_per_page'] > 0)
{
    $members_per_page = $g_preferences['lists_members_per_page'];     // Anzahl der Mitglieder, die auf einer Seite angezeigt werden
}
else
{
    $members_per_page = $num_members;
}

// jetzt erst einmal zu dem ersten relevanten Datensatz springen
if(!$g_db->data_seek($result_list, 0))
{
    $g_message->show("invalid");
}

for($j = 0; $j < $members_per_page && $j + $req_start < $num_members; $j++)
{
    if($row = $g_db->fetch_array($result_list))
    {
        if($req_mode != "csv")
        {
            // erst einmal pruefen, ob es ein Leiter ist, falls es Leiter in der Gruppe gibt, 
            // dann muss noch jeweils ein Gruppenkopf eingefuegt werden
            if($leader_head != $row['mem_leader']
            && ($row['mem_leader'] != 0 || $leader_head != -1))
            {
                if($row['mem_leader'] == 1)
                {
                    $title = "Leiter";
                }
                else
                {
                    $title = "Teilnehmer";
                }
                echo "<tr>
                    <td class=\"$class_sub_header\" colspan=\"". (count($arr_fields) + 1). "\">
                        <div class=\"$class_sub_header_font\" style=\"float: left;\">&nbsp;$title</div>
                    </td>
                </tr>";
                $leader_head = $row['mem_leader'];
            }
        }

        if($req_mode == "html")
        {
            echo "<tr class=\"listMouseOut\" onmouseover=\"this.className='listMouseOver'\"
            onmouseout=\"this.className='listMouseOut'\" style=\"cursor: pointer\"
            onclick=\"window.location.href='$g_root_path/adm_program/modules/profile/profile.php?user_id=". $row['usr_id']. "'\">\n";
        }
        else if($req_mode == "print")
        {
            echo "<tr>\n";
        }

        // Felder zu Datensatz
        for($i = $start_column; $i < count($arr_fields); $i++)
        {
            if(strpos($arr_fields[$i], ".") > 0)
            {
                // pruefen, ob ein benutzerdefiniertes Feld und Kennzeichen merken
                $b_user_field = true;

                // die usf_id steht im Tabellen-Alias hinter dem f
                $usf_id = substr($arr_fields[$i], 1, strpos($arr_fields[$i], "."));
            }
            else
            {
                $b_user_field = false;
                $usf_id = 0;
            }

            if($req_mode != "csv")
            {
                $align = "left";
                if($b_user_field == true)
                {
                    if($arr_usf_types[$usf_id] == "CHECKBOX")
                    {
                        $align = "center";
                    }
                    elseif($arr_usf_types[$usf_id] == "NUMERIC")
                    {
                        $align = "right";
                    }
                }
                else
                {
                    if($arr_fields[$i] == "usr_gender")
                    {
                        $align = "center";
                    }
                }
                if($i == $start_column)
                {
                    // die Laufende Nummer noch davorsetzen
                    echo "<td style=\"text-align: $align;\">$irow</td>";
                }
                echo "<td style=\"text-align: $align;\">";
            }
            else
            {
                if($i == $start_column)
                {
                    // erste Spalte zeigt lfd. Nummer an
                    $str_csv = $str_csv. $value_quotes. "$irow". $value_quotes;
                }
            }

            $content  = "";
            $usf_type = "";

            // Feldtyp bei Spezialfeldern setzen
            if($arr_fields[$i] == "mem_begin" || $arr_fields[$i] == "mem_end")
            {
                $usf_type = "DATE";
            }
            elseif($arr_fields[$i] == "usr_login_name")
            {
                $usf_type = "TEXT";
            }
            elseif($usf_id > 0)
            {
                $usf_type = $arr_usf_types[$usf_id];
            }
                        
            // Ausgabe je nach Feldtyp aufbereiten
            if($usf_id == $g_current_user->getProperty("Geschlecht", "usf_id"))
            {
                // Geschlecht anzeigen
                if($row[$i] == 1)
                {
                    if($req_mode == "csv" || $req_mode == "print")
                    {
                        $content = "männlich";
                    }
                    else
                    {
                        $content = "<img src=\"". THEME_PATH. "/icons/male.png\"
                                    style=\"vertical-align: middle;\" alt=\"m&auml;nnlich\" />";
                    }
                }
                elseif($row[$i] == 2)
                {
                    if($req_mode == "csv" || $req_mode == "print")
                    {
                        $content = "weiblich";
                    }
                    else
                    {
                        $content = "<img src=\"". THEME_PATH. "/icons/female.png\"
                                    style=\"vertical-align: middle;\" alt=\"weiblich\" />";
                    }
                }
                else
                {
                    if($req_mode != "csv")
                    {
                        $content = "&nbsp;";
                    }
                }
            }
            elseif($arr_fields[$i] == "usr_photo")
            {
                // Benutzerfoto anzeigen
                if(($req_mode == "html" || $req_mode == "print") && $row[$i] != NULL)
                {
                    $_SESSION['profilphoto'][$row['usr_id']]=$row[$i];
                    $content = "<img src=\"photo_show.php?usr_id=".$row['usr_id']."\"
                                style=\"vertical-align: middle;\" alt=\"Benutzerfoto\" />";
                }
                if ($req_mode == "csv" && $row[$i] != NULL)
                {
                    $content = "Profilfoto Online";
                }
            }
            else
            {
                switch($usf_type)
                {
                    case "CHECKBOX":
                        // Checkboxen werden durch ein Bildchen dargestellt
                        if($row[$i] == 1)
                        {
                            if($req_mode == "csv")
                            {
                                $content = "ja";
                            }
                            else
                            {
                                echo "<img src=\"". THEME_PATH. "/icons/checkbox_checked.gif\"
                                    style=\"vertical-align: middle;\" alt=\"on\" />";
                            }
                        }
                        else
                        {
                            if($req_mode == "csv")
                            {
                                $content = "nein";
                            }
                            else
                            {
                                echo "<img src=\"". THEME_PATH. "/icons/checkbox.gif\"
                                    style=\"vertical-align: middle;\" alt=\"off\" />";
                            }
                        }
                        break;

                    case "DATE":
                        // Datum muss noch formatiert werden
                        $content = mysqldate('d.m.y', $row[$i]);
                        break;

                    case "EMAIL":
                        // E-Mail als Link darstellen
                        if(strlen($row[$i]) > 0)
                        {
                            if($req_mode == "html")
                            {
                                if($g_preferences['enable_mail_module'] == 1 && $arr_usf_names[$usf_id] == "E-Mail")
                                {
                                    $content = "<a href=\"$g_root_path/adm_program/modules/mail/mail.php?usr_id=". $row['usr_id']. "\">". $row[$i]. "</a>";
                                }
                                else
                                {
                                    $content = "<a href=\"mailto:". $row[$i]. "\">". $row[$i]. "</a>";
                                }
                            }
                            else
                            {
                                $content = $row[$i];
                            }
                        }
                        break;

                    case "URL":
                        // Homepage als Link darstellen
                        if(strlen($row[$i]) > 0)
                        {
                            if($req_mode == "html")
                            {
                                $content = "<a href=\"". $row[$i]. "\" target=\"_blank\">". substr($row[$i], 7). "</a>";
                            }
                            else
                            {
                                $content = substr($row[$i], 7);
                            }
                        }
                        break;

                    default:
                        $content = $row[$i];
                        break;                            
                }
            }

            if($req_mode == "csv")
            {
                $str_csv = $str_csv. $separator. $value_quotes. "$content". $value_quotes;
            }

            else
            {
                echo $content. "</td>\n";
            }
        }

        if($req_mode == "csv")
        {
            $str_csv = $str_csv. "\n";
        }
        else
        {
            echo "</tr>\n";
        }

        $irow++;
    }
}  // End-While (jeder gefundene User)

if($req_mode == "csv")
{
    // nun die erstellte CSV-Datei an den User schicken
    $filename = $g_organization. "-". str_replace(" ", "_", str_replace(".", "", $role->getValue("rol_name"))). ".csv";
    header("Content-Type: text/comma-separated-values; charset=ISO-8859-1");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    echo $str_csv;
}
else
{
    echo "</tbody></table>";

    if($req_mode != "print")
    {
        // Navigation mit Vor- und Zurueck-Buttons
        $base_url = "$g_root_path/adm_program/modules/lists/lists_show.php?type=$req_type&mode=$req_mode&rol_id=$req_rol_id";
        echo generatePagination($base_url, $num_members, $members_per_page, $req_start, TRUE);
    }

    //INFOBOX zur Gruppe
    //nur anzeigen wenn zusatzfelder gefüllt sind
    if(strlen($role->getValue("rol_start_date")) > 0
    || $role->getValue("rol_weekday") > 0
    || strlen($role->getValue("rol_start_time")) > 0
    || strlen($role->getValue("rol_location")) > 0
    || strlen($role->getValue("rol_cost")) > 0
    || strlen($role->getValue("rol_max_members")) > 0)
    {
        echo "
        <br />
        
        <div class=\"groupBox\" id=\"infoboxListsBox\">
            <div class=\"groupBoxHeadline\">Infobox: ". $role->getValue("rol_name"). "</div>
            <div class=\"groupBoxBody\">
                <ul class=\"formFieldList\">
                    <li>";
                        //Kategorie
                        echo"
                        <dl>
                            <dt>Kategorie:</dt>
                            <dd>".$role->getValue("cat_name")."</dd>
                        </dl>
                    </li>";

                        //Beschreibung
                        if(strlen($role->getValue("rol_description")) > 0)
                        {
                            echo"<li>
                                <dl>
                                    <dt>Beschreibung:</dt>
                                    <dd>".$role->getValue("rol_description")."</dd>
                                </dl>
                            </li>";
                        }

                        //Zeitraum
                        if(strlen($role->getValue("rol_start_date")) > 0)
                        {
                            echo"<li>
                                <dl>
                                    <dt>Zeitraum:</dt>
                                    <dd>". mysqldate("d.m.y", $role->getValue("rol_start_date")). " bis ". mysqldate("d.m.y", $role->getValue("rol_end_date")). "</dd>
                                </dl>
                            </li>";
                        }

                        //Termin
                        if($role->getValue("rol_weekday") > 0 || strlen($role->getValue("rol_start_time")) > 0)
                        {
                            echo"<li>
                                <dl>
                                    <dt>Termin: </dt>
                                    <dd>"; 
                                        if($role->getValue("rol_weekday") > 0)
                                        {
                                            echo $arrDay[$role->getValue("rol_weekday")-1];
                                        }
                                        if(strlen($role->getValue("rol_start_time")) > 0)
                                        {
                                            echo " von ". mysqltime("h:i", $role->getValue("rol_start_time")). " bis ". mysqltime("h:i", $role->getValue("rol_end_time"));
                                        }

                                    echo"</dd>
                                </dl>
                            </li>";
                        }

                        //Treffpunkt
                        if(strlen($role->getValue("rol_location")) > 0)
                        {
                            echo"<li>
                                <dl>
                                    <dt>Treffpunkt:</dt>
                                    <dd>".$role->getValue("rol_location")."</dd>
                                </dl>
                            </li>";
                        }

                        //Beitrag
                        if(strlen($role->getValue("rol_cost")) > 0)
                        {
                            echo"<li>
                                <dl>
                                    <dt>Beitrag:</dt>
                                    <dd>". $role->getValue("rol_cost"). " &euro;</dd>
                                </dl>
                            </li>";
                        }

                        //maximale Teilnehmerzahl
                        if(strlen($role->getValue("rol_max_members")) > 0)
                        {
                            echo"<li>
                                <dl>
                                    <dt>Max. Teilnehmer:</dt>
                                    <dd>". $role->getValue("rol_max_members"). "</dd>
                                </dl>
                            </li>";
                        }
                echo"</ul>
            </div>
        </div>";
    } // Ende Infobox
    
    if($req_mode == "print")
    {
        echo "</body></html>";
    }
    else
    {    
        require(THEME_SERVER_PATH. "/overall_footer.php");
    }
}

?>