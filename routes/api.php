<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

/* this is the route for the users controller
for example, if you want to get all users, you can use the following URL:
http://localhost:8000/api/user
*/
Route::group([
    'prefix' => '/user',
    'middleware' => 'cors',
    'headers' => [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ],
], function () {
    // All routes go here
    Route::get('/', 'Api\UsersController@index')->middleware('auth.jwt');
    Route::post('/register', 'Api\UsersController@store')->middleware('basic.token');
    Route::post('/login', 'Api\UsersController@login')->middleware('basic.token');
    Route::get('/{id}', 'Api\UsersController@show')->middleware('auth.jwt');
    Route::put('/{user}', 'Api\UsersController@update')->middleware('auth.jwt');
    Route::delete('/{id}', 'Api\UsersController@destroy')->middleware('auth.jwt');
    Route::get('/address/{id}', 'Api\UsersController@userAddress')->middleware('auth.jwt');
});

/* this is the route for the addresses controller
for example, if you want to get all addressess, you can use the following URL:
http://localhost:8000/api/address
*/
Route::group([
    'prefix' => '/address',
    'middleware' => 'cors',
    'headers' => [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ],
], function () {
    // All routes go here
    Route::get('/', 'Api\AddressesController@index')->middleware('auth.jwt');
    Route::post('/', 'Api\AddressesController@store')->middleware('basic.token');
    Route::get('/{id}', 'Api\AddressesController@show')->middleware('auth.jwt');
    Route::put('/{address}', 'Api\AddressesController@update')->middleware('auth.jwt');
    Route::delete('/{id}', 'Api\AddressesController@destroy')->middleware('auth.jwt');
});

Route::get('/getFormulasYear', 'Api\FormulasController@countRequestsByYear');
Route::get('/getAllFormulasByPatient/{id}', 'Api\FormulasController@getAllFormulasByPatient')->middleware('auth.jwt');
Route::get('/getAllPharmacies', 'Api\UsersController@getAllPharmacies')->middleware('auth.jwt');
Route::get('/getAllUsersAdress', 'Api\UsersController@getUsersWithAddresses')->middleware('auth.jwt');

/* this is the route for the formulas controller
for example, if you want to get all formulas, you can use the following URL:
http://localhost:8000/api/formula
*/
Route::group([
    'middleware' => ['auth.jwt', 'cors'],
    'prefix' => '/formula',
    'headers' => [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ],
], function () {
    // All routes go here
    Route::get('/', 'Api\FormulasController@index');
    Route::get('/all/{id}', 'Api\FormulasController@getAllById');
    Route::post('/', 'Api\FormulasController@store');
    Route::get('/{id}', 'Api\FormulasController@show');
    Route::put('/{formula}', 'Api\FormulasController@update');
    Route::delete('/{id}', 'Api\FormulasController@destroy');
});


/* this is the route for the statuses controller
for example, if you want to get all statuses, you can use the following URL:
http://localhost:8000/api/status
*/
Route::group([
    'middleware' => ['auth.jwt', 'cors'],
    'prefix' => '/status',
    'headers' => [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ],
], function () {
    // All routes go here
    Route::get('/', 'Api\StatusesController@index');
    Route::post('/', 'Api\StatusesController@store');
    Route::get('/{id}', 'Api\StatusesController@show');
    Route::put('/{status}', 'Api\StatusesController@update');
    Route::delete('/{id}', 'Api\StatusesController@destroy');
});


$routes = glob(__DIR__ . '/api/*.php');
foreach ($routes as $route) {
    require $route;
}
