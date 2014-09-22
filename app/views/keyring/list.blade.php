@extends('layouts.master')

@section('title')
    {{ Lang::get('secretstore.keyring') }}
@stop

@section('content')
    <h1>{{ Lang::get('secretstore.your_keyrings') }}</h1>
	<div id="keyrings">
		@foreach ($keyrings as $keyring)
			<?php $onclick = !$keyring->isUnlocked() ? "unlockKeyring('".$keyring->getId()."'); return false" : "";?>
			<?php $class = $keyring->isUnlocked() ? "unlocked" : "";?>
			<div class="keyring {{ $class }}" id="keyring-{{{ $keyring->getId() }}}">
			    <a href="#" onclick="{{{ $onclick }}}" class="unlock">
			        {{ $keyring->getDisplayName() }}
			    </a>
			    @if ($keyring->isUnlocked())
			        @include('keyring.show', compact('keyring'))
			    @endif
			</div>
		@endforeach
	</div>

	<script>
        var unlockUrl = "{{{ url('keyring/{keyringId}/unlock') }}}";
        var secretUrl = "{{{ url('keyring/{keyringId}/secret/{entryId}') }}}";
	</script>
@stop
