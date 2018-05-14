<?php
/**
 * TM 
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * TM plugin.
 */
class TMPlugin extends Omeka_Plugin_AbstractPlugin
{
    /**
     * @var array Filters for the plugin.
     */
    protected $_filters = array('admin_navigation_main');

    /**
     * Add the TM link to the admin main navigation.
     * 
     * @param array Navigation array.
     * @return array Filtered navigation array.
     */
    public function filterAdminNavigationMain($nav)
    {
        $nav[] = array(
            'label' => __('Tag Management'),
            'uri' => url('tm'),
        );
        return $nav;
    }
}
