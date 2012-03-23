<?php

/**
 * Smarty plugin:
 * This is a Smarty plugin to handle the creation of HTML List elements for Sugar Action Menus.
 * Based on the theme, the plugin generates a proper group of button lists.
 *
 * @param $params array - its structure is
 *     'buttons' => list of button htmls, such as ( html_element1, html_element2, ..., html_element_n),
 *     'id' => id property for ul element
 *     'class' => class property for ul element
 * @param $smarty
 *
 * @return string - compatible sugarActionMenu structure, such as
 * <ul>
 *     <li>html_element1
 *         <ul>
 *              <li>html_element2</li>
 *                  ...
 *              </li>html_element_n</li>
 *         </ul>
 *     </li>
 * </ul>
 * ,which is generated by @see function smarty_function_sugar_menu
 *
 * <pre>
 * 1. SugarButton on smarty
 *
 * add appendTo to generate button lists
 * {{sugar_button ... appendTo='buttons'}}
 *
 * ,and then create menu
 * {{sugar_action_menu ... buttons=$buttons ...}}
 *
 * 2. Code generate in PHP
 * <?php
 * ...
 *
 * $buttons = array(
 *      '<input ...',
 *      '<a href ...',
 *      ...
 * );
 * require_once('include/Smarty/plugins/function.sugar_action_menu.php');
 * $action_button = smarty_function_sugar_action_menu(array(
 *      'id' => ...
 * 'buttons' => $buttons,
 * ...
 * ,$xtpl);
 * $template->assign("ACTION_BUTTON", $action_button);
 * return $
 * ?>
 * 3. Passing array to smarty in PHP
 * $action_button = array(
 *      'id' => 'id',
 *      'buttons' => array(
 *          '<input ...',
 *          '<a href ...',
 *          ...
 *      ),
 *      ...
 * );
 * $tpl->assign('action_button', $action_button);
 * in the template file
 * {sugar_action_menu params=$action_button}
 *
 * 4. Append button element in the Smarty
 * {php}
 * $this->append('buttons', "<a ...");
 * $this->append('buttons', "<input ...");
 * {/php}
 * {{sugar_action_menu ... buttons=$buttons ...}}
 * </pre>
 *
 * @author Justin Park (jpark@sugarcrm.com)
 */
function smarty_function_sugar_action_menu($params, &$smarty)
{
    $theme = !empty($params['theme']) ? $params['theme'] : SugarThemeRegistry::current()->name;
    $addition_params = $params['params'];
    if($addition_params) {
        unset($params['params']);
        $params = array_merge_recursive($params, $addition_params);
    }

    if(is_array($params['buttons']) && $theme != 'Classic') {

        $menus = array(
            'html' => array_shift($params['buttons']),
            'items' => array()
        );

        foreach($params['buttons'] as $item) {
            if(strlen($item)) {
                array_push($menus['items'],array(
                   'html' => $item
               ));
            }
        }
        $action_menu = array(
            'id' => $params['id'] ? (is_array($params['id']) ? $params['id'][0] : $params['id']) : '',
            'htmlOptions' => array(
                'class' => $params['class'] && strpos($params['class'], 'clickMenu') !== false  ? $params['class'] : 'clickMenu '.$params['class'],
                'name' => $params['name'] ? $params['name'] : '',
                'title' => 'sugar_action_menu'
            ),
            'itemOptions' => array(
                'class' => (count($menus['items']) == 0) ? 'single' : ''
            ),
            'submenuHtmlOptions' => array(
                'class' => 'subnav'
            ),
            'items' => array(
                $menus
            )
        );
        require_once('function.sugar_menu.php');
        return smarty_function_sugar_menu($action_menu, $smarty);

    }

    if (is_array($params['buttons'])) {
        return '<div class="action_buttons">' . implode(' ', $params['buttons']).'</div>';
    } else if(is_array($params)) {
        return '<div class="action_buttons">' . implode(' ', $params).'</div>';
    }

    return $params['buttons'];
}

?>