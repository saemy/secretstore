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

function unlockKeyring(keyringId) {
    var doUnlock = function(password) {
        var url = unlockUrl.replace("{keyringId}", keyringId);
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
            var keyring = $("#keyring-" + keyringId);
            keyring
                .removeClass('locked')
                .addClass('unlocked')
                .append(data);

            keyring.children("a.unlock")[0].onclick = null;
            
            unlockDialog.dialog("close");
        });
    };
    
    unlockDialog.on("dialogclose", function() {
        unlockDialog.find("form")[0].reset();
            unlockDialog.find("#unlock-error").empty();
    });
    unlockDialog.find("form").on("submit", function() {
        doUnlock($("#unlock-password").val());
        return false;
    });
    unlockDialog.dialog("open");
}

function showSecret(keyringId, entryId) {
    var url = secretUrl
        .replace("{keyringId}", keyringId)
        .replace("{entryId}", entryId);
    
    $.get(url)
    .fail(function(jqxhr, textStatus, error) {
        alert(evalError(jqxhr));
    })
    .done(function(data) {
        var secret = $("#entry-" + keyringId + "-" + entryId + " .secret span");
        secret.html(data);

        $("#entry-" + keyringId + "-" + entryId + " .secret a.hide").show();
        setTimeout(function(){ hideSecret(keyringId, entryId); }, 60000);
    });

    $("#entry-" + keyringId + "-" + entryId + " .secret a.show").hide();
}

function hideSecret(keyringId, entryId) {
    var secret = $("#entry-" + keyringId + "-" + entryId + " .secret span");
    secret.empty();
    
    $("#entry-" + keyringId + "-" + entryId + " .secret a.hide").hide();
    $("#entry-" + keyringId + "-" + entryId + " .secret a.show").show();
}
