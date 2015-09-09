$(document).ready(function () {
    $('#start_example').click(function() {
        var $dialog = $('<div/>', {
            id: 'pbm-dialog',
            html: '<div class=pbm-dialog-header>'
                + '<div class="pbm-dialog-icon pbm-icon-default"></div>'
                + '<div class="pbm-dialog-right">'
                    + '<h3>Dear Customer!</h3><br/>'
                    + '<p class="pbm-dialog-text">'
                        + 'You have been sent a <a href="http://www.passbyme.com">PassBy[ME]</a> '
                        + 'alert to your mobile device to confirm the transaction.<br/>'
                        + 'Please, check your <a href="http://www.passbyme.com">PassBy[ME]</a> mobile app.'
                    + '</p>'
                    + '<br/>'
                    + '<div>Identifier: <b id="pbm-identifier"></b></div>'
                + '</div>'
            + '</div>',
            className: 'pbm-dialog'
        });
        
        if (!$dialog.length) {
            $('body').append($dialog);
        }
        var dialogTitle = 'PassBy[ME] Authentication!';
        var passbyme = $.PassByMe2FAClientJs({
            url: 'client.php',
            data: {
                'user_id' : $('#pbm_user_id').val(),
                'msg' : $('#msg').val()
            },
            type: 'POST',
            waiting: function() {
                $dialog.dialog({
                    title: dialogTitle,
                    width: 500,
                    modal: true,
                    buttons: { 
                        "Cancel": function() {
                            sendCancelRequest();
                        }
                    },
                    close: function () {
                        closeEvent();
                    }
                });
                setIdentifier();
            },
            success: function () {
                removeButtons();
                $('.pbm-dialog-right').html(
                    '<h3>Congratulations!</h3>'
                    + '<br/>'
                    + '<p class="pbm-dialog-text">Your PassBy[ME] authentication was successful!</p>'
                );
            },
            error: function(msg) {
                $dialog.dialog({
                    title: dialogTitle,
                    width: 500,
                    buttons: {}
                });
                $('.pbm-dialog-right').html(msg);
            }
        });
    
        function removeButtons() {
            $dialog.dialog("option", "buttons", {});
        }    
    
        function sendCancelRequest() {
            $.ajax({
                url: 'client.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    'session' : passbyme.getSession(),
                    'cancel' : true
                },
                success: function (resp) {
                    passbyme.stopPolling();
                    removeButtons();
                    $('.pbm-dialog-right').html('Your transaction has been cancelled!');
                }
            });
        }

        function setIdentifier() {
            $('#pbm-identifier').text(passbyme.getIdentifier());
        }
        
        function closeEvent() {
            passbyme.stopPolling();
            $dialog.dialog('destroy').remove();
        }
    }); 
    
    /**
     * Character counter     
     */
    var maxLength = 255;
    $('#chars').text(maxLength + ' Characters Left');
    $('textarea').keyup(function() {
        var length = $(this).val().length;
        var length = maxLength-length;
        $('#chars').text(length + ' Characters Left');
    });
});