(function () {
    "use strict";
    $.PassByMe2FAClientJs = function (options) {
        var poll;
        var pbmIdentifier;
        var session;
        //Default setting
        var settings = $.extend({
            url: '',
            type: 'GET',
            data: '',
            success: function () {
                return undefined;
            },
            error: function (msg) {
                return msg;
            },
            waiting: function () {
                return undefined;
            },
            dataType: 'json',
            pollingTimer: 3000,
            cache: false,
            timeout: 30000
        }, options);
        
        // methods can be used outside of the plugin
        var pbm = {
            stopPolling: function() {
                clearTimeout(poll);
            },
            setIdentifier: function(resp) {
                var list = resp.split(/;/);
                pbmIdentifier = list[1];
            }, 
            getIdentifier: function() {
                return pbmIdentifier;
            },
            getSession: function() {
                return session;
            }
        };
        
        function polling(session) {
            poll = setTimeout(function () {
                $.ajax({
                    url: settings.url,
                    type: settings.type,
                    data: {
                        'session' : session
                    },
                    success: function (resp) {
                        switch (resp) {
                            case 'APPROVED':
                                pbm.stopPolling();
                                settings.success();
                                break;
                            case 'DENIED':
                                pbm.stopPolling();
                                settings.error('Request rejected!');
                                break;
                            case 'TIMEOUT':
                                pbm.stopPolling();
                                settings.error('Confirmation time has expired!');
                                break;
                            default:
                                //PENDING
                                break;
                        }
                    },
                    dataType: settings.dataType,
                    timeout: settings.timeout,
                    cache: settings.cache,
                    complete: polling(session)
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
                        //Ok
                        pbm.setIdentifier(resp);
                        settings.waiting();
                        //Save session
                        session = resp;
                    }
                } else {
                    settings.error('Empty result from server!');
                }
            },
            dataType: settings.dataType,
            complete: function () {
                if (session) {
                    polling(session);
                }
            }
        });
        return pbm;
    };
}());