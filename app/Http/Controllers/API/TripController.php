<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Trip;

class TripController extends Controller
{
    public function index()
    {
        return response()->json(Trip::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'destination' => 'required|string|max:255',
            'price' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $trip = Trip::create($data);

        return response()->json($trip, 201);
    }

    public function show($id)
    {
        $trip = Trip::findOrFail($id);
        return response()->json($trip);
    }

    public function update(Request $request, $id)
    {
        $trip = Trip::findOrFail($id);
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'destination' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
        ]);

        $trip->update($data);

        return response()->json($trip);
    }

    public function destroy($id)
    {
        $trip = Trip::findOrFail($id);
        $trip->delete();
        return response()->json(null, 204);
    }
}
