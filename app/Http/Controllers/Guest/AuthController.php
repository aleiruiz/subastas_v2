<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Core\LoginRequest;
use App\Http\Requests\Core\NewPasswordRequest;
use App\Http\Requests\Core\PasswordResetRequest;
use App\Http\Requests\Core\RegisterRequest;
use App\Services\Core\VerificationService;
use App\Services\Guest\AuthService;
use App\Services\User\UserService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;


class AuthController extends Controller
{
    protected $service;

    /**
     * AuthController constructor.
     * @param AuthService $service
     */
    public function __construct(AuthService $service)
    {
        $this->service = $service;
    }

    public function loginForm()
    {
        return view('backend.login');
    }

    /*
     * login admin
     */

    public function login(LoginRequest $request)
    {
        $response = $this->service->login($request);

        return response()->json(['loginResponse' => $response]);
    }

    public function adminLogin(LoginRequest $request)
    {
        $response = $this->service->login($request);

        if ($response[SERVICE_RESPONSE_STATUS]) {

            $redirectRoute = REDIRECT_ROUTE_TO_USER_AFTER_LOGIN;
            if (auth()->user()->role_id != FIXED_USER_SUPER_ADMIN)
            {
                $redirectRoute = REDIRECT_ROUTE_TO_FRONTEND_USER_AFTER_LOGIN;
            }

            return redirect()->route($redirectRoute)->with(SERVICE_RESPONSE_SUCCESS, $response[SERVICE_RESPONSE_MESSAGE]);
        }

        return redirect()->back()->with(SERVICE_RESPONSE_ERROR, $response[SERVICE_RESPONSE_MESSAGE]);
    }

    /**
     * @developer: M.G. Rabbi
     * @date: 2018-08-13 5:12 PM
     * @description:
     * @return RedirectResponse
     */
    public function logout()
    {
        Auth::logout();
        session()->flush();
        return redirect()->route('home');
    }

    public function regUser(RegisterRequest $request)
    {
        $parameters = $request->only(['first_name', 'last_name', 'email', 'username', 'password']);

        if ($user = app(UserService::class)->generate($parameters)) {
            app(VerificationService::class)->_sendEmailVerificationLink($user);

            return [
                SERVICE_RESPONSE_STATUS => true,
                SERVICE_RESPONSE_MESSAGE => __('Registration is successful.'),
            ];
        }

        return [
            SERVICE_RESPONSE_STATUS => false,
            SERVICE_RESPONSE_MESSAGE => __('Registration failed. Please try after sometime.'),
        ];
    }

    public function storeUser(RegisterRequest $request)
    {
        $response = $this->regUser($request);

        return response()->json(['regResponse' => $response]);
    }

    /**
     * @developer: M.G. Rabbi
     * @date: 2018-08-13 5:13 PM
     * @description:
     * @return Factory|View
     */
    public function forgetPassword()
    {
        return view('backend.forget_password');
    }

    /**
     * @developer: M.G. Rabbi
     * @date: 2018-08-13 5:13 PM
     * @description:
     * @param PasswordResetRequest $request
     * @return RedirectResponse
     */
    public function sendPasswordResetMail(PasswordResetRequest $request)
    {
        $response = $this->service->sendPasswordResetMail($request);
        $status = $response[SERVICE_RESPONSE_STATUS] ? SERVICE_RESPONSE_SUCCESS : SERVICE_RESPONSE_ERROR;

        return redirect()->route('forget-password.index')->with($status, $response[SERVICE_RESPONSE_MESSAGE]);
    }

    /**
     * @developer: M.G. Rabbi
     * @date: 2018-08-13 5:13 PM
     * @description:
     * @param Request $request
     * @param $id
     * @return Factory|View
     */
    public function resetPassword(Request $request, $id)
    {
        $data = $this->service->resetPassword($request, $id);

        return view('backend.reset_password', $data);
    }

    /**
     * @developer: M.G. Rabbi
     * @date: 2018-08-13 5:13 PM
     * @description:
     * @param NewPasswordRequest $request
     * @param $id
     * @return RedirectResponse
     */
    public function updatePassword(NewPasswordRequest $request, $id)
    {
        $response = $this->service->updatePassword($request, $id);
        $status = $response[SERVICE_RESPONSE_STATUS] ? SERVICE_RESPONSE_SUCCESS : SERVICE_RESPONSE_ERROR;

        return redirect()->route('login')->with($status, $response[SERVICE_RESPONSE_MESSAGE]);
    }
}
