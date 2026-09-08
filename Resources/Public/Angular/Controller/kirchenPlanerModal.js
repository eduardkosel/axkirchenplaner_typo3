app.controller('kirchenPlanerModalCtrl', function ($scope, $uibModalInstance, $sce, details, borderColor) {
    //Get data in scope for two way binding to HTML
    $scope.details = details;
    $scope.borderColor = borderColor;
    //Function for creating correctly formatted Location-Name
    $scope.getRaumName = function(){
        var raum_name = '';
        if($scope.details.raum_name_lang != ''){
            raum_name += $scope.details.raum_name_lang;
        }
        if($scope.details.raum_str != ''){
            if(raum_name != ''){
                raum_name += ', ';
            }
            raum_name += $scope.details.raum_str +  ', ';
        }
        if($scope.details.raum_plz != ''){
            raum_name += $scope.details.raum_plz + ' ';
        }
        if($scope.details.raum_ort != ''){
            raum_name += $scope.details.raum_ort;
        }
        return raum_name;
    }
    //Close Modal
    $scope.close = function(){
        $uibModalInstance.close();
    }
    //Create URL for GMaps Embed
    $scope.getOrtUrl = function(e){
        return 'https://www.google.com/maps?f=q&source=s_q&hl=de&geocode=&q='+encodeURIComponent(e.raum_str)+'+'+encodeURIComponent(e.raum_plz)+'+'+encodeURIComponent(e.raum_ort)+'&t=h&ie=UTF8&hq=&hnear=&output=embed';
    }
    //Get BorderColor for highlighting purposes if activated
    $scope.getBorderColor = function(e){
        if(e.farbe_1 == ''){
            return;
        }else{
            return {'color':'#'+e.farbe_1}
        }
    }
});
//Filter for trusting HTML inside Angular Attributes - Applied globally
app.filter('trustedHtml', ['$sce', function($sce){
    return function(text) {
        return $sce.trustAsHtml(text);
    };
}]);
// Filter for trusting URLs inside Angular Attributes - Applied globally
app.filter('trustedUrl', ['$sce', function($sce){
    return function(url) {
        return $sce.trustAsResourceUrl(url);
    };
}]);