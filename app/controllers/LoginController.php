<?php

use Secretstore\Repositories\KeyringRepositoryInterface;

class TwoStepAuthCode {
    /**
     * How long the 2step verification should be.
     * @var int
     */
    const TWOSTEP_CODE_NUM_DIGITS = 6;

    /**
     * How long the 2step verification code is valid.
     * @var int
     */
    const TWOSTEP_CODE_VALID_S = 120;

    private $code;
    private $expiryDate;

    public function __construct($code, $expiryDate) {
        $this->code = $code;
        $this->expiryDate = $expiryDate;
    }

    static function create() {
        $expiryDate = (new DateTime())->add(new DateInterval(
                sprintf('PT%dS', self::TWOSTEP_CODE_VALID_S)));
        $code = mt_rand(pow(10, self::TWOSTEP_CODE_NUM_DIGITS - 1),
                        pow(10, self::TWOSTEP_CODE_NUM_DIGITS) - 1);

        return new TwoStepAuthCode($code, $expiryDate);
    }

    public function code() {
        return $this->code;
    }

    public function isExpired() {
        return (new DateTime()) > $this->expiryDate;
    }
}

class LoginController extends BaseController {

    // Session keys.
    const LOGIN_USERNAME = 'login_username';
    const LOGIN_PASSWORD = 'login_password';
    const TWOSTEP_CODE = 'login_2step_code';

    // Settings.
    private $do2StepVerification;
    private $doAutoUnlockKeyrings;

    private $keyringRepo;

    /**
     * Create a new login controller instance.
     *
     * @param $keyringRepo The keyring repository that is used to auto-unlock
     *                     keyrings.
     * @return LoginController
     */
    public function __construct(KeyringRepositoryInterface $keyringRepo) {
        parent::__construct();

        $this->keyringRepo = $keyringRepo;

        $this->do2StepVerification =
            Config::get('secretstore.use_2step_verification');
        $this->doAutoUnlockKeyrings =
            Config::get('secretstore.auto_unlock_keyrings');
    }

    /**
     * Get the user login view.
     */
    public function getIndex() {
        return View::make('login');
    }

    /**
     * Handle a user login attempt.
     */
    public function postIndex() {
        $username = mb_strtolower(Input::get('username'));
        $password = Input::get('password');
        $credentials = array('username' => $username, 'password' => $password);

        if ($this->do2StepVerification) {
            // Verifies the login.
            if (Auth::validate($credentials)) {
                // Remembers the user s.t. he can be logged in after 2step
                // verification.
                Session::set(self::LOGIN_USERNAME, $username);
                Session::set(self::LOGIN_PASSWORD, Crypt::encrypt($password));
                return Redirect::to('login/verify');
            }
        } else {
            // Attempts the login.
            if (Auth::attempt($credentials)) {
                $this->autoUnlockKeyrings($password);
                return Redirect::intended();
            }
        }

        // The login attempt failed.
        return Redirect::back()
            ->withInput(Input::except('password'))
            ->with('login_errors', true);
    }

    /**
     * Gets the 2-factor verification page.
     */
    public function getVerify() {
        // Checks if we even do 2-step verification.
        if (!$this->do2StepVerification) {
            return Redirect::to('login');
        }

        // Ensures that the 2step code is not expired.
        if (!$this->ensure2StepCodeNotExpired($response, false)) {
            return $response;
        }

        // Checks if the login user is set.
        if (!Session::has(self::LOGIN_USERNAME)) {
            return Redirect::to('login');
        }

        // Generates a new 2step verification code if needed.
        $login_username = Session::get(self::LOGIN_USERNAME);
        if (!Session::has(self::TWOSTEP_CODE)) {
            try {
                $this->reset2StepCode($login_username);
            } catch (Exception $e) {
                return Response::make(
                        'Failed to send the verification code.', 500);
            }
        }

        return View::make('login_verify');
    }

    /**
     * Handles the verification attempt.
     */
    public function postVerify() {
        // Checks if we even do 2-step verification.
        if (!$this->do2StepVerification) {
            return Redirect::to('login');
        }

        // Ensures that the 2step code exists and is not expired.
        if (!$this->ensure2StepCodeNotExpired($response, true)) {
            return $response;
        }

        // Checks the code.
        $code = Input::get('verify_code');
        $realCode = $this->get2StepCode()->code();
        if (strcmp($code, $realCode) != 0) {
            // TODO add countermeasure for broadcast.
            return Redirect::back()
                ->with('invalid_code', true);
        }

        // Actually logs in the user.
        $user = User::where('username', '=', Session::get(self::LOGIN_USERNAME))
            ->firstOrFail();
        Auth::login($user);

        // Auto-unlocks keyrings.
        $password = Crypt::decrypt(Session::get(self::LOGIN_PASSWORD));
        $this->autoUnlockKeyrings($password);

        $this->unsetLoginInfo();

        return Redirect::intended();
    }

    /**
     * Returns false if the 2step verification code is expired. If $mustExist is
     * set we also require a code to be set.
     *
     * @param $reponse The redirect response to send to the user if the code is
     *                 expired. Only set if false is returned.
     * @param $mustExist True if we require a code to be around.
     * @return bool True, if no code is set or it is valid.
     */
    private function ensure2StepCodeNotExpired(&$response, $mustExist) {
        // Checks if there is a 2step code.
        $r = Redirect::to('login');
        if (Session::has(self::TWOSTEP_CODE)) {
            // Checks the expiry date.
            $twostep_code = $this->get2StepCode();
            if (!$twostep_code->isExpired()) {
                return true;
            }

            $r->with('error_msg', Lang::get('secretstore.login_verify_timeout'));
        } elseif (!$mustExist) {
            return true;
        }

        // Forgets about the login attempt.
        $this->unsetLoginInfo();

        $response = $r;
        return false;
    }

    /**
     * Unlocks all keyrings that are protected by the given password.
     *
     * @param $password The password to try unlocking the keyrings with.
     */
    private function autoUnlockKeyrings($password) {
        if (!$this->doAutoUnlockKeyrings) {
            return;
        }

        // The user is now logged in whereas the keyring repo might have been 
        // initialized without a valid user around.
        $this->keyringRepo->reloadUser();

        $this->keyringRepo->unlockAll($password);
    }

    /**
     * Forgets about any login specific state.
     */
    private function unsetLoginInfo() {
        Session::forget(self::LOGIN_USERNAME);
        Session::forget(self::LOGIN_PASSWORD);
        Session::forget(self::TWOSTEP_CODE);
    }

    /**
     * Resets the 2step verification code and sends it to the given user.
     *
     * @param string $username The user to send the code to.
     */
    private function reset2StepCode($username) {
        // Re-creates a code.
        $code = TwoStepAuthCode::create();

        // Sends it to the user.
        $user = User::where('username', '=', Session::get(self::LOGIN_USERNAME))
            ->firstOrFail();
        $this->sendCodeToUser($user, $code);

        // Saves the code.
        Session::set(self::TWOSTEP_CODE, serialize($code));
    }

    /**
     * Sends the given code to given user. It is expected that the given user's
     * phone number is set and is valid.
     *
     * @param User $user
     * @param TwoStepAuthCode $code
     */
    private function sendCodeToUser($user, $code) {
        // Loads the config params.
        $smssender_cli = Config::get('secretstore.smssender_path');
        $account = Config::get('secretstore.smssender_account');

        // Assembles the command.
        $command = sprintf('%s --account=%s --recipient=%s --message=%s',
                escapeshellcmd($smssender_cli), escapeshellarg($account),
                escapeshellarg($user->phone), escapeshellarg(
                sprintf(Lang::get('secretstore.2step_sms_msg'), $code->code())));

        // Sends the message.
        exec($command, $output, $exit_code);
        if ($exit_code != 0) {
            throw new Exception('Could not send code to user.');
        }
    }

    /**
     * Returns the 2step verification code from the session.
     *
     * @return TwoStepAuthCode
     */
    private function get2StepCode() {
        return unserialize(Session::get(self::TWOSTEP_CODE));
    }

    /**
     * Log out the user
     */
    public function getLogout() {
        Auth::logout();
        Session::flush();
        return Redirect::to('login');
    }

}

