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

        <form method="post" action="{{ url('login') }}">
            <fieldset>
                <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">

                <input type="text" id="username" name="username"
                    placeholder="{{ Lang::get('secretstore.login_username') }}"
                    value="{{ Input::old('username') }}"
                    class="text ui-widget-content ui-corner-all">
                <input type="password" id="password" name="password"
                    placeholder="{{ Lang::get('secretstore.login_password') }}"
                    class="text ui-widget-content ui-corner-all">

                <button type="submit" class="btn">
                    {{ Lang::get('secretstore.login_sign_in') }}
                </button>
            </fieldset>
        </form>
    </div>
    <style>
        #content {
            margin: 0 auto;
            max-width: 500px;
        }

        form input {
            display: block;
            margin: 5px auto;
            width: 240px;
        }

        form button {
            margin-top: 10px;
        }
    </style>
@stop
