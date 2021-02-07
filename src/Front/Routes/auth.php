<?php
Auth::routes();
$prefixMember = sc_config('PREFIX_MEMBER') ?? 'customer';
$langUrl = config('app.seoLang'); 

//--Auth
Route::group(
    [
        'namespace' => 'Auth', 
        'prefix' => $langUrl.$prefixMember
    ],
    function ($router) use ($suffix) {
        $router->get('/login'.$suffix, 'LoginController@showLoginFormProcessFront')
            ->name('login');
        $router->post('/login'.$suffix, 'LoginController@login')
            ->name('postLogin');

        $router->get('/register'.$suffix, 'RegisterController@showRegisterFormProcessFront')
            ->name('register');
        $router->post('/register'.$suffix, 'RegisterController@register')
            ->name('postRegister');

        $router->post('/logout', 'LoginController@logout')
            ->name('logout');

        $router->get('/forgot'.$suffix, 'ForgotPasswordController@showLinkRequestFormProcessFront')
            ->name('forgot');

        $router->get('/password/reset/{token}', 'ResetPasswordController@showResetFormProcessFront')
            ->name('password.reset');
        $router->post('/password/email', 'ForgotPasswordController@sendResetLinkEmail')
            ->name('password.email');
        $router->post('/password/reset', 'ResetPasswordController@reset');
    }
);

//Email verify
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect(sc_route('home'));
})->middleware(['auth', 'signed'])->name('verification.verify');

//Resending The Verification Email
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.resend');


if ($suffix) {
    Route::get('/login', function () {
        return redirect(sc_route('login'));
    });
}


//End Auth