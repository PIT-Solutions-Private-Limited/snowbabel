/*
 AngularJS for translation module
*/
angular
    .module('translation', ['ngMaterial', 'rzTable'])
    .controller('inputController', function ($scope, $http, $mdDialog) {
        $scope.extensions = [];
        $scope.selection = [];
        $scope.availlang = [];
        $scope.selectedlang = [];
        $scope.settings = [];
        $scope.labels = [];

        //Managing extension selection
        $scope.extSelection = function (selectedExt) {
            $scope.IsDisplay = selectedExt.ExtensionId;
            var click = '#' + selectedExt.ExtensionKey;
            //Managing selection of checkbox underlying the label tags
            angular.forEach($scope.extensions, function (extension) {
                angular.element('#' + extension.ExtensionKey).removeAttr('checked');
            });
            angular.element(click).attr('checked', 'checked');
            angular.element('.col-options').css('display', 'block');

            var config = {
                params: {
                    uid: $scope.IsDisplay
                }
            };
            $http.get(TYPO3.settings.ajaxUrls['fetch_label_listing'], config).then(function (response) {
                $scope.labels = response.data;
            });
        };

        //Managing language label edit
        $scope.editLabel = function (keyEvent, language, labels) {
            var lanCode = 'TranslationId_' + language.split("_").pop();
            angular.forEach(labels, function (value, key) {
                if (key == lanCode) {
                    id = value;
                }
            });
            var config = {
                params: {
                    ActionKey: 'ListView_Update',
                    TranslationId: id,
                    TranslationValue: keyEvent.target.textContent
                }
            };
            $http.get(TYPO3.settings.ajaxUrls['fetch_label_listing_language'], config).then(function (response) {
                top.TYPO3.Notification.success('Translation Saved', 'New translation saved into the file');
            });
        }

        //Managing selected language
        $scope.langSelection = function (language) {
            var selectedExt;
            $('#extension-filter input:checked').each(function () {
                selectedExt = $(this).attr('value');
            });
            var config = {
                params: {
                    ExtensionId: selectedExt,
                    LanguageId: language.LanguageId,
                    ActionKey: 'LanguageSelection'
                }
            };
            angular.element('.coptions').css('display', 'table');
            $http.get(TYPO3.settings.ajaxUrls['fetch_label_listing_language'], config).then(function (response) {
                $scope.labels = response.data;
                language.LanguageSelected = !language.LanguageSelected;
            });
        };

        //Managing column selection
        $scope.colSelection = function (clickEvent) {
            var selectedExt;
            $('#extension-filter input:checked').each(function () {
                selectedExt = $(this).attr('value');
            });
            var config = {
                params: {
                    ExtensionId: selectedExt,
                    ColumnId: angular.element(clickEvent.target).attr('name'),
                    ActionKey: 'ColumnSelection'
                }
            };
            $http.get(TYPO3.settings.ajaxUrls['fetch_label_listing_language'], config).then(function (response) {
                $scope.labels = response.data;
            });
        };
        //Fetching selected extension list
        $http.get(TYPO3.settings.ajaxUrls['fetch_trans_ext_list']).then(function (response) {
            $scope.extensions = response.data;
        });
        //Fetching selected language list
        $http.get(TYPO3.settings.ajaxUrls['fetch_trans_lang_list']).then(function (response) {
            $scope.availlang = response.data;
        });
        //Fetching selected column list
        $http.get(TYPO3.settings.ajaxUrls['fetch_trans_col_list']).then(function (response) {
            $scope.settings = response.data;
        });

        var pressed = false;
        var start = undefined;
        var startX, startWidth;
        $('.custom-chip').on('mousedown', function (e) {
            start = $(this);
            var pressed = true;
            startX = e.pageX;
            startWidth = $(this).width();
            $(start).addClass("resizing");
        });

        angular.element('#SnowbabelSnowbabel-SnowbabelTranslation-').mousemove(function (e) {
            if (pressed) {
                $(start).width(startWidth + (e.pageX - startX));
            }
        });

        angular.element('#SnowbabelSnowbabel-SnowbabelTranslation-').mouseup(function () {
            if (pressed) {
                $(start).removeClass("resizing");
                pressed = false;
            }
        });

        $(".label-search").on('keyup change click', function(e){
            var value = '';
            if($('.label-search').length >0){
                value = $('.label-search').val().toLowerCase();
            } 
            $(".first-cell").each(function( index ) {
                $(this).show();
                $(this).find('div').each(function( index ) {
                    if($(this).text().toLowerCase().indexOf(value) > -1){
                        $(this).parent().parent().show();
                    }else
                    {
                        $(this).parent().parent().hide();
                    }
                });
            });
        });

        //Need of scheduler run detected
        var config = {
            params: {
                ActionKey: 'ConfigurationChanged'
            }
        };
        $http.get(TYPO3.settings.ajaxUrls['fetch_label_listing_language'], config).then(function (response) {
            if (response.data == 'failure') {
                $mdDialog.show({
                    contentElement: '#myDialog',
                    parent: angular.element(document.body),
                    clickOutsideToClose: false
                });
            }
        });
    });