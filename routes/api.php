<?php

use Illuminate\Http\Request;
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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

// login
Route::post('login', 'API\UserController@login');

// register
Route::post('register', 'API\UserController@register');


Route::group(['middleware' => 'auth:api'], function(){

    // get a ticket information
    Route::post('ticketDetail', 'API\TicketController@getTicket');

    // get all tickets
    Route::post('getTicketList', 'API\TicketController@getTicketList');
    
    // for staff
    Route::group(['middleware' => ['auth', 'CheckAccess']], function(){
        // create ticket
        Route::post('create', 'API\TicketController@createTicket');

        // update ticket information when request back
        Route::post('updateTicketInfo', 'API\TicketController@updateTicket');

        // request more ticket information
        Route::post('requestTicketInfo', 'API\TicketController@requestTicket');

        // approve ticket
        Route::post('approveTicket', 'API\TicketController@approveTicket');
    });
});
