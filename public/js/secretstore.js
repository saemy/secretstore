$.ajaxSetup({
   headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') }
});

function evalError(jqxhr) {
    switch (jqxhr.status) {
        case 403: return "Authorization required.";
        case 404: return "The ressource can not be found.";
        case 500:
            return "An internal error occured: " + jqxhr.responseJSON.error.message;
        default:
            return "An unknown error occured: " + jqxhr.responseJSON.error.message;
    }
}

(function ($) {
    $.Keyring = function(id) {
        this.id = id;
        this.element = $("#keyring-" + id);
        this.data = this.element.children(".elements-wrapper").html();
    };

    $.Keyring.prototype = {

        // Lock/unlock
            
        toggleLock: function() {
            if (this.element.hasClass('locked')) {
                this.unlock();
            } else {
                this.lock();
            }
        },
        
        unlock: function() {
            var that = this;
            
            var doUnlock = function(password) {
                var url = unlockUrl.replace("{keyringId}", that.id);
                $.post(url, {
                    password: password,
                })
                .fail(function(jqxhr, textStatus, error) {
                    var text = jqxhr.status == 403
                        ? jqxhr.responseText
                        : evalError(jqxhr);
                    unlockDialog.find("#unlock-error").html(text);
                })
                .done(function(data) {
                    that.data = data;
                    that.element
                        .removeClass('locked')
                        .addClass('unlocked');
                    that.open();

                    unlockDialog.dialog("close");
                });
            };
    
            unlockDialog.find("form").on("submit", function() {
                doUnlock($("#unlock-password").val());
                return false;
            });
            unlockDialog.dialog("open");
        },
        
        lock: function() {
            var that = this;
            
            var url = lockUrl.replace("{keyringId}", that.id);
            $.get(url)
            .fail(function(jqxhr, textStatus, error) {
                alert(evalError(jqxhr));
            })
            .done(function(data) {
                that.close();
                that.element
                    .removeClass('unlocked')
                    .addClass('locked');
            });
        },
        
        
        // Open/close
        
        toggleOpen: function() {
            if (this.element.hasClass('locked')) {
                // Unlock it first.
                this.unlock();
            } else if (this.element.hasClass('closed')) {
                this.open();
            } else {
                this.close();
            }
        },
        
        open: function() {
            this.element
                .removeClass('closed')
                .addClass('open');
        },
        
        close: function() {
            this.element
                .removeClass('open')
                .addClass('closed');
        },
        
        
        // Secrets
        
        showSecret: function(entryId) {
            var that = this;
            
            var url = secretUrl
                .replace("{keyringId}", that.id)
                .replace("{entryId}", entryId);
            
            $.get(url)
            .fail(function(jqxhr, textStatus, error) {
                alert(evalError(jqxhr));
            })
            .done(function(data) {
                var secret = $("#entry-" + that.id + "-" + entryId + " .secret span");
                secret.html(data);
        
                $("#entry-" + that.id + "-" + entryId + " .secret a.hide").show();
                setTimeout(function(){ that.hideSecret(entryId); }, 60000);
            });
        
            $("#entry-" + that.id + "-" + entryId + " .secret a.show").hide();
        },
        
        hideSecret: function(entryId) {
            var secret = $("#entry-" + this.id + "-" + entryId + " .secret span");
            secret.empty();
            
            $("#entry-" + this.id + "-" + entryId + " .secret a.hide").hide();
            $("#entry-" + this.id + "-" + entryId + " .secret a.show").show();
        }
    };
}(jQuery));

function keyring(id) {
    return $("#keyring-" + id).data("keyring");
}

// Initializes the keyrings.
$(function() {
    $(".keyring").each(function() {
        $(this).data("keyring",
                     new $.Keyring($(this).attr("id").replace("keyring-", "")));
    });
});
