<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $favorites = [];

        for ($i = 1; $i <= 3; $i++) {
            $name = $user->{"favorite{$i}_name"};
            $placeId = $user->{"favorite{$i}_place_id"};

            if ($name && $placeId) {
                $favorites[] = [
                    'name' => $name,
                    'place_id' => $placeId
                ];
            }
        }

        return response()->json($favorites);
    }

public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string',
        'place_id' => 'required|string',
    ]);

    $user = Auth::user();

    // Check if the mosque is already saved
    for ($i = 1; $i <= 3; $i++) {
        if ($user->{"favorite{$i}_place_id"} === $request->place_id) {
            return response()->json(['message' => 'Already saved'], 200);
        }
    }

    // Save in first available empty slot
    for ($i = 1; $i <= 3; $i++) {
        if (!$user->{"favorite{$i}_place_id"}) {
            $user->{"favorite{$i}_name"} = $request->name;
            $user->{"favorite{$i}_place_id"} = $request->place_id;
            $user->save();

            return response()->json(['message' => 'Favorite saved']);
        }
    }

    return response()->json(['message' => 'Max 3 favorites reached'], 400);
}


    public function destroy($place_id)
    {
        $user = Auth::user();

        for ($i = 1; $i <= 3; $i++) {
            if ($user->{"favorite{$i}_place_id"} === $place_id) {
                $user->{"favorite{$i}_name"} = null;
                $user->{"favorite{$i}_place_id"} = null;
                $user->save();

                return response()->json(['message' => 'Favorite removed']);
            }
        }

        return response()->json(['message' => 'Favorite not found'], 404);
    }
}



