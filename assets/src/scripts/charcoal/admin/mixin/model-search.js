;(function (Admin) {
    'use strict';

    /**
     * This provides methods used for handling collection search.
     *
     * @mixin
     */
    Admin.Mixin_Model_Search = {
        search_query: null,

        /**
         * Set the user search query
         *
         * @param  {string|null} query
         * @return {void}
         */
        set_search_query: function (query) {
            this.search_query = query;
        },

        /**
         * Get the user search query
         *
         * @return {string|null}
         */
        get_search_query: function () {
            return this.search_query;
        }
    };

}(Charcoal.Admin));
