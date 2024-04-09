<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Address;

class AddressesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $addressess = Address::all();

        if ($addressess->count() > 0) {
            return response()->json([
                'message' => $addressess
            ], 200);
        } else {
            return response()->json([
                'message' => 'No addresses found'
            ], 404);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'city' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'postal_code' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->messages()
            ], 422);
        } else {
            $address = Address::create([
                'city' => $request->city,
                'street' => $request->street,
                'country' => $request->country,
                'postal_code' => $request->postal_code,
                'enabled' => true,
            ]);

            if ($address) {
                return response()->json([
                    'message' => 'Address created',
                    'address' => $address
                ], 201);
            } else {
                return response()->json([
                    'message' => 'Error creating address',
                ], 400);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $address = Address::find($id);

        if ($address) {
            return response()->json([
                'message' => $address
            ], 200);
        } else {
            return response()->json('No address found', 400);
        }

    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'city' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'postal_code' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->messages()
            ], 422);
        } else {
            $address = Address::find($id);
            if ($address) {
                $address->update([
                    'city' => $request->city,
                    'street' => $request->street,
                    'country' => $request->country,
                    'postal_code' => $request->postal_code,
                    'enabled' => true,
                ]);
                if ($address) {
                    return response()->json([
                        'message' => 'Address updated',
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'Error updating address',
                    ], 400);
                }
            } else {
                return response()->json('Address Not Found', 400);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Find the address by ID
        $address = Address::find($id);

        // Check if address exists
        if (!$address) {
            return response()->json(['message' => 'Address not found'], 404);
        }

        // Disable the address
        $address->enabled = false;
        $address->save();

        $address->user()->update(['enabled' => false]);

        return response()->json(['message' => 'Address disabled successfully'], 200);
    }

}
