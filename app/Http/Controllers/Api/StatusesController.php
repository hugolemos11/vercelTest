<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Status;
use Validator;

class StatusesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $statuses = Status::all();

        if ($statuses->count() > 0) {
            return response()->json(
                $statuses,
                200
            );
        } else {
            return response()->json([
                'message' => 'No statuses found'
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
            'description' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->messages()
            ], 422);
        } else {
            $status = Status::create([
                'description' => $request->description,
                'enabled' => true,
            ]);

            if ($status) {
                return response()->json([
                    'message' => 'Status created successfully',
                    'status' => $status
                ], 201);
            } else {
                return response()->json([
                    'message' => 'Error creating status'
                ], 400);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $status = Status::find($id);
        if ($status) {
            return response()->json([
                'message' => $status
            ], 200);
        } else {
            return response()->json([
                'message' => 'Status not found'
            ], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     *q
     * @param  \Illuminate\Http\Request  $reuest
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->messages(),
            ], 422);
        } else {
            $status = Status::find($id);
            if ($status) {
                $status->description = $request->description;
                $status->save();
                return response()->json([
                    'message' => 'Status updated successfully',
                    'status' => $status
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Status not found'
                ], 400);
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
        // Find status
        $status = Status::find($id);

        if ($status) {
            $status->enabled = false;
            $status->save();

            $status->formulas()->update(['enabled' => false]);
            return response()->json([
                'message' => 'Status deleted successfully'
            ], 200);
        } else {
            return response()->json([
                'message' => 'Status not found'
            ], 404);
        }
    }
}