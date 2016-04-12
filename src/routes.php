<?php

// Bloku AJAX pieprasījumi
Route::post('/block_ajax', array('as' => 'block_ajax',  'middleware' => 'public_ajax', 'uses'=>'mindwo\pages\BlockAjaxController@getData'));

// Kalendāra ieraksti
Route::post('/event', array('as' => 'event',  'middleware' => 'public_ajax', 'uses'=>'mindwo\pages\CalendarController@getEvent'));

// Lapas
Route::get('/{id}/{item}', array('as' => 'page',  'middleware' => 'public', 'uses'=>'mindwo\pages\PagesController@showPageItem'));
Route::get('/{id}', array('as' => 'page',  'middleware' => 'public', 'uses'=>'mindwo\pages\PagesController@showPage'));
Route::post('/{id}', array('as' => 'page',  'middleware' => 'public', 'uses'=>'mindwo\pages\PagesController@showPage'));

// Noklusētā lapa
Route::get('/', array('as' => 'home', 'middleware' => 'public', 'uses'=>'mindwo\pages\PagesController@showRoot'));