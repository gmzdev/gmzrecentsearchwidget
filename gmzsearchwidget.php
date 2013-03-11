<?php
/*
Plugin Name: Gmz Recent Search Widget
Plugin URI: http://gumz-ex-press.com/
Description: Shows recent searches in a sidebar as widget.
Author: Garry James Agum
Version: 0.1
Author URI: http://gumz-ex-press.com/
Text Domain: gmz-recent-search-widget
*/

/*  Copyright 2013  Garry James Agum

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class GMZRecentSearchWidget extends WP_Widget{
    //initialization
    function GMZRecentSearchWidget(){
        $widget_ops = array('classname' => 'GMZRecentSearchWidget', 'description' => 'Use this widget to display displays recent searches by visitors' );
        $this->WP_Widget('GMZRecentSearchWidget', 'GMZ Recent Search Widget', $widget_ops);
        add_action( 'template_redirect', array( &$this, 'template_redirect' ) );
    }

    //form inside widget area
    function form($instance){
        $instance = wp_parse_args( (array) $instance, array( 'title' => 'Recent Searches', 'max' => 20, 'nofollow' => '' ) );
        $title = strip_tags($instance['title']);
        $max = $instance['max'];
        $nofollow = $instance['nofollow'] ? 'checked="checked"' : '';
        ?>
	    <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'gmz-recent-searche-widget'); ?> 
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
            </label>
        </p>
	    <p>
            <label for="<?php echo $this->get_field_id('max'); ?>"><?php _e('Max Results:', 'gmz-recent-searche-widget'); ?> 
                <input id="<?php echo $this->get_field_id('max'); ?>" name="<?php echo $this->get_field_name('max'); ?>" type="text" size="3" maxlength="5" value="<?php echo esc_attr($max); ?>" />
            </label>
        </p>
	    <p>
            <label for="<?php echo $this->get_field_id('nofollow'); ?>"><?php _e('Add <code>rel="nofollow"</code> to links:', 'gmz-recent-searche-widget'); ?> 
                <input type="checkbox" <?php echo $nofollow; ?> id="<?php echo $this->get_field_id('nofollow'); ?>" name="<?php echo $this->get_field_name('nofollow'); ?>" />
            </label>
        </p>
	    <?php
    }

    //check for updates
    function update($new_instance, $old_instance){
        $instance = $old_instance;
        $instance['title'] = $new_instance['title'];
        $instance['max'] = $new_instance['max'];
        $instance['nofollow'] = $new_instance['nofollow'];
        update_option('gmz_search_widget_option', $instance);
        return $instance;
    }

    //display widget in actual website
    function widget($args, $instance){
        extract($args, EXTR_SKIP);
        echo $before_widget;
        $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
 
        if (!empty($title)){
            echo $before_title . $title . $after_title;
        }
            
        $data = get_option('gmz_search_widget_data');
        if ( !is_array( $data ) ) {
            $data = array();
        }

        $option = get_option('gmz_search_widget_option');

        $nofollow = $option['nofollow'] ? 'rel="nofollow"' : '';

        if (count($data)>0){
            echo '<ul>';
            foreach($data as $search){
                echo '<li>';
                echo '<a href="'.get_search_link( $search ).'"'.$nofollow.' >';
                echo esc_html($search);
                echo '</a>';
                echo '</li>';
            }
            echo '</ul>';
        }
        echo $after_widget;
    }

    //search is triggered
    function template_redirect(){
        if ( is_search() ) {
			// Store search term
			$query = get_search_query();

            //apttern to validate research keyword
            $pattern = '/[^a-zA-Z0-9-\\s-\']+/i';
			
			//validate research keyword
            if(preg_match($pattern, $query)) return;
			
			//register set options in admin area
            $options = get_option('gmz_search_widget_option' );
			
			//set max as defined option
            $max = $options['max'];
			
			//retrieve saved data in gmz_search_widget_data option
            $data = get_option('gmz_search_widget_data', array());
			
			//search keyword in the database
            $pos = array_search($query, $data);
			
			//check if seach keyword exists in $data
            if ( $pos !== false ) {
				//if keyword exists in data
				if ( $pos != 0 ) {
					//if keyword exists in data and not in 0 position
					//remove the existing keyword and add the recent keyword to the 0 position
					$data = array_merge(array_slice($data, 0, $pos ), array($query), array_slice( $data, $pos + 1 ) );
				}
			} else {
				//if the keyword does not exist in the data
				//insert the recent keyword to the 0 position
				array_unshift($data, $query);
				
				//after insert, check the maximum number of keyword to display
				if (count($data) > $max ) {
					//if the number of keywords exceed as defined then remove the last keyword
					array_pop( $data );
				}
			}
			
			//update the keyword data
            update_option( 'gmz_search_widget_data', $data );
        }
    }
}

//initialize widget plugin
add_action( 'widgets_init', create_function('', 'return register_widget("GMZRecentSearchWidget");') );

?>
