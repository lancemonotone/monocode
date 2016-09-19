(function () {
    'use strict';

    String.prototype.ucwords = function () {
        var str = this.toLowerCase();
        return str.replace(/(^([a-zA-Z\p{M}]))|([ -][a-zA-Z\p{M}])/g,
            function ($1) {
                return $1.toUpperCase();
            });
    };

    angular.module('wmsDining', ['ngRoute'])
        .constant('apiUrl', 'http://dining.williams.edu/wp-json/dining')
        //.constant('apiUrl', 'http://test.wms.dev/wp-json/dining')
        .constant('includedServiceUnits', [
            'Driscoll Dining Hall',
            'Mission Dining Hall',
            'Eco-Cafe',
            'Paresky Whitmans Market',
            'Paresky Grab N Go'
        ])
        .constant('excludedCourses', ['Condiments'])
        .constant('translatedCourses', {'Starch': 'Sides', 'Marche': 'Entrees'})
        .constant('translatedServiceUnits', {
            'Driscoll Dining Hall': 'Driscoll',
            'Mission Dining Hall': 'Mission Park',
            'Eco-Cafe': 'Eco Café',
            'Paresky Whitmans Market': 'Whitmans\' Marketplace',
            'Paresky Grab N Go': 'Grab & Go'
        })
        .constant('debug', false)
        .config(['$locationProvider', function ($locationProvider) {
            $locationProvider
                .html5Mode(true)
                .hashPrefix('!');
        }]);
})();