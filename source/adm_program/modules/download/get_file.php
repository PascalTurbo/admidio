<?php
/******************************************************************************
 * Download Script
 *
 * Copyright    : (c) 2004 - 2007 The Admidio Team
 * Homepage     : http://www.admidio.org
 * Module-Owner : Martin Günzler
 *
 * Uebergaben:
 *
 * folder :  relativer Pfad zu der Datei / Ordners
 * default_folder : gibt den Ordner in adm_my_files/download an, ab dem die
 *                  Verzeichnisstruktur angezeigt wird. Wurde ein Default-Ordner
 *                  gesetzt, kann der Anwender nur noch in Unterordner und nicht
 *                  in hoehere Ordner des Default-Ordners navigieren
 * file   :  die Datei / der Ordner der / die verarbeitet wird
 ******************************************************************************
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * version 2 as published by the Free Software Foundation
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 *****************************************************************************/

require("../../system/common.php");

// pruefen ob das Modul ueberhaupt aktiviert ist
if ($g_preferences['enable_download_module'] != 1)
{
    // das Modul ist deaktiviert
    $g_message->show("module_disabled");
}

$folder = strStripTags(urldecode($_GET['folder']));
$file   = strStripTags(urldecode($_GET['file']));
$default_folder = strStripTags(urldecode($_GET['default_folder']));
$act_folder     = "../../../adm_my_files/download";

// uebergebene Ordner auf Gueltigkeit pruefen
// und Ordnerpfad zusammensetzen
if(strlen($default_folder) > 0)
{
   if(strpos($default_folder, "..") !== false
   || strpos($default_folder, ":/") !== false)
   {
      $g_message->show("invalid_folder");
   }
   $act_folder = "$act_folder/$default_folder";
}

if(strlen($folder) > 0)
{
   if(strpos($folder, "..") !== false
   || strpos($folder, ":/") !== false)
   {
      $g_message->show("invalid_folder");
   }
   $act_folder = "$act_folder/$folder";
}

$filename = "$act_folder/$file";

// Passenden Datentyp erzeugen.
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"$file\"");

// Datei ausgeben.
readfile($filename);
?>