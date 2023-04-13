<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use App\Http\Controllers\Auth\OAuth2Controller;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


//route oauth2
Route::group(['prefix' => 'o-auth-2'], function () use ($router) {
    $router->get('credentials', function (Request $request) {
        $request->session()->put('state', $state = Str::random(40));

        $query = http_build_query([
            'client_id' => '98e95ccf-9be6-4e35-b2e2-27e3e0635035',
            'redirect_uri' => 'http://172.21.7.29:8081/o-auth-2/authorize',
            'response_type' => 'code',
            'scope' => '',
            'state' => $state,
            'prompt' => "login", // "none", "consent", or "login"
        ]);

        return redirect()->away('http://172.21.7.29:8000/oauth/authorize?' . $query);
    })->name('login.sso');

    $router->get('/authorize', function (Request $request) {

        // return $request->all();
        if ($request->error) {
            return $request->error;
        }

        $state = $request->session()->pull('state');

        throw_unless(
            strlen($state) > 0 && $state === $request->state,
            InvalidArgumentException::class,
            'Invalid state value.'
        );

        $response = Http::asForm()->post('http://172.21.7.29:8000/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => '98e95ccf-9be6-4e35-b2e2-27e3e0635035',
            'client_secret' => 'RJiLRSPSDiFPIQnj8GIdwjBHLkc4HWbM7hM0eNNZ',
            'redirect_uri' => 'http://172.21.7.29:8081/o-auth-2/authorize',
            'code' => $request->code,
        ]);

        // return $response->json();

        $user = Http::withToken($response['access_token'])->get(
            'http://172.21.7.29:8000/api/user'
        );

        $OAuth2Controller = new OAuth2Controller();
        return $OAuth2Controller->loginOrRegisterUseSilvia($user->json());
    });
});
// Route::get('/o-auth/login', function (Request $request) {
//     $request->session()->put('state', $state = Str::random(40));

//     $query = http_build_query([
//         'client_id' => '98e9123f-100a-4292-bb6f-9cc56af851e3',
//         'redirect_uri' => 'http://172.21.7.29:8081/callback',
//         'response_type' => 'code',
//         'scope' => '',
//         'state' => $state,
//         'prompt' => "login", // "none", "consent", or "login"
//     ]);

//     return redirect('http://172.21.7.29:8000/oauth/authorize?' . $query);
// });

// Route::get('/login', function () {
//     // $query = http_build_query([
//     //     'name' => 'test_1',
//     //     'redirect' => 'http://localhost:8000' . '/callback',
//     // ]);

//     $token = Http::asForm()->post(
//         'http://172.21.7.29:8000' . '/oauth/clients',
//         [
//             'name' => 'test_1',
//             'redirect' => 'http://172.21.7.29:8081' . '/callback'
//         ]
//     );

//     return $token->json();
// });

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
