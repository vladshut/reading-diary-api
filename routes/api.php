<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

Auth::routes(['verify' => true]);

Route::middleware('auth:api')->get('/user', static function (Request $request) {
    return $request->user();
});

Route::get('dictionary', 'DictionaryController@index');
Route::get('public-report/{publicKey}', 'PublicUserBookController@show');
Route::get('public-report/{publicKey}/sections', 'PublicUserBookController@sections');

Route::group(['middleware' => ['auth:api']], static function () {
    Route::get('books/my/{userBook}', 'UserBookController@show');
    Route::post('books/my/{userBook}/start-reading', 'UserBookController@startReading');
    Route::post('books/my/{userBook}/finish-reading', 'UserBookController@finishReading');
    Route::post('books/my/{userBook}/resume-reading', 'UserBookController@resumeReading');
    Route::post('books/my/{userBook}/make-public', 'UserBookController@makePublic');
    Route::post('books/my/{userBook}/make-private', 'UserBookController@makePrivate');
    Route::get('books/my', 'UserBookController@myBooks');
    Route::post('books/my/{userBook}/publish-report', 'UserBookController@publishReport');
    Route::post('books/my/{userBook}/unpublish-report', 'UserBookController@unpublishReport');

    Route::get('books/my/{userBook}/sections', 'BookSectionController@index');
    Route::post('books/my/{userBook}/sections', 'BookSectionController@store');
    Route::put('books/my/sections/{section}', 'BookSectionController@update');
    Route::delete('books/my/sections/{section}', 'BookSectionController@delete');

    Route::post('books/my/sections/{section}/report-items', 'ReportItemController@store');
    Route::post('books/my/sections/{section}/report-items/save-book-section-report', 'ReportItemController@saveBookSectionReport');
    Route::get('books/my/sections/{section}/report-items', 'ReportItemController@index');
    Route::put('books/my/sections/report-items/{reportItem}', 'ReportItemController@update');
    Route::delete('books/my/sections/report-items/{reportItem}', 'ReportItemController@destroy');

    Route::post('books/add-new', 'UserBookController@addNew');
    Route::post('books/add-existing', 'UserBookController@addExisting');

    Route::get('authors/search', 'AuthorController@search');

    Route::get('books/search', 'BookController@search');

    Route::post('/auth/change-password', 'AuthController@changePassword');

    Route::get('users', 'UserController@index');
    Route::get('users/{user}/followers', 'UserController@followers');
    Route::get('users/{user}/followees', 'UserController@followees');
    Route::get('users/{user}/followers-ids', 'UserController@followersIds');
    Route::get('users/{user}/followees-ids', 'UserController@followeesIds');
    Route::post('users/{followee}/follow', 'UserController@follow');
    Route::post('users/{followee}/unfollow', 'UserController@unfollow');

    Route::get('users/{user}/books/top-languages', 'UserBookController@topLanguages');
    Route::get('users/{user}/books/top-authors', 'UserBookController@topAuthors');
    Route::get('users/{user}/books/top-statuses', 'UserBookController@topStatuses');

    Route::resources([
        'authors' => 'AuthorController',
        'books' => 'BookController',
    ]);
});


Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {
    Route::post('login', 'AuthController@login');
    Route::post('send-reset-password-mail', 'AuthController@sendResetPasswordMail');
    Route::post('reset-password', 'AuthController@resetPassword');
    Route::post('register', 'AuthController@register');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');

    Route::get('login/socialite/{type}', 'Auth\SocialiteController@redirectToProvider');
    Route::get('login/socialite/{type}/callback', 'Auth\SocialiteController@handleProviderCallback');
});

Route::group(['prefix' => 'files'], static function () {
    Route::get('process', 'FilepondController@upload');
    Route::post('process', 'FilepondController@upload');
    Route::delete('process', 'FilepondController@delete');
    Route::get('{model}/{id}/{mediaName}/{mediaId}/{fileName}', 'DownloadMediaController@download');
    Route::get('', 'FilepondController@load');
});

Route::group(['prefix' => 'users'], static function () {
    Route::get('{user}', 'UserController@get');
    Route::put('{user}', 'UserController@update');
    Route::get('{user}/resend-verification', 'Auth\VerificationController@resend');
});
