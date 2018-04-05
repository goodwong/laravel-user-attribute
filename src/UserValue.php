<?php

namespace Goodwong\UserAttribute;

use Goodwong\UserAttribute\Handlers\UserValueDecorator;
use Goodwong\UserAttribute\Handlers\UserValueHandler;

class UserValue
{
    /**
     * decorate user
     * 
     * @param  int  $user_id
     * @return \Goodwong\UserAttribute\Handlers\UserValueDecorator
     */
    public static function user (int $user_id)
    {
        return new UserValueDecorator($user_id);
    }

    /**
     * set context and instantiate ValueHandler
     * 
     * @param  string  $context
     * @return \Goodwong\UserAttribute\Handlers\UserValueHandler
     */
    public static function context (string $context)
    {
        return (new UserValueHandler)->context($context);
    }
}
