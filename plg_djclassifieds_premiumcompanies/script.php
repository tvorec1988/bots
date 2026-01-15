<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.PremiumCompanies
 *
 * @copyright   Copyright (C) 2026. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

/**
 * Script file of Premium Companies plugin
 *
 * @since  1.0.0
 */
class PlgContentPremiumCompaniesInstallerScript extends InstallerScript
{
    /**
     * Minimum Joomla version required to install the extension
     *
     * @var    string
     * @since  1.0.0
     */
    protected $minimumJoomla = '4.0';

    /**
     * Minimum PHP version required to install the extension
     *
     * @var    string
     * @since  1.0.0
     */
    protected $minimumPhp = '7.4';

    /**
     * Method to install the extension
     *
     * @param   InstallerAdapter  $parent  The class calling this method
     *
     * @return  boolean  True on success
     *
     * @since   1.0.0
     */
    public function install($parent)
    {
        echo '<h2>' . Text::_('PLG_DJCLASSIFIEDS_PREMIUMCOMPANIES') . '</h2>';
        echo '<p>' . Text::_('PLG_DJCLASSIFIEDS_PREMIUMCOMPANIES_INSTALL_SUCCESS') . '</p>';

        return true;
    }

    /**
     * Method to uninstall the extension
     *
     * @param   InstallerAdapter  $parent  The class calling this method
     *
     * @return  boolean  True on success
     *
     * @since   1.0.0
     */
    public function uninstall($parent)
    {
        echo '<p>' . Text::_('PLG_DJCLASSIFIEDS_PREMIUMCOMPANIES_UNINSTALL_SUCCESS') . '</p>';

        return true;
    }

    /**
     * Method to update the extension
     *
     * @param   InstallerAdapter  $parent  The class calling this method
     *
     * @return  boolean  True on success
     *
     * @since   1.0.0
     */
    public function update($parent)
    {
        echo '<h2>' . Text::_('PLG_DJCLASSIFIEDS_PREMIUMCOMPANIES') . '</h2>';
        echo '<p>' . Text::_('PLG_DJCLASSIFIEDS_PREMIUMCOMPANIES_UPDATE_SUCCESS') . '</p>';

        return true;
    }

    /**
     * Function called before extension installation/update/removal procedure commences
     *
     * @param   string            $type    The type of change (install, update or discover_install, not uninstall)
     * @param   InstallerAdapter  $parent  The class calling this method
     *
     * @return  boolean  True on success
     *
     * @since   1.0.0
     */
    public function preflight($type, $parent)
    {
        // Check if DJ-Classifieds is installed
        if (!$this->isComponentInstalled('com_djclassifieds')) {
            Factory::getApplication()->enqueueMessage(
                Text::_('PLG_DJCLASSIFIEDS_PREMIUMCOMPANIES_WARNING_DJCLASSIFIEDS'),
                'warning'
            );
        }

        // Check if J-Business Directory is installed
        if (!$this->isComponentInstalled('com_jbusinessdirectory')) {
            Factory::getApplication()->enqueueMessage(
                Text::_('PLG_DJCLASSIFIEDS_PREMIUMCOMPANIES_WARNING_JBUSINESS'),
                'warning'
            );
        }

        return true;
    }

    /**
     * Function called after extension installation/update/removal procedure commences
     *
     * @param   string            $type    The type of change (install, update or discover_install, not uninstall)
     * @param   InstallerAdapter  $parent  The class calling this method
     *
     * @return  boolean  True on success
     *
     * @since   1.0.0
     */
    public function postflight($type, $parent)
    {
        // Enable plugin on install
        if ($type === 'install') {
            $this->enablePlugin();

            echo '<div style="margin: 20px 0; padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px;">';
            echo '<h3 style="margin-top: 0; color: #155724;">' . Text::_('PLG_DJCLASSIFIEDS_PREMIUMCOMPANIES_POSTINSTALL_TITLE') . '</h3>';
            echo '<p>' . Text::_('PLG_DJCLASSIFIEDS_PREMIUMCOMPANIES_POSTINSTALL_MESSAGE') . '</p>';
            echo '<ul style="margin: 10px 0;">';
            echo '<li>' . Text::_('PLG_DJCLASSIFIEDS_PREMIUMCOMPANIES_POSTINSTALL_STEP1') . '</li>';
            echo '<li>' . Text::_('PLG_DJCLASSIFIEDS_PREMIUMCOMPANIES_POSTINSTALL_STEP2') . '</li>';
            echo '<li>' . Text::_('PLG_DJCLASSIFIEDS_PREMIUMCOMPANIES_POSTINSTALL_STEP3') . '</li>';
            echo '</ul>';
            echo '</div>';
        }

        return true;
    }

    /**
     * Check if a component is installed
     *
     * @param   string  $componentName  Component option name (e.g., 'com_content')
     *
     * @return  boolean
     *
     * @since   1.0.0
     */
    protected function isComponentInstalled($componentName)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('extension_id')
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('element') . ' = ' . $db->quote($componentName))
            ->where($db->quoteName('type') . ' = ' . $db->quote('component'));

        $db->setQuery($query);

        try {
            $result = $db->loadResult();
            return !empty($result);
        } catch (Exception $e) {
            Log::add($e->getMessage(), Log::ERROR, 'jerror');
            return false;
        }
    }

    /**
     * Enable the plugin after installation
     *
     * @return  boolean
     *
     * @since   1.0.0
     */
    protected function enablePlugin()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('enabled') . ' = 1')
            ->where($db->quoteName('element') . ' = ' . $db->quote('premiumcompanies'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('content'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'));

        $db->setQuery($query);

        try {
            $db->execute();
            return true;
        } catch (Exception $e) {
            Log::add($e->getMessage(), Log::ERROR, 'jerror');
            return false;
        }
    }
}
