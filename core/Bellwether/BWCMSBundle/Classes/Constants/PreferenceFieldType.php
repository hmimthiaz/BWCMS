<?php

namespace Bellwether\BWCMSBundle\Classes\Constants;


class PreferenceFieldType
{

    const Internal = 'PF.Internal';

    const Number = 'PF.Number';

    const String = 'PF.String';

    const Date = 'PF.Date';

    const Time = 'PF.Time';

    const DateTime = 'PF.DateTime';

    const Serialized = 'PF.Serialized';

    const Content = 'PF.Content';

    const Custom = 'PF.Custom';

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
                self::DateTime,
                self::Custom
            );
        }
        return $list;
    }


}
