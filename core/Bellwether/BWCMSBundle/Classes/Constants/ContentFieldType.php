<?php

namespace Bellwether\BWCMSBundle\Classes\Constants;


class ContentFieldType
{

    const Internal = 'CF.Internal';

    const Number = 'CF.Number';

    const String = 'CF.String';

    const Date = 'CF.Date';

    const Time = 'CF.Time';

    const DateTime = 'CF.DateTime';

    const Serialized = 'CF.Serialized';

    const Content = 'CF.Content';

    const Custom = 'CF.Custom';

    public static function getList()
    {
        static $list = null;
        if ($list === null) {
            $list = array(
                self::Internal,
                self::Number,
                self::String,
                self::Date,
                self::Time,
                self::DateTime,
                self::Serialized,
                self::Content,
                self::Custom
            );
        }
        return $list;
    }
}
