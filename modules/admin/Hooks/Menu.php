<?php

namespace TranscyAdmin\Hooks;

use Google\Rpc\Help;
use Illuminate\Interfaces\IHook;
use Illuminate\Utils\Helper;

class Menu implements IHook
{
    public function registerHooks()
    {
        //register menu
        add_action('admin_menu', array($this, 'adminMenu'));

        //add menus bar
        add_action('admin_bar_menu', array($this, 'registerMenuBar'), 999);

        add_filter('plugin_action_links_' . plugin_basename(TRANSCY_FILE), array($this, 'pluginActionLinks'), 999);
    }

    public function adminMenu()
    {
        add_menu_page(
            __('Transcy dashboard', 'transcy'),
            __('Transcy', 'transcy'),
            'manage_options',
            'transcy-dashboard',
            array($this, 'transcyDashboard'),
            TRANSCY_ASSETS_FOLDER . '/images/icon.svg',
            2
        );

        if (Helper::isActiveOnApp() && getPlanApp() !== false) {
            // Menu Switcher
            global $submenu;
            $domain     = Helper::getDomain();
            $token      = $GLOBALS['appService']->generateToken();
            $version    = Helper::getTranscyVersion();
            $subMenuTranscy = [
                'home'          => __('Home', 'transcy'),
                'language'      => __('Language', 'transcy'),
                'translation'   => __('Translation', 'transcy'),
                'currency'      => __('Currency', 'transcy'),
                'switcher'      => __('Switcher', 'transcy')
            ];
            foreach ($subMenuTranscy as $key => $menu) {
                $url    = sprintf('%s/auth/login?domain=%s&app_version=%s&access_token=%s&adminRedirectUrl=/%s', Helper::getAppUrl(), $domain, $version, $token, $key);
                $class  = 'transcy_menu_switcher';
                if ($key == 'home') {
                    $url    = admin_url('admin.php?page=transcy-dashboard');
                    $class  = 'current';
                }
                $submenu['transcy-dashboard'][] = array($menu, 'manage_options', $url, '', $class);
            }
        }
    }

    public function transcyDashboard()
    {
        echo '<div id="app"></div>';
    }

    public function registerMenuBar($adminBar)
    {
        // if (!AdminHelper::isAdminRole()) {
        //     return;
        // }

        // $adminBar->add_node([
        //     'id'    => 'hhg-spine-api',
        //     'title' => __('HHG SPINE', 'hhg-spine')
        // ]);

        // $adminBar->add_menu([
        //     'parent' => 'hhg-spine-api',
        //     'id'     => 'hhg-spine-api-setting',
        //     'title'  => __('Cache Settings', 'hhg-spine'),
        //     'href'   => admin_url('admin.php?page=hhg-spine')
        // ]);

        // //add cache menu
        // $this->addCacheMenus($adminBar);
    }

    public function pluginActionLinks($links)
    {
        // whole plugin is for admins only
        if (false === current_user_can('administrator')) {
            return $links;
        }
        $settingsLink = '<a class="settings-transcy" href="' . admin_url('admin.php?page=transcy-dashboard') . '">' . __('Settings', 'transcy') . '</a>';

        array_unshift($links, $settingsLink);

        return $links;
    }
}
