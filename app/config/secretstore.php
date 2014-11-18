<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | Should 2-step verification be used.
    |--------------------------------------------------------------------------
    |
    | If you want to use 2-step verification set this flag to true.
    |
    */
    'use_2step_verification' => true,

    /*
    |--------------------------------------------------------------------------
    | The path to the smssender-cli executable.
    |--------------------------------------------------------------------------
    |
    | Smssender is used for sending 2-step verification codes to the user. You
    | can download it from http://smssender.gorrion.ch. This property must point
    | to the smssender-cli executable when 2-step verification is used.
    |
    */
    'smssender_path' => '/opt/smssender/smssender-cli',

    /*
    |--------------------------------------------------------------------------
    | The smssender account that should be used to send sms.
    |--------------------------------------------------------------------------
    |
    | You can set up multiple accounts in smssender. Specify which one that
    | should be used for sending sms.
    |
    */
    'smssender_account' => 'the-account',
);
