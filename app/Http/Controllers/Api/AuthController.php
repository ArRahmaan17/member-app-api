<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\HasApiTokens;
use Milon\Barcode\DNS1D;

class AuthController extends Controller
{
    use HasApiTokens;

    public function index()
    {
        return Response()->json([
            'message' => 'You Allowed To Be Here',
            'status' => 'Allowed',
        ]);
    }

    public function denied()
    {
        return Response()->json([
            'message' => 'Please login to access previous page',
            'status' => 'Not Allowed',
        ]);
    }

    public function registration(Request $request)
    {
        try {
            $request->validate([
                'phone_number' => 'required|numeric|max_digits:13|unique:users,phone_number',
                'password' => 'required|alpha|min:5|max:30',
                'referral_code' => 'same:referral_code',
            ]);
            $setupUser = [
                'phone_number' => $request->phone_number,
                'password' => bcrypt($request->password),
                'developer' => false,
                'created_at' => now(),
            ];
            if ('' != $request->referral_code) {
                $setupUser['referral_code'] = $request->referral_code . '@';
            }
            DB::beginTransaction();
            try {
                User::insert($setupUser);
                DB::commit();
                $statusCode = 201;
                $response = [
                    'status' => 'Acceptable',
                    'message' => 'Registration successfully',
                ];
            } catch (\Throwable $th) {
                DB::rollBack();
                $statusCode = 406;
                $response = [
                    'status' => 'Not Acceptable',
                    'message' => $th->getMessage(),
                ];
            }

            return Response()->json($response, $statusCode);
        } catch (ValidationException $th) {
            return Response()->json([
                'status' => 'Not Acceptable',
                'message' => $th->getMessage(),
                'errors' => $th->errors(),
            ], 422);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'phone_number' => 'required|numeric|max_digits:13',
                'password' => 'required',
            ]);
            $credentials = [
                'phone_number' => $request->phone_number,
                'password' => $request->password,
            ];
            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                $fileName = $user->id . '-' . $user->phone_number . '.png';
                if (!File::exists($_SERVER['DOCUMENT_ROOT'] . '/storage/' . $fileName)) {
                    DB::beginTransaction();
                    try {
                        $storage = Storage::build([
                            'driver' => 'local',
                            'root' => $_SERVER['DOCUMENT_ROOT'] . '/storage',
                        ]);
                        $storage->put($fileName, base64_decode(DNS1D::getBarcodePNG($fileName, 'C128')));
                        $user->qr_code = '/storage/' . $fileName;
                        $user->save();
                        DB::commit();
                    } catch (\Throwable $th) {
                        DB::rollBack();

                        return response()->json([
                            'status' => 'Have Error',
                            'message' => 'Has Error on Creating Your Qr Code',
                            'technical_message' => $th->getMessage(),
                        ], 501);
                    }
                }
                if ($user->developer) {
                    $token = $user->createToken('developer-token')->plainTextToken;
                } else {
                    $token = $user->createToken('user-token')->plainTextToken;
                }

                return response()->json([
                    'status' => 'Match',
                    'message' => 'Your credentials match to our records',
                    'user' => $user,
                    'token' => $token,
                ], 201);
            } else {
                return response()->json([
                    'status' => 'Not Match',
                    'message' => 'Your credentials not match to our records',
                ], 404);
            }
        } catch (ValidationException $th) {
            return Response()->json([
                'status' => 'Not Acceptable',
                'message' => $th->getMessage(), 'errors' => $th->errors(),
            ], 422);
        }
    }
}
