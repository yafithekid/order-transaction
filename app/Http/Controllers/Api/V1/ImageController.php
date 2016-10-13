<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ImageController extends Controller
{
    public function postUpload(Request $request){
        return response()->json([
            'status' => ResponseStatus::OK,
            'url' => 'http://lorempixel.com/200/200/'
        ]);
    }
}
