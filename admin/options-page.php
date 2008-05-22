<?php
$simpleKarma = new SimpleKarma('');
function bbpress_options()
{
	if (function_exists('add_options_page'))
	{
		//Add a new submenu under options
		add_options_page("Simple Karma", "bbPress Options", 7, "simplekarmaconfig", "bbpress_optionspage");
	}
}

function bbpress_optionspage()
{
	global $simpleKarma;
	?>
	<div class="wrap">
	<h2><?php _e('Simple Karma for bbpress'); ?></h2>
	<form method="post" action="options.php">
		<?php wp_nonce_field('update-options'); ?>
		<p class="submit">
			<input type="submit" name="Submit" value="<?php _e('Update Options &raquo;') ?>" />
		</p>
		<p><?php _e('Configuration options for Simple Karma integrated into bbPress'); ?></p>
		<h3>Table Name:</h3>
			<input type="text" name="bbpress_table" size="15" value="<?php echo $simpleKarma->getTableOption(); ?>" />
			<p>Please write the name of the table, not including the prefix (i.e. posts) for the posts table.
			<br/>
		</p>
		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="bbpress_table" />
		<p class="submit">
			<input type="submit" name="Submit" value="<?php _e('Update Options &raquo;') ?>" />
		</p>
	</form>
</div>
<?php
}
?>