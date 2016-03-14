<?php

/**
 * @author marcus
 */
class DashboardAdmin extends ModelAdmin {
    private static $menu_title = 'Dashboards';
    private static $url_segment = 'dashboards';
    private static $managed_models = array('DashboardPage');
}
