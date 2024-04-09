<?php

namespace App\Http\Controllers\Api;

use App\Models\Formula;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic;
use Carbon\Carbon;
use DB;

class FormulasController extends Controller
{

    public function teste()
    {
        $ftp_server = env('FTP_HOST');
        $ftp_user = env('FTP_USERNAME');
        $ftp_pass = env('FTP_PASSWORD');

        // set up a connection or die
        $ftp = ftp_connect($ftp_server) or die("Couldn't connect to $ftp_server");

        // try to login
        if (@ftp_login($ftp, $ftp_user, $ftp_pass)) {
            \Log::debug("Connected as $ftp_user@$ftp_server\n");
        } else {
            \Log::debug("Couldn't connect as $ftp_user\n");
        }

        // close the connection
        ftp_close($ftp);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $formulas = Formula::all();

        if ($formulas->count() == 0) {
            return response()->json(['message' => 'No formulas found'], 400);
        } else {
            return response()->json(['message' => $formulas], 200);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // Store a newly created resource in storage.
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'prescription' => 'required|string|max:255',
            'patient' => 'required|string|max:255',
            'status_id' => 'required|integer',
            'request_date' => 'required|date',
            'prescriber' => 'required|string|max:255',
            'user_id' => 'required|integer',
            'recipe_url' => 'required|image|mimes:jpeg,png,jpg,gif',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->messages()
            ], 422);
        } else {
            if ($request->hasFile('recipe_url')) {
                $image = ImageManagerStatic::make($request->file('recipe_url'))->encode('webp');
                $imageName = date('Y-M-d-h-i') . '.webp';
                // Store the image to the specified disk (configured in filesystems.php)
                $imagePath = $request->file('recipe_url')->storeAs(
                    date('Y/m/d'), // Store in folders based on year, month, and day
                    $imageName,
                    'ftp' // Disk configured for FTP in filesystems.php
                );

                // Get the full URL of the uploaded image
                $imageUrl = Storage::disk('ftp')->url('https://www.media.appsfarma.com/formulas/' . $imagePath);

            }

            // Convert 'enabled' attribute to boolean
            $request['enabled'] = (bool) $request['enabled'];

            $formula = Formula::create([
                'prescription' => $request->prescription,
                'patient' => $request->patient,
                'prescriber' => $request->prescriber,
                'status_id' => $request->status_id,
                'user_id' => $request->user_id,
                'recipe_url' => $imageUrl ?? null,
                'request_date' => $request->request_date,
                'enabled' => $request['enabled'],
            ]);

            if ($formula) {
                return response()->json([
                    'message' => 'Formula created successfully',
                    'formula' => $formula
                ], 201);
            } else {
                return response()->json([
                    'message' => 'An error occurred while trying to create the formula'
                ], 204);
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
        $formula = Formula::find($id);

        if ($formula) {
            return response()->json([
                $formula
            ], 200);
        } else {
            return response()->json([
                'message' => 'No formula found'
            ], 400);
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
        $formula = Formula::find($id);

        if ($formula) {
            $formula->update([
                'prescription' => $request->prescription,
                'patient' => $request->patient,
                'status_id' => $request->status_id,
                'user_id' => $request->user_id,
                'request_date' => $request->request_date,
                'prescriber' => $request->prescriber,
                'recipe_url' => $request->recipe_url,
                'enabled' => true,
            ]);

            return response()->json([
                'message' => 'Formula updated successfully',
                'formula' => $formula
            ], 200);
        } else {
            return response()->json([
                'message' => 'No formula found'
            ], 400);
        }

    }

    /**
     * Get all formulas in the database by id 
     * 
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getAllById($id)
    {      // Retrieve all formulas for the specified user ID
        $formulas = Formula::where('user_id', $id)->get();

        // Check if any formulas are found
        if ($formulas->isEmpty()) {
            return response()->json([

            ], 400);
        }

        // Return the formulas as JSON response
        return response()->json($formulas, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $formula = Formula::find($id);
        if ($formula) {
            $formula->enabled = false;
            $formula->save();
            return response()->json([
                'message' => 'Formula deleted successfully'
            ], 200);
        } else {
            return response()->json([
                'message' => 'No formula found'
            ], 400);
        }
    }

    public function countRequestsByYear()
    {
        $formulas = Formula::select(
            DB::raw('EXTRACT(YEAR_MONTH FROM request_date) as request_date'),
            DB::raw('group_concat(DISTINCT users.username) as username'),
            DB::raw('count(*) as total_requests')
        )
            ->join('users', 'formulas.user_id', '=', 'users.id')
            ->groupBy('request_date')
            ->get();

        return response()->json($formulas, 200);
    }

    /**
     * Get all formulas in the database by patient ID
     * 
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getAllFormulasByPatient($id)
    {
        $formulas = DB::table('formulas as f')
            ->join('users as u', 'f.patient', '=', 'u.username')
            ->select('f.*')
            ->where('u.id', $id)
            ->get();

        if ($formulas->isEmpty()) {
            return response()->json([
                'message' => 'No formulas found for this patient'
            ], 404);
        } else {
            return response()->json($formulas, 200);
        }
    }
}