<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException as ValidationValidationException;
use Laravel\Sanctum\HasApiTokens;

class UserController extends Controller
{
    use HasApiTokens;

    public function transactionTax(Request $request)
    {
        dd($request->all());
    }

    public function attendanceUser(Request $request, $id)
    {
        dd($request, $id);
    }

    public function completeProfile(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|min:5|max:15|regex:/^[a-zA-Z\s]+$/',
                'address' => 'required|min:15',
                'email' => 'email',
            ]);

            DB::beginTransaction();
            try {
                if ($id == Auth::user()->id) {
                    $updated = $request->all();
                    $updated['id'] = $id;
                    $updated['updated_at'] = now('Asia/Jakarta');
                    User::completeProfile($updated);
                    DB::commit();
                    $statusCode = 200;
                    $response = [
                        'status' => 'Success',
                        'message' => 'successfully updated your profile',
                        'user' => User::find($id),
                    ];
                } else {
                    throw new \Exception('your not able to update other users profile');
                }
            } catch (\Throwable $th) {
                DB::rollBack();
                $statusCode = 400;
                $response = [
                    'status' => 'Failed',
                    'message' => $th->getMessage() ?? 'unexpected error on update your profile',
                    'user' => [],
                ];
            }

            return Response()->json($response, $statusCode);
        } catch (ValidationValidationException $th) {
            return Response()->json([
                'status' => 'Not Acceptable',
                'message' => $th->getMessage(), 'errors' => $th->errors(),
            ], 422);
        }
    }

    public function editProfile(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|min:5|max:15|regex:/^[a-zA-Z\s]+$/',
                'address' => 'required|min:15',
                'email' => 'required|email',
            ]);
            DB::beginTransaction();
            try {
                if ($id == Auth::user()->id) {
                    $updated = $request->all();
                    $updated['id'] = $id;
                    $updated['updated_at'] = now('Asia/Jakarta');
                    User::completeProfile($updated);
                    DB::commit();
                    $statusCode = 200;
                    $user = User::find($id);
                    $response = [
                        'status' => 'Success',
                        'message' => 'successfully updated your profile',
                        'user' => $user,
                    ];
                } else {
                    throw new \Exception('your not able to update other users profile');
                }
            } catch (\Throwable $th) {
                DB::rollBack();
                $statusCode = 400;
                $response = [
                    'status' => 'Failed',
                    'message' => $th->getMessage() ?? 'unexpected error on update your profile',
                    'user' => [],
                ];
            }

            return Response()->json($response, $statusCode);
        } catch (ValidationValidationException $th) {
            return Response()->json([
                'status' => 'Not Acceptable',
                'message' => $th->getMessage(), 'errors' => $th->errors(),
            ], 422);
        }
    }

    public function logoutUser($id)
    {
        DB::beginTransaction();
        try {
            if ($id == Auth::user()->id) {
                Auth::user()->tokens()->delete();
                DB::commit();
                $statusCode = 200;
                $response = [
                    'status' => 'Success',
                    'message' => 'successfully logout from application',
                ];
            } else {
                throw new \Exception('your not able to logout other users!');
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            $statusCode = 400;
            $response = [
                'status' => 'Failed',
                'message' => $th->getMessage() ?? 'unexpected error on logout from application',
            ];
        }

        return Response()->json($response, $statusCode);
    }
}
