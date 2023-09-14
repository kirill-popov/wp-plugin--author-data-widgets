<?php

namespace Secure_Author_Data_Plugin;

class Secure_Author_Data_Widgets_Loader
{
    public function register_new_widgets() {
		error_log('func:' . __FUNCTION__ .'; '. print_r('register',true));
		register_widget( 'Secure_Author_Data_Plugin\Widgets\Author_Posts_Widget' );
	}

    public static function unregister_new_widgets() {
        error_log('func:' . __FUNCTION__ .'; '. print_r('un-register',true));
        unregister_widget( 'Secure_Author_Data_Plugin\Widgets\Author_Posts_Widget' );
    }
}