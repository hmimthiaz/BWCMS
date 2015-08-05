<?php

namespace Bellwether\BWCMSBundle\Classes\Constants;


class ContentSortOrderType
{
    const ASC = 'CSO.ASC';

    const DESC = 'CSO.DESC';

    public static function getList()
    {
        static $list = null;
        if ($list === null) {
            $list = array(
                self::ASC,
                self::DESC
            );
        }
        return $list;
    }

}
