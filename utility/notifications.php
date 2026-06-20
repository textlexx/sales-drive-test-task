<?php

class Notifications{

    private static $errors = [];
    private static $successes = [];

    

    public static function  set_e( $text ){

        Notifications::$errors[] = $text;
    }

    public static function get_t(){

        return Notifications::$errors;
    }

    public static function set_s( $text ){

        Notifications::$successes[] = $text;
    }

    public static function get_s(){

        return Notifications::$successes;
    }
}