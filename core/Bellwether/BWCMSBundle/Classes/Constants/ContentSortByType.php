<?php

namespace Bellwether\BWCMSBundle\Classes\Constants;


class ContentSortByType
{
    const Title = 'CSB.Title';

    const Size = 'CSB.Size';

    const Created = 'CSB.Created';

    const Published = 'CSB.Published';

    const SortIndex = 'CSB.SortIndex';

    public static function getList()
    {
        static $list = null;
        if ($list === null) {
            $list = array(
                self::Title,
                self::Size,
                self::Created,
                self::Published,
                self::SortIndex
            );
        }
        return $list;
    }

}


