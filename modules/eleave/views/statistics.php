<?php
/**
 * @filesource modules/eleave/views/statistics.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Eleave\Statistics;

use Kotchasan\Html;
use Kotchasan\Http\Request;

/**
 * module=eleave-statistics
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * แสดงรายการลา
     *
     * @param Request $request
     * @param array $params
     *
     * @return string
     */
    public function render(Request $request, $params)
    {
        // form
        $form = Html::create('form', array(
            'class' => 'table_nav',
            'method' => 'get',
            'action' => 'index.php',
            'ajax' => false,
            'token' => false,
        ));
        $div = $form->add('div');
        $fieldset = $div->add('fieldset');
        $fieldset->add('date', array(
            'id' => 'from',
            'label' => '{LNG_from}',
            'value' => $params['from'],
        ));
        $fieldset = $div->add('fieldset');
        $fieldset->add('date', array(
            'id' => 'to',
            'label' => '{LNG_to}',
            'value' => $params['to'],
        ));
        $div->add('fieldset', array(
            'class' => 'go',
            'innerHTML' => '<button type="submit" class="button go">{LNG_Go}</button>',
        ));
        $div->add('hidden', array(
            'id' => 'module',
            'value' => 'eleave-statistics',
        ));
        $thead = array();
        $tbody = array();
        foreach (\Eleave\Statistics\Model::execute($params) as $i => $item) {
            $thead[] = '<th>'.$item->topic.'</th>';
            $tbody[] = '<td>'.$item->days.' {LNG_days}</td>';
        }
        $content = '<section class="setup_frm">';
        $content .= $form->render();
        $content .= '<div id=pageview_graph class=ggraphs>';
        $content .= '<canvas></canvas>';
        $content .= '<table class="hidden">';
        $content .= '<thead><tr><th>{LNG_Leave type}</th>'.implode('', $thead).'</tr></thead>';
        $content .= '<tbody><tr><th>{LNG_Number of leave days}</th>'.implode('', $tbody).'</tr></tbody>';
        $content .= '</table>';
        $content .= '</div>';
        $content .= '</section>';
        $content .= '<script>new GGraphs("pageview_graph",{type:"donut",centerX:30+Math.round($G("pageview_graph").getHeight()/2),labelOffset:35,centerOffset:30,strokeColor:null});</script>';

        return $content;
    }
}
