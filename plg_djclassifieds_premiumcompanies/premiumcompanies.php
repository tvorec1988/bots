<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.PremiumCompanies
 *
 * @copyright   Copyright (C) 2026. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Premium Companies Plugin for DJ-Classifieds and J-Business Directory Integration
 *
 * @since  1.0.0
 */
class PlgContentPremiumCompanies extends CMSPlugin
{
    /**
     * Load the language file on instantiation
     *
     * @var    boolean
     * @since  1.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * Application object
     *
     * @var    \Joomla\CMS\Application\CMSApplication
     * @since  1.0.0
     */
    protected $app;

    /**
     * Database object
     *
     * @var    \Joomla\Database\DatabaseDriver
     * @since  1.0.0
     */
    protected $db;

    /**
     * Plugin that displays premium companies from J-Business Directory
     * when adding an order in DJ-Classifieds
     *
     * @param   string   $context  The context of the content being passed to the plugin
     * @param   object   &$row     The article object
     * @param   mixed    &$params  The article params
     * @param   integer  $page     The 'page' number
     *
     * @return  mixed
     *
     * @since   1.0.0
     */
    public function onContentBeforeDisplay($context, &$row, &$params, $page = 0)
    {
        // Check if we're in DJ-Classifieds context
        if (!$this->isDJClassifiedsContext($context)) {
            return;
        }

        // Load CSS
        $this->loadAssets();

        // Get premium companies
        $companies = $this->getPremiumCompanies($row);

        if (empty($companies)) {
            return;
        }

        // Generate HTML output
        $html = $this->renderCompanies($companies);

        // Determine position
        $position = $this->params->get('show_position', 'before');

        if ($position === 'before') {
            return $html;
        }

        return;
    }

    /**
     * Plugin that displays premium companies after content
     *
     * @param   string   $context  The context of the content being passed to the plugin
     * @param   object   &$row     The article object
     * @param   mixed    &$params  The article params
     * @param   integer  $page     The 'page' number
     *
     * @return  mixed
     *
     * @since   1.0.0
     */
    public function onContentAfterDisplay($context, &$row, &$params, $page = 0)
    {
        // Check if we're in DJ-Classifieds context
        if (!$this->isDJClassifiedsContext($context)) {
            return;
        }

        $position = $this->params->get('show_position', 'before');

        if ($position !== 'after') {
            return;
        }

        // Get premium companies
        $companies = $this->getPremiumCompanies($row);

        if (empty($companies)) {
            return;
        }

        // Generate HTML output
        return $this->renderCompanies($companies);
    }

    /**
     * Check if current context is DJ-Classifieds
     *
     * @param   string  $context  The context string
     *
     * @return  boolean
     *
     * @since   1.0.0
     */
    protected function isDJClassifiedsContext($context)
    {
        $input = $this->app->input;
        $option = $input->get('option', '');
        $view = $input->get('view', '');

        // Check for DJ-Classifieds component
        if ($option !== 'com_djclassifieds') {
            return false;
        }

        // Check for item/order views
        if (!in_array($view, ['item', 'additem', 'edititem', 'items'])) {
            return false;
        }

        return true;
    }

    /**
     * Get premium companies from J-Business Directory
     *
     * @param   object  $item  The DJ-Classifieds item
     *
     * @return  array
     *
     * @since   1.0.0
     */
    protected function getPremiumCompanies($item)
    {
        try {
            $db = $this->db;
            $query = $db->getQuery(true);

            // Build query to get premium companies
            $query->select('c.*')
                ->from($db->quoteName('#__jbusinessdirectory_companies', 'c'))
                ->where($db->quoteName('c.published') . ' = 1')
                ->where($db->quoteName('c.approved') . ' = 1');

            // Join with packages table to filter premium companies
            $query->join('LEFT', $db->quoteName('#__jbusinessdirectory_company_package', 'cp') .
                ' ON ' . $db->quoteName('cp.companyId') . ' = ' . $db->quoteName('c.id'))
                ->join('LEFT', $db->quoteName('#__jbusinessdirectory_packages', 'p') .
                ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('cp.packageId'))
                ->where($db->quoteName('p.type') . ' = ' . $db->quote('premium'));

            // Add active package condition
            $query->where('(' . $db->quoteName('cp.expire_date') . ' IS NULL OR ' .
                $db->quoteName('cp.expire_date') . ' >= ' . $db->quote(Factory::getDate()->toSql()) . ')');

            // Match by category if enabled
            if ($this->params->get('match_by_category', 1) && isset($item->cat_id)) {
                $query->where($db->quoteName('c.main_category') . ' = ' . (int) $item->cat_id);
            }

            // Limit results
            $maxCompanies = (int) $this->params->get('max_companies', 5);
            $query->setLimit($maxCompanies);

            // Order by featured first, then by date
            $query->order($db->quoteName('c.featured') . ' DESC, ' . $db->quoteName('c.created') . ' DESC');

            $db->setQuery($query);
            $companies = $db->loadObjectList();

            return $companies ?: [];
        } catch (Exception $e) {
            // Log error but don't break the page
            Factory::getApplication()->enqueueMessage(
                Text::sprintf('PLG_DJCLASSIFIEDS_PREMIUMCOMPANIES_ERROR', $e->getMessage()),
                'error'
            );
            return [];
        }
    }

    /**
     * Render companies HTML
     *
     * @param   array  $companies  Array of company objects
     *
     * @return  string
     *
     * @since   1.0.0
     */
    protected function renderCompanies($companies)
    {
        $displayStyle = $this->params->get('display_style', 'cards');
        $showLogo = $this->params->get('show_logo', 1);

        $html = '<div class="premium-companies-container premium-companies-' . $displayStyle . '">';
        $html .= '<h3 class="premium-companies-title">' . Text::_('PLG_DJCLASSIFIEDS_PREMIUMCOMPANIES_TITLE') . '</h3>';
        $html .= '<div class="premium-companies-list">';

        foreach ($companies as $company) {
            $html .= $this->renderCompanyCard($company, $displayStyle, $showLogo);
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Render individual company card
     *
     * @param   object   $company       Company object
     * @param   string   $displayStyle  Display style (cards/list/compact)
     * @param   boolean  $showLogo      Show company logo
     *
     * @return  string
     *
     * @since   1.0.0
     */
    protected function renderCompanyCard($company, $displayStyle, $showLogo)
    {
        $html = '<div class="premium-company-card">';

        // Logo
        if ($showLogo && !empty($company->logo_location)) {
            $logoUrl = Uri::root() . $company->logo_location;
            $html .= '<div class="company-logo">';
            $html .= '<img src="' . htmlspecialchars($logoUrl) . '" alt="' . htmlspecialchars($company->name) . '" />';
            $html .= '</div>';
        }

        // Company info
        $html .= '<div class="company-info">';
        $html .= '<h4 class="company-name">';

        // Create link to company
        $companyUrl = $this->getCompanyUrl($company);
        $html .= '<a href="' . $companyUrl . '" target="_blank">' . htmlspecialchars($company->name) . '</a>';
        $html .= '</h4>';

        // Description
        if (!empty($company->description)) {
            $description = strip_tags($company->description);
            $description = mb_substr($description, 0, 150);
            if (mb_strlen($company->description) > 150) {
                $description .= '...';
            }
            $html .= '<p class="company-description">' . htmlspecialchars($description) . '</p>';
        }

        // Contact info
        if ($displayStyle !== 'compact') {
            $html .= '<div class="company-contact">';

            if (!empty($company->phone)) {
                $html .= '<span class="company-phone"><i class="icon-phone"></i> ' . htmlspecialchars($company->phone) . '</span>';
            }

            if (!empty($company->email)) {
                $html .= '<span class="company-email"><i class="icon-envelope"></i> ' . htmlspecialchars($company->email) . '</span>';
            }

            if (!empty($company->website)) {
                $html .= '<span class="company-website"><i class="icon-link"></i> <a href="' . htmlspecialchars($company->website) . '" target="_blank">' . Text::_('PLG_DJCLASSIFIEDS_PREMIUMCOMPANIES_WEBSITE') . '</a></span>';
            }

            $html .= '</div>';
        }

        // Premium badge
        $html .= '<span class="premium-badge">' . Text::_('PLG_DJCLASSIFIEDS_PREMIUMCOMPANIES_PREMIUM_BADGE') . '</span>';

        $html .= '</div>'; // .company-info
        $html .= '</div>'; // .premium-company-card

        return $html;
    }

    /**
     * Get company URL
     *
     * @param   object  $company  Company object
     *
     * @return  string
     *
     * @since   1.0.0
     */
    protected function getCompanyUrl($company)
    {
        // Generate URL to company in J-Business Directory
        $itemId = $this->getJBusinessDirectoryMenuId();

        $url = 'index.php?option=com_jbusinessdirectory&view=company&id=' . (int) $company->id;

        if ($itemId) {
            $url .= '&Itemid=' . $itemId;
        }

        return \Joomla\CMS\Router\Route::_($url);
    }

    /**
     * Get J-Business Directory menu item ID
     *
     * @return  integer|null
     *
     * @since   1.0.0
     */
    protected function getJBusinessDirectoryMenuId()
    {
        $db = $this->db;
        $query = $db->getQuery(true);

        $query->select('id')
            ->from($db->quoteName('#__menu'))
            ->where($db->quoteName('link') . ' LIKE ' . $db->quote('%option=com_jbusinessdirectory%'))
            ->where($db->quoteName('published') . ' = 1')
            ->setLimit(1);

        $db->setQuery($query);

        try {
            return $db->loadResult();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Load plugin assets (CSS and JS)
     *
     * @return  void
     *
     * @since   1.0.0
     */
    protected function loadAssets()
    {
        $doc = Factory::getDocument();
        $pluginPath = Uri::root() . 'plugins/content/premiumcompanies/assets/';

        // Load CSS
        $doc->addStyleSheet($pluginPath . 'css/style.css');

        // Load custom CSS if provided
        $customCss = $this->params->get('custom_css', '');
        if (!empty($customCss)) {
            $doc->addStyleDeclaration($customCss);
        }

        // Load JS if needed
        // $doc->addScript($pluginPath . 'js/script.js');
    }
}
