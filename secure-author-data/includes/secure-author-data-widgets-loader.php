<?php

namespace Secure_Author_Data_Plugin;

class Secure_Author_Data_Widgets_Loader
{
    public function register_new_widgets() {
		register_widget( 'Secure_Author_Data_Plugin\Widgets\Author_Posts_Widget' );
	}

    public static function unregister_new_widgets() {
        unregister_widget( 'Secure_Author_Data_Plugin\Widgets\Author_Posts_Widget' );
    }
}