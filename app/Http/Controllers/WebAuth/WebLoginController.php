<?php

namespace App\Http\Controllers\WebAuth;

use App\Http\Controllers\Controller; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use App\Notifications\LoggedInNotification;
use App\Log;
use App\User;
use Google2FA;
use Session;
use Notification;
use Encryption;
use Hash;
use Illuminate\Support\Facades\Auth;

class WebLoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */
 

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function index()
    {
      return view('auth.login');
    }

    public function login(Request $request)
    {
      $email = $request->email; 
      $password = $request->password; 
      $remember = $request->remember ? true : false;

      if ($this->hasTooManyLoginAttempts($request)) {
        $this->fireLockoutEvent($request);
        return $this->sendLockoutResponse($request);
      }
 

      if (Auth::attempt(['email' => $email, 'password' => $password, 'active' => 1], $remember)) {
        $this->incrementLoginAttempts($request);
        return redirect()->back()->withInput()->withErrors(['message' => 'Either email or password is incorrect!']);
      }
  
      return redirect()->intended('/admin/dashboard');
    }

    public function logout()
    { 
      auth()->logout();
      return redirect('/')->with('message', 'You are now logged out!');
    }

    public function maxAttempts()
    {
        return 3;
    }

    public function decayMinutes()
    {
        return 10;
    }

    protected function sendLockoutResponse(Request $request)
    {
      $seconds = $this->limiter()->availableIn(
          $this->throttleKey($request)
      );
      if ($seconds > 60) {
        $minutes = floor($seconds / 60) > 1 ? floor($seconds / 60) + 1 . ' minutes' : floor($seconds / 60) + 1 . ' minute';
      } else {
        $minutes = $seconds > 1 ? $seconds . ' seconds' : $seconds . ' second';
      }
      $message = Lang::get('auth.throttle', ['seconds' => $seconds, 'minutes' => $minutes]);
      $errors = ['attempt' => $message];

      if ($request->expectsJson()) {
          return response()->json($errors, 423);
      }

      return redirect()->back()
          ->withInput($request->only($this->username(), 'remember'))
          ->withErrors($errors);
    }

}
