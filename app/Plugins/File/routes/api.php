<?php

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

Route::middleware(['before' => 'jwt.auth', 'after' => 'jwt.refresh'])->prefix('')->group(function () {
    Route::get('/{id}/as_html', 'FileController@asHtml')->where('id', '[0-9]+');
    Route::get('/{id}/archive/list', 'FileController@getArchiveFileList')->where('id', '[0-9]+');
    Route::get('/{id}/archive/download', 'FileController@downloadArchivedFile')->where('id', '[0-9]+');
    // Filters
    Route::get('/filter', 'FileController@getFilterOptions');

    Route::post('/', 'FileController@getFiles');
    Route::post('/{id}/replace', 'FileController@replaceFileContent')->where('id', '[0-9]+');
    Route::post('/new', 'FileController@uploadFile');
    Route::post('/export', 'FileController@exportFiles');
    // File Batch Delete
    Route::post('/batch_delete', 'FileController@deleteFilesBatch');

    Route::patch('/{id}', 'FileController@patchProperty')->where('id', '[0-9]+');
    Route::patch('/{id}/tag', 'FileController@patchTags')->where('id', '[0-9]+');

    Route::put('/{id}/link', 'FileController@linkToEntity')->where('id', '[0-9]+');

    Route::delete('/{id}', 'FileController@deleteFile')->where('id', '[0-9]+');
    Route::delete('/{id}/link/{eid}', 'FileController@unlinkEntity')->where('id', '[0-9]+')->where('eid', '[0-9]+');
});
