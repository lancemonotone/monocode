<?php
//	Defaults
$instance = wp_parse_args( 
	(array) $instance, array( 
		'title' 	=> __('The Multi Widget'),
		'number' 	=> '6',
		'infinite'	=> 'n'
	) 
);
$title = strip_tags($instance['title']);
$number = intval(strip_tags($instance['number']));
$infinite = strip_tags($instance['infinite']);
?>

<?php // text ?>
<p>
	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Module Title:' ); ?></label><br>
	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title?>"/>
</p>

<?php // select ?>
<p>
	<label for="<?php echo $this->get_field_id('number'); ?>"><?php _e( 'Number of posts:' ); ?></label>
	<select class=""  name="<?php echo $this->get_field_name('number'); ?>" id="<?php echo $this->get_field_id('number'); ?>">
		<option value="" <?php selected( $instance['number'],  "", true); ?>>No Ad</option>
		<?php for($i = 1; $i < 21; $i++){?>
		<option value="<?php echo $i?>" <?php selected( $instance['number'],  $i, true); ?>><?php echo $i?></option>
		<?php } ?>
	</select>
</p>

<?php // checkbox ?>
<p>
	<input id="<?php echo $this->get_field_id('infinite'); ?>" name="<?php echo $this->get_field_name('infinite'); ?>" type="checkbox" value="y" <?php checked( 'y', $instance['infinite'] ); ?> />
	<label for="<?php echo $this->get_field_id('infinite'); ?>"><?php _e('Show issue link?') ?></label>
</p>