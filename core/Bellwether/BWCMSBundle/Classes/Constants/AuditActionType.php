<?php

namespace Bellwether\BWCMSBundle\Classes\Constants;


class AuditActionType
{

    const Add = 'AA.Add';

    const Approve = 'AA.Approve';

    const Accept = 'AA.Accept';

    const Edit = 'AA.Edit';

    const Delete = 'AA.Delete';

    const Duplicate = 'AA.Duplicate';

    const Login = 'AA.Login';

    const Logout = 'AA.Logout';

    const Publish = 'AA.Publish';

    const Read = 'AA.Read';

    const Reject = 'AA.Reject';

    const Send = 'AA.Send';

    const Sync = 'AA.Sync';

    const Verify = 'AA.Verify';

    const Upload = 'AA.Upload';


    public static function getList()
    {
        static $list = null;
        if ($list === null) {
            $list = array(
                self::Add,
                self::Approve,
                self::Accept,
                self::Edit,
                self::Delete,
                self::Duplicate,
                self::Login,
                self::Publish,
                self::Read,
                self::Reject,
                self::Send,
                self::Sync,
                self::Verify,
                self::Upload
            );
        }
        return $list;
    }

}
