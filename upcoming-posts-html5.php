<?php
/*
 * Plugin Name: Upcoming Posts HTML5
 * Version: 0.0.1
 * Description: Upcoming Posts widget that uses HTML5
 * Author: Amber Kayle Armstrong
 * Author URI: http://www.amberkayle.com
 */
 
 
add_action( 'widgets_init', create_function('', 'return register_widget("Upcoming_Posts_Html5");') );


 
class Upcoming_Posts_Html5 extends WP_Widget {


  function Upcoming_Posts_Html5() {
    $widget_ops = array('classname' => 'widget_upcoming_posts_html5', 'description' => __( "Upcoming Posts widget that uses HTML5") );      
    $this->WP_Widget( 'upcoming_posts_html5', __('Upcoming Posts HTML5'), $widget_ops);

		add_action( 'save_post', array(&$this, 'flush_widget_cache') );
		add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
		add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );  
  
  
  }
  
  
  
  
  /* 
  Creates the edit form for the widget.
  */
	function form($instance) {
	
	  // Set any uninitialized args to default values
    $instance = wp_parse_args( (array) $instance, array(  'upcoming_title' => 'Upcoming Posts',
                                                          'upcoming_number' => 5,
                                                          'include_author' => true ) );
                 
    # Get current values or set to defaults                                        
		$upcoming_title = isset($instance['upcoming_title']) ? esc_attr($instance['upcoming_title']) : 'abc';
		$upcoming_number = isset($instance['upcoming_number']) ? absint($instance['upcoming_number']) : 5;
    $include_author = isset($instance['include_author']) ? (bool) $instance['include_author'] : true;		
		
		
		
		# Widget Title
		$output_title = "<p style='text-align:left'>";
    $output_title .= '<label for="' . $this->get_field_name('upcoming_title') . '">' . __('Title: ');		
		$output_title .= "<input id='{$this->get_field_id('upcoming_title')}' name='{$this->get_field_name('upcoming_title')}'";
	  $output_title .= "type='text' value='{$upcoming_title}' />";
	  $output_title .= "</label></p>";
		
		
		# Number of posts to list
    // dropdown: number of blogs to display at one time
    $output_number = '<p style="text-align:left;">';
    $output_number .= '<label for="' . $this->get_field_name('upcoming_number') . '">' . __('Number of posts to display: ');
    $output_number .= '<select id="' . $this->get_field_id('upcoming_number') . 
                                  '" name="' . $this->get_field_name('upcoming_number') . '"> ';
    for( $i = 1; $i <=10; ++$i ){
      $selected =  ($upcoming_number == $i ? ' selected="selected"' : '' );
      $output_number  .= '<option value="' . $i . '"' .  $selected . '>' . $i .  '</option>';
    }		
    $output_number .= '</label></select></p>';	
		
		
    # Include Author
    $output_include_author = '<p style="text-align:left;">';    
    $output_include_author .= '<label for="' . $this->get_field_id('include_author') . '">' . __('Include Author? ');
    $output_include_author .= '<input type="checkbox" id="' . $this->get_field_id('include_author') . 
                                                     '" name="' . $this->get_field_name('include_author') . '"';
    if( $include_author ){
      $output_include_author .= ' checked="checked" ';
    }
    $output_include_author .= '/>';
    $output_number .= '</label></p>';	
    
    		

    echo $output_title;
    echo $output_number;
    echo $output_include_author;
	
	}
	
	
	
	
  /*
  Saves the widgets settings.
  */
	function update($new_instance, $old_instance) {
	  // The old instance($old_instance) is overwritten by the new instance($new_instance) which values are taken from the widget form
    $instance = $old_instance;
    
    // strip_tags() and stripslashes() to ensure that no matter what a user puts in the widget options, 
    // they will not break the page the widget appears on.
    $instance['upcoming_title'] = strip_tags(stripslashes($new_instance['upcoming_title']));
    $instance['upcoming_number'] = strip_tags(stripslashes($new_instance['upcoming_number']));
    
    # Include author = checkbox
    $instance['include_author'] = 0;
    if( isset( $new_instance['include_author'] ) ){
      $instance['include_author'] = 1;
    }          
    
    
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_upcoming_entries']) )
			delete_option('widget_upcoming_entries');    
    
   return $instance;
	}
	
	
	
  /*
  Displays the widget
  */
	function widget($args, $instance) {
	  // Cache stuff
		$cache = wp_cache_get('widget_upcoming_posts', 'widget');

		if ( !is_array($cache) )
			$cache = array();

		if ( isset($cache[$args['widget_id']]) ) {
			echo $cache[$args['widget_id']];
			return;
		}	
		
		ob_start();
		// create native WP variables.. such as  $before_widget, $before_title, $after_title, and $after_widget
		extract($args);
		
		// extract widget config options. 
    // $title has a special filter applied because it is the title of the widget which WordPress recognizes.
    $upcoming_title = apply_filters('widget_title', empty($instance['upcoming_title']) ? '&nbsp;' : $instance['upcoming_title']);
    $upcoming_number = empty($instance['upcoming_number']) ? '5' : $instance['upcoming_number'];
    $include_author = (isset($instance['include_author']) && $instance['include_author']) ? true : false;
    
    
    # Before the widget
    echo $before_widget;
    
    # The title
    if ( $upcoming_title ){
     echo $before_title . $upcoming_title . $after_title;
    }    
    
    // Get posts 
    $query = new WP_Query(array('posts_per_page' => $upcoming_number, 'no_found_rows' => true, 'post_status' => 'draft', 'ignore_sticky_posts' => true));
    
      
    // Gather post output
    $output = "<ul class='upcoming-posts-html5'>";
    while ($query->have_posts()){
      $query->the_post();
      
      $permalink = get_permalink();
      $post_title = esc_attr(get_the_title() ? get_the_title() : get_the_ID());
      if( get_the_title() ){
        $post_name = get_the_title();
      } else {
        $post_name = get_the_ID();
      }
      
      $output .= "<li>";
      $output .= "<cite>$post_name</cite>";
      if( $include_author ){
        $author = get_the_author();
        $output .= "<dt>$author</dt>";
      } 
      $output .= "</li>";
      
    }
    $output .= "</ul>";
    echo $output;
    
    
    # After the widget
    echo $after_widget;    
    
    // Reset the global $the_post as this query will have stomped on it
		wp_reset_postdata();    
		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_set('widget_upcoming_posts', $cache, 'widget');    
  }
  
  
	function flush_widget_cache() {
		wp_cache_delete('widget_upcoming_posts', 'widget');
	}  
		  


}



 
 
?>
