app.controller('kirchenPlanerCtrl', function($scope, $document, $filter, $http, $uibModal, $window, $sce) {
    //Binding of Data results from TYPO3 FLUID Template located in EXT:axkirchenplaner/Resources/Private/Templates/Kirchenplaner/Show.html
    //Initialize Resultset
    $scope.uid = 0;
    $scope.init_uid = function(uid){
        $scope.uid = uid;
        $scope.results = [];
        $scope.totalResults = 0;
        //Settings for Pagination
        $scope.maxSize = 3;
        $scope.itemsPerPage = kirchenplaner_config[$scope.uid].itemsPerPage;
        //HTML Template
        $scope.template = kirchenplaner_config[$scope.uid].htmlTemplate;
        //Detail Link Type - Modal or Link to Static Page
        $scope.detailLinkType = kirchenplaner_config[$scope.uid].detailLinkType;
        //ID of Detail Page
        $scope.detailsPage = kirchenplaner_config[$scope.uid].detailsPage;
        //Catholic, Protestant or both holidays to display
        $scope.holidays = kirchenplaner_config[$scope.uid].holidays;
        //Should Items of the resultset be grouped by date?
        $scope.groupItems = kirchenplaner_config[$scope.uid].groupItems;
        //Should Items be bordered with predefined Colors?
        $scope.borderColors = kirchenplaner_config[$scope.uid].borderColors;
        //Where to Show Pagination
        $scope.showPagination = kirchenplaner_config[$scope.uid].showPagination;
        //PageID used for Link Back from detail Page
        $scope.pageId = kirchenplaner_config[$scope.uid].pageId;
        //Base URL of the installation
        $scope.baseUrl = kirchenplaner_config[$scope.uid].baseUrl;

        $scope.date = new Date();

        //Input Model of the search and pagination modules
        $scope.input = {};
        //Set Page on Pagination
        $scope.input.currentPage = 1;
        $scope.input.category = '';
        $scope.input.location = '';
        $scope.input.targetAudience = '';
        $scope.input.search = '';
        $scope.input.communities = '';
        //Initialize StartDate with it being today
        $scope.input.startDate = kirchenplaner_config[$scope.uid].startDate ? Date.parse(kirchenplaner_config[$scope.uid].startDate) : new Date();
        $scope.input.startDateFormatted = $filter('date')($scope.input.startDate, 'dd.MM.yyyy').toString();
        $scope.input.endDate = kirchenplaner_config[$scope.uid].endDate ? Date.parse(kirchenplaner_config[$scope.uid].endDate) : '';
        $scope.input.endDateFormatted = $scope.input.endDate ? $filter('date')($scope.input.endDate, 'dd.MM.yyyy').toString() : '';

        //Get Copy of the Input Model for reset
        $scope.initial = {};
        $scope.initial.input = angular.copy($scope.input);

        $scope.input.category = kirchenplaner_config[$scope.uid].searchCategoryValue;
        $scope.input.location = kirchenplaner_config[$scope.uid].searchLocationValue;
        $scope.input.targetAudience = kirchenplaner_config[$scope.uid].searchTargetAudienceValue;
        $scope.input.search = kirchenplaner_config[$scope.uid].searchTextSearchValue;
        $scope.input.communities = kirchenplaner_config[$scope.uid].searchCommunityValue;

        //Callback function for Resetting the Input Model
        $scope.resetInput = function(){
            $scope.input = angular.copy($scope.initial.input);
        }

        //Query Settings - Copy Values passed by TYPO3
        $scope.query = {}
        $scope.query.apiUrl = kirchenplaner_config[$scope.uid].apiUrl+'/api/index/';
        $scope.query.apikey = kirchenplaner_config[$scope.uid].apiKey;
        $scope.query.categories = kirchenplaner_config[$scope.uid].topics;
        $scope.query.locations = kirchenplaner_config[$scope.uid].locations;
        $scope.query.communities = kirchenplaner_config[$scope.uid].communities;
        $scope.query.targetAudience = kirchenplaner_config[$scope.uid].targetAudience;
        $scope.query.format = 'json';
        $scope.query.daysToFetch = kirchenplaner_config[$scope.uid].daysToFetch;
        $scope.query.includeSubCommunities = kirchenplaner_config[$scope.uid].includeSubCommunities;
        $scope.query.additionalItems = kirchenplaner_config[$scope.uid].additionalItems;
        $scope.query.items = kirchenplaner_config[$scope.uid].items;
        $scope.query.detailItem = kirchenplaner_config[$scope.uid].detailItem;

        //Search Settings - Copy Values passed by TYPO3
        $scope.search = {};
        $scope.search.displaySearch = kirchenplaner_config[$scope.uid].displaySearch;
        $scope.search.textSearch = kirchenplaner_config[$scope.uid].searchTextSearch;
        $scope.search.categories = kirchenplaner_config[$scope.uid].searchCategories;
        $scope.search.targetAudiences = kirchenplaner_config[$scope.uid].searchTargetAudiences;
        $scope.search.locations = kirchenplaner_config[$scope.uid].searchLocations;
        $scope.search.communities = kirchenplaner_config[$scope.uid].searchCommunities;
        $scope.search.calendar = kirchenplaner_config[$scope.uid].searchCalendar;
        $scope.search.past = kirchenplaner_config[$scope.uid].searchPast;

        //StartDate Options
        $scope.startDateOptions = {
            format: 'dd.MM.yyyy',
            minDate: $scope.initial.input.startDate,
            startingDay: 1,
            initDate: ''
        };
        if($scope.search.past == "ja"){
            $scope.startDateOptions = {
                format: 'dd.MM.yyyy',
                startingDay: 1,
                initDate: ''
            };
        }

        //EndDate Options
        $scope.endDateOptions = {
            format: 'dd.MM.yyyy',
            minDate: $scope.input.startDate,
            startingDay: 1,
            initDate: ''
        };

        //Initialize StartDateDialog
        $scope.startDateDialog = {
            opened: false
        }
        //Initialize EndDateDialog
        $scope.endDateDialog = {
            opened: false
        }

        //Functions for opening Date-Dialogues
        $scope.openStartDateDialog = function(){
            $scope.startDateDialog.opened = true;
        }

        $scope.openEndDateDialog = function(){
            $scope.endDateDialog.opened = true;
        }
        //Function for opening modals if selected
        $scope.openDetailsModal = function(e){
            var modalInstance = $uibModal.open({
                animation: false,
                templateUrl: $scope.baseUrl + '/typo3conf/ext/axist.axkirchenplaner/Resources/Public/Angular/Controller/kirchenPlanerModal.html',
                controller: 'kirchenPlanerModalCtrl',
                resolve: {
                    borderColor: function(){
                        return $scope.borderColors;
                    },
                    details: function(){
                        return e;
                    }
                }
            })
        }
        //Function for linking to detailpage
        $scope.openDetailsPage = function(e){
            var queryChar = '&';
            if(!$scope.detailsPage.includes('?')){
                queryChar = '?'
            }
            console.log($scope.detailsPage + queryChar + 'v_id='+ e.id + '&p_id='+ $scope.pageId);
            $window.location.href = $scope.detailsPage + queryChar + 'v_id='+ e.id + '&p_id='+ $scope.pageId;
        }
        //Get Backlink to previous Page
        $scope.getBacklink = function(){
            return $scope.baseUrl + 'index.php?id=' + $scope.pageId;
        }
        //Create Name of Location
        $scope.getOrt = function(e){
            var raum_name = '';
            if(e.raum_name_lang != ''){
                raum_name += e.raum_name_lang;
            }
            if(e.raum_str != ''){
                if(raum_name != ''){
                    raum_name += ', ';
                }
                raum_name += e.raum_str + ', ';
            }
            if(e.raum_plz != ''){
                raum_name += e.raum_plz + ' ';
            }
            if(e.raum_ort != ''){
                raum_name += e.raum_ort;
            }
            return raum_name;
        }

        //Create URL for GMaps
        $scope.getOrtUrl = function(e){
            return 'https://www.google.com/maps?f=q&source=s_q&hl=de&geocode=&q='+encodeURIComponent(e.raum_str)+'+'+encodeURIComponent(e.raum_plz)+'+'+encodeURIComponent(e.raum_ort)+'&t=h&ie=UTF8&hq=&hnear=&output=embed';
        }
        //Get Holidays
        $scope.getHoliday = function(e){
            switch($scope.holidays){
                case("keine"):
                    return;
                    break;
                case("protestantisch"):
                    return e.beginn_prot_feiertage;
                    break;
                case("katholisch"):
                    return e.beginn_kath_feiertage;
                    break;
                case("protestantisch+katholisch"):
                    return e.beginn_feiertage;
                    break;
                default:
                    return;
            }
        }
        //Get Width of Search and Resultset
        $scope.getWidth = function(){
            switch ($scope.search.displaySearch) {
                case("oben"):
                    return "col-md-12";
                    break;
                case("links"):
                case("rechts"):
                    return "col-md-6";
                    break;
                default:
                    return "col-md-12";
                    break;
            }
        }

        //Get Border-Style for highlighting if activated
        $scope.getBorderStyle = function(e){
            if(e.farbe_1 == ''){
                return;
            }else{
                return {'border-left' : '4px solid #' + e.farbe_1};
            }
        }

        //Get BorderColor for highlighting purposes if activated
        $scope.getBorderColor = function(e){
            if(e.farbe_1 == ''){
                return;
            }else{
                return {'color':'#'+e.farbe_1}
            }
        }

        //Trivial but necessary... Return first Element of passed Array
        $scope.first = function(e){
            return _.first(e);
        }

        $scope.scrollToTop = function(){
            paginationTop = angular.element(document.getElementById('pagination-top-'+$scope.uid));
            $document.scrollToElement(paginationTop, 195, 500);
        }
        //Fetching the Data
        $scope.getResults = function(){
            $scope.fetchingData = true;
            params = {};
            params.key = $scope.query.apikey;
            //Check if its a detailPage or an Overview
            if($scope.query.detailItem == ''){
                //Overview - Assign all Filters to standard or filtered values
                params.q_rubrik = $scope.query.categories;
                if($scope.input.category != ''){
                    params.q_rubrik_filter = $scope.input.category;
                }
                params.q_ort_ids = $scope.query.locations;
                if($scope.input.location != ''){
                    params.q_ort_ids = $scope.input.location;
                }
                params.q_datum_wiederholen = "ja";
                params.q_gemeinde = $scope.query.communities;
                if($scope.input.communities != ''){
                    params.q_gemeinde = $scope.input.communities;
                }
                params.q_limit = $scope.itemsPerPage;
                params.q_offset = $scope.input.currentPage == 1 ? '0' : ($scope.input.currentPage - 1) * $scope.itemsPerPage;
                params.q_unterorganisationen = $scope.query.includeSubCommunities;
                params.q_zielgruppe = $scope.query.targetAudience;
                if($scope.input.targetAudience != ''){
                    params.q_zielgruppe_filter = $scope.input.targetAudience;
                }
                params.q_format = $scope.query.format;

                if($scope.input.startDateFormatted != ''){
                    params.q_start = $scope.input.startDateFormatted
                }
                if($scope.input.endDateFormatted != ''){
                    params.q_ende = $scope.input.endDateFormatted;
                }else{
                    params.q_tage = $scope.query.daysToFetch;
                }
                if($scope.input.search != ''){
                    params.q_stichwort = $scope.input.search;
                }
                if($scope.query.items != ''){
                    params.q_vid = $scope.query.items;
                }
            }else{
                //DetailPage
                params.q_vid = $scope.query.detailItem;
                params.q_format = $scope.query.format;
            }
            $http.get($scope.query.apiUrl + encodeURIComponent(JSON.stringify(params)),{cache: true})
                .then(function(response){
                    $scope.results = response.data.termine;
                    if($scope.results){
                        $scope.totalResults = parseInt(response.data.total_count);
                    }else{
                        $scope.totalResults = 0;
                    }
                    $scope.fetchingData = false;
                })
        }

        //Get Initial Results
        $scope.fetchingData = true;
        $scope.getResults();
    }


    //Watcher for Input Date
    $scope.$watch('input', function(newValue, oldValue) {
        $scope.endDateOptions.minDate = newValue.startDate;
        $scope.input.startDateFormatted = $filter('date')(newValue.startDate, 'dd.MM.yyyy').toString();
        if(!newValue.endDate){
            $scope.input.endDate = '';
            $scope.input.endDateFormatted = '';
        }else{
            $scope.input.endDateFormatted = $filter('date')(newValue.endDate, 'dd.MM.yyyy').toString();
        }
        if(newValue.search != oldValue.search){
            $scope.input.currentPage = 1;
        }
        if(newValue.category != oldValue.category){
            $scope.input.currentPage = 1;
        }
        if(newValue.targetAudiences != oldValue.targetAudiences){
            $scope.input.currentPage = 1;
        }
        if(newValue.communities != oldValue.communities){
            $scope.input.currentPage = 1;
        }
        $scope.getResults();
    }, true);
});
