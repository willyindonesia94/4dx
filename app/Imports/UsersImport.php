<?php

namespace App\Imports;

use App\Models\User;
use App\Models\MasterUnit;
use App\Models\MasterBidang;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Spatie\Permission\Models\Role;
use Exception;

class UsersImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Pastikan kolom wajib terisi
            if (empty($row['nama']) || empty($row['username']) || empty($row['role_name']) || empty($row['nama_unit'])) {
                continue; // Skip jika data tidak lengkap
            }

            // Cari Unit
            $unit = MasterUnit::where('name', $row['nama_unit'])->first();
            if (!$unit) {
                throw new Exception("Unit dengan nama '{$row['nama_unit']}' tidak ditemukan di sistem. Harap periksa kembali template Excel Anda.");
            }

            // Cari Matrix Group
            $matrixGroupId = null;
            if (!empty($row['nama_matrix_group'])) {
                if (strtoupper($row['nama_matrix_group']) === 'ALL') {
                    $matrixGroupId = 'ALL';
                } else {
                    $matrixGroup = MasterBidang::where('name', $row['nama_matrix_group'])->first();
                    if ($matrixGroup) {
                        $matrixGroupId = $matrixGroup->id;
                    } else {
                        throw new Exception("Matrix Group dengan nama '{$row['nama_matrix_group']}' tidak ditemukan.");
                    }
                }
            }

            // Role
            $roleName = $row['role_name'];
            if (!Role::where('name', $roleName)->exists()) {
                throw new Exception("Role '{$roleName}' tidak valid.");
            }

            // Cek apakah user sudah ada
            $user = User::where('username', $row['username'])->first();
            
            $password = !empty($row['password']) ? Hash::make($row['password']) : Hash::make('pln12345');

            if ($user) {
                // Update user
                $user->update([
                    'name' => $row['nama'],
                    'username' => $row['username'],
                    'role_name' => $roleName,
                    'unit_id' => $unit->id,
                    'matrix_group_id' => $matrixGroupId,
                ]);
                
                if (!empty($row['password'])) {
                    $user->update(['password' => $password]);
                }
                
                $user->syncRoles([$roleName]);
                continue;
            }

            // Create new user
            $newUser = new User([
                'name' => $row['nama'],
                'username' => $row['username'],
                'password' => $password,
                'role_name' => $roleName,
                'unit_id' => $unit->id,
                'matrix_group_id' => $matrixGroupId,
            ]);
            
            $newUser->save();
            $newUser->assignRole($roleName);
        }
    }
}
