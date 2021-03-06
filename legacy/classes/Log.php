<?php

class Log
{
    const LOG_ERROR = 'error';
    const LOG_DEBUG = 'debug';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    public static $monolog;

    public static function add($level, $tag, $message)
    {
        $vargs = array_slice(func_get_args(), 3);
        if (count($vargs) > 0) {
            $params = array_merge(array($message), $vargs);
            $message = call_user_func_array('sprintf', $params);
        }

        self::$monolog->log($level, $message, ['tag' => $tag]);
    }
    
    public static function debug($tag, $message)
    {
        $args = array_merge(array(self::LOG_DEBUG), func_get_args());
        call_user_func_array('Log::add', $args);
    }
    
    public static function error($tag, $message)
    {
        $args = array_merge(array(self::LOG_ERROR), func_get_args());
        call_user_func_array('Log::add', $args);
    }
}
