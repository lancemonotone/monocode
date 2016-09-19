(function () {
    'use strict';

    angular.module('wmsDining')
        .controller('MenuCtrl', MenuCtrl);

    MenuCtrl.$inject = ['MenuService', '$filter', 'includedServiceUnits', 'excludedCourses', 'translatedCourses', 'translatedServiceUnits', '$location', '$log', 'debug'];

    function MenuCtrl(MenuService, $filter, includedServiceUnits, excludedCourses, translatedCourses, translatedServiceUnits, $location, $log, debug) {
        var _self = this;

        // properties
        _self.errorMsg = '';
        _self.fetchingData = true;
        _self.filteredMeals = {};
        _self.status = '';
        _self.serviceUnits = [];
        _self.selectedServiceUnit = {};
        _self.meals = [];
        _self.mealList = [];
        _self.selectedMeal = null;

        // methods
        _self.parseURL = parseURL;
        _self.init = init;
        _self.getServiceUnits = getServiceUnits;
        _self.getMealsByServiceUnit = getMealsByServiceUnit;
        _self.includeServiceUnits = includeServiceUnits;
        _self.translateAndExcludeCourses = translateAndExcludeCourses;
        _self.translateServiceUnits = translateServiceUnits;
        _self.buildMealList = buildMealList;
        _self.initMealList = initMealList;
        _self.getMenuByMeal = getMenuByMeal;
        _self.preloadServiceUnit = preloadServiceUnit;
        _self.preloadMeal = preloadMeal;
        _self.isServiceUnitSelected = isServiceUnitSelected;
        _self.isMealSelected = isMealSelected;
        _self.setSelectedServiceUnit = setSelectedServiceUnit;
        _self.setSelectedMeal = setSelectedMeal;
        _self.showFullMenuLink = showFullMenuLink;
        _self.showBookmarkLink = showBookmarkLink;
        _self.showMeals = showMeals;
        _self.showMealList = showMealList;

        _self.init();

        function init() {
            _self.getServiceUnits();
            _self.parseURL();
        }

        function parseURL() {
            var url = window.location.href;
            var pattern = RegExp("^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\\?([^#]*))?(#(.*))?");
            var matches = url.match(pattern);
            _self.thisURL = {
                scheme: matches[2],
                authority: matches[4],
                path: matches[5],
                query: matches[7],
                fragment: matches[9]
            };
        }

        function showMeals() {
            return Object.keys(_self.mealList).length && !_self.fetchingData;
        }

        function showMealList() {
            return Object.keys(_self.filteredMeals).length && !_self.fetchingData;
        }

        function showFullMenuLink() {
            return Object.keys(_self.filteredMeals).length && _self.selectedMeal && _self.selectedServiceUnit.net_nutrition;
        }

        function showBookmarkLink() {
            return Object.keys(_self.filteredMeals).length && _self.selectedMeal && _self.selectedServiceUnit;
        }

        function isServiceUnitSelected(id) {
            return _self.selectedServiceUnit.unitid === id;
        }

        function isMealSelected(meal) {
            return _self.selectedMeal === meal;
        }

        function getServiceUnits() {
            MenuService.request(MenuService.url('serviceUnits'))
                .success(function (data, status) {
                    _self.serviceUnits = _self.includeServiceUnits(data);
                    _self.fetchingData = false;
                    _self.preloadServiceUnit();
                    _self.preloadMeal();
                })
                .error(function (data, status, headers, config) {
                    _self.errorMsg = "Request failed: Unable to load facilities: " + status;
                    _self.fetchingData = false;
                });
        }

        function setSelectedServiceUnit(unitid) {
            _self.selectedServiceUnit = _.findWhere(_self.serviceUnits, {'unitid': unitid});
        }

        /**
         * Preload Facility from URL param
         */
        function preloadServiceUnit() {
            var unitid = $location.search()['unitid'] || null;
            if (unitid) {
                _self.getMealsByServiceUnit(unitid);
            }
        }

        function translateServiceUnits(data){
            data = _.each(data, function(item){
                if (item['service_unit'] in translatedServiceUnits) {
                    item['service_unit'] = translatedServiceUnits[item['service_unit']];
                }
            });
            return data;
        }

        /**
         * Exclude dining facilities (e.g., Faculty House)
         */
        function includeServiceUnits(data) {
            data = _.filter(data, function (item) {
                return includedServiceUnits.indexOf(item.service_unit) > -1
            });
            return _self.translateServiceUnits(data);
        }

        /**
         * Preload Meal from URL param
         */
        function preloadMeal() {
            _self.setSelectedMeal($location.search()['meal'] || null);
        }

        /**
         * Make ajax call to service_units API.
         * @success
         * @param unitid
         */
        function getMealsByServiceUnit(unitid) {
            if (unitid === _self.selectedServiceUnit.unitid) {
                return false;
            }
            _self.fetchingData = true;
            if (unitid !== null) {
                _self.setSelectedServiceUnit(unitid);
                MenuService.request(MenuService.url('serviceUnits', unitid))
                    .success(function (data, status) {
                        _self.meals = _self.translateAndExcludeCourses(data);
                        _self.buildMealList();
                        _self.getMenuByMeal();
                        _self.fetchingData = false;
                    })
                    .error(function (data, status, headers, config) {
                        _self.errorMsg = "Request failed: Unable to load menus: " + status;
                        _self.fetchingData = false;
                    });
            } else {
                _self.meals = [];
                _self.mealList = [];
                _self.fetchingData = false;
            }
        }

        function setSelectedMeal(meal) {
            _self.selectedMeal = meal || null;
        }

        /**
         * Push meal names (e.g., 'Lunch') into array to populate
         * meals select element.
         */
        function buildMealList() {
            _self.mealList = [];
            _.each(_self.meals, function (meal) {
                if (_self.mealList.indexOf(meal.meal) === -1) {
                    _self.mealList.push(meal.meal);
                }
            });
        }

        /**
         * Translate course names (e.g., 'Starch' to 'Sides')
         * Exclude courses (e.g, Condiments)
         */
        function translateAndExcludeCourses(data) {
            _.each(data, function (item) {
                if (item['course'] in translatedCourses) {
                    item['course'] = translatedCourses[item['course']];
                }
            });
            return _.reject(data, function (item) {
                return excludedCourses.indexOf(item.course) > -1
            });
        }

        /**
         * 1. If there are no meals matching selected meal, reset selected meal.
         * 2. If there is only 1 meal at facility, set selected meal.
         */
        function initMealList() {
            if (_self.selectedMeal === null) {
                if (_self.mealList.indexOf(_self.selectedMeal) === -1) {
                    _self.setSelectedMeal(null);
                }
                if (_self.mealList.length === 1) {
                    _self.setSelectedMeal(_self.mealList[0]);
                }
            }
        }

        function getMenuByMeal(meal) {

            meal = meal || _self.selectedMeal;
            _self.setSelectedMeal(meal);
            _self.initMealList();
            _self.filteredMeals = {};
            if (_self.selectedMeal) {
                var filtered = $filter('filter')(_self.meals, {meal: _self.selectedMeal});
                if (filtered.length > 0) {
                    _self.filteredMeals = _.groupBy(filtered, 'course');
                }
            }
        }

        /**
         * Get the index of a specific object by key/value pair.
         * @param array
         * @param key
         * @param value
         * @returns index
         */
        function objectFindIndexByKey(array, key, value) {
            for (var i = 0; i < array.length; i++) {
                if (array[i][key] === value) {
                    return i;
                }
            }
            return -1;
        }
    }
})();