<?php

namespace Bellwether\BWCMSBundle\Classes\Constants;


class AuditLevelType
{

    const Low = 'AL.Low';

    const Normal = 'AL.Normal';

    const High = 'AL.High';

    const Critical = 'AL.Critical';

    public static function getList()
    {
        static $list = null;
        if ($list === null) {
            $list = array(
                self::Low,
                self::Normal,
                self::High,
                self::Critical
            );
        }
        return $list;
    }

}
