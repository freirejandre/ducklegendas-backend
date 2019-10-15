<?php

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Subtitle;
use App\Category;

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
// SE FOR USAR VERIFICAÇÃO COLOCAR O MIDDLEWARE 'verified' NAS ROTAS QUE QUISER

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//ROTA DE TESTE DE RELACIONAMENTOS ENTRE TABELAS
Route::get('/relacionamento', function (Request $request) {

    $categories = Category::with('subtitles')->get();
    $subtitles = Subtitle::with('category', 'author')->get();
    $users = User::with('subtitles.category', 'subtitles.author')
        ->has('subtitles')->get();

    return response()->json([
        'categories' =>$categories,
        'subtitles' =>$subtitles,
        'users' =>$users
    ],200); 
});

// GRUPO DE ROTAS PARA CONTROLADORES DENTRO DA PASTA Api/
Route::group(['namespace' => 'Api'],function () {

    Route::middleware('auth:api')->group(function () {
        Route::group(['prefix' => 'categories'],function () {
            Route::get('/{id}', 'CategoryController@findCategory');
            Route::post('/', 'CategoryController@getAll');
            Route::post('/store', 'CategoryController@store');
            Route::patch('/{id}', 'CategoryController@update');
            Route::delete('/{id}', 'CategoryController@delete');
        });
        Route::post('subtitles', 'SubtitleController@getAll');
        Route::post('progress', 'SubtitleProgressController@getAll');
    });
});

// GRUPO DE ROTAS PARA CONTROLADORES DENTRO DA PASTA Api/Auth/
Route::group(['namespace' => 'Api\Auth'],function () {
    
    //ROTAS DE AUTENTICAÇÃO
    Route::post('login', 'UserController@login');
    Route::middleware('auth:api')->group(function () {
        Route::group(['prefix' => 'users'],function () {
            Route::get('/{id}', 'UserController@findUser');
            Route::post('/', 'UserController@getAll');
            Route::post('/store', 'UserController@register');
            Route::patch('/{id}', 'UserController@registerUpdate');
            Route::delete('/{id}', 'UserController@delete');
        });
    });

    

    //ROTAS DE VERIFICAÇÃO DE E-MAIL
    Route::group(['prefix' => 'email'], function () {  
        Route::get('verify/{id}', 'VerificationController@verify')->name('verification.verify');
        Route::get('resend', 'VerificationController@resend')->name('verification.resend');
        Route::get('notice', 'VerificationController@notice')->name('verification.notice');
    });

    //ROTAS DE TROCA DE SENHA
    Route::group(['prefix' => 'password'], function () {    
        Route::post('create', 'PasswordResetController@create');
        Route::post('reset', 'PasswordResetController@reset');
    });

});

// ROTA PARA DELETAR TOKENS DE AUTENTICAÇÃO
Route::get('error', function (){
    DB::table('oauth_access_tokens')->whereDate('expires_at','<',Carbon::now()->toDateTimeString())->delete();
    return response()->json(['error' => ['Não autenticado!']], 401);
})->name('error');
