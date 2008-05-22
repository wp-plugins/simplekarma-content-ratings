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
require_once(ABSPATH . 'wp-content/plugins/simple-karma/classes/classes.php');
$simpleKarma = new SimpleKarma('');

function SimpleKarma_options()
{
	if (function_exists('add_options_page'))
	{
		//Add a new submenu under options
		add_options_page("Simple Karma", "Simple Karma Options", 7, "simplekarmaconfig", "SimpleKarma_optionspage");
		// Add a new submenu under Manage
		add_management_page('Manage Simple Karma', 'Simple Karma', 7, 'testmanage', 'SimpleKarma_ManagmentPage');
    }
}

function SimpleKarma_optionspage()
{
	global $simpleKarma;
	if( isset($_POST['action']) && $_POST['action']=='update') 
	{
		$threshold = $_POST['threshold'];
		$threshold_message = $_POST['threshold_message'];
		$bbpress_table = $_POST['bbpress_table'];
		$simpleKarma->updateOptions($threshold, $threshold_message, $bbpress_table);
		
	
	?>
	<div id="message" class="updated fade"><p><strong><?php _e('Options saved.') ?></strong></p></div>
	<?php
	}
	?>
	<div class="wrap">
	<h2><?php _e('Simple Karma '); ?></h2>
	<form method="post" action="options-general.php?page=simplekarmaconfig">
		<?php wp_nonce_field('update-options'); ?>
		<p class="submit">
			<input type="submit" name="Submit" value="<?php _e('Update Options &raquo;'); $updated=true; ?>" />
		</p>
		<p><?php _e('Configuration options for Simple Karma.'); ?></p>
		<h3>Karma Limit</h3>
		<p>When the rating of an object on the site goes lower than:
			<input type="text" name="threshold" size="2" value="<?php echo $simpleKarma->getThresholdOption(); ?>" />
			the object will be flagged.
			<br/>
			<b>Warning:</b>
			You must enter a negative number. If you enter a positive number it will be converted to negative.
		</p>
		<h3>Over Threshold Message:</h3>
		<p>
			<input type="text" name="threshold_message" size="45" value="<?php echo $simpleKarma->getMessageOption(); ?>" /><br />
			Please enter the message you would like to display when objects have tripped the threshold.
			<br/>
		</p>
		<h3>bbpress Table Name:</h3>
		<p>
			<input type="text" name="bbpress_table" size="15" value="<?php echo $simpleKarma->getTableOption(); ?>" /> <br />
			Please enter the name of your bbpress post table.
			<br/>
					
		</p>
		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="threshold,threshold_message,bbpress_table" />
		<p class="submit">
			<input type="submit" name="Submit" value="<?php _e('Update Options &raquo;'); ?>" />
		</p>
	</form>
</div>

<?php 
}

function SimpleKarma_ManagmentPage()
{
	$csps_SimpleKarma = new SimpleKarma($_GET["prefix"]);
	if( isset($_GET['prefix']))
	{
		if( isset($_GET['action']))
		{
			switch($_GET['action'])
			{
				case 'deletecomment':
				$csps_SimpleKarma->deleteComment($_GET['id']);
				?>
				<div id="message" class="updated fade"><p>
				<?php _e('Object<strong> deleted</strong>.')?>
				</p></div>
				<?php
				break;
			}
		}
		?>
		<div class="wrap">
		<h2><?php _e('Simple Karma Management'); ?></h2>
		<br/><br/>
		<a href="/edit.php?page=testmanage">Back&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</a>
		<?php
		
		if($_GET['type'] == 'neg')
		{
			echo "<a href=\"./edit.php?page=testmanage&type=pos&prefix=" . $_GET['prefix'] . "\">Sort Ascending</a>";
		}
		else
		{
			echo "<a href=\"./edit.php?page=testmanage&type=neg&prefix=" . $_GET['prefix'] . "\">Sort Descending</a>";
		}
		?>
		<br/><hr/>
		<?php
		if( $_GET['type'] == 'neg' )
		{
		   
			$results = $csps_SimpleKarma->getHighRateObjects();
		    foreach ($results as $result)
		    {
				echo $result->text.'<br/>';
				echo($csps_SimpleKarma->getAdminKarmaWidget($result->id));
				echo "<a href=\"./edit.php?page=testmanage&action=deletecomment&id=$result->id&prefix=" . $_GET['prefix'] . "\">Delete Object</a>";
				if($result->postID!='')
				{	
					if($_GET["prefix"] == $csps_SimpleKarma->getTableOption())
					{
						echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<a href='" . get_bloginfo('wpurl') . "/forum/topic/$result->topic_id'> Go to Object On Site </a>";
					}
					else
					{
						echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<a href='" . get_bloginfo('wpurl') . "/?p=$result->postID#$result->id'> Go to Object On Site </a>";
					}
				}
				else
				{
					if($_GET["prefix"] == $csps_SimpleKarma->getTableOption())
					{
						echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<a href='" . get_bloginfo('wpurl') . "/forum/topic/$result->topic_id'> Go to Object On Site </a>";
					}
					else
					{
						echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<a href='" . get_bloginfo('wpurl') . "/?p=$result->id'> Go to Object On Site </a>";
					}
				}	
				echo '<hr/>';
		    }
		}
		
		else
		{
			$results = $csps_SimpleKarma-> getFlaggedObjects();
		    foreach ($results as $count => $result)
		    {
				echo $result->text.'<br/>';
				echo($csps_SimpleKarma->getAdminKarmaWidget($result->id));
				echo "<a href=\"./edit.php?page=testmanage&action=deletecomment&id=$result->id&prefix=" . $_GET['prefix'] . "\">Delete Object</a>";
				if($result->postID!='')
				{
					if($_GET["prefix"] == $csps_SimpleKarma->getTableOption())
					{
						echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<a href='" . get_bloginfo('wpurl') . "/forum/topic/$result->topic_id'> Go to Object On Site </a>";
					}
					else
					{
						echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<a href='" . get_bloginfo('wpurl') . "/?p=$result->postID#$result->id'> Go to Object On Site </a>";
					}
				}
				else
				{
					if($_GET["prefix"] == $csps_SimpleKarma->getTableOption())
					{
						echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<a href='" . get_bloginfo('wpurl') . "/forum/topic/$result->topic_id'> Go to Object On Site </a>";
					}
					else
					{
						echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<a href='" . get_bloginfo('wpurl') . "/?p=$result->id'> Go to Object On Site </a>";
					}
				}	
				
				echo '<hr/>';
		    }
		}
		echo "<a href=\"./edit.php?page=testmanage\">Back</a>";
		echo "</p></form></div>";
	}
	else
	{
		?>
		<div class="wrap">
		<h2><?php _e('Simple Karma Management'); ?></h2>
		<h4>Select the database you would like to manage</h4>
		<br/>
		<?php 
		$csps_SimpleKarma = new SimpleKarma('manage');
		$results=$csps_SimpleKarma->getForeignTables(); 
		foreach ($results as $result)
		{
			echo "<a href=\"./edit.php?page=testmanage&prefix=" . $result->foreign_table . "\">" . $result->foreign_table . "</a><br>" ;
		}
		?>
		</p></form></div>
		<?php
	}
}
?>