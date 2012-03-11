<?php
/*
 * Plugin Name: Upcoming Posts HTML5
 * Version: 0.0.3
 * Description: Upcoming Posts widget that uses HTML5
 * Author: Kayle Armstrong
 * Author URI: http://www.amberkayle.com
 */
 
 
/**
 * Protection 
 * 
 * This string of code will prevent hacks from accessing the file directly.
 */
defined('ABSPATH') or die("Cannot access pages directly.");
 
add_action( 'widgets_init', create_function('', 'return register_widget("Upcoming_Posts_Html5");') );


 
class Upcoming_Posts_Html5 extends WP_Widget {

  private $defaults;

  function Upcoming_Posts_Html5() {
    $this->defaults = array( 'widget_title' =>  'Upcoming Posts',
                              'upcoming_number' => 5,
                              'include_author' => true,
                              'upcoming_title' => 'An Untitled Post',
                              'grab_by_tag' => false,
                              'upcoming_tag_slug' => 'ready-to-post',
                              'upcoming_post_type' => 'future' );
  
    $widget_ops = array('classname' => 'widget_upcoming_posts_html5', 
                        'description' => __( "Upcoming Posts widget that uses HTML5") );      
    $this->WP_Widget( 'upcoming-posts-html5', __('Upcoming Posts HTML5'), $widget_ops);

		add_action( 'save_post', array(&$this, 'flush_widget_cache') );
		add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
		add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );  
  
  
  }
  
  
  
  
  /* 
  Creates the edit form for the widget.
  */
	function form($instance) {
	
	  // Set any uninitialized args to default values
    $instance = wp_parse_args( (array) $instance, 
                                array(  'widget_title' => $this->defaults['widget_title'],
                                        'upcoming_number' => $this->defaults['upcoming_number'],
                                        'include_author' => $this->defaults['include_author'],
                                        'upcoming_title' => $this->defaults['upcoming_title'],
                                        'grab_by_tag' => $this->defaults['grab_by_tag'],
                                        'upcoming_tag_slug' => $this->defaults['upcoming_tag_slug'],
                                        'upcoming_post_type' => $this->defaults['upcoming_post_type'] ) );
                 
    // Get values                                      
    $widget_title = esc_attr($instance['widget_title']);
		$upcoming_number = absint($instance['upcoming_number']);
    $include_author = (bool) $instance['include_author'];		
    $upcoming_title = esc_attr($instance['upcoming_title']); 
    $grab_by_tag = (bool) $instance['grab_by_tag'];   
    $upcoming_tag_slug = esc_attr($instance['upcoming_tag_slug']);
		$upcoming_post_type = esc_attr($instance['upcoming_post_type']);
		
		
		// Widget Title
		$output_widget_title = "<p style='text-align:left'>";
    $output_widget_title .= '<label for="' . $this->get_field_name('widget_title') . 
                            '">' . __('Widget Title: ');		
		$output_widget_title .= "<input id='{$this->get_field_id('widget_title')}' name='{$this->get_field_name('widget_title')}'";
	  $output_widget_title .= "type='text' value='{$widget_title}' />";
	  $output_widget_title .= "</label></p>";
	  
		
		// Number of posts to list
#    $output_upcoming_number = '<p style="text-align:left;">';
    $output_upcoming_number = '<label for="' . $this->get_field_name('upcoming_number') . 
                               '">' . __('Number of posts to display: ');
    $output_upcoming_number .= '<select id="' . $this->get_field_id('upcoming_number') . 
                                  '" name="' . $this->get_field_name('upcoming_number') . '"> ';
    for( $i = 1; $i <=10; ++$i ){
      $selected =  ($upcoming_number == $i ? ' selected="selected"' : '' );
      $output_upcoming_number  .= '<option value="' . $i . '"' .  $selected . '>' . $i .  '</option>';
    }		
    $output_upcoming_number .= '</label></select></p>';	
		
		
    // Include Author
#    $output_include_author = '<p style="text-align:right;">';    
    $output_include_author = '<label for="' . $this->get_field_id('include_author') .'">';
    $output_include_author .= '<input type="checkbox" id="' . $this->get_field_id('include_author') . 
                              '" name="' . $this->get_field_name('include_author') . '"';
    if( $include_author ){
      $output_include_author .= ' checked="checked" ';
    }
    $output_include_author .= '/>' .  __('Include Author? ');
    $output_include_author .= '</label>';	
    
		// Default Post Title
#		$output_upcoming_title = "<p style='text-align:left'>";
    $output_upcoming_title = '<label for="' . $this->get_field_name('upcoming_title') . 
                              '">' . __('Default Post Title: ') ;		
		$output_upcoming_title .= "<input id='{$this->get_field_id('upcoming_title')}' " . 
		                          "name='{$this->get_field_name('upcoming_title')}'" . 
		                          "type='text' value='{$upcoming_title}' />";
	  $output_upcoming_title .= "</label>";	 
	      
    // Grab by tag
#    $output_grab_by_tag = '<p style="text-align:left;">';    
    $output_grab_by_tag  = '<label for="' . $this->get_field_id('grab_by_tag ') . 
                            '">' . __('Grab drafts by tag? ');
    $output_grab_by_tag .= '<input type="checkbox" id="' . $this->get_field_id('grab_by_tag') . 
                                                     '" name="' . $this->get_field_name('grab_by_tag') . '"';
    if( $grab_by_tag  ){
      $output_grab_by_tag .= ' checked="checked" ';
    }
    $output_grab_by_tag  .= '/>';
    $output_grab_by_tag .= '</label>';	
	  
		// Default Tag slug
#		$output_upcoming_tag_slug = "<p style='text-align:left'>";
    $output_upcoming_tag_slug = "<label for={$this->get_field_name('upcoming_tag_slug')}" . 
                                 "'>'" . __('Draft Tag Slug: ');		
		$output_upcoming_tag_slug .= "<input id='{$this->get_field_id('upcoming_tag_slug')}' " . 
  		                          "name='{$this->get_field_name('upcoming_tag_slug')}'" . 
	  	                          "type='text' value='{$upcoming_tag_slug}' />";
	  $output_upcoming_tag_slug .= "</label>";	  	      
    
    
    
    // Draft or Scheduled posts
#		$output_upcoming_post_type = "<p style='text-align:left'>";
    $output_upcoming_post_type = '<label for="' . $this->get_field_name('upcoming_post_type') . 
                                  '">' . __('Draft or Scheduled post? <br />');		
    if( 0 == strcmp( 'draft', $upcoming_post_type ) ){                              
		  $output_upcoming_post_type .= "<input type='radio' id='{$this->get_field_id('upcoming_post_type')}'" . 
	                                  " name='{$this->get_field_name('upcoming_post_type')}'" . 
	                                  " value='draft' checked='checked' />Draft";
		  $output_upcoming_post_type .= "<input type='radio' id='{$this->get_field_id('upcoming_post_type')}'" . 
	                                  " name='{$this->get_field_name('upcoming_post_type')}'" . 
	                                  " value='future' />Scheduled";
	                                  
    } else {
		  $output_upcoming_post_type .= "<input type='radio' id='{$this->get_field_id('upcoming_post_type')}'" . 
	                                  " name='{$this->get_field_name('upcoming_post_type')}'" . 
	                                  " value='draft' />Draft";      
		  $output_upcoming_post_type .= "<input type='radio' id='{$this->get_field_id('upcoming_post_type')}'" . 
	                                  " name='{$this->get_field_name('upcoming_post_type')}'" . 
	                                  " value='future' checked='checked' />Scheduled";
    }
	  $output_upcoming_post_type .= "</label>";	



    // Output the form
    echo "<p style='text-align:left;'>$output_widget_title</p>";
    echo "<p>$output_upcoming_number $output_include_author</p>";
    echo "<p>$output_upcoming_post_type</p>";
    echo $output_upcoming_title;
    echo $output_grab_by_tag;
    echo $output_upcoming_tag_slug;
	
	}
	
	
	
	
  /*
  Saves the widgets settings.
  */
	function update($new_instance, $old_instance) {
	  // The old instance($old_instance) is overwritten by the new instance($new_instance) which values are taken from the widget form
    $instance = $old_instance;
    
    $instance['widget_title'] = strip_tags(stripslashes($new_instance['widget_title']));
    $instance['upcoming_number'] = strip_tags(stripslashes($new_instance['upcoming_number']));
    $instance['upcoming_title'] = strip_tags(stripslashes($new_instance['upcoming_title']));
    $instance['upcoming_tag_slug'] = strip_tags(stripslashes($new_instance['upcoming_tag_slug']));
    $instance['upcoming_post_type'] = strip_tags(stripslashes($new_instance['upcoming_post_type']));
    
    // Include author = checkbox
    $instance['include_author'] = 0;
    if( isset( $new_instance['include_author'] ) ){
      $instance['include_author'] = 1;
    }  
    
    // grab_by_tag = checkbox
    $instance['grab_by_tag'] = 0;
    if( isset( $new_instance['grab_by_tag'] ) ){
      $instance['grab_by_tag'] = 1;
    }        
    
    
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_upcoming_posts_html5']) ){
			delete_option('widget_upcoming_posts_html5');    
		}
    
   return $instance;
	}
	
	
	
  /*
  Displays the widget
  */
	function widget($args, $instance) {
		$cache = wp_cache_get('widget_upcoming_posts_html5', 'widget');

		if ( !is_array($cache) )
			$cache = array();

		if ( ! isset( $args['widget_id'] ) )
			$args['widget_id'] = $this->id;

		if ( isset( $cache[ $args['widget_id'] ] ) ) {
			echo $cache[ $args['widget_id'] ];
			return;
		}

		ob_start();
		extract($args);
		
		// extract widget config options. 
    $include_author = (isset($instance['include_author']) && $instance['include_author']) ? 
                      true : false;
    $grab_by_tag = (isset( $instance['grab_by_tag'] ) && $instance['grab_by_tag'] ) ?
                    true : false;
    $upcoming_title = empty($instance['upcoming_title']) ? 
                      $this->defaults['upcoming_title'] : $instance['upcoming_title'];
    $upcoming_number = empty($instance['upcoming_number']) ? 
                       $this->defaults['upcoming_number'] : $instance['upcoming_number'];
    $upcoming_tag_slug = empty($instance['upcoming_tag_slug']) ? 
                      $this->defaults['upcoming_tag_slug'] : $instance['upcoming_tag_slug'];
    $upcoming_post_type = empty($instance['upcoming_post_type']) ? 
                       $this->defaults['upcoming_post_type'] : $instance['upcoming_post_type'];
    // $title has a special filter applied because it is the title of the widget which WordPress recognizes.
    $widget_title = apply_filters('widget_title', empty($instance['widget_title']) ? 
                    $this->defaults['widget_title'] : $instance['widget_title']);

    
    // Before the widget
    echo $before_widget;
    
    // The title
    if ( $widget_title ){
     echo $before_title . $widget_title . $after_title;
    }    
    
    
    // Get posts 
    if( $grab_by_tag ){
      $query = new WP_Query(array(  'posts_per_page' => $upcoming_number, 
                                    'no_found_rows' => true, 
                                    'post_status' => $upcoming_post_type, 
                                    'ignore_sticky_posts' => true, 
                                    'tag' => $upcoming_tag_slug ) );
    } else {
      $query = new WP_Query(array(  'posts_per_page' => $upcoming_number, 
                                    'no_found_rows' => true, 
                                    'post_status' => $upcoming_post_type, 
                                    'ignore_sticky_posts' => true ) );
    }
    
    
    // Gather post output
    $output = "<ul class='upcoming-posts-html5'>";
    while ($query->have_posts()){
      $query->the_post();
      
      $permalink = get_permalink();
      $post_title = esc_attr(get_the_title() ? get_the_title() : $upcoming_title );
      
      $output .= "<li>";
      $output .= "<cite>$post_title</cite>";
      if( $include_author ){
        $author = get_the_author();
        $output .= "<dt>$author</dt>";
      } 
      $output .= "</li>";
      
    }
    $output .= "</ul>";
    
    // output!
    echo $output;
    
    
    // After the widget
    echo $after_widget;    
    
    // Reset the global $the_post as this query will have stomped on it
		wp_reset_postdata();    
		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_set('widget_upcoming_posts_html5', $cache, 'widget');    
  }
  
  
	function flush_widget_cache() {
		wp_cache_delete('widget_upcoming_posts_html5', 'widget');
	}  
		  


}



 
 
?>