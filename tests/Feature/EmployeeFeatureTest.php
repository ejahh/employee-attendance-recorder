<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Employee;

class EmployeeFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_be_created()
    {
        $response = $this->postJson('/api/employees', [
            'name' => 'Test Employee',
            'email' => 'employee@example.com',
            'position' => 'Developer',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['id', 'name', 'email', 'position']);
        $this->assertDatabaseHas('employees', ['email' => 'employee@example.com']);
    }

    public function test_employee_can_be_retrieved()
    {
        $employee = Employee::factory()->create();
        $response = $this->getJson("/api/employees/{$employee->id}");
        $response->assertStatus(200)
                 ->assertJson(['id' => $employee->id]);
    }

    public function test_employee_can_be_updated()
    {
        $employee = Employee::factory()->create();
        $response = $this->putJson("/api/employees/{$employee->id}", [
            'name' => 'Updated Employee',
        ]);
        $response->assertStatus(200)
                 ->assertJson(['name' => 'Updated Employee']);
        $this->assertDatabaseHas('employees', ['id' => $employee->id, 'name' => 'Updated Employee']);
    }

    public function test_employee_can_be_deleted()
    {
        $employee = Employee::factory()->create();
        $response = $this->deleteJson("/api/employees/{$employee->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('employees', ['id' => $employee->id]);
    }
}
