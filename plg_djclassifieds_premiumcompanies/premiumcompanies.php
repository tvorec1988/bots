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
use Joomla\CMS\Router\Route;

/**
 * Premium Companies Plugin for DJ-Classifieds and J-Business Directory Integration
 *
 * Displays relevant companies from J-Business Directory AND ads from DJ-Classifieds
 * when adding orders. Premium companies/ads are shown first, with optional AI-powered matching.
 *
 * @since  2.0.0
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
     * Plugin that displays companies/ads before content
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
        if (!$this->isTargetPage()) {
            return '';
        }

        $position = $this->params->get('show_position', 'before');
        if ($position !== 'before') {
            return '';
        }

        return $this->displayResults($row);
    }

    /**
     * Plugin that displays companies/ads after content
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
        if (!$this->isTargetPage()) {
            return '';
        }

        $position = $this->params->get('show_position', 'before');
        if ($position !== 'after') {
            return '';
        }

        return $this->displayResults($row);
    }

    /**
     * Display companies and ads (premium first)
     *
     * @param   object  $item  The DJ-Classifieds item
     *
     * @return  string
     *
     * @since   2.0.0
     */
    protected function displayResults($item)
    {
        $this->loadAssets();

        $itemData = $this->extractItemData($item);

        // Get companies from J-Business Directory
        $companies = $this->getCompanies($itemData);

        // Get ads from DJ-Classifieds
        $ads = [];
        if ($this->params->get('search_djclassifieds', 1)) {
            $ads = $this->getDJClassifiedsAds($itemData);
        }

        // Combine results
        $allResults = $this->combineResults($companies, $ads);

        if (empty($allResults)) {
            return '';
        }

        // Use AI to rank if enabled
        if ($this->params->get('enable_chatgpt', 0) && !empty($itemData['text'])) {
            $allResults = $this->rankWithAI($allResults, $itemData);
        }

        return $this->renderResults($allResults);
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

        if (empty($targetUrls)) {
            return false;
        }

        $input = $this->app->input;
        $option = $input->get('option', '');
        $view = $input->get('view', '');

        $currentUrl = Uri::getInstance()->toString();
        $currentQuery = $_SERVER['QUERY_STRING'] ?? '';

        $targetUrlsArray = array_filter(array_map('trim', explode("\n", $targetUrls)));

        foreach ($targetUrlsArray as $targetUrl) {
            if (strpos($currentUrl, $targetUrl) !== false ||
                strpos($currentQuery, str_replace(['index.php?', '&amp;'], ['', '&'], $targetUrl)) !== false) {
                return true;
            }

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

        $input = $this->app->input;

        $data['title'] = $input->getString('name', '') ?: ($item->name ?? '');
        $data['description'] = $input->getString('description', '') ?: ($item->description ?? '');
        $data['category_id'] = $input->getInt('cat_id', 0) ?: ($item->cat_id ?? 0);

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

            $query->select('c.*,
                CASE
                    WHEN p.type = ' . $db->quote('premium') . ' AND (cp.expire_date IS NULL OR cp.expire_date >= ' . $db->quote(Factory::getDate()->toSql()) . ')
                    THEN 1
                    ELSE 0
                END as is_premium')
                ->from($db->quoteName('#__jbusinessdirectory_companies', 'c'))
                ->where($db->quoteName('c.published') . ' = 1')
                ->where($db->quoteName('c.approved') . ' = 1');

            $query->join('LEFT', $db->quoteName('#__jbusinessdirectory_company_package', 'cp') .
                ' ON ' . $db->quoteName('cp.companyId') . ' = ' . $db->quoteName('c.id'))
                ->join('LEFT', $db->quoteName('#__jbusinessdirectory_packages', 'p') .
                ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('cp.packageId'));

            if (!$showAllCompanies) {
                $query->where($db->quoteName('p.type') . ' = ' . $db->quote('premium'))
                    ->where('(' . $db->quoteName('cp.expire_date') . ' IS NULL OR ' .
                        $db->quoteName('cp.expire_date') . ' >= ' . $db->quote(Factory::getDate()->toSql()) . ')');
            }

            if ($matchByCategory && !empty($itemData['category_id'])) {
                $query->where($db->quoteName('c.main_category') . ' = ' . (int) $itemData['category_id']);
            }

            $query->order('is_premium DESC, ' . $db->quoteName('c.featured') . ' DESC, ' .
                $db->quoteName('c.created') . ' DESC');

            $query->setLimit($maxCompanies);

            $db->setQuery($query);
            $companies = $db->loadObjectList();

            // Mark as company type
            foreach ($companies as $company) {
                $company->item_type = 'company';
            }

            return $companies ?: [];
        } catch (Exception $e) {
            Factory::getApplication()->enqueueMessage(
                Text::sprintf('PLG_DJCLASSIFIEDS_PREMIUMCOMPANIES_ERROR', $e->getMessage()),
                'error'
            );
            return [];
        }
    }

    /**
     * Get ads from DJ-Classifieds (from specific categories like "Production Ads")
     *
     * @param   array  $itemData  Item data for filtering
     *
     * @return  array
     *
     * @since   2.0.0
     */
    protected function getDJClassifiedsAds($itemData)
    {
        try {
            $db = $this->db;
            $query = $db->getQuery(true);

            $maxAds = (int) $this->params->get('max_djclassifieds_ads', 5);
            $categoryIds = $this->params->get('djclassifieds_category_ids', '');

            if (empty($categoryIds)) {
                return [];
            }

            // Parse category IDs
            $catIds = array_filter(array_map('trim', explode(',', $categoryIds)));
            $catIds = array_map('intval', $catIds);

            if (empty($catIds)) {
                return [];
            }

            // Query DJ-Classifieds items
            $query->select('i.*, c.name as category_name,
                CASE
                    WHEN p.published = 1 AND (p.exp_days = 0 OR p.exp_days > DATEDIFF(NOW(), i.date_start))
                    THEN 1
                    ELSE 0
                END as is_promoted')
                ->from($db->quoteName('#__djcf_items', 'i'))
                ->join('LEFT', $db->quoteName('#__djcf_categories', 'c') .
                    ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('i.cat_id'))
                ->join('LEFT', $db->quoteName('#__djcf_promotions', 'p') .
                    ' ON ' . $db->quoteName('p.item_id') . ' = ' . $db->quoteName('i.id'))
                ->where($db->quoteName('i.published') . ' = 1')
                ->where($db->quoteName('i.cat_id') . ' IN (' . implode(',', $catIds) . ')');

            // Order by promoted first
            $query->order('is_promoted DESC, ' . $db->quoteName('i.date_start') . ' DESC');

            $query->setLimit($maxAds);

            $db->setQuery($query);
            $ads = $db->loadObjectList();

            // Mark as ad type and add is_premium flag
            foreach ($ads as $ad) {
                $ad->item_type = 'ad';
                $ad->is_premium = $ad->is_promoted; // Promoted ads treated as premium
            }

            return $ads ?: [];
        } catch (Exception $e) {
            Factory::getApplication()->enqueueMessage(
                Text::sprintf('PLG_DJCLASSIFIEDS_PREMIUMCOMPANIES_ERROR_ADS', $e->getMessage()),
                'warning'
            );
            return [];
        }
    }

    /**
     * Combine companies and ads (premium first)
     *
     * @param   array  $companies  Companies from J-Business Directory
     * @param   array  $ads        Ads from DJ-Classifieds
     *
     * @return  array
     *
     * @since   2.0.0
     */
    protected function combineResults($companies, $ads)
    {
        $combined = array_merge($companies, $ads);

        // Sort: premium/promoted first, then regular
        usort($combined, function($a, $b) {
            $aPremium = $a->is_premium ?? 0;
            $bPremium = $b->is_premium ?? 0;

            if ($aPremium != $bPremium) {
                return $bPremium - $aPremium; // Premium first
            }

            // Same premium status, sort by date
            $aDate = $a->created ?? $a->date_start ?? '';
            $bDate = $b->created ?? $b->date_start ?? '';

            return strcmp($bDate, $aDate); // Newer first
        });

        return $combined;
    }

    /**
     * Rank results using ChatGPT API
     *
     * @param   array  $results   Array of company/ad objects
     * @param   array  $itemData  Item data for context
     *
     * @return  array
     *
     * @since   2.0.0
     */
    protected function rankWithAI($results, $itemData)
    {
        $apiKey = $this->params->get('chatgpt_api_key', '');

        if (empty($apiKey) || empty($results)) {
            return $results;
        }

        try {
            $model = $this->params->get('chatgpt_model', 'gpt-4o-mini');
            $maxResults = (int) $this->params->get('ai_max_results', 5);

            $resultList = [];
            foreach ($results as $idx => $result) {
                $resultList[] = [
                    'id' => $idx,
                    'type' => $result->item_type,
                    'name' => $result->name ?? '',
                    'description' => strip_tags($result->description ?? $result->intro_desc ?? ''),
                    'is_premium' => $result->is_premium ?? 0
                ];
            }

            $prompt = $this->buildAIPrompt($itemData, $resultList, $maxResults);
            $response = $this->callOpenAI($apiKey, $model, $prompt);

            if ($response && isset($response['item_ids'])) {
                return $this->reorderByAI($results, $response['item_ids']);
            }

        } catch (Exception $e) {
            Factory::getApplication()->enqueueMessage(
                Text::sprintf('PLG_DJCLASSIFIEDS_PREMIUMCOMPANIES_AI_ERROR', $e->getMessage()),
                'warning'
            );
        }

        return $results;
    }

    /**
     * Build AI prompt for ranking
     *
     * @param   array  $itemData    Item data
     * @param   array  $resultList  Result list
     * @param   int    $maxResults  Max results to return
     *
     * @return  string
     *
     * @since   2.0.0
     */
    protected function buildAIPrompt($itemData, $resultList, $maxResults)
    {
        $prompt = "You are helping to match relevant companies and production ads with a service request.\n\n";
        $prompt .= "Service Request Information:\n";
        $prompt .= "Title: " . $itemData['title'] . "\n";
        $prompt .= "Description: " . $itemData['description'] . "\n\n";

        $prompt .= "Available Companies and Ads:\n";
        $prompt .= json_encode($resultList, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

        $prompt .= "Instructions:\n";
        $prompt .= "1. Analyze the service request and identify what products/services the user needs\n";
        $prompt .= "2. Match companies/ads that would be most relevant\n";
        $prompt .= "3. Prioritize premium/promoted items (is_premium=1) when relevance is similar\n";
        $prompt .= "4. Return the top {$maxResults} most relevant item IDs\n\n";

        $prompt .= "Return ONLY a JSON object with this format:\n";
        $prompt .= '{"item_ids": [0, 3, 1], "reasoning": "brief explanation"}';

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
                    'content' => 'You are a helpful assistant that matches businesses and production ads with service requests. Always respond with valid JSON.'
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
     * Reorder results based on AI recommendations
     *
     * @param   array  $results  Original results array
     * @param   array  $itemIds  AI recommended order
     *
     * @return  array
     *
     * @since   2.0.0
     */
    protected function reorderByAI($results, $itemIds)
    {
        $reordered = [];

        foreach ($itemIds as $id) {
            if (isset($results[$id])) {
                $reordered[] = $results[$id];
            }
        }

        foreach ($results as $idx => $result) {
            if (!in_array($idx, $itemIds)) {
                $reordered[] = $result;
            }
        }

        return $reordered;
    }

    /**
     * Render results HTML (companies + ads) with UIkit styling
     *
     * @param   array  $results  Array of company/ad objects
     *
     * @return  string
     *
     * @since   2.0.0
     */
    protected function renderResults($results)
    {
        $displayStyle = $this->params->get('display_style', 'cards');
        $showLogo = $this->params->get('show_logo', 1);

        // Inline mode for form integration - minimal styling
        if ($displayStyle === 'inline') {
            $html = '<div class="premium-companies-inline uk-margin-medium-top uk-margin-medium-bottom">';
            $html .= '<div class="uk-card uk-card-default uk-card-body uk-card-small">';
            $html .= '<h4 class="uk-card-title uk-text-primary"><span uk-icon="icon: star; ratio: 0.9"></span> ' . Text::_('PLG_DJCLASSIFIEDS_PREMIUMCOMPANIES_TITLE') . '</h4>';
            $html .= '<div class="uk-grid-small uk-child-width-1-2@s" uk-grid>';

            foreach ($results as $result) {
                $html .= $this->renderResultCard($result, $displayStyle, $showLogo);
            }

            $html .= '</div>'; // grid
            $html .= '</div>'; // card
            $html .= '</div>'; // container

            return $html;
        }

        // Regular mode with full section
        $html = '<div class="uk-section uk-section-muted uk-section-small premium-companies-container">';
        $html .= '<div class="uk-container">';
        $html .= '<h3 class="uk-heading-line uk-text-center"><span>' . Text::_('PLG_DJCLASSIFIEDS_PREMIUMCOMPANIES_TITLE') . '</span></h3>';

        if ($displayStyle === 'cards') {
            $html .= '<div class="uk-grid-match uk-child-width-1-2@s uk-child-width-1-3@m" uk-grid>';
        } else {
            $html .= '<div class="uk-grid-small" uk-grid>';
        }

        foreach ($results as $result) {
            $html .= $this->renderResultCard($result, $displayStyle, $showLogo);
        }

        $html .= '</div>'; // grid
        $html .= '</div>'; // container
        $html .= '</div>'; // section

        return $html;
    }

    /**
     * Render individual result card (company or ad) with UIkit
     *
     * @param   object   $result        Company or ad object
     * @param   string   $displayStyle  Display style
     * @param   boolean  $showLogo      Show logo
     *
     * @return  string
     *
     * @since   2.0.0
     */
    protected function renderResultCard($result, $displayStyle, $showLogo)
    {
        $isCompany = ($result->item_type === 'company');
        $isPremium = !empty($result->is_premium);
        $resultUrl = $this->getResultUrl($result);

        // Inline mode - ultra compact for form integration
        if ($displayStyle === 'inline') {
            $html = '<div>';
            $html .= '<div class="uk-card uk-card-default uk-card-hover uk-card-small" style="padding: 12px;">';

            // Title with premium indicator
            $html .= '<div class="uk-flex uk-flex-between uk-flex-middle">';
            $html .= '<h5 class="uk-margin-remove">';
            if ($isPremium) {
                $html .= '<span uk-icon="icon: star; ratio: 0.7" class="uk-text-warning"></span> ';
            }
            $html .= '<a href="' . $resultUrl . '" class="uk-link-reset" target="_blank" rel="noopener noreferrer">' . htmlspecialchars($result->name) . '</a>';
            $html .= '</h5>';
            $html .= '</div>';

            // Type label + button
            $html .= '<div class="uk-flex uk-flex-between uk-flex-middle uk-margin-small-top">';
            $html .= '<span class="uk-text-meta uk-text-small">';
            $html .= '<span uk-icon="icon: ' . ($isCompany ? 'home' : 'tag') . '; ratio: 0.7"></span> ';
            $html .= $isCompany ? Text::_('PLG_DJCLASSIFIEDS_PREMIUMCOMPANIES_TYPE_COMPANY') : Text::_('PLG_DJCLASSIFIEDS_PREMIUMCOMPANIES_TYPE_AD');
            $html .= '</span>';
            $html .= '<a href="' . $resultUrl . '" class="uk-button uk-button-text uk-button-small" target="_blank" rel="noopener noreferrer">';
            $html .= Text::_('PLG_DJCLASSIFIEDS_PREMIUMCOMPANIES_VIEW_DETAILS');
            $html .= '</a>';
            $html .= '</div>';

            $html .= '</div>'; // card
            $html .= '</div>'; // div wrapper
            return $html;
        }

        // Regular modes
        $html = '<div>';
        $html .= '<div class="uk-card uk-card-default uk-card-hover uk-card-body uk-card-small">';

        // Premium badge
        if ($isPremium) {
            $html .= '<div class="uk-card-badge uk-label uk-label-warning">';
            $html .= $isCompany ? Text::_('PLG_DJCLASSIFIEDS_PREMIUMCOMPANIES_PREMIUM_BADGE') : Text::_('PLG_DJCLASSIFIEDS_PREMIUMCOMPANIES_PROMOTED_BADGE');
            $html .= '</div>';
        }

        // Logo/Image
        if ($showLogo) {
            $imageUrl = $this->getResultImage($result);
            if ($imageUrl) {
                $html .= '<div class="uk-card-media-top">';
                $html .= '<img src="' . htmlspecialchars($imageUrl) . '" alt="' . htmlspecialchars($result->name) . '" class="uk-border-rounded">';
                $html .= '</div>';
            }
        }

        // Title
        $html .= '<h4 class="uk-card-title uk-margin-small-bottom">';
        $html .= '<a href="' . $resultUrl . '" class="uk-link-reset" target="_blank" rel="noopener noreferrer">' . htmlspecialchars($result->name) . '</a>';
        $html .= '</h4>';

        // Type label
        $html .= '<p class="uk-text-meta uk-margin-remove-top">';
        $html .= '<span uk-icon="icon: ' . ($isCompany ? 'home' : 'tag') . '; ratio: 0.8"></span> ';
        $html .= $isCompany ? Text::_('PLG_DJCLASSIFIEDS_PREMIUMCOMPANIES_TYPE_COMPANY') : Text::_('PLG_DJCLASSIFIEDS_PREMIUMCOMPANIES_TYPE_AD');
        $html .= '</p>';

        // Description
        $description = $this->getResultDescription($result);
        if ($description) {
            $html .= '<p class="uk-text-small">' . htmlspecialchars($description) . '</p>';
        }

        // Contact info for companies
        if ($isCompany && $displayStyle !== 'compact' && $displayStyle !== 'inline') {
            $html .= '<div class="uk-margin-small-top">';

            if (!empty($result->phone)) {
                $html .= '<div class="uk-text-small"><span uk-icon="icon: receiver; ratio: 0.8"></span> ' . htmlspecialchars($result->phone) . '</div>';
            }

            if (!empty($result->website)) {
                $html .= '<div class="uk-text-small"><span uk-icon="icon: world; ratio: 0.8"></span> <a href="' . htmlspecialchars($result->website) . '" target="_blank" rel="noopener noreferrer" class="uk-link-muted">' . Text::_('PLG_DJCLASSIFIEDS_PREMIUMCOMPANIES_WEBSITE') . '</a></div>';
            }

            $html .= '</div>';
        }

        // View button
        $html .= '<div class="uk-margin-small-top">';
        $html .= '<a href="' . $resultUrl . '" class="uk-button uk-button-text" target="_blank" rel="noopener noreferrer">' . Text::_('PLG_DJCLASSIFIEDS_PREMIUMCOMPANIES_VIEW_DETAILS') . ' <span uk-icon="icon: arrow-right; ratio: 0.8"></span></a>';
        $html .= '</div>';

        $html .= '</div>'; // card-body
        $html .= '</div>'; // div wrapper
        return $html;
    }

    /**
     * Get result image URL
     *
     * @param   object  $result  Company or ad object
     *
     * @return  string|null
     *
     * @since   2.0.0
     */
    protected function getResultImage($result)
    {
        if ($result->item_type === 'company') {
            return !empty($result->logo_location) ? Uri::root() . $result->logo_location : null;
        } else {
            // For DJ-Classifieds ads, get first image
            if (!empty($result->image_url)) {
                return $result->image_url;
            }

            // Try to get from images table
            try {
                $db = $this->db;
                $query = $db->getQuery(true);
                $query->select('name')
                    ->from($db->quoteName('#__djcf_images'))
                    ->where($db->quoteName('item_id') . ' = ' . (int) $result->id)
                    ->where($db->quoteName('type') . ' = ' . $db->quote('item'))
                    ->order('ordering ASC')
                    ->setLimit(1);

                $db->setQuery($query);
                $imageName = $db->loadResult();

                if ($imageName) {
                    return Uri::root() . 'images/com_djclassifieds/' . $imageName;
                }
            } catch (Exception $e) {
                // Ignore errors
            }
        }

        return null;
    }

    /**
     * Get result description
     *
     * @param   object  $result  Company or ad object
     *
     * @return  string
     *
     * @since   2.0.0
     */
    protected function getResultDescription($result)
    {
        $description = '';

        if ($result->item_type === 'company') {
            $description = strip_tags($result->description ?? '');
        } else {
            $description = strip_tags($result->intro_desc ?? $result->description ?? '');
        }

        if (mb_strlen($description) > 120) {
            $description = mb_substr($description, 0, 120) . '...';
        }

        return $description;
    }

    /**
     * Get result URL
     *
     * @param   object  $result  Company or ad object
     *
     * @return  string
     *
     * @since   2.0.0
     */
    protected function getResultUrl($result)
    {
        if ($result->item_type === 'company') {
            $itemId = $this->getJBusinessDirectoryMenuId();
            $url = 'index.php?option=com_jbusinessdirectory&view=company&id=' . (int) $result->id;
            if ($itemId) {
                $url .= '&Itemid=' . $itemId;
            }
            return Route::_($url);
        } else {
            // DJ-Classifieds ad
            $url = 'index.php?option=com_djclassifieds&view=item&cid=' . (int) $result->cat_id . '&id=' . (int) $result->id;
            if (!empty($result->alias)) {
                $url .= ':' . $result->alias;
            }
            return Route::_($url);
        }
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
        $doc->addStyleSheet($pluginPath . 'css/style-uikit.css');

        // Load custom CSS if provided
        $customCss = $this->params->get('custom_css', '');
        if (!empty($customCss)) {
            $doc->addStyleDeclaration($customCss);
        }
    }
}
