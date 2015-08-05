<?php

namespace Bellwether\BWCMSBundle\Classes\Constants;


class ContentPublishType
{
    const Draft = 'CP.Draft';

    const Published = 'CP.Published';

    const WorkFlow = 'CP.WorkFlow';

    const Expired = 'CP.Expired';

    public static function getList()
    {
        static $list = null;
        if ($list === null) {
            $list = array(
                self::Draft,
                self::Published,
                self::WorkFlow,
                self::Expired
            );
        }
        return $list;
    }
}
