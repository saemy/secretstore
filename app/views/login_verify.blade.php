@extends('layouts.master')

@section('title')
    {{ Lang::get('secretstore.login') }}
@stop

@section('content')
    <div id="login">
        <h1>{{ Lang::get('secretstore.login_verify') }}</h1>
        @if (Session::has('invalid_code'))
            <div class="alert alert-block alert-error">
                <p>{{ Lang::get('secretstore.login_verify_incorrect') }}</p>
            </div>
        @endif

        <form method="post" action="{{ url('login/verify') }}">
            <fieldset>
                <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">

                <p>{{ Lang::get('secretstore.login_verify_description') }}</p>
                <input type="text" id="verify_code" name="verify_code"
                    placeholder="{{ Lang::get('secretstore.login_verify_verification') }}"
                    maxlength="6" class="text ui-widget-content ui-corner-all">

                <button type="submit" class="btn">
                    {{ Lang::get('secretstore.login_do_verify') }}
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
            width: 100px;
            text-align: center;
        }

        form button {
            margin-top: 10px;
        }
    </style>
@stop
