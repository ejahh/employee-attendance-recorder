<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Attendance;
use App\Models\Employee;

class AttendanceFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_can_be_created()
    {
        $employee = Employee::factory()->create();
        $response = $this->postJson('/api/attendance', [
            'employee_id' => $employee->id,
            'date' => now()->toDateString(),
            'status' => 'present',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['id', 'employee_id', 'date', 'status']);
        $this->assertDatabaseHas('attendance', ['employee_id' => $employee->id, 'status' => 'present']);
    }

    public function test_attendance_can_be_retrieved()
    {
        $attendance = Attendance::factory()->create();
        $response = $this->getJson("/api/attendance/{$attendance->id}");
        $response->assertStatus(200)
                 ->assertJson(['id' => $attendance->id]);
    }

    public function test_attendance_can_be_updated()
    {
        $attendance = Attendance::factory()->create();
        $response = $this->putJson("/api/attendance/{$attendance->id}", [
            'status' => 'absent',
        ]);
        $response->assertStatus(200)
                 ->assertJson(['status' => 'absent']);
        $this->assertDatabaseHas('attendance', ['id' => $attendance->id, 'status' => 'absent']);
    }

    public function test_attendance_can_be_deleted()
    {
        $attendance = Attendance::factory()->create();
        $response = $this->deleteJson("/api/attendance/{$attendance->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('attendance', ['id' => $attendance->id]);
    }
}
