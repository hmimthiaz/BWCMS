<?php

namespace Bellwether\BWCMSBundle\Classes\Constants;


class AuditActionType
{

    const Add = 'AA.Add';

    const Approve = 'AA.Approve';

    const Edit = 'AA.Edit';

    const Delete = 'AA.Delete';

    const Login = 'AA.Login';

    const Logout = 'AA.Logout';

    const Publish = 'AA.Publish';

    const Read = 'AA.Read';

    const Reject = 'AA.Reject';

    const Send = 'AA.Send';

    const Sync = 'AA.Sync';

    public static function getList()
    {
        static $list = null;
        if ($list === null) {
            $list = array(
                self::Add,
                self::Approve,
                self::Edit,
                self::Delete,
                self::Login,
                self::Publish,
                self::Read,
                self::Reject,
                self::Send,
                self::Sync
            );
        }
        return $list;
    }

}
