<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Trip;
use App\Models\Customer;

class BookingController extends Controller
{
    public function index()
    {
        return response()->json(Booking::with(['trip', 'customer'])->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'trip_id' => 'required|exists:trips,id',
            'customer_id' => 'required|exists:customers,id',
            'seats' => 'required|integer|min:1',
        ]);

        $trip = Trip::findOrFail($data['trip_id']);
        $customer = Customer::findOrFail($data['customer_id']);

        $total = $trip->price * $data['seats'];

        $booking = Booking::create([
            'trip_id' => $data['trip_id'],
            'customer_id' => $data['customer_id'],
            'seats' => $data['seats'],
            'total_price' => $total,
            'status' => 'confirmed',
        ]);

        return response()->json($booking->load(['trip', 'customer']), 201);
    }

    public function show($id)
    {
        $booking = Booking::with(['trip', 'customer'])->findOrFail($id);
        return response()->json($booking);
    }

    public function update(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $data = $request->validate([
            'seats' => 'sometimes|required|integer|min:1',
            'status' => 'sometimes|required|string',
        ]);

        if (isset($data['seats'])) {
            $booking->total_price = $booking->trip->price * $data['seats'];
        }

        $booking->fill($data);
        $booking->save();

        return response()->json($booking->load(['trip', 'customer']));
    }

    public function destroy($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->delete();
        return response()->json(null, 204);
    }
}
