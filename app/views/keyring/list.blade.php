@extends('layouts.master')

@section('title')
    {{ Lang::get('secretstore.keyrings') }}
@stop

@section('content')
    <h1>{{ Lang::get('secretstore.your_keyrings') }}</h1>
    <div id="keyrings">
        @foreach ($keyrings as $keyring)
            <?php $kid = $keyring->getId(); ?>
            <?php $class = $keyring->isUnlocked() ? "unlocked open" : "locked";?>
            <?php $lock = sprintf("keyring('%s').toggleLock(); return false;", $kid); ?>
            <?php $open = sprintf("keyring('%s').toggleOpen(); return false;", $kid); ?>
            <div class="keyring {{ $class }}" id="keyring-{{{ $kid }}}">
                <a class="lock-status" href="#" onclick="{{ $lock }}"></a>
                <a href="#" onclick="{{ $open }}" class="open">
                    {{ $keyring->getDisplayName() }}
                </a>
                <div class="elements-wrapper">
                    @if ($keyring->isUnlocked())
                        @include('keyring.show', compact('keyring'))
                    @endif
                </div>
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
        var lockUrl = "{{{ url('keyring/{keyringId}/lock') }}}";
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
                }],
                close: function() {
                    unlockDialog.find("form")[0].reset();
                    unlockDialog.find("#unlock-error").empty();
                }
            });
        });
    </script>
@stop
