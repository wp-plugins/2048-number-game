<?php
defined( 'ABSPATH' ) OR exit;

class WP2048_Widget_Highscore extends WP_Widget 
{

	public function __construct()
    {
		$widget_ops = array( 'classname' => 'wp2048', 'description' => __('Displays site high score and linked to the game page.', 'wp2048') );
		$control_ops = array( 'id_base' => 'wp2048-hs-widget' );
		$this->WP_Widget( 'wp2048-hs-widget', __('2048 High Score', 'wp2048'), $widget_ops, $control_ops );
	}

	public function widget( $args, $instance ) {
		// Widget output
		extract( $args );

		//Our variables from the widget settings.
		$title = apply_filters('widget_title', $instance['title'] );
		$show = $instance['show'];
		$pageid = $instance['pageid'];
		
		// Enqueue the CSS when this Widget appear
		wp_enqueue_style('2048_widget');
		
		echo $before_widget;
		
		// Display the widget title 
		if ( $title )
			echo $before_title . $title . $after_title;

		// Output the High Score or Scoreboard or None
		if ( $show == 'highscore' ) {
			$wp2048_highscore = json_decode( get_option('wp2048_highscore'), true );
			echo '<div class="highscore">'.$wp2048_highscore['score'].'</div>';
		} else if ( $show == 'scoreboard' ) {
			echo do_shortcode('[scoreboard2048 top=5 link=none]');
		}
		
		echo '<p>Join the numbers and get to the <strong>2048 tile!</strong></p>';
		echo '<a href="'.get_permalink($pageid).'" class="play-button">Play Game</a>';
		
		echo $after_widget;
	}

	public function update( $new_instance, $old_instance ) {
		// Save widget options
		$instance = $old_instance;

		//Strip tags from title and name to remove HTML 
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['show'] = strip_tags( $new_instance['show'] );
		$instance['pageid'] = (int)$new_instance['pageid'];

		return $instance;
	}

	public function form( $instance ) {
		// Output admin widget options form
		// Set up some default widget settings.
		$defaults = array(
			'title' => __('2048 High Score', 'wp2048'),
			'show' => 'highscore',
			'pageid' => 0,
			);
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'wp2048'); ?></label>
			<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('show'); ?>"><?php _e('Show:', 'wp2048'); ?></label><br/>
			<input type="radio" name="<?php echo $this->get_field_name('show'); ?>" value="none" <?php if( $instance['show'] == 'none') echo 'checked'; ?>><?php _e('None', 'wp2048'); ?><br />
			<input type="radio" name="<?php echo $this->get_field_name('show'); ?>" value="highscore" <?php if( $instance['show'] == 'highscore') echo 'checked'; ?>><?php _e('Highest Score', 'wp2048'); ?><br />
			<input type="radio" name="<?php echo $this->get_field_name('show'); ?>" value="scoreboard" <?php if( $instance['show'] == 'scoreboard') echo 'checked'; ?>><?php _e('Top 5 Scores', 'wp2048'); ?><br />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('pageid'); ?>"><?php _e('Game Page:', 'wp2048'); ?></label>
			<select id="<?php echo $this->get_field_id('pageid'); ?>" name="<?php echo $this->get_field_name('pageid'); ?>" class="widefat">
			<?php
				$selected = (int)$instance['pageid'];
				$allpages = new WP_Query( array( 'post_type' => 'page', 'orderby' => 'title', 'posts_per_page' => -1 ) );
				if ( $allpages->have_posts() ) :
				while ( $allpages->have_posts() ) : $allpages->the_post();
				if ( get_the_ID() == $selected ) {
					echo "<option value='".get_the_ID()."' selected='selected'>".get_the_title()."</option>";
				} else {
					echo "<option value='".get_the_ID()."'>".get_the_title()."</option>";
				}
				endwhile;
				wp_reset_postdata();
				endif;
			?>
			</select>
		</p>
	<?php
	}
}

function wp2048_widgets() {
	register_widget( 'WP2048_Widget_Highscore' );
}
add_action( 'widgets_init', 'wp2048_widgets' );
