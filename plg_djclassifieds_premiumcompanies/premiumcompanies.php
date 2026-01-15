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
use Joomla\CMS\Http\HttpFactory;

/**
 * Premium Companies Plugin for DJ-Classifieds and J-Business Directory Integration
 *
 * Displays relevant companies from J-Business Directory when adding orders in DJ-Classifieds.
 * Premium companies are shown first, with optional AI-powered relevance matching.
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
     * Plugin that displays companies before content
     *
     * @param   string   $context  The context of the content
     * @param   object   &$row     The content object
     * @param   mixed    &$params  The params
     * @param   integer  $page     The page number
     *
     * @return  mixed
     *
     * @since   1.0.0
     */
    public function onContentBeforeDisplay($context, &$row, &$params, $page = 0)
    {
        // Check if we're on the target page
        if (!$this->isTargetPage()) {
            return '';
        }

        // Check position setting
        $position = $this->params->get('show_position', 'before');
        if ($position !== 'before') {
            return '';
        }

        return $this->displayCompanies($row);
    }

    /**
     * Plugin that displays companies after content
     *
     * @param   string   $context  The context of the content
     * @param   object   &$row     The content object
     * @param   mixed    &$params  The params
     * @param   integer  $page     The page number
     *
     * @return  mixed
     *
     * @since   1.0.0
     */
    public function onContentAfterDisplay($context, &$row, &$params, $page = 0)
    {
        // Check if we're on the target page
        if (!$this->isTargetPage()) {
            return '';
        }

        // Check position setting
        $position = $this->params->get('show_position', 'before');
        if ($position !== 'after') {
            return '';
        }

        return $this->displayCompanies($row);
    }

    /**
     * Display companies with premium first
     *
     * @param   object  $item  The DJ-Classifieds item
     *
     * @return  string
     *
     * @since   1.0.0
     */
    protected function displayCompanies($item)
    {
        // Load CSS and JS
        $this->loadAssets();

        // Get item data for AI analysis
        $itemData = $this->extractItemData($item);

        // Get companies (premium first, then others)
        $companies = $this->getCompanies($itemData);

        if (empty($companies)) {
            return '';
        }

        // Use AI to rank/filter if enabled
        if ($this->params->get('enable_chatgpt', 0) && !empty($itemData['text'])) {
            $companies = $this->rankCompaniesWithAI($companies, $itemData);
        }

        // Generate HTML output
        return $this->renderCompanies($companies);
    }

    /**
     * Check if current page is in target URLs
     *
     * @return  boolean
     *
     * @since   1.0.0
     */
    protected function isTargetPage()
    {
        $targetUrls = $this->params->get('target_urls', '');

        // If no target URLs specified, don't show anywhere
        if (empty($targetUrls)) {
            return false;
        }

        // Get current URL components
        $input = $this->app->input;
        $option = $input->get('option', '');
        $view = $input->get('view', '');
        $task = $input->get('task', '');

        // Build current URL pattern
        $currentUrl = Uri::getInstance()->toString();
        $currentPath = parse_url($currentUrl, PHP_URL_PATH);
        $currentQuery = $_SERVER['QUERY_STRING'] ?? '';

        // Split target URLs by line
        $targetUrlsArray = array_filter(array_map('trim', explode("\n", $targetUrls)));

        foreach ($targetUrlsArray as $targetUrl) {
            // Check if target URL matches current URL
            if (strpos($currentUrl, $targetUrl) !== false ||
                strpos($currentQuery, str_replace(['index.php?', '&amp;'], ['', '&'], $targetUrl)) !== false) {
                return true;
            }

            // Check option and view match
            if (strpos($targetUrl, 'option=' . $option) !== false &&
                (empty($view) || strpos($targetUrl, 'view=' . $view) !== false)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract item data for analysis
     *
     * @param   object  $item  The item object
     *
     * @return  array
     *
     * @since   1.0.0
     */
    protected function extractItemData($item)
    {
        $data = [
            'text' => '',
            'category_id' => null,
            'title' => '',
            'description' => ''
        ];

        // Try to get data from input (form submission)
        $input = $this->app->input;

        $data['title'] = $input->getString('name', '') ?: ($item->name ?? '');
        $data['description'] = $input->getString('description', '') ?: ($item->description ?? '');
        $data['category_id'] = $input->getInt('cat_id', 0) ?: ($item->cat_id ?? 0);

        // Combine text for AI analysis
        $data['text'] = trim($data['title'] . ' ' . strip_tags($data['description']));

        return $data;
    }

    /**
     * Get companies from J-Business Directory (premium first, then all others)
     *
     * @param   array  $itemData  Item data for filtering
     *
     * @return  array
     *
     * @since   1.0.0
     */
    protected function getCompanies($itemData)
    {
        try {
            $db = $this->db;
            $query = $db->getQuery(true);
            $maxCompanies = (int) $this->params->get('max_companies', 10);
            $showAllCompanies = $this->params->get('show_all_companies', 1);
            $matchByCategory = $this->params->get('match_by_category', 1);

            // Build base query
            $query->select('c.*,
                CASE
                    WHEN p.type = ' . $db->quote('premium') . ' AND (cp.expire_date IS NULL OR cp.expire_date >= ' . $db->quote(Factory::getDate()->toSql()) . ')
                    THEN 1
                    ELSE 0
                END as is_premium')
                ->from($db->quoteName('#__jbusinessdirectory_companies', 'c'))
                ->where($db->quoteName('c.published') . ' = 1')
                ->where($db->quoteName('c.approved') . ' = 1');

            // Left join with packages to identify premium companies
            $query->join('LEFT', $db->quoteName('#__jbusinessdirectory_company_package', 'cp') .
                ' ON ' . $db->quoteName('cp.companyId') . ' = ' . $db->quoteName('c.id'))
                ->join('LEFT', $db->quoteName('#__jbusinessdirectory_packages', 'p') .
                ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('cp.packageId'));

            // If show only premium
            if (!$showAllCompanies) {
                $query->where($db->quoteName('p.type') . ' = ' . $db->quote('premium'))
                    ->where('(' . $db->quoteName('cp.expire_date') . ' IS NULL OR ' .
                        $db->quoteName('cp.expire_date') . ' >= ' . $db->quote(Factory::getDate()->toSql()) . ')');
            }

            // Match by category if enabled and category is provided
            if ($matchByCategory && !empty($itemData['category_id'])) {
                $query->where($db->quoteName('c.main_category') . ' = ' . (int) $itemData['category_id']);
            }

            // Order by: premium first, then featured, then by date
            $query->order('is_premium DESC, ' . $db->quoteName('c.featured') . ' DESC, ' .
                $db->quoteName('c.created') . ' DESC');

            // Limit results
            $query->setLimit($maxCompanies);

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
     * Rank companies using ChatGPT API
     *
     * @param   array  $companies  Array of company objects
     * @param   array  $itemData   Item data for context
     *
     * @return  array
     *
     * @since   1.0.0
     */
    protected function rankCompaniesWithAI($companies, $itemData)
    {
        $apiKey = $this->params->get('chatgpt_api_key', '');

        if (empty($apiKey) || empty($companies)) {
            return $companies;
        }

        try {
            $model = $this->params->get('chatgpt_model', 'gpt-4o-mini');
            $maxResults = (int) $this->params->get('ai_max_results', 5);

            // Prepare company data for AI
            $companyList = [];
            foreach ($companies as $idx => $company) {
                $companyList[] = [
                    'id' => $idx,
                    'name' => $company->name,
                    'description' => strip_tags($company->description ?? ''),
                    'is_premium' => $company->is_premium ?? 0
                ];
            }

            // Create prompt for ChatGPT
            $prompt = $this->buildAIPrompt($itemData, $companyList, $maxResults);

            // Call OpenAI API
            $response = $this->callOpenAI($apiKey, $model, $prompt);

            if ($response && isset($response['company_ids'])) {
                // Reorder companies based on AI recommendations
                return $this->reorderCompaniesByAI($companies, $response['company_ids']);
            }

        } catch (Exception $e) {
            // Log error but continue with original order
            Factory::getApplication()->enqueueMessage(
                Text::sprintf('PLG_DJCLASSIFIEDS_PREMIUMCOMPANIES_AI_ERROR', $e->getMessage()),
                'warning'
            );
        }

        return $companies;
    }

    /**
     * Build AI prompt for company ranking
     *
     * @param   array  $itemData      Item data
     * @param   array  $companyList   Company list
     * @param   int    $maxResults    Max results to return
     *
     * @return  string
     *
     * @since   1.0.0
     */
    protected function buildAIPrompt($itemData, $companyList, $maxResults)
    {
        $prompt = "You are helping to match relevant companies with a classified ad.\n\n";
        $prompt .= "Classified Ad Information:\n";
        $prompt .= "Title: " . $itemData['title'] . "\n";
        $prompt .= "Description: " . $itemData['description'] . "\n\n";

        $prompt .= "Available Companies:\n";
        $prompt .= json_encode($companyList, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

        $prompt .= "Instructions:\n";
        $prompt .= "1. Analyze the classified ad and identify what services/products the user might need\n";
        $prompt .= "2. Match companies that would be most relevant to help with this ad\n";
        $prompt .= "3. Prioritize premium companies (is_premium=1) when relevance is similar\n";
        $prompt .= "4. Return the top {$maxResults} most relevant company IDs\n\n";

        $prompt .= "Return ONLY a JSON object with this format:\n";
        $prompt .= '{"company_ids": [0, 3, 1], "reasoning": "brief explanation"}';

        return $prompt;
    }

    /**
     * Call OpenAI API
     *
     * @param   string  $apiKey  API key
     * @param   string  $model   Model name
     * @param   string  $prompt  Prompt text
     *
     * @return  array|null
     *
     * @since   1.0.0
     */
    protected function callOpenAI($apiKey, $model, $prompt)
    {
        $url = 'https://api.openai.com/v1/chat/completions';

        $data = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a helpful assistant that matches businesses with relevant classified ads. Always respond with valid JSON.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.7,
            'max_tokens' => 500,
            'response_format' => ['type' => 'json_object']
        ];

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ];

        // Use Joomla HTTP client
        try {
            $http = HttpFactory::getHttp();
            $response = $http->post($url, json_encode($data), $headers, 30);

            if ($response->code === 200) {
                $result = json_decode($response->body, true);

                if (isset($result['choices'][0]['message']['content'])) {
                    $content = json_decode($result['choices'][0]['message']['content'], true);
                    return $content;
                }
            }
        } catch (Exception $e) {
            throw new Exception('OpenAI API Error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Reorder companies based on AI recommendations
     *
     * @param   array  $companies    Original companies array
     * @param   array  $companyIds   AI recommended order
     *
     * @return  array
     *
     * @since   1.0.0
     */
    protected function reorderCompaniesByAI($companies, $companyIds)
    {
        $reordered = [];

        foreach ($companyIds as $id) {
            if (isset($companies[$id])) {
                $reordered[] = $companies[$id];
            }
        }

        // Add remaining companies that weren't selected by AI
        foreach ($companies as $idx => $company) {
            if (!in_array($idx, $companyIds)) {
                $reordered[] = $company;
            }
        }

        return $reordered;
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

        // Premium badge (only for premium companies)
        if (!empty($company->is_premium)) {
            $html .= '<span class="premium-badge">' . Text::_('PLG_DJCLASSIFIEDS_PREMIUMCOMPANIES_PREMIUM_BADGE') . '</span>';
        }

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

        // Load JS
        $doc->addScript($pluginPath . 'js/script.js');
    }
}
