<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
        ]);

        $file = $request->file('file');
        $directory = public_path('media/articles');
        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $name = now()->format('YmdHis').'-'.Str::lower(Str::random(8)).'.'.$file->getClientOriginalExtension();
        $file->move($directory, $name);

        return response()->json([
            'location' => '/media/articles/'.$name,
        ]);
    }
}
