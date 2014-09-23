@extends('layouts.master')

@section('title')
    {{ Lang::get('secretstore.keyrings') }}
@stop

@section('content')
    <h1>{{ Lang::get('secretstore.your_keyrings') }}</h1>
    <div id="keyrings">
        @foreach ($keyrings as $keyring)
            <?php $onclick = !$keyring->isUnlocked() ? "unlockKeyring('".$keyring->getId()."'); return false" : "";?>
            <?php $class = $keyring->isUnlocked() ? "unlocked" : "locked";?>
            <div class="keyring {{ $class }}" id="keyring-{{{ $keyring->getId() }}}">
                <a href="#" onclick="{{{ $onclick }}}" class="unlock">
                    {{ $keyring->getDisplayName() }}
                    <span class="lock-status"></span>
                </a>
                @if ($keyring->isUnlocked())
                    @include('keyring.show', compact('keyring'))
                @endif
            </div>
        @endforeach
    </div>

    <div id="unlock-dialog">
        <span id="unlock-error"></span>
        <form>
            <fieldset>
                <p>{{ Lang::get('secretstore.keyring_unlock_text') }}</p>
                <input type="password" name="password" id="unlock-password"
                    placeholder="{{ Lang::get('secretstore.keyring_password') }}"
                    class="text ui-widget-content ui-corner-all">
                <!-- Allow form submission with keyboard without duplicating the dialog button -->
                <input type="submit" tabindex="-1" style="display: none">
            </fieldset>
        </form>
    </div>

    <script>
        var unlockUrl = "{{{ url('keyring/{keyringId}/unlock') }}}";
        var secretUrl = "{{{ url('keyring/{keyringId}/secret/{entryId}') }}}";
        var unlockDialog;

        $(function() {
            unlockDialog = $("#unlock-dialog").dialog({
                autoOpen: false,
                dialogClass: "no-close",
                draggable: false,
                modal: true,
                buttons: [{
                    text: "{{ Lang::get('secretstore.keyring_unlock') }}",
                    click: function() { $("#unlock-dialog form").submit(); },
                }]
            });
        });
    </script>
@stop
