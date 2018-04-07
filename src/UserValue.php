<?php

namespace Goodwong\UserValue;

use Goodwong\UserValue\Handlers\UserValueDecorator;
use Goodwong\UserValue\Handlers\UserValueHandler;

class UserValue
{
    /**
     * decorate user
     * 
     * @param  int  $user_id
     * @return \Goodwong\UserValue\Handlers\UserValueDecorator
     */
    public static function user (int $user_id)
    {
        return new UserValueDecorator($user_id);
    }

    /**
     * set context and instantiate ValueHandler
     * 
     * @param  string  $context
     * @return \Goodwong\UserValue\Handlers\UserValueHandler
     */
    public static function context (string $context)
    {
        return (new UserValueHandler)->context($context);
    }
}
