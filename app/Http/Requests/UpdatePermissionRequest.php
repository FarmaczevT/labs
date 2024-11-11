<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\DTO\PermissionDTO;

class UpdatePermissionRequest extends FormRequest
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
        $permissionId = $this->route('permission');
        return [
            'name' => 'required|string|max:255|unique:roles,name,' . $permissionId,
            'code' => 'required|string|max:50|unique:roles,code,' . $permissionId,
            'description' => 'nullable|string|max:1000',
        ];
    }

    // Метод для получения RoleDTO
    public function toRoleDTO(): PermissionDTO
    {
        return new PermissionDTO($this->validated());
    }
}