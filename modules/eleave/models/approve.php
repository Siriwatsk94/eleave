<?php
/**
 * @filesource modules/eleave/models/approve.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Eleave\Approve;

use Gcms\Login;
use Kotchasan\Date;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * เพิ่ม/แก้ไข ประเภทการลา
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
    /**
     * อ่านข้อมูลรายการที่เลือก
     *
     * @param int $id ID
     *
     * @return object|null คืนค่าข้อมูล object ไม่พบคืนค่า null
     */
    public static function get($id)
    {
        return static::createQuery()
            ->from('leave_items I')
            ->join('leave F', 'LEFT', array('F.id', 'I.leave_id'))
            ->join('user U', 'LEFT', array('U.id', 'I.member_id'))
            ->where(array('I.id', $id))
            ->first('I.*', 'F.topic leave_type', 'U.username', 'U.name');
    }

    /**
     * บันทึกข้อมูลที่ส่งมาจากฟอร์ม (approve.php)
     *
     * @param Request $request
     */
    public function submit(Request $request)
    {
        $ret = array();
        // session, token, สมาชิก
        if ($request->initSession() && $request->isSafe() && $login = Login::isMember()) {
            // ค่าที่ส่งมา
            $save = array(
                'leave_id' => $request->post('leave_id')->toInt(),
                'department' => $request->post('department')->toInt(),
                'detail' => $request->post('detail')->textarea(),
                'communication' => $request->post('communication')->textarea(),
                'status' => $request->post('status')->toInt(),
                'reason' => $request->post('reason')->topic(),
            );
            // ตรวจสอบรายการที่เลือก
            $index = self::get($request->post('id')->toInt());
            if (!$index || !Login::checkPermission($login, 'can_approve_eleave')) {
                // ไม่พบ, ไม่สามารถอนุมัติ
                $ret['alert'] = Language::get('Sorry, Item not found It&#39;s may be deleted');
            } else {
                // วันลา
                $start_date = $request->post('start_date')->date();
                $end_date = $request->post('end_date')->date();
                $start_period = $request->post('start_period')->toInt();
                $end_period = $request->post('end_period')->toInt();
                if ($end_date == '') {
                    // ไม่ได้กรอกวันที่สิ้นสุดมา ใช้วันที่เดียวกันกับวันที่เริ่มต้น (ลา 1 วัน)
                    $end_date = $start_date;
                }
                if ($start_date == '') {
                    // ไม่ได้กรอกวันที่เริมต้น
                    $ret['ret_start_date'] = 'Please fill in';
                }
                $diff = Date::compare($start_date, $end_date);
                if ($diff['days'] > 0 && $start_period == 1) {
                    // ถ้าลาหลายวัน ไม่สามารถเลือกตัวเลือก ครึ่งวันเช้าได้
                    $ret['ret_start_period'] = Language::get('Cannot select this option');
                } else {
                    if ($start_date == $end_date) {
                        // ลาภายใน 1 วัน ใช้จำนวนวันลาจาก คาบการลา
                        $save['days'] = self::$cfg->eleave_periods[$start_period];
                    } else {
                        // ใช้จำนวนวันลาจากที่คำนวณ
                        $save['days'] = $diff['days'] + self::$cfg->eleave_periods[$start_period] + self::$cfg->eleave_periods[$end_period] - 1;
                    }
                    $save['start_date'] = $start_date;
                    $save['end_date'] = $end_date;
                    $save['start_period'] = $start_period;
                    $save['end_period'] = $end_period;
                }
                if ($end_date < $start_date) {
                    // วันที่สิ้นสุด น้อยกว่าวันที่เริ่มต้น
                    $ret['ret_end_date'] = Language::get('End date must be greater than or equal to the start date');
                }
                if ($save['detail'] == '') {
                    // ไม่ได้กรอก detail
                    $ret['ret_detail'] = 'Please fill in';
                }
                if (empty($ret)) {
                    // อัปโหลดไฟล์แนบ
                    \Download\Upload\Model::execute($ret, $request, $index->id, 'eleave', self::$cfg->eleave_file_typies, self::$cfg->eleave_upload_size);
                }
                if (empty($ret)) {
                    // แก้ไข
                    $this->db()->update($this->getTableName('leave_items'), $index->id, $save);
                    if ($save['status'] != $index->status) {
                        $save['leave_type'] = $index->leave_type;
                        $save['name'] = $index->name;
                        $save['id'] = $index->id;
                        // ส่งอีเมลแจ้งการขอลา
                        $ret['alert'] = \Eleave\Email\Model::send($index->username, $save['name'], $save);
                    } else {
                        // ไม่ต้องส่งอีเมล
                        $ret['alert'] = Language::get('Saved successfully');
                    }
                    $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'eleave-report', 'status' => $index->status));
                    // เคลียร์
                    $request->removeToken();
                }
            }
        }
        if (empty($ret)) {
            $ret['alert'] = Language::get('Unable to complete the transaction');
        }
        // คืนค่าเป็น JSON
        echo json_encode($ret);
    }
}
