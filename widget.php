<?php

/**
 * Adds Civi_Prof_Messages widget.
 */
class Civi_Prof_Messages extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'Civi_Prof_Messages', // Base ID
			'Civi_Prof_Messages', // Name
			array( 'description' => __( 'Civi Profile Messages', 'text_domain' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
	//Display only on logged in
	
	if ( is_user_logged_in() ) {
		echo $args['before_widget'];
		//Run Logic and macro
			$iset = 0;  //Initialize index for messages
			$current_user = wp_get_current_user();
			$user_photo = get_user_meta( $current_user->ID, "civiprof_photoset",false );
			//Discover if member image has been set
				if($user_photo == false) {
					$message_text[$iset] = "Membership Photo Not Yet Set";
					$message_link[$iset] = WP_SITEURL . "/members/" . $current_user->user_login ."/profile/membphoto/";
					$iset++;
				}
		//Display Widget
		if(count($message_text) !== 0 || messages_get_unread_count() > 0) {
			if ( ! empty( $title ) )
				echo $args['before_title'] . $title . $args['after_title'];
			//Loop thorough messages
				if(count($message_text) !== 0) {
				echo '<div id="civiprof-messages-header">Tasks</div>';
				echo '<div id="civiprof-messages">';
				echo "<ul>";
				for($i=0;$i<count($message_link);$i++){
					echo '<li class="civiprof-message"><a href="' . $message_link[$i] . '">' . $message_text[$i] . '</a>';
				}
				echo "</ul>";
				echo "</div>";
				}
				//Buddypress Messages
				if(bp_has_message_threads()) {
					$firstrow = true;
					$endrow = false;
					while ( bp_message_threads() ){
						bp_message_thread();
						if(bp_message_thread_has_unread()) {
							if ($firstrow == true) {
							echo '<div class="civiprof-bp-messages-header">User Inbox</div>';
							echo "<ul>";
							$firstrow = false;
							$endrow = true;
							} 
							
							echo '<li class="civiprof-message"><a href="';
							bp_message_thread_view_link();
							echo '" class="bpcivimessage">';
							bp_message_thread_subject();
							echo '</a></li>';
						}
					}
					if ($endrow == true) {
					echo "</ul>";
					}
				}
				
				//Diagnostics
				
				
				
				//echo "bp_message_threads<pre>";	
				//print_r();
				//echo "</pre>";
			
			
			echo $args['after_widget'];
		}
	}
		
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'New title', 'text_domain' );
		}
		?>
		<p>
		<label for="<?php echo $this->get_field_name( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

} // class Civi_Prof_Messages

function register_civiprof_messages() {
    register_widget( 'Civi_Prof_Messages' );
}
add_action( 'widgets_init', 'register_civiprof_messages' );


// Filter wp_nav_menu() to add profile link
add_filter( 'wp_nav_menu_items', 'my_nav_menu_profile_link' );
function my_nav_menu_profile_link($menu) { 	
	if (!is_user_logged_in())
		return $menu;
	else
		$profilelink = '<li><a href="' . bp_loggedin_user_domain( '/' ) . '">' . __('Profile') . '</a></li>';
		$menu = $profilelink . $menu ;
		return $menu;
}