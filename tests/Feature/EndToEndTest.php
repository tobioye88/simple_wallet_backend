<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class TestEndToEnd extends TestCase
{
    use DatabaseMigrations;


    public array $user;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function beforeEachTest(): string
    {
        $this->post('/api/register', [
            "first_name" => "John",
            "last_name" => "doe",
            "password" => "password",
            "email" => "john.doe@gmail.com",
            "bvn" => "1234567890",
            "dob" => "1999-01-22"
        ]);

        $response = $this->post('/api/login', [
            "email" => "john.doe@gmail.com",
            "password" => "password",
        ]);
        return $response['data']['token'];
    }
    public function test_registration()
    {
        $response = $this->post('/api/register', [
            "first_name" => "John",
            "last_name" => "doe",
            "password" => "password",
            "email" => "john.doe@gmail.com",
            "bvn" => "1234567890",
            "dob" => "1999-01-22"
        ]);
        // dd($response['data']);
        $this->user = $response['data'];

        $response->assertStatus(200);
    }

    public function test_login()
    {
        User::factory()->create([ 'email' => 'john.doe@gmail.com']);

        $response = $this->post('/api/login', [
            "email" => "john.doe@gmail.com",
            "password" => "password",
        ]);

        $this->token = $response['data']['token'];

        $response->assertStatus(200);
    }

    public function test_balance()
    {
        $token = $this->beforeEachTest();

        $response = $this->get('/api/balance', [
            'Authorization' => "Bearer " . $token
        ]);

        $response->assertStatus(200);
    }

    public function test_transactions()
    {
        $token = $this->beforeEachTest();
        $response = $this->get('/api/transactions', [
            'Authorization' => "Bearer " . $token
        ]);
        // dd($response);

        $response->assertStatus(200);
    }

    public function test_wallet_add()
    {
        $token = $this->beforeEachTest();
        $response = $this->post('/api/wallet/add',
        [
            'amount'=> 1000
        ], [
            'Authorization' => "Bearer " . $token
        ]);

        $response->assertStatus(200);
    }

    public function test_wallet_send()
    {
        $token = $this->beforeEachTest();
        $this->post('/api/wallet/add',
        [
            'amount'=> 1000
        ], [
            'Authorization' => "Bearer " . $token
        ]);
        $response = $this->post('/api/register', [
            "first_name" => "John",
            "last_name" => "doe",
            "password" => "password",
            "email" => "jane.doe@gmail.com",
            "bvn" => "1234567890",
            "dob" => "1999-01-22"
        ]);
        $wallet = $response['data']['wallet'];

        $response = $this->post('/api/wallet/send',[
            'wallet' => $wallet['address'],
            'amount' => 200
        ], [
            'Authorization' => "Bearer " . $token
        ]);

        $response->assertStatus(200);
    }

}
