/*
 AngularJS for translation module
*/
angular
	.module('settings', ['ngMaterial'])
	.controller('inputController', function ($scope, $http) {
		$scope.currentNavItem 	= 'page1';
		$scope.extensions 		= [];
		$scope.selectedExts 	= [];
		$scope.languages 		= [];
		$scope.selectedLangs 	= [];
		$scope.settings 		= [];

		//Fetch general settings from LocalConfiguration file
		$http.get(TYPO3.settings.ajaxUrls['fetch_general_settings']).then(function (response) {
			$scope.settings = response.data;
		});

		//Fetching available extensions list
		$http.get(TYPO3.settings.ajaxUrls['fetch_ext_list']).then(function (response) {
			$scope.extensions = response.data;
		});

		//Fetching selected extensions list
		$http.get(TYPO3.settings.ajaxUrls['fetch_added_ext_list']).then(function (response) {
			$scope.selectedExts = response.data;
		});

		//Fetching available languages list
		$http.get(TYPO3.settings.ajaxUrls['fetch_lang_list']).then(function (response) {
			$scope.languages = response.data;
		});

		//Fetching selected languages list
		$http.get(TYPO3.settings.ajaxUrls['fetch_added_lang_list']).then(function (response) {
			$scope.selectedLangs = response.data;
		});

		//Selection of extensions
		$scope.toggleSelection = function toggleSelection(extName) {
			var item = $scope.extensions.indexOf(extName);
			$scope.selectedExts.push(extName);
			$scope.extensions.splice(item, 1);
		};

		//Deselection of extensions
		$scope.toggleDeSelection = function toggleDeSelection(extName) {
			var item = $scope.selectedExts.indexOf(extName);
			$scope.extensions.push(extName);
			$scope.selectedExts.splice(item, 1);
		};

		//Selection of languages
		$scope.toggleLangSelection = function toggleLangSelection(langName) {
			var item = $scope.languages.indexOf(langName);
			$scope.selectedLangs.push(langName);
			$scope.languages.splice(item, 1);
		};

		//Deselection of languages
		$scope.toggleLangDeSelection = function toggleLangDeSelection(langName) {
			var item = $scope.selectedLangs.indexOf(langName);
			$scope.languages.push(langName);
			$scope.selectedLangs.splice(item, 1);
		};

		//Loader screen
		angular.element(document).ready(function () {
			$(".loader").fadeOut("slow");
			$('.typo3-fullDoc').css('visibility', 'visible');
		});

        $("body").on('keyup change click', '.search', function(e){
            var value = '';
            if($(this).length >0){
                value = $(this).val().toLowerCase();
            }
            $(this).parent().parent().siblings().children().each(function( index ) {
                $(this).show();
                $(this).children().each(function( index ) {

                    if($(this).text().toLowerCase().indexOf(value) > -1){
                        $(this).parent().show();
                    }else
                    {
                        $(this).parent().hide();
                    }
                });
            });
        });

	});