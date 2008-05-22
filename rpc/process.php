<?php
/*
This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along
    with this program; if not, write to the Free Software Foundation, Inc.,
    51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/ 
require_once( "../../../../wp-config.php" );
require_once( "../classes/classes.php" );
$simpleKarma = new SimpleKarma($_GET["prefix"]);
$prefix = $_GET["prefix"];
$action = $_GET["actionString"];
$id = $_GET["objectId"];
$value = $_GET["value"];
if ($action == 'subtract')
{
	$value = 0 - $value;
}
$karma = $simpleKarma->modifyKarma($id , $value);
if($simpleKarma->isFlagged($id))
{
	$message = $simpleKarma->getMessageOption();
}
else
{
	$message = ' Post Flagged';
}
$imgpathup = get_bloginfo('wpurl') . '/wp-content/plugins/simple-karma/images/gray_up.gif';
$imgpathdown = get_bloginfo('wpurl') . '/wp-content/plugins/simple-karma/images/gray_down.gif';
$imgpathflag = get_bloginfo('wpurl') . '/wp-content/plugins/simple-karma/images/inappropriate-off-icon.gif';

echo '{"id" : "'.$id.'", "karma" : "'.$karma.'", "message" : "'.$message.'" , "table" : "' . $simpleKarma->getParentTable() . '", "imgpathup" : "'.$imgpathup.'", "imgpathdown" : "'.$imgpathdown.'", "imgpathflag" : "'.$imgpathflag.'"}';
?>
