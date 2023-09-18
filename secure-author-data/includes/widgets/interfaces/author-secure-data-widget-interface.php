<?php

namespace Secure_Author_Data_Plugin\Widgets\Interfaces;

interface Author_Secure_Data_Widget_Interface
{
    public function admin_load_scripts(): void;
    public function load_styles(): void;
    public function widget($args, $instance): void;
    public function form($instance): void;
    public function update($new_instance, $old_instance): array;
}