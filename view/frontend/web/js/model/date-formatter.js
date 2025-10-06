define(['jquery'], function ($) {
    'use strict';

    var getLocale = function () {
        // Get the locale from the main container.
        return $('.subscriptions-container[data-locale]').attr('data-locale');
    };

    return function (dateTimeString) {
        var splitDate = dateTimeString.split(/\D/);
        var date = new Date(
            Date.UTC(
                splitDate[0],
                splitDate[1] - 1,
                splitDate[2],
                splitDate[3],
                splitDate[4],
                splitDate[5]
            )
        );
        var locale = getLocale();
        var formattedReleaseDate = new Intl.DateTimeFormat(locale).format(date);
        return formattedReleaseDate;
    }
});
