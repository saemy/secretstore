$.ajaxSetup({
   headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') }
});

function unlockKeyring(keyringId) {
    var url = unlockUrl.replace("{keyringId}", keyringId);

    $.post(url, {
        password: "test2",
    })
    .fail(function(jqxhr, textStatus, error) {
        alert("An error occured: " + jqxhr.responseJSON.error.message);
    })
    .done(function(data) {
        var keyring = $("#keyring-" + keyringId);
        keyring
            .addClass('unlocked')
            .append(data);

        keyring.children("a.unlock")[0].onclick = null;
    });
}

function showSecret(keyringId, entryId) {
    var url = secretUrl
        .replace("{keyringId}", keyringId)
        .replace("{entryId}", entryId);
    
    $.get(url)
    .fail(function(jqxhr, textStatus, error) {
        alert("An error occured: " + jqxhr.responseJSON.error.message);
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
