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
if(!class_exists("SimpleKarma"))
{
	class SimpleKarma
	{
		protected $db;
		
		function SimpleKarma($parent_table = "", $db = "")
		{
			global $wpdb;
            // Set this equal to your bbPress posts table name
            if($db != "")
			{
			    $this->db = $db;
			}
			else
			{
			    $this->db = $wpdb;
			}
			
			
			
			$this->threshold = $this->getThreshold();
			$this->bbpress_posts = $this->getTableOption();
			
			if($parent_table != "")
			{
			    $this->table = $parent_table;
			}
			else
			{
			    $this->table = 'comments';
			}
			
			if($parent_table == $this->bbpress_posts)
			{
			    $this->prefixed_table = $this->bbpress_posts;
			}
			else
			{
			    $this->prefixed_table = $this->db->prefix . $parent_table;
			}

			$this->skt = $this->db->prefix . 'simple_karma';
			$this->skot = $this->db->prefix . 'simple_karma_options';
		}
		
		function getParentTable()
		{
			return $this->prefixed_table;
		}
		
		function getKarma($id)
		{
			$query = "select karma from $this->skt where foreign_table='$this->table' and object_id='$id';"; 
			$result = $this->db->get_results($query);
			$row = $result[0];
			$karma = $row->karma;
			if($karma == null)
			{
				return 0;
			}
			return $karma;
		}
		
		function setKarma($id, $karma)
		{
			$query = "UPDATE $this->skt SET karma = $karma where foreign_table='$this->table' and object_id='$id';";
			$result = $this->db->query($query); 
		}
		
		function recordExists($id)
		{
			$query = "select * from $this->skt where foreign_table='$this->table' and object_id='$id';"; 
			$result = $this->db->get_results($query);
			$row = $result[0];
			if ($row == null)
			{
				return false;
			}
			return true;
			
		}
				
		function modifyKarma($id, $value)
		{
			$karma = $this->getKarma($id);					
			if($this->recordExists($id) == false)
			{
				$this->db->query("INSERT INTO $this->skt (object_id, foreign_table, karma) VALUES ('$id', '".$this->table."', '$value');");
				$karma = $value;
			}
			else
			{
				$karma = $karma + $value;
				$this->setKarma($id, $karma);
			}

			if($this->isFlagged($id))
			{
				$this->emailAdmin($id);
			}
			return $karma;
		}
		
		function getKarmaWidget($id)
		{
			$plugin_path = get_bloginfo('url') . '/wp-content/plugins/simple-karma/';
			$ck_link = str_replace('http://', '', get_bloginfo('url'));
			$karma = $this->getKarma($id);					
			//Simple Karma Widget Begin
			$widget = "<!-- SimpleKarma Widget Version 0.1 -->";
			$widget .= "<div class='simplekarma-widget'>";
			if(isset($_COOKIE['simple-karma-' . $this->prefixed_table]))
			{	
				$results = split("-", $_COOKIE['simple-karma-' . $this->prefixed_table]);
				foreach ($results as $result)
				{
					if($result == $id)
					{
						$widget .= ("<img id=\"up-$id\" style=\"cursor: pointer;\" width=16 height=14 src=\"{$plugin_path}images/gray_up.gif\" alt=\"Add karma\">&nbsp");
						$widget .= ("<img id=\"down-$id\" style=\"cursor: pointer;\" width=16 height=14 src=\"{$plugin_path}images/gray_down.gif\" alt=\"Subtract karma\"> "); 			
						$count ++;
					}
				}
			}
			if($count == 0)
			{
				$widget .= ("<img id=\"up-$id\" style=\"cursor: pointer;\" src=\"{$plugin_path}images/up.gif\" width=16 height=14 alt=\"Add karma\" onclick=\"javascript:modifyKarma('$id',1, 'add', '{$ck_link}/wp-content/plugins/simple-karma/', '".$this->table."');\" />&nbsp;");
				$widget .= ("<img id=\"down-$id\" style=\"cursor: pointer;\" src=\"{$plugin_path}images/down.gif\" width=16 height=14 alt=\"Subtract karma\" onclick=\"javascript:modifyKarma('$id',1, 'subtract', '{$ck_link}/wp-content/plugins/simple-karma/', '".$this->table."')\" />"); 			
			}
			$widget .= "&nbsp;&nbsp;<small id=\"karma-{$id}\">{$karma}</small>";
			$widget .= "<div class='no-simple-karma-message' id='no-simple-karma-message-$id'></div>";
			$widget .= "</div>";
			$widget .= "<!-- End SimpleKarma Widget -->";
			//Simple Karma Widget End
			return $widget;
		}
		
		function getAdminKarmaWidget($id)
		{
			$plugin_path = get_bloginfo('url') . '/wp-content/plugins/simple-karma/';
			$ck_link = str_replace('http://', '', get_bloginfo('url'));
			$karma = $this->getKarma($id);				
			//Simple Karma Admin Widget Begins
			$widget .= "<!-- SimpleKarma Admin Widget Version 0.1 -->";
			$widget .= "<div class='simplekarma-widget'>";
			$widget .= ("<img id=\"up-$id\" style=\"cursor: pointer;\" src=\"{$plugin_path}images/up.gif\"  width=16 height=14 alt=\"Add karma\" onclick=\"javascript:modifyKarma('$id',1, 'add', '{$ck_link}/wp-content/plugins/simple-karma/', '".$this->table."', modifyKarmaCallBackAdmin);\" />&nbsp;");
			$widget .= ("<img id=\"down-$id\" style=\"cursor: pointer;\" src=\"{$plugin_path}images/down.gif\" width=16 height=14 alt=\"Subtract karma\" onclick=\"javascript:modifyKarma('$id',1, 'subtract', '{$ck_link}/wp-content/plugins/simple-karma/', '".$this->table."', modifyKarmaCallBackAdmin)\" />"); 			
			$widget .= "&nbsp;&nbsp;<small id=\"karma-{$id}\">{$karma}</small>";
			$widget .= "</div>";
			$widget .= "<!-- End SimpleKarma Admin Widget -->";
			//Simple Karma Admin Widget Ends
			return $widget;
		}
		
		function getAbuseWidget($id)
		{
			$plugin_path = get_bloginfo('url') . '/wp-content/plugins/simple-karma/';
			$ck_link = str_replace('http://', '', get_bloginfo('url'));
			$karma = $this->getKarma($id);
			//Simple Karma Abuse Widget Begins
			$widget = "<!-- SimpleKarma Abuse Widget Version 0.1 -->";
			$widget .= "<div class='simplekarma-widget'>";
			if(isset($_COOKIE['simple-karma-' . $this->prefixed_table]))
			{	
				$results = split("-", $_COOKIE['simple-karma-' . $this->prefixed_table]);
				foreach($results as $result)
				{
					if($result == $id)
					{
						$widget .= ("<img id=\"up-$id\" style=\"cursor: pointer;\" src=\"{$plugin_path}images/inappropriate-off-icon.gif\" width=16 height=14 alt=\"Add karma\">&nbsp");
						$count ++;
					}
				}
			}
			if($count==0)
			{
				$widget .=("<img id=\"down-$id\" style=\"cursor: pointer;\" src=\"{$plugin_path}images/inappropriate-active-icon.gif\" width=16 height=14 title='Flag this post as inappropriate' onclick=\"javascript:modifyKarma('$id',1, 'subtract', '{$ck_link}/wp-content/plugins/simple-karma/', '".$this->table."', modifyAbuseCallBack)\" />"); 			
			}
			$widget .= "&nbsp;&nbsp;<small id=\"karma-{$id}\">{$karma}</small>";
			$widget .= "<div class='simple-karma-message' id='simple-karma-message-$id'> Flag this post as inappropriate </div>";
			$widget .= "</div>";
			$widget .= "<!-- End SimpleKarma Abuse Widget -->";
			return $widget;
		}
		
		function updateOptions($threshold, $message, $table)
		{
			$insert = "update $this->skot set option_value=$threshold where option_name='threshold'";
			$this->db->query($insert);
			$insert = "update $this->skot set option_value='$message' where option_name='threshold_message'";
			$this->db->query($insert);
			$insert = "update $this->skot set option_value='$table' where option_name='bbpress_table'";
			$this->db->query($insert);			
		}
		
		function getThresholdOption()
		{
			$query = "select option_value from $this->skot where option_name='threshold'";
			$result = $this->db->get_var($query);
			return $result;
		}
		
		function getMessageOption()
		{
			$query = "select option_value from $this->skot where option_name='threshold_message'";
			$result = $this->db->get_var($query);
			return $result;	
		}
		
		function getTableOption()
		{
			$query = "select option_value from $this->skot where option_name='bbpress_table'";
			$result = $this->db->get_var($query);
			return $result;		
		}
		
		function getForeignTables()
		{	
			$query = "SELECT distinct foreign_table FROM $this->skt"; 
			$results = $this->db->get_results($query);
			return $results;
		}
		
		function getCssClass($id, $flagged, $notFlagged)
		{	
			if ($this->isFlagged($id))
			{
				return $flagged;
			}
			return $notFlagged;
		}
		
		function getHighRateObjects()
		{
           	switch($this->getParentTable())
			{
				case $this->db->prefix . 'comments':
					$query = "SELECT comment_ID as id, comment_content as text, comment_post_ID as postID, karma from " . $this->getParentTable() . ", $this->skt where foreign_table=\"" . $this->table . "\" and " . $this->getParentTable() . ".comment_ID=$this->skt.object_id order by karma desc limit 50";
					break;
				
				case $this->db->prefix . 'simple_comments':
					$query = "SELECT " . $this->db->prefix . "simple_comments.id as id, comment as text, karma from " . $this->getParentTable() . ", $this->skt where foreign_table=\"" . $this->table . "\" and " . $this->getParentTable() . ".id=$this->skt.object_id order by karma desc limit 50";
					break;
				
				case $this->db->prefix . 'posts':
					$query = "SELECT " . $this->db->prefix . "posts.ID as id, post_content as text, karma from " . $this->getParentTable() . ", $this->skt where foreign_table=\"" . $this->table . "\" and " . $this->getParentTable() . ".ID=$this->skt.object_id order by karma desc limit 50";
					break;
				
				case $this->bbpress_posts:
					$query = "SELECT $this->bbpress_posts.post_id as id, post_text as text,topic_id,post_position,post_status, karma from " . $this->getParentTable() . ", $this->skt where post_status='0' and foreign_table=\"" . $this->table . "\" and " . $this->getParentTable() . ".post_id=$this->skt.object_id order by karma desc limit 50";
					break;
				
				default:
					$query = "SELECT id as id, text as text, karma from " . $this->getParentTable() . ", $this->skt where foreign_table=\"" . $this->table . "\" and " . $this->getParentTable() . ".id=$this->skt.object_id and order by karma desc limit 50";
					break;
			}
			return $this->db->get_results($query);
		}
		
		function getFlaggedObjects()
		{
			switch($this->getParentTable())
			{
				case $this->db->prefix . 'comments':
					$query = "SELECT comment_ID as id, comment_content as text, comment_post_ID as postID, karma from ".$this->getParentTable().", $this->skt where foreign_table=\"" . $this->table . "\" and " . $this->getParentTable() . ".comment_ID=$this->skt.object_id order by karma asc limit 50";
					break;
				
				case $this->db->prefix . 'simple_comments':
					$query = "SELECT " . $this->db->prefix . "simple_comments.id as id, comment as text, karma from ".$this->getParentTable().", $this->skt where foreign_table=\"" . $this->table . "\" and " . $this->getParentTable() . ".id=$this->skt.object_id order by karma asc limit 50";
					break;
				
				case $this->db->prefix . 'posts':
					$query = "SELECT " . $this->db->prefix . "posts.ID as id, post_content as text, karma from " . $this->getParentTable()  . ", $this->skt where foreign_table=\"" . $this->table . "\" and " . $this->getParentTable() . ".ID=$this->skt.object_id order by karma asc limit 50";
					break;
				
				case $this->bbpress_posts:
					$query = "SELECT $this->bbpress_posts.post_id as id, post_text as text,topic_id,post_position,post_status, karma from " . $this->getParentTable(). ", $this->skt where post_status='0' and foreign_table=\"" . $this->table . "\" and " . $this->getParentTable() . ".post_id=$this->skt.object_id order by karma asc  limit 50;";
					break;
				
				default:
					$query = "SELECT id as id, text as text, karma from " . $this->getParentTable() . ", $this->skt where foreign_table=\"".$this->table."\" and " . $this->getParentTable() . ".id=$this->skt.object_id order by karma asc limit 50";
					break;
			}
			return $this->db->get_results($query);
		}
		
		function deleteComment($id)
		{
			switch($this->getParentTable())
			{
				case $this->db->prefix . 'comments':
					wp_delete_comment($id);
					break;
				
				case $this->db->prefix . 'simple_comments':
					$query1 = "delete from wp_simple_comments where id=$id";
					break;
				
				case $this->db->prefix . 'posts':
					wp_delete_post($id);
					break;
				
				case $this->bbpress_posts:
					$query1 = "delete from ".$this->bbpress_posts." where post_id=$id";
					break;
				
				default:
					$query1 = "delete from " . $this->getParentTable() . " where id=$id";
					break;
			}
			$query = "delete from $this->skt where object_id=$id and foreign_table=\"" . $this->table . "\"";  
			$this->db->query($query);
			$this->db->query($query1);
			return $query1;
		}
		
		function emailAdmin($id)
		{
			//doesn't seem to work 
			wp_notify_moderator($id);
		}

        function getThreshold()
        {
            $threshold = $this->getThresholdOption();
			
			if($threshold > 0)
			{
				$threshold = 0 - $threshold;
			}
			if($threshold == 0)
			{
				$threshold = -1;
			}
            return $threshold;
        }

		function isFlagged($id)
		{	
			return $this->getKarma($id) <= $this->threshold;
		}

		function isAtThreshold($id)
		{
            return $this->threshold == $this->getKarma($id);
		}
		
	}
}
?>
