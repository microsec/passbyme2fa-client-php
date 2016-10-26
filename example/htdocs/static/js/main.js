/*jslint browser: true*/
/*global $, jQuery*/
(function ($) {
    "use strict";
    $(document).ready(function () {
        function dialogContent(title, html) {
            return $("<div/>", {
                id: "pbm-dialog",
                title: title,
                html: "<div class=pbm-dialog-header>" + "<div class='pbm-dialog-icon pbm-icon-default'></div>" + "<div class='pbm-dialog-right'>" + html + "</div><div class='pbm-dialog-statuses'></div></div>",
                className: "pbm-dialog"
            });
        }

        function removeDialogs() {
            $(".ui-dialog:has(#pbm-dialog)").empty().remove();
        }

        function sendCancelRequest(passbyme) {
            $.ajax({
                url: "Authorization.php",
                type: "POST",
                dataType: "json",
                data: {
                    "messageId": passbyme.getMessageId(),
                    "cancel": true
                },
                success: function () {
                    passbyme.stopPolling();
                    removeDialogs();
                    var cancelDialog = dialogContent("PassBy[ME]", "Your transaction has been cancelled!");
                    cancelDialog.dialog({
                        width: 500,
                        modal: true,
                        buttons: {}
                    });
                }
            });
        }

        function send(url, onProgress, type) {
            if (!onProgress.length) {
                $("body").append(onProgress);
            }
            removeDialogs();
            var passbyme;
            var users;
            users = [];
            $.each($("#pbm_user_id").val().split(","), function (index, item) {
                users[index] = $.trim(item);
            });

            passbyme = $.passbyme2FaClientJs({
                url: url,
                data: {
                    "userId": users,
                    "body": $("#msg").val(),
                    "subject": $("#subject").val(),
                    "action": $(type).attr("id")
                },
                type: "POST",
                waiting: function (resp) {
                    if ($(type).attr("id") === "authorization") {
                        onProgress.dialog({
                            width: 500,
                            modal: true,
                            buttons: {
                                "Cancel": function () {
                                    sendCancelRequest(passbyme);
                                }
                            },
                            close: function () {
                                passbyme.stopPolling();
                            }
                        });
                    } else {
                        onProgress.dialog({
                            width: 500,
                            modal: true,
                            close: function () {
                                passbyme.stopPolling();
                            }
                        });
                    }
                    $("#pbm-identifier").html(passbyme.getSecureIdentifier());

                    var id;
                    if (resp.hasOwnProperty("messageId")) {
                        id = resp.messageId;
                    } else if (resp.hasOwnProperty("sessionId")) {
                        id = resp.sessionId;
                        $("#qrcode-container").qrcode({
                            render: "canvas",
                            size: "300",
                            text: resp.qrContent
                        });
                    } else if (resp.hasOwnProperty("secureId")) {
                        id = resp.secureId;
                    } else {
                        passbyme.stopPolling();
                        $(".pbm-dialog-right").html("No session identifier found!");
                    }

                    return id;
                },
                success: function (response) {
                    var msgProc;
                    var finished;
                    var looped;
                    msgProc = "<table class='table table-striped'>" +
                        "<thead><tr><th>User Id</th><th>Message Status</th></tr></thead>" +
                        "<tbody>";
                    if (response.hasOwnProperty("recipients")) {
                        finished = 0;
                        looped = 0;
                        $.each(response.recipients, function (index, element) {
                            if (element.status !== "PENDING" &&
                                element.status !== "NOTIFIED" &&
                                element.status !== "DOWNLOADED") {
                                finished += 1;
                                if (element.status === "SEEN" || element.status === "APPROVED") {
                                    msgProc += "<tr class='success'>";
                                    onProgress.dialog("option", "buttons", {});
                                } else {
                                    msgProc += "<tr class='danger'>";
                                    onProgress.dialog("option", "buttons", {});
                                }
                            } else {
                                msgProc += "<tr>";
                            }
                            msgProc += "<td>" + element.userId + "</td>" +
                                "<td>" + element.status + "</td>" +
                                "</tr>";
                            looped = index + 1;
                        });
                        if (finished === looped) {
                            passbyme.stopPolling();
                        }

                        msgProc += "</tbody>";
                        msgProc += "</table>";
                        $(".pbm-dialog-statuses").html(msgProc);
                    } else {
                        passbyme.stopPolling();
                        $(".pbm-dialog-right").html("Unknown response!");
                    }
                },
                error: function (msg) {
                    onProgress.dialog({
                        width: 500,
                        modal: true,
                        buttons: {}
                    });
                    $(".pbm-dialog-right").html(msg);
                }
            });
        }

        $("#authorization").click(function () {
            var onProgress;
            onProgress = dialogContent("PassBy[ME] Authorization message",
                "<p class='pbm-dialog-text'>" + "You have been sent a PassBy[ME] authentication message to your mobile.<br/>" + "<div>Identifier: <strong id='pbm-identifier'></strong></div></p>"
            );
            send("Authorization.php", onProgress, this);
        });

        $("#generalMessage").click(function () {
            var onProgress;
            onProgress = dialogContent("PassBy[ME] General message",
                "<p class='pbm-dialog-text'>" + "You have been sent a PassBy[ME] message to your mobile." + "</p>"
            );
            send("Messaging.php", onProgress, this);
        });

        var maxLength = 4094;
        var textarea = $("textarea");
        var length = maxLength - textarea.val().length;
        $("#chars").text(length + " Characters Left.");
        textarea.bind("input propertychange", function () {
            length = maxLength - $(this).val().length;
            $("#chars").text(length + " Characters Left.");
        });
    });
}(jQuery));