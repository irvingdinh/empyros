<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->records as $record) {
            $record['password'] = Hash::make('password');
            $record['created_at'] = now();
            $record['updated_at'] = now();

            DB::table('users')->insert($record);
        }
    }

    private array $records = [
        [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
        ]
    ];
}
