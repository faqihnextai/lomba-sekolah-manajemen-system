<?php

namespace App\Support;

use App\Models\School;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SchoolRegistry
{
    public function findOrCreate(string $name, string $code): School
    {
        $name = trim($name);
        $code = Str::upper(trim($code));

        $school = School::query()->firstOrCreate(
            ['code' => $code],
            ['name' => $name],
        );

        if ($school->name !== $name) {
            throw ValidationException::withMessages([
                'school_code' => 'Kode sekolah tersebut sudah terdaftar dengan nama sekolah yang berbeda.',
            ]);
        }

        return $school;
    }

    public function update(School $school, string $name, string $code): School
    {
        $name = trim($name);
        $code = Str::upper(trim($code));

        $codeInUse = School::query()
            ->where('code', $code)
            ->where('id', '!=', $school->getKey())
            ->exists();

        if ($codeInUse) {
            throw ValidationException::withMessages([
                'school_code' => 'Kode sekolah tersebut sudah digunakan oleh sekolah lain.',
            ]);
        }

        $school->update(['name' => $name, 'code' => $code]);

        return $school;
    }
}
