<?php
namespace Axist\AxKirchenplaner\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\SystemResource\Publishing\SystemResourcePublisherInterface;
use TYPO3\CMS\Core\SystemResource\Publishing\UriGenerationOptions;
use TYPO3\CMS\Core\SystemResource\SystemResourceFactory;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * KirchenplanerController
 */
class KirchenplanerController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

    public function __construct(
        protected readonly SystemResourceFactory $systemResourceFactory,
        protected readonly SystemResourcePublisherInterface $systemResourcePublisher,
    ) {}

	public function showAction(): ResponseInterface {
        $request = $this->request;
        $queryParams = $request->getQueryParams();
        $contentObject = $request->getAttribute('currentContentObject');
        $contentObjectData = is_object($contentObject) && isset($contentObject->data)
            ? $contentObject->data
            : [];
        $normalizedParams = $request->getAttribute('normalizedParams');
        $requestUrl = $normalizedParams->getSiteUrl();
        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
            //, false, stream_context_create($arrContextOptions)
        );
        $pageRenderer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Page\PageRenderer::class);
        $pageRenderer->addHeaderData('<script>var kirchenplaner_config = [];</script>');
        //$pageRenderer->setHtmlTag('<html lang="de" ng-app="kirchenPlanerApp">');
        $extkey = 'axkirchenplaner';
        $extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
        )->get($extkey);

        $resourceFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Resource\ResourceFactory:: class
        );

        $apiKey = $extensionConfiguration['apiKey'] ?? '';
        $apiUrl = parse_url($extensionConfiguration['apiUrl'] ?? '');
        $apiUrl = $apiUrl['scheme']."://".$apiUrl['host'];
        $userGroup = explode("|",$extensionConfiguration['userGroup'] ?? '');
        $userGroup = $userGroup[0];
        $appScriptUri = (string)$this->systemResourcePublisher->generateUri(
            $this->systemResourceFactory->createPublicResource('EXT:axkirchenplaner/Resources/Public/Angular/app.js'),
            $request,
            new UriGenerationOptions(absoluteUri: true),
        );

        // $appScriptUri has a timestamp as a query parameter (e.g. ...?1790592247), so we need to remove it first.
        $publicResourcesArray = explode('?', $appScriptUri);
        $appScriptUri = $publicResourcesArray[0];

        $public_resources = substr($appScriptUri, 0, -strlen('/Angular/app.js'));

        //Add CSS Library Files
        if ($extensionConfiguration['fontAwesomeCss']) {
            $pageRenderer->addCssFile($public_resources.'/FontAwesome/css/font-awesome.min.css', 'stylesheet', 'all', '', false, false);
        }
        $pageRenderer->addCssFile($public_resources.'/Bootstrap4/css/glyphicons.css', 'stylesheet', 'all', '', false, false);
        $pageRenderer->addCssFile($public_resources.'/Css/kirchenplaner.css', 'stylesheet', 'all', '', false, false);

        //Add JS Library Files
        $pageRenderer->addJsFile($public_resources.'/Angular/libs/angular.min.js', 'text/javascript', false, false);
        $pageRenderer->addJsFile($public_resources.'/Angular/libs/angular-locale_de-de.js', 'text/javascript', false, false);
        $pageRenderer->addJsFile($public_resources.'/Angular/libs/angular-ui-bootstrap.min.js', 'text/javascript', false, false);
        $pageRenderer->addJsFile($public_resources.'/Angular/libs/angular-filter.min.js', 'text/javascript', false, false);
        $pageRenderer->addJsFile($public_resources.'/Angular/libs/angular-animate.min.js', 'text/javascript', false, false);
        $pageRenderer->addJsFile($public_resources.'/Angular/libs/angular-scroll.min.js', 'text/javascript', false, false);
        $pageRenderer->addJsFile($public_resources.'/Angular/libs/underscore.min.js', 'text/javascript', false, false);
        $pageRenderer->addJsFile($public_resources.'/Angular/app.js', 'text/javascript', false, false);
        $pageRenderer->addJsFile($public_resources.'/Angular/Controller/kirchenPlaner.js', false, false);
        $pageRenderer->addJsFile($public_resources.'/Angular/Controller/kirchenPlanerModal.js', false, false);

        $page_type = $this->settings['pageconfig']['seitentyp'];
        $html_template = $public_resources . '/Angular/templates/defaultListing.html';
        if($page_type == 'details'){
            $html_template = $public_resources . '/Angular/templates/defaultDetail.html';
        }
        if($page_type == 'suche'){
            $html_template = $public_resources . '/Angular/templates/defaultSearch.html';
        }
        if(isset($this->settings['pageconfig']['templatedatei']) && $this->settings['pageconfig']['templatedatei'] !== '' && $this->settings['pageconfig']['templatedatei'] !== '0'){
            $html_template_file_id = $this->settings['pageconfig']['templatedatei'];
            $html_template = $resourceFactory->getFileObject($html_template_file_id);
            $html_template = $requestUrl . $html_template->getPublicUrl();
        }

        if(isset($this->settings['pageconfig']['cssdatei']) && $this->settings['pageconfig']['cssdatei'] !== '' && $this->settings['pageconfig']['cssdatei'] !== '0'){
            $css_file_id = $this->settings['pageconfig']['cssdatei'];
            $css_file = $resourceFactory->getFileObject($css_file_id);
            $css_file = $requestUrl . $css_file->getPublicUrl();
            $pageRenderer->addCssFile($css_file, 'stylesheet', 'all', '', false, false);
        }

        if($this->settings['pageconfig']['framework'] == "bootstrap3"){
            $pageRenderer->addCssFile($public_resources.'/Bootstrap3/css/bootstrap.min.css', 'stylesheet', 'all', '', false, true);
            $pageRenderer->addJsFile($public_resources.'/Bootstrap3/js/bootstrap.min.js', 'text/javascript', false, false);
        }

        if($this->settings['pageconfig']['framework'] == "bootstrap4"){
            $pageRenderer->addCssFile($public_resources.'/Bootstrap4/css/glyphicons.css', 'stylesheet', 'all', '', false, true);
            $pageRenderer->addCssFile($public_resources.'/Bootstrap4/css/bootstrap.min.css', 'stylesheet', 'all', '', false, true);
            $pageRenderer->addJsFile($public_resources.'/Bootstrap4/js/bootstrap.min.js', 'text/javascript', false, false);
        }

        $detail_link_type = $this->settings['pageconfig']['detaillinktyp'] ?? '';

        $details_page_id = $this->settings['pageconfig']['detailseite'] ?? '';

        if (isset($details_page_id) && $details_page_id !== '') {
            $details_page = $this->uriBuilder->reset()
                ->setTargetPageUid(($details_page_id))
                ->setCreateAbsoluteUri(TRUE)
                ->build();
        } else {
            $details_page = false;
        }
        $format = $this->settings['query']['format'] ?? '';
        $topics_list = $this->settings['query']['rubriken'] ?? '';
        $target_audience_list = $this->settings['query']['zielgruppen'] ?? '';
        $locations_list = $this->settings['query']['orte'] ?? '';
        $items_list = $this->settings['query']['termine'] ?? '';
        $start_date = $this->settings['query']['startdatum'] ?? '';
        $end_date = $this->settings['query']['enddatum'] ?? '';
        $days_to_fetch = $this->settings['query']['abruftage'] ?? '';
        $override_communities = $this->settings['query']['overridegemeinden'] ?? '';
        $communities = $override_communities ?: ($this->settings['query']['gemeinden'] ?? '');
        $include_sub_communities = $this->settings['query']['unterorganisationen'] ?? '';
        $items_per_page = $this->settings['pageconfig']['eintraegeproseite'] ?? '';
        $holidays = $this->settings['pageconfig']['feiertage'] ?? '';
        $groupItems = $this->settings['pageconfig']['datumgruppieren'] ?? '';
        $borderColors = $this->settings['pageconfig']['randfarben'] ?? '';
        $showPagination = $this->settings['pageconfig']['paginierungzeigen'] ?? '';
        $additional_items = $this->settings['pageconfig']['zusatztermine'] ?? '';

        $date_override = (string)($queryParams['datum'] ?? '');

        $display_search = $this->settings['search']['anzeigen'] ?? '';
        $search_text_search = $this->settings['search']['textsuche'] ?? '';
        $search_text_search_value = (string)($queryParams['s'] ?? '');
        $search_categories = explode(',',$this->settings['search']['kategorien'] ?? '');
        $search_categories_raw = file_get_contents($apiUrl."/api/themen", false, stream_context_create($arrContextOptions));
        //Explode String into Array seperated by the newline control char<
        $search_categories_array = explode("\n",$search_categories_raw);
        //Create empty array for storage in FlexForm style
        $search_categories_list = array();
        //Loop over exploded array to separate values and labels
        foreach ($search_categories_array as $t) {
            //Explode Topic String with "|" as a delimiter into Array with Label at [0] and Value at [1]
            $label_and_value = explode("|", $t);
            if (!isset($label_and_value[1])) {
                continue;
            }
            if(in_array($label_and_value[1],$search_categories)){
                $topic = array();
                $topic['id'] = $label_and_value[1];
                $topic['name'] = $label_and_value[0];
                //Push into storage array
                if($topic['id'] != null ){
                    $search_categories_list[] = $topic;
                }
            }
        }
        $search_category_value = (string)($queryParams['kategorie'] ?? '');

        $search_target_audiences = explode(',',$this->settings['search']['zielgruppen'] ?? '');
        $search_target_audiences_raw = file_get_contents($apiUrl."/api/zielgruppen", false, stream_context_create($arrContextOptions));
        //Explode String into Array seperated by the newline control char
        $search_target_audiences_array = explode("\n",$search_target_audiences_raw);
        //Create empty array for storage in FlexForm style
        $search_target_audiences_list = array();
        //Loop over exploded array to separate values and labels
        foreach ($search_target_audiences_array as $t) {
            //Explode Topic String with "|" as a delimiter into Array with Label at [0] and Value at [1]
            $label_and_value = explode("|", $t);
            if (!isset($label_and_value[1])) {
                continue;
            }
            if(in_array($label_and_value[1],$search_target_audiences)){
                $topic = array();
                $topic['id'] = $label_and_value[1];
                $topic['name'] = $label_and_value[0];
                //Push into storage array
                if($topic['id'] != null ){
                    $search_target_audiences_list[] = $topic;
                }
            }
        }
        $search_target_audience_value = (string)($queryParams['zielgruppe'] ?? '');

        $search_locations = explode(',',$this->settings['search']['orte'] ?? '');
        $search_locations_raw = html_entity_decode(file_get_contents($apiUrl.'/api/raeume/{"key":"'.$apiKey.'"}', false, stream_context_create($arrContextOptions)));
        //Explode String into Array seperated by the newline control char
        $search_locations_array = explode("\n",$search_locations_raw);
        //Create empty array for storage in FlexForm style
        $search_locations_list = array();
        //Loop over exploded array to separate values and labels
        foreach ($search_locations_array as $t) {
            //Explode Topic String with "|" as a delimiter into Array with Label at [0] and Value at [1]
            $label_and_value = explode("|", $t);
            if (!isset($label_and_value[1])) {
                continue;
            }
            if(in_array($label_and_value[1],$search_locations)){
                $location = array();
                $location['id'] = $label_and_value[1];
                $location['name'] = $label_and_value[0];
                //Push into storage array
                if($location['id'] != null ){
                    $search_locations_list[] = $location;
                }
            }
        }

        $search_location_value = (string)($queryParams['ort'] ?? '');

        $search_communities = explode(',',$this->settings['search']['gemeinden'] ?? '');
        $search_communities_raw = html_entity_decode(file_get_contents($apiUrl.'/api/organisationen/{"key":"'.$apiKey.'"}', false, stream_context_create($arrContextOptions)));
        //Explode String into Array seperated by the newline control char
        $search_communities_array = json_decode(html_entity_decode($search_communities_raw));
        //Create empty array for storage in FlexForm style
        $search_communities_list = array();
        //Loop over exploded array to separate values and labels
        foreach ($search_communities_array as $t) {
            //Explode Topic String with "|" as a delimiter into Array with Label at [0] and Value at [1]
            if(in_array($t[1],$search_communities)){
                $community = array();
                $community['id'] = $t[1];
                $community['name'] = $t[0];
                //Push into storage array
                if($community['id'] != null ){
                    $search_communities_list[] = $community;
                }
            }
        }

        $override_search_communities = explode(',',$this->settings['search']['overridegemeinden'] ?? '');
        $override_search_communities_list = array();

        foreach ($override_search_communities as $o){
            $split = explode('|', $o);
            if (!isset($split[1])) {
                continue;
            }
            $community = array();
            $community['id'] = $split[0];
            $community['name'] = $split[1];
            $override_search_communities_list[] = $community;
        }
        if (!empty($this->settings['search']['overridegemeinden'])){
           $search_communities_list = $override_search_communities_list;
        }

        $search_community_value = (string)($queryParams['gemeinde'] ?? '');

        $search_calendar = $this->settings['search']['kalender'] ?? '';
        $search_past = $this->settings['search']['vergangenheit'] ?? '';

        $this->view->assign('uid', $contentObjectData['uid'] ?? 0);
        $this->view->assign('apiKey', $apiKey);
        $this->view->assign('apiUrl', $apiUrl);
        $this->view->assign('userGroup', $userGroup);
        $this->view->assign('topics', $topics_list);
        $this->view->assign('locations', $locations_list);
        $this->view->assign('baseUrl', $requestUrl);
        $this->view->assign('page_id', $queryParams['p_id'] ?? ($contentObjectData['pid'] ?? 0));

        $this->view->assign('targetAudience', $target_audience_list);
        $this->view->assign('htmlTemplate', $html_template);
        $this->view->assign('detailLinkType', $detail_link_type);
        $this->view->assign('detailsPage', $details_page);
        $this->view->assign('format', $format);
        $this->view->assign('startDate', ($start_date == '' || $start_date == 0) ? '' : date('Y-m-d',$start_date));
        $this->view->assign('endDate', ($end_date == '' || $end_date == 0) ? '' : date('Y-m-d',$end_date));
        $this->view->assign('daysToFetch', $days_to_fetch);
        $this->view->assign('communities', $communities);
        $this->view->assign('includeSubCommunities', $include_sub_communities);
        $this->view->assign('itemsPerPage', $items_per_page);
        $this->view->assign('holidays', $holidays);
        $this->view->assign('groupItems', $groupItems);
        $this->view->assign('borderColors', $borderColors);
        $this->view->assign('showPagination', $showPagination);
        $this->view->assign('additionalItems', $additional_items);
        $this->view->assign('items', $items_list);
        $this->view->assign('detailItem', $queryParams['v_id'] ?? null);

        $this->view->assign('displaySearch', $display_search);
        $this->view->assign('searchTextSearch', $search_text_search);
        $this->view->assign('searchCategories', html_entity_decode(json_encode($search_categories_list)));
        $this->view->assign('searchTargetAudiences', html_entity_decode(json_encode($search_target_audiences_list)));
        $this->view->assign('searchLocations', html_entity_decode(json_encode($search_locations_list)));
        $this->view->assign('searchCommunities', html_entity_decode(json_encode($search_communities_list)));
        $this->view->assign('searchCalendar', $search_calendar);
        $this->view->assign('searchPast', $search_past);

        $this->view->assign('dateOverride', $date_override);
        $this->view->assign('searchTextSearchValue', $search_text_search_value);
        $this->view->assign('searchCategoryValue', $search_category_value);
        $this->view->assign('searchTargetAudienceValue', $search_target_audience_value);
        $this->view->assign('searchLocationValue', $search_location_value);
        $this->view->assign('searchCommunityValue', $search_community_value);

        return $this->htmlResponse();

	}
}
