<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\DTO\PermissionDTO;

class StorePermissionRequest extends FormRequest
{
    // public function authorize(): bool
    // {
    //     try {
    //         $user = JWTAuth::parseToken()->authenticate();
    //         return $user !== null;
    //     } catch (\Exception $e) {
    //         return false;
    //     }
    // }

    public function rules(): array
    {
        return [
            'name' => 'required|string|unique:roles,name|max:255',
            'code' => 'required|string|unique:roles,code|max:50',
            'description' => 'nullable|string|max:1000',
        ];
    }

    // Метод для получения RoleDTO
    public function toRoleDTO(): PermissionDTO
    {
        return new PermissionDTO($this->validated());
    }
}
