(function () {
    'use strict';

    angular.module('wmsDining')
        .factory('MenuService', MenuService);

    MenuService.$inject = ['$http', 'apiUrl'];

    function MenuService($http, apiUrl) {
        var api = apiUrl;
        var urls = {
            all: api + '/menus',
            serviceUnits: api + '/service_units'
        };

        /**
         * Returns a specific URL from the urls config object
         * @param which
         * @returns string url
         */
        var url = function (which, id) {
            id = typeof id !== 'undefined' ? '/' + id : '';
            return urls[which] + id;
        };

        /**
         * Send http request
         * @param url
         * @returns promise {}
         */
        var request = function (url) {
            return $http({url: url, method: 'get'});
        };

        return {
            request: request,
            url: url
        };
    }
})();