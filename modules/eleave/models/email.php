<?php
/**
 * @filesource modules/eleave/models/email.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Eleave\Email;

use Kotchasan\Language;

/**
 * ส่งอีเมลไปยังผู้ที่เกี่ยวข้อง
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
    /**
     * ส่งอีเมลแจ้งการทำรายการ
     *
     * @param string $mailto อีเมล
     * @param string $name   ชื่อ
     * @param array  $order ข้อมูล
     */
    public static function send($mailto, $name, $order)
    {
        $ret = array();
        if (self::$cfg->noreply_email != '') {
            // ส่งอีเมลไปยังผู้ทำรายการเสมอ
            $emails = array($mailto => $mailto.'<'.$name.'>');
            // อีเมลของมาชิกที่สามารถอนุมัติได้ทั้งหมด
            $query = static::createQuery()
                ->select('U.username', 'U.name')
                ->from('user U')
                ->where(array(
                    array('U.permission', 'LIKE', '%,can_approve_eleave,%'),
                    array('U.active', 1),
                ))
                ->cacheOn();
            foreach ($query->execute() as $item) {
                $emails[$item->username] = $item->username.'<'.$item->name.'>';
            }
            // ส่งอีเมล
            $subject = '['.self::$cfg->web_title.'] '.Language::trans('{LNG_Request for leave}');
            $msg = Language::trans(createClass('Eleave\View\View')->render($order));
            $err = \Kotchasan\Email::send(implode(',', $emails), self::$cfg->noreply_email, $subject, $msg);
            if ($err->error()) {
                // คืนค่า error
                $ret[] = strip_tags($err->getErrorMessage());
            }
        }

        return empty($ret) ? Language::get('Your message was sent successfully') : implode("\n", $ret);
    }
}
