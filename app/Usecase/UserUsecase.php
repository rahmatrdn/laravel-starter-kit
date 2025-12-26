<?php

namespace App\Usecase;

use App\Entities\DatabaseEntity;
use App\Entities\ResponseEntity;
use App\Http\Presenter\Response;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserUsecase extends Usecase
{
    public string $className;

    public function __construct()
    {
        $this->className = "UserUsecase";
    }

    public function getAll(array $filterData = []): array
    {
        $funcName = $this->className . ".getAll";

        try {
            $data = DB::table(DatabaseEntity::USER)
                ->whereNull("deleted_at")
                ->orderBy("created_at", "desc")
                ->paginate(20);

            return Response::buildSuccess(
                [
                    'list' => $data,
                ],
                ResponseEntity::HTTP_SUCCESS
            );
        } catch (Exception $e) {
            Log::error($e->getMessage(), [
                "func_name" => $funcName,
                'user' => Auth::user()
            ]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    public function getByID(int $id): array
    {
        $funcName = $this->className . ".getByID";

        try {
            $data = DB::table(DatabaseEntity::USER)
                ->whereNull("deleted_at")
                ->where('id', $id)
                ->first();

            return Response::buildSuccess(
                data: collect($data)->toArray()
            );
        } catch (Exception $e) {
            Log::error($e->getMessage(), [
                "func_name" => $funcName,
                'user' => Auth::user()
            ]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    public function create(Request $data): array
    {
        $funcName = $this->className . ".create";

        $validator = Validator::make($data->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'access_type' => 'required|in:admin,user',
        ]);

        $validator->validate();

        DB::beginTransaction();
        try {
            DB::table(DatabaseEntity::USER)
                ->insert([
                    'name'        => $data['name'],
                    'email'       => $data['email'],
                    'access_type' => $data['access_type'],
                    'password'    => Hash::make('asdasd'),
                    'is_active'   => 1,
                    'created_by'  => Auth::user()?->id,
                    'created_at'  => now(),
                ]);

            DB::commit();

            return Response::buildSuccessCreated();
        } catch (Exception $e) {
            DB::rollback();

            Log::error($e->getMessage(), [
                "func_name" => $funcName,
                'user' => Auth::user()
            ]);
            return Response::buildErrorService($e->getMessage());
        }
    }

    public function update(Request $data, int $id): array
    {
        $funcName = $this->className . ".update";

        $validator = Validator::make($data->all(), [
            'name' => 'required|min:2',
            'email' => 'required|email',
        ]);

        $validator->validate();

        $update = [
            'name'        => $data['name'],
            'email'       => $data['email'],
            'access_type' => $data['access_type'],
            'updated_by'  => Auth::user()?->id,
            'updated_at'  => now(),
        ];

        DB::beginTransaction();

        try {
            DB::table(DatabaseEntity::USER)
                ->where("id", $id)
                ->update($update);

            DB::commit();

            return Response::buildSuccess(
                message: ResponseEntity::SUCCESS_MESSAGE_UPDATED
            );
        } catch (Exception $e) {
            DB::rollback();

            Log::error($e->getMessage(), [
                "func_name" => $funcName,
                'user' => Auth::user()
            ]);
            return Response::buildErrorService($e->getMessage());
        }
    }

    public function delete(int $id): array
    {
        $funcName = $this->className . ".delete";

        DB::beginTransaction();

        try {
            $delete = DB::table(DatabaseEntity::USER)
                ->where('id', $id)
                ->update([
                    'deleted_by' => Auth::user()?->id,
                    'deleted_at' => now(),
                ]);

            if (!$delete) {
                DB::rollback();
                throw new Exception("FAILED DELETE DATA");
            }

            DB::commit();

            return Response::buildSuccess(
                message: ResponseEntity::SUCCESS_MESSAGE_DELETED
            );
        } catch (Exception $e) {
            DB::rollback();

            Log::error($e->getMessage(), [
                "func_name" => $funcName,
                'user' => Auth::user()
            ]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    public function changePassword(array $data): array
    {
        $userID = Auth::user()?->id;
        $funcName = $this->className . ".changePassword";

        $validator = Validator::make($data, [
            'current_password' => [
                'required',
                function ($attribute, $value, $fail) use ($userID) {
                    $user = DB::table(DatabaseEntity::USER)
                        ->where('id', (int) $userID)
                        ->first(['password']);

                    if (!Hash::check($value, $user->password)) {
                        $fail('Password saat ini salah.');
                    }
                },
            ],
            'password'         => 'required|min:6',
            're_password'      => 'required|same:password',
        ]);

        $customAttributes = [
            'current_password' => 'Password Lama',
            'password'         => 'Password Baru',
            're_password'      => 'Ulangi Password Baru',
        ];
        $validator->setAttributeNames($customAttributes);
        $validator->validate();

        DB::beginTransaction();

        try {
            $locked = DB::table(DatabaseEntity::USER)
                ->where('id', $userID)
                ->whereNull("deleted_at")
                ->lockForUpdate()
                ->first(['id']);

            if (!$locked) {
                DB::rollback();

                throw new Exception("FAILED LOCKED DATA");
            }

            DB::table(DatabaseEntity::USER)
                ->where("id", $userID)
                ->update([
                    'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                ]);

            DB::commit();

            return Response::buildSuccess(
                message: ResponseEntity::SUCCESS_MESSAGE_UPDATED
            );
        } catch (Exception $e) {
            DB::rollback();

            Log::error($e->getMessage(), [
                "func_name" => $funcName,
            ]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    public function resetPassword(int $id): array
    {
        $funcName = $this->className . ".resetPassword";

        $defaultPassword = 'asdasd';

        DB::beginTransaction();

        try {
            DB::table(DatabaseEntity::USER)
                ->where('id', $id)
                ->update([
                    'password' => Hash::make($defaultPassword),
                    'updated_by' => Auth::user()?->id,
                    'updated_at' => now(),
                ]);

            DB::commit();

            return Response::buildSuccess(
                message: 'Password berhasil direset'
            );
        } catch (Exception $e) {
            DB::rollback();

            Log::error($e->getMessage(), [
                "func_name" => $funcName,
                'user' => Auth::user()
            ]);

            return Response::buildErrorService($e->getMessage());
        }
    }
}
