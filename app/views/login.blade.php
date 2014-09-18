@extends('layouts.master')

@section('title')
    {{ Lang::get('secretstore.login') }}
@stop

@section('content')
    <div id="login">
        <h1>{{ Lang::get('secretstore.login') }}</h1>
        @if (Session::has('login_errors'))
            <div class="alert alert-block alert-error">
                <p>{{ Lang::get('secretstore.login_incorrect') }}</p>
            </div>
        @endif

        <form method="post" action="{{ url('login') }}" class="form-horizontal">
            <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">

            <p>
                <input type="text" id="username" name="username"
                    placeholder="{{ Lang::get('secretstore.login_username') }}"
                    value="{{ Input::old('username') }}">
            </p>
            <p>
                <input type="password" id="password" name="password"
                    placeholder="{{ Lang::get('secretstore.login_password') }}">
            </p>
            <button type="submit" class="btn">
                {{ Lang::get('secretstore.login_sign_in') }}
             </button>
        </form>
    </div>
@stop
