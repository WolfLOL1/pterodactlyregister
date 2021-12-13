<?php

namespace Pterodactyl\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Auth\AuthManager;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Auth\Events\Failed;
use Illuminate\Http\RedirectResponse;
use Pterodactyl\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Pterodactyl\Contracts\Repository\UserRepositoryInterface;
use Pterodactyl\Exceptions\Repository\RecordNotFoundException;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Pterodactyl\Services\Users\UserCreationService;
use Pterodactyl\Http\Requests\Admin\UserFormRequest;
use Pterodactyl\Http\Requests\Base\RegisterFormRequest;

class RegisterController extends Controller
{
    use RegistersUsers;

    const USER_INPUT_FIELD = 'user';

    /**
     * @var \Illuminate\Auth\AuthManager
     */
    private $auth;

    /**
     * @var \Illuminate\Contracts\Cache\Repository
     */
    private $cache;

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    private $config;

    /**
     * @var Encrypter
     */
    private $encrypter;

    /**
     * @var \Pterodactyl\Contracts\Repository\UserRepositoryInterface
     */
    private $repository;

    /**
     * @var \PragmaRX\Google2FA\Google2FA
     */
    private $google2FA;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Lockout time for failed login requests.
     *
     * @var int
     */
    protected $lockoutTime;

    /**
     * After how many attempts should logins be throttled and locked.
     *
     * @var int
     */
    protected $maxLoginAttempts;

    /**
     * @var \Pterodactyl\Services\Users\UserCreationService
     */
    protected $creationService;
    /**
     * LoginController constructor.
     *
     * @param \Illuminate\Auth\AuthManager $auth
     * @param \Illuminate\Contracts\Cache\Repository $cache
     * @param \Illuminate\Contracts\Config\Repository $config
     * @param Encrypter $encrypter
     * @param \PragmaRX\Google2FA\Google2FA $google2FA
     * @param \Pterodactyl\Contracts\Repository\UserRepositoryInterface $repository
     * @param UserCreationService $creationService
     */
    public function __construct(
        AuthManager $auth,
        CacheRepository $cache,
        ConfigRepository $config,
        Encrypter $encrypter,
        Google2FA $google2FA,
        UserRepositoryInterface $repository,
        UserCreationService $creationService
    ) {
        $this->auth = $auth;
        $this->cache = $cache;
        $this->config = $config;
        $this->encrypter = $encrypter;
        $this->google2FA = $google2FA;
        $this->repository = $repository;
        $this->creationService = $creationService;

        $this->lockoutTime = $this->config->get('auth.lockout.time');
        $this->maxLoginAttempts = $this->config->get('auth.lockout.attempts');
    }


    public function register(RegisterFormRequest $request)
    {
        $request->request->add(['root_admin' => 0]); //set all registrations to normal users
        $user = $this->creationService->handle($request->normalize());
        $this->auth->guard()->login($user, true);


        return redirect()->route('index');
    }

}
