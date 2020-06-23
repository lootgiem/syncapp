<?php

use App\Models\User;
use Illuminate\Database\Seeder;
use Laravel\Passport\ClientRepository;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @param ClientRepository $clients
     * @return void
     */
    public function run(ClientRepository $clients)
    {
        $user = User::create([
            'name' => 'Michael',
            'email' => 'test@hotmail.fr',
            'email_verified_at' => time(),
            'password' => Hash::make('azeazeaze'),
        ]);

        $name = config('app.name').' ClientCredentials Grant Client';
        $clients->createPasswordGrantClient($user->getAuthIdentifier(), $name, '');
    }
}
