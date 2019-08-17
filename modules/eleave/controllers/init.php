<?php
/**
 * @filesource modules/eleave/controllers/init.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Eleave\Init;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * Init Module.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\KBase
{
    /**
     * ฟังก์ชั่นเริ่มต้นการทำงานของโมดูลที่ติดตั้ง
     * และจัดการเมนูของโมดูล.
     *
     * @param Request                $request
     * @param \Index\Menu\Controller $menu
     * @param array                  $login
     */
    public static function execute(Request $request, $menu, $login)
    {
        // รายการเมนูสมาชิกทุกคน
        $submenus = array();
        $reportmenu = array();
        foreach (Language::get('LEAVE_STATUS') as $k => $label) {
            $submenus[] = array(
                'text' => $label,
                'url' => 'index.php?module=eleave&amp;status='.$k,
            );
            $reportmenu[] = array(
                'text' => $label,
                'url' => 'index.php?module=eleave-report&amp;status='.$k,
            );
        }
        $submenus[] = array(
            'text' => '{LNG_Statistics for leave}',
            'url' => 'index.php?module=eleave-statistics',
        );
        $submenus[] = array(
            'text' => '{LNG_Add New} {LNG_Request for leave}',
            'url' => 'index.php?module=eleave-leave',
        );
        $menu->addTopLvlMenu('eleave', '{LNG_E-Leave}', null, $submenus, 'module');
        // สามารถอนุมัติได้
        if (Login::checkPermission($login, 'can_approve_eleave')) {
            $menu->add('report', '{LNG_E-Leave}', null, $reportmenu);
        }
        // เมนูตั้งค่า
        $submenus = array();
        if (Login::checkPermission($login, 'can_manage_eleave')) {
            $submenus[] = array(
                'text' => '{LNG_List of} {LNG_Leave type}',
                'url' => 'index.php?module=eleave-setup',
            );
            foreach (Language::get('ELEAVE_CATEGORIES') as $type => $label) {
                $submenus[] = array(
                    'text' => $label,
                    'url' => 'index.php?module=eleave-categories&amp;type='.$type,
                );
            }
        }
        if (Login::checkPermission($login, 'can_config')) {
            $submenus[] = array(
                'text' => '{LNG_Settings}',
                'url' => 'index.php?module=eleave-settings',
            );

        }
        if (!empty($submenus)) {
            $menu->add('settings', '{LNG_E-Leave}', null, $submenus);
        }
    }

    /**
     * รายการ permission ของโมดูล.
     *
     * @param array $permissions
     *
     * @return array
     */
    public static function updatePermissions($permissions)
    {
        $permissions['can_manage_eleave'] = '{LNG_Can set the module} ({LNG_E-Leave})';
        $permissions['can_approve_eleave'] = '{LNG_Can be approve} ({LNG_E-Leave})';

        return $permissions;
    }
}
