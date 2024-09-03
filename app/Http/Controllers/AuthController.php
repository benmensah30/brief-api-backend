<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Interface\AuthInterface;
use App\Models\User;
use App\Responses\ApiResponse;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    private AuthInterface $authInterface;

    public function __construct(AuthInterface $authInterface)
    {
        $this->authInterface = $authInterface;
    }

    public function register(RegisterRequest $registerRequest)
    {
        $data = [
            'name' => $registerRequest->name,
            'email' => $registerRequest->email,
            'password' => $registerRequest->password,
        ];

        DB::beginTransaction();
        try {
            $user = $this->authInterface->register($data);



            DB::commit();

            return ApiResponse::sendResponse(true, [new UserResource($user)], 'Opération effectuée.', 201);
        } catch (\Throwable $th) {
            return $th;
            return ApiResponse::rollback($th);
        }
    }
    public function login(LoginRequest $logonRequest)
    {
        $data = [
            'email' => $logonRequest->email,
            'password' => $logonRequest->password,
        ];


        DB::beginTransaction();
        try {
            $user = $this->authInterface->login($data);

            DB::commit();

            if (!$user) {
                return ApiResponse::sendResponse(
                    $user,
                    [],
                    'nom d\'utilisateur ou mot de passe incorrecte',
                    200
                );
            }

            return ApiResponse::sendResponse(
                $user,
                [],
                'Opération effectuée.',
                200
            );
        } catch (\Throwable $th) {
            return $th;
            return ApiResponse::rollback($th);
        }
    }
    public function checkOtpCode(Request $request)
    {
        $data = [
            'email' => $request->email,
            'code' => $request->code,
        ];

        DB::beginTransaction();
        try {
            $user = $this->authInterface->checkOtpCode($data);

            DB::commit();

            if (!$user) {

                return ApiResponse::sendResponse(
                    false,
                    [],
                    'Code de Confirmation Invalide.',
                    200
                );
            }


            return ApiResponse::sendResponse(
                true,
                [new UserResource($user)],
                'Opérations effectué.',
                200
            );
        } catch (\Throwable $th) {

            return ApiResponse::rollback($th);
        }
    }

    public function logout()
    {

        $user = User::find(auth()->user()->getAuthIdentifier());
        $user->token()->delate();


        return ApiResponse::sendResponse(
            true,
            [],
            'utilisateurs deconnecté.',
            200
        );
    }
}
