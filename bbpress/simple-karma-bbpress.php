<?php

/*
Plugin Name: SimpleKarmaBBPRESS
Description:  allows members to report a post to admin/moderators
Plugin URI:  http://bbpress.org/plugins/topic/64
Author: _ck_
Author URI: http://CKon.wordpress.com
Version: 0.11
*/


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
if(!class_exists("SimpleKarma"))
{
	class SimpleKarma
	{
		function SimpleKarma($prefix, $db="")
		{
			global $bbdb, $table_prefix;

		    if($db != "")
			{
			    $this->db = $db;
			}
			else
			{
			    $this->db = $bbdb;
			}
            $this->table = $prefix;

			$this->sk = 'wp_1_simple_karma';
		}

		function modifyKarma($id,$value)
		{
			$query = "SELECT karma FROM $this->sk where foreign_table='$this->table' and object_id='$id';";
			$result = $this->db->get_results($query);
			$row = $result[0];
			if(!$result)
			{
				$this->db->query("INSERT INTO $this->sk (object_id, foreign_table, karma) VALUES ( '$id', '".$this->table."', '$value');");
				$karma = $value;
			}

			else
			{
				$karma = $row->karma;
				$karma = $karma + $value;
				$query = "UPDATE $this->sk SET karma = $karma where foreign_table='$this->table' and object_id='$id';";
				$result = $this->db->query($query);
			}

			$bool = $this->hasPassedThreshold($id);
			if($bool==true)
			{
				$this->emailAdmin($id);
			}
			return $karma;
		}

		function getKarmaWidget($id)
		{
			$plugin_path = bb_get_plugin_uri() . '../../wp-content/plugins/simple-karma/';
			$ck_link = str_replace('http://', '', bb_get_plugin_uri().'../..');

			$query="SELECT karma FROM $this->sk where foreign_table='$this->table' and object_id='$id';";
			$result=$this->db->get_results($query);
			$row = $result[0];
			if(!$result)
			{
				$value = 0;
				$karma = '0';
			}

			else
			{
				$row = $result[0];
				$value = $row->karma;
				$karma = $value;
			}

			//Simple Karma widget
			echo "<!-- SimpleKarma Widget Version 0.1 -->";


			echo "<div class='simplekarma-widget'>";

			if (isset($_COOKIE['simple-karma-' ."wp_1_bb_posts"]))
			{
				$results=split("-", $_COOKIE['simple-karma-'."wp_1_bb_posts"]);
				foreach ($results as $result)
				{
					if($result==$id)
					{
						echo ("<img id=\"up-$id\" style=\"cursor: pointer;\" src=\"{$plugin_path}images/gray_up.gif\" alt=\"Add karma\">&nbsp");
						echo("<img id=\"down-$id\" style=\"cursor: pointer;\" src=\"{$plugin_path}images/gray_down.gif\" alt=\"Subtract karma\"> ");
						$count ++;
					}
				}
			}
			if($count==0)
			{
				echo ("<div style='display:inline;' id='up-img-$id' > <img id=\"up-$id\" style=\"cursor: pointer;\" src=\"{$plugin_path}images/up.gif\" alt=\"Add karma\" onclick=\"javascript:modifyKarma('$id',1, 'add', '{$ck_link}/wp-content/plugins/simple-karma/', '".$this->table."');\" />&nbsp;</div>");
				echo("<div style='display:inline;' id='down-img-$id' ><img id=\"down-$id\" style=\"cursor: pointer;\" src=\"{$plugin_path}images/down.gif\" alt=\"Subtract karma\" onclick=\"javascript:modifyKarma('$id',1, 'subtract', '{$ck_link}/wp-content/plugins/simple-karma/', '".$this->table."')\" /></div>");
			}
			echo "&nbsp;&nbsp;<small id=\"karma-{$id}\">{$karma}</small>";
			echo "</div>";
			echo "<!-- End SimpleKarma Widget -->";
			return $karma;
		}

		function getForeignTables()
		{
			$query="SELECT distinct foreign_table FROM $this->sk";
			$result=$this->db->get_results($query);

			for ($i=0; $i<sizeof($result); $i++)
			{
				$row = $result[$i];
				$table = $row->foreign_table;
				echo "<a href=\"./edit.php?page=testmanage&prefix=$table\">$table</a>" ;
				echo "<br>";
			}
			return $result;
		}

		function isAboveThreshold($id, $flagged, $notFlagged)
		{
			$query="SELECT karma FROM wp_simple_karma where object_id='$id' and foreign_table='$this->table';";
			$result=$this->db->get_results($query);
			$karma = $result[0]->karma;
			$threshold = get_option('threshold');
			if (!$result)
			{
				return "Karma does not exsist";
			}
			else
			{
				if ($karma <= $threshold)
				{
					return $flagged;
				}
				else if ($karma > $threshold)
				{
					return $notFlagged;
				}
			}


		}

		function getHighRateObjects()
		{
                    echo '<h1>'.$this->getPrefix().'</h1>';
			switch($this->getPrefix())
			{
				case $this->db->prefix.'comments':
					$query="SELECT comment_ID as id, comment_content as text, comment_post_ID as postID, karma from ".$this->getPrefix().", $this->sk where foreign_table=\"".$this->table."\" and ".$this->getPrefix().".comment_ID=$this->sk.object_id order by karma desc limit 50";
					break;

				case $this->db->prefix.'simple_comments':
					$query="SELECT ".$this->db->prefix."simple_comments.id as id, comment as text, karma from ".$this->getPrefix().", $this->sk where foreign_table=\"".$this->table."\" and ".$this->getPrefix().".id=$this->sk.object_id order by karma desc limit 50";
					break;

				case $this->db->prefix.'posts':
					$query="SELECT ".$this->db->prefix."posts.ID as id, post_content as text, karma from " .$this->getPrefix(). ", $this->sk where foreign_table=\"".$this->table."\" and ".$this->getPrefix().".ID=$this->sk.object_id order by karma desc limit 50";
					break;

				case 'bb_posts':
					$query="SELECT bb_posts.post_id as id, post_text as text, karma from " .$this->getPrefix(). ", $this->sk where foreign_table=\"".$this->table."\" and ".$this->getPrefix().".post_id=$this->sk.object_id order by karma desc limit 50";
					break;

				default:
					$query="SELECT id as id, text as text, karma from ".$this->getPrefix().", $this->sk where foreign_table=\"".$this->table."\" and ".$this->getPrefix().".id=$this->sk.object_id and order by karma desc limit 50";
					break;
			}
			return $this->db->get_results($query);
		}

		function getFlaggedObjects()
		{
			$threshold = get_option('threshold');
			if ($threshold > 0)
			{
				$threshold = 0 - $threshold;
			}
			if ($threshold == 0)
			{
				$threshold = -1;
			}
			switch($this->getPrefix())
			{
				case $this->db->prefix.'comments':
					$query="SELECT comment_ID as id, comment_content as text, comment_post_ID as postID, karma from ".$this->getPrefix().", $this->sk where foreign_table=\"".$this->table."\" and ".$this->getPrefix().".comment_ID=$this->sk.object_id and karma<=$threshold order by karma asc";
					break;

				case $this->db->prefix.'simple_comments':
					$query="SELECT ".$this->db->prefix."simple_comments.id as id, comment as text, karma from ".$this->getPrefix().", $this->sk where foreign_table=\"".$this->table."\" and ".$this->getPrefix().".id=$this->sk.object_id and karma<=$threshold order by karma asc";
					break;

				case $this->db->prefix.'posts':
					$query="SELECT ".$this->db->prefix."posts.ID as id, post_content as text, karma from " .$this->getPrefix(). ", $this->sk where foreign_table=\"".$this->table."\" and ".$this->getPrefix().".ID=$this->sk.object_id and karma<=$threshold order by karma asc;";
					break;

				case 'bb_posts':
					$query="SELECT bb_posts.post_id as id, post_text as text, karma from " .$this->getPrefix(). ", $this->sk where foreign_table=\"".$this->table."\" and ".$this->getPrefix().".post_id=$this->sk.object_id and karma<=$threshold order by karma asc;";
					break;

				default:
					$query="SELECT id as id, text as text, karma from ".$this->getPrefix().", $this->sk where foreign_table=\"".$this->table."\" and ".$this->getPrefix().".id=$this->sk.object_id and karma<=$threshold order by karma asc";
					break;
			}
			return $this->db->get_results($query);
		}

		function deleteComment($id)
		{
			switch($this->getPrefix())
			{
				case $this->db->prefix.'comments':
					wp_delete_comment($id);
					break;

				case $this->db->prefix.'simple_comments':
					$query1="delete FROM wp_simple_comments where id=$id";
					break;

				case $this->db->prefix.'posts':
					wp_delete_post($id);
					break;

				case 'bb_posts':
					$query1="delete FROM bb_posts where post_id=$id";
					break;

				default:
					$query1="delete FROM" . $this->getPrefix() . "where id=$id";
					break;
			}

			$query="delete FROM $this->sk where object_id=$id and foreign_table=\"" . $this->table . "\"";
			$this->db->query($query);
			$this->db->query($query1);
		}

		function emailAdmin($id)
		{
			wp_notify_moderator($id);
		}

		function hasPassedThreshold($id)
		{
			$query="SELECT karma FROM $this->sk where foreign_table= ".$this->table." and object_id='$id';";
			$result=$this->db->get_results($query);
			$threshold = get_option('threshold');
			if ($threshold > 0)
			{
				$threshold = 0 - $threshold;
			}
			if ($threshold == 0)
			{
				$threshold = -1;
			}

			if (!result)
			{
				if(0 == $threshold)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			else
			{
				$row = $result[0];
				$karma = $row->karma;

				if ($karma == $threshold)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}
	}
}

?>
