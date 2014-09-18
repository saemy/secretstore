@extends('layouts.master')

@section('title')
    {{ Lang::get('secretstore.keyring') }}
@stop

@section('content')
    <h1>{{ Lang::get('secretstore.your_keyrings') }}</h1>
	<div id="keyrings">
		@foreach($keyrings as $keyring)
			<div class="keyring" id="{{ $keyring->getName() }}">
			    <a href="{{ url('keyring', $keyring->getName()) }}">
			        {{ $keyring->getName() }}
			    </a>
			</div>
		@endforeach
	</div>
@stop