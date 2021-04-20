define([
        'jquery',
        'uiComponent',
    ], function ($, Component) {
        'use strict';
        return Component.extend({
            initialize: function () {
                this._super();
                var events = [];
                $.ajax({
                    url: this.queuedevents.url,
                    type: 'post',
                    success: function (data) {
                        $('head').append(data);
                    }
                });
            },
        });
    }
);
