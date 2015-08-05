<?php

namespace Bellwether\BWCMSBundle\Classes\Constants;


class ContentScopeType
{

    const CPublic = 'CS.CPublic';

    const CPrivate = 'CS.CPrivate';

    const CPageBuilder = 'CS.CPB';

    public static function getList()
    {
        static $list = null;
        if ($list === null) {
            $list = array(
                self::CPublic,
                self::CPrivate,
                self::CPageBuilder
            );
        }
        return $list;
    }

}
