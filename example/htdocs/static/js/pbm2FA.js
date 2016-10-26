/*jslint browser: true*/
/*global $, jQuery*/
(function ($) {
    "use strict";
    $.passbyme2FaClientJs = function (options) {
        var poll;
        var response;
        var settings;
        var pbm;
        var secureId;
        settings = $.extend({
            url: "",
            type: "GET",
            data: "",
            success: function () {
                return undefined;
            },
            error: function (msg) {
                return msg;
            },
            waiting: function (resp) {
                return resp;
            },
            dataType: "json",
            pollingTimer: 1000,
            cache: false,
            timeout: 30000
        }, options);
        pbm = {
            stopPolling: function () {
                clearTimeout(poll);
            },
            getSecureIdentifier: function () {
                return secureId;
            },
            getMessageId: function () {
                return response.messageId;
            }
        };

        function polling(response) {
            poll = setTimeout(function () {
                var extraParams = {
                    "messageId": response.messageId,
                    "polling": true
                };
                var params = $.extend(settings.data, extraParams);
                $.ajax({
                    url: settings.url,
                    type: settings.type,
                    data: params,
                    success: function (resp) {
                        if (resp.errormsg) {
                            pbm.stopPolling();
                            settings.error(resp.errormsg);
                        } else {
                            settings.success(resp);
                        }
                    },
                    dataType: settings.dataType,
                    timeout: settings.timeout,
                    cache: settings.cache,
                    complete: polling(response)
                });
            }, settings.pollingTimer);
        }

        $.ajax({
            url: settings.url,
            type: settings.type,
            data: settings.data,
            timeout: settings.timeout,
            cache: settings.cache,
            success: function (resp) {
                if (!$.isEmptyObject(resp)) {
                    if (resp.errormsg) {
                        //Error
                        settings.error(resp.errormsg);
                    } else {
                        if (resp.code) {
                            settings.error("PassBy[ME] error message: " + resp.message);
                        } else {
                            secureId = resp.secureId;
                            var messageId;
                            var waitingResp = settings.waiting(resp);
                            if (waitingResp) {
                                messageId = waitingResp;
                            } else {
                                messageId = resp.messageId;
                            }
                            response = {
                                "messageId": messageId
                            };
                        }
                    }
                } else {
                    settings.error("Empty result from server!");
                }
            },
            dataType: settings.dataType,
            complete: function () {
                if (response) {
                    polling(response);
                }
            }
        });
        return pbm;
    };
}(jQuery));