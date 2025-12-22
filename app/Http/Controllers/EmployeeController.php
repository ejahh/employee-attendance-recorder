<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Http\Requests\EmployeeRequest;
use App\Http\Requests\EmployeePatchRequest;
use Opis\JsonSchema\Validator;
use Opis\JsonSchema\ValidationResult;
use Illuminate\Support\Facades\Log;
use App\Http\Helpers\XmlResponseHelper;

class EmployeeController extends Controller
{
    /**
     * Bulk PATCH update employees by IDs.
     * Accepts: { "ids": [1,2,3], "data": { ...fields... } }
     * Route: PATCH /api/employees/bulk-update
     */
    public function patchUpdateMultiple(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:employees,id',
            'data' => 'required|array',
        ]);

        $updated = Employee::whereIn('id', $request->ids)->update($request->data);

        // Retrieve the updated employees
        $employees = Employee::whereIn('id', $request->ids)->get();

        return response()->json([
            'message' => 'Employees patched successfully.',
            'updated_count' => $updated,
            'employees' => $employees
        ]);
    }
    /**
     * Bulk update employees by IDs.
     * Accepts: { "ids": [1,2,3], "data": { ...fields... } }
     * Route: PUT /api/employees/bulk-update
     */
    public function updateMultiple(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:employees,id',
            'data' => 'required|array',
        ]);

        $updated = Employee::whereIn('id', $request->ids)->update($request->data);

        return response()->json([
            'message' => 'Employees updated successfully.',
            'updated_count' => $updated
        ]);
    }
    /**
     * Delete multiple employees by IDs.
     * Accepts: { "ids": [1,2,3] }
     * Route: DELETE /api/employees/bulk-delete
     */
    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:employees,id',
        ]);

        $deleted = Employee::whereIn('id', $request->ids)->delete();

        return response()->json([
            'message' => 'Employees deleted successfully.',
            'deleted_count' => $deleted
        ]);
    }
    /**
     * Test Opis\JsonSchema loading
     */
    public function testValidator()
    {
        try {
            $validator = new Validator();
            // Example data and schema for testing
            $data = [
                'first_name' => 'Test',
                'middle_name' => null,
                'last_name' => 'User',
                'date_of_birth' => '2000-01-01',
                'place_of_birth' => 'Test City',
                'age' => 25,
                'sex' => 'Male',
                'address' => '123 Test St',
                'job_title' => 'Developer',
                'department' => 'IT',
                'status' => 'Active',
                'date_of_service' => '2020-01-01',
                'salary' => 50000.0
            ];
            // Ensure types match JSON expectations
            $data = json_decode(json_encode($data));
            $schemaPath = base_path('schemas/employee-request.schema.json');
            $schema = json_decode(file_get_contents($schemaPath));
            $result = $validator->validate($data, $schema);
            if ($result->isValid()) {
                return response()->json(['status' => 'Validator loaded', 'validation' => 'passed']);
            } else {
                $error = $result->error();
                return response()->json([
                    'status' => 'Validator loaded',
                    'validation' => 'failed',
                    'keyword' => $error ? $error->keyword() : null,
                    'message' => $error ? $error->message() : null
                ], 500);
            }
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Request validation: validate query parameters
        $validated = request()->validate([
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'department' => 'sometimes|string',
            'status' => 'sometimes|string',
            'job_title' => 'sometimes|string',
            'age' => 'sometimes|integer|min:20',
            'age_min' => 'sometimes|integer|min:20',
            'age_max' => 'sometimes|integer|max:60',
            'sex' => 'sometimes|string|in:Male,Female',
            // 'sort_by' and 'sort_order' removed
            'search' => 'sometimes|string',
        ]);
        // Build query with filters
        $query = Employee::query();
        if (isset($validated['age'])) {
            $query->where('age', $validated['age']);
        }
        if (isset($validated['department'])) {
            $query->where('department', 'like', "%" . $validated['department'] . "%");
        }
        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }
        if (isset($validated['job_title'])) {
            $query->where('job_title', $validated['job_title']);
        }
        if (isset($validated['age_min'])) {
            $query->where('age', '>=', $validated['age_min']);
        }
        if (isset($validated['age_max'])) {
            $query->where('age', '<=', $validated['age_max']);
        }
        if (isset($validated['sex'])) {
            $query->where('sex', $validated['sex']);
        }
        if (isset($validated['search'])) {
            $search = $validated['search'];
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                  ->orWhere('last_name', 'like', "%$search%")
                  ->orWhere('department', 'like', "%$search%")
                  ->orWhere('job_title', 'like', "%$search%")
                  ->orWhere('address', 'like', "%$search%")
                  ->orWhere('place_of_birth', 'like', "%$search%")
                  ->orWhere('status', 'like', "%$search%")
                  ->orWhere('sex', 'like', "%$search%")
                  ->orWhere('age', 'like', "%$search%")
                  ->orWhere('date_of_service', 'like', "%$search%")
                  ->orWhere('date_of_birth', 'like', "%$search%")
                  ->orWhere('middle_name', 'like', "%$search%")
                  ;
            });
        }
        // Sorting logic removed as requested

        // Pagination (optional)

        if (isset($validated['per_page'])) {
            $employees = $query->get(); // You can use paginate($validated['per_page']) if you want paginated response
        } else {
            $employees = $query->get();
        }

        // Debug: Log the order of ages returned
        \Log::info('Ages returned:', collect($employees)->pluck('age')->toArray());

        if ($employees->isEmpty()) {
            if ($request->accepts('application/xml')) {
                $xml = XmlResponseHelper::toXml('employees', []);
                return response($xml, 404)->header('Content-Type', 'application/xml');
            }
            return response()->json([
                'message' => 'No employees found matching the criteria.'
            ], 404);
        }
        if ($request->accepts('application/xml')) {
            $xml = XmlResponseHelper::toXml('employees', $employees->toArray());
            return response($xml, 200)->header('Content-Type', 'application/xml');
        }
        return response()->json($employees);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EmployeeRequest $request)
    {
        $employee = Employee::create($request->validated());
        // Reload the employee to ensure all fields (id, timestamps) are present
        $employee = Employee::find($employee->id);
        $employeeArray = $employee->toArray();
        // Ensure salary is a float for schema validation
        if (isset($employeeArray['salary'])) {
            $employeeArray['salary'] = (float) $employeeArray['salary'];
        }
        \Log::info('Employee array for response:', $employeeArray);
        $this->validateResponse($employeeArray, 'employee-response.schema.json');
        if ($request->accepts('application/xml')) {
            $xml = XmlResponseHelper::toXml('employee', $employeeArray);
            return response($xml, 201)->header('Content-Type', 'application/xml');
        }
        return response()->json($employeeArray, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json([
                'message' => 'Employee not found.'
            ], 404);
        }
        $employeeArray = $employee->toArray();
        $employeeArray['salary'] = (float) $employeeArray['salary'];
        $this->validateResponse($employeeArray, 'employee-response.schema.json');
        if ($request->accepts('application/xml')) {
            $xml = XmlResponseHelper::toXml('employee', $employeeArray);
            return response($xml, 200)->header('Content-Type', 'application/xml');
        }
        return response()->json($employeeArray);
    }

    /**
     * PUT: Full update of the resource.
     */
    public function update(EmployeeRequest $request, string $id)
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json([
                'message' => 'Employee not found.'
            ], 404);
        }
        $employee->update($request->validated());
        $employeeArray = $employee->toArray();
        $employeeArray['salary'] = (float) $employeeArray['salary'];
        $this->validateResponse($employeeArray, 'employee-response.schema.json');
        if ($request->accepts('application/xml')) {
            $xml = XmlResponseHelper::toXml('employee', $employeeArray);
            return response($xml, 200)->header('Content-Type', 'application/xml');
        }
        return response()->json($employeeArray);
    }

    /**
     * PATCH: Partial update of the resource.
     */
    public function patchUpdate(EmployeePatchRequest $request, string $id)
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json([
                'message' => 'Employee not found.'
            ], 404);
        }
        $employee->update($request->validated());
        $employeeArray = $employee->toArray();
        $employeeArray['salary'] = (float) $employeeArray['salary'];
        $this->validateResponse($employeeArray, 'employee-response.schema.json');
        if ($request->accepts('application/xml')) {
            $xml = XmlResponseHelper::toXml('employee', $employeeArray);
            return response($xml, 200)->header('Content-Type', 'application/xml');
        }
        return response()->json($employeeArray);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json([
                'message' => 'Employee not found.'
            ], 404);
        }
        $employee->delete();
        if ($request->accepts('application/xml')) {
            $xml = XmlResponseHelper::toXml('response', ['message' => 'Employee deleted successfully']);
            return response($xml, 200)->header('Content-Type', 'application/xml');
        }
        return response()->json(['message' => 'Employee deleted successfully']);
    }

    /**
     * Validate response body against JSON schema
     */
    protected function validateResponse($data, $schemaFile)
    {
        // Use Opis SchemaLoader and SchemaResolver for $ref support
        $schemaPath = base_path('schemas/' . $schemaFile);
        $employeeSchemaPath = base_path('schemas/employee-response.schema.json');

        $loader = new \Opis\JsonSchema\SchemaLoader();
        $resolver = new \Opis\JsonSchema\Resolvers\SchemaResolver();

        // Register the referenced schema with a custom URI
            $resolver->registerFile('urn:employee-response', $employeeSchemaPath);
        $loader->setResolver($resolver);

        $validator = new \Opis\JsonSchema\Validator();
        $validator->setLoader($loader);
        $schema = json_decode(file_get_contents($schemaPath));
        // Set base URI for the main schema to help $ref resolution
        $schema->id = 'urn:employee-list-response';
        \Log::info('Validating response array:', $data);

        // Cast associative array to object for JSON schema validation
        $data = json_decode(json_encode($data));

        $result = $validator->validate($data, $schema);
        if (!$result->isValid()) {
            $error = $result->error();
            \Log::error('Schema validation failed', [
                'keyword' => $error ? $error->keyword() : null,
                'message' => $error ? $error->message() : null,
                'data' => $data,
                'schema' => $schema,
                'full_error' => $error,
            ]);
            abort(response()->json([
                'error' => 'Response validation failed',
                'keyword' => $error ? $error->keyword() : null,
                'message' => $error ? $error->message() : null,
                'data' => $data,
                'schema' => $schema,
            ], 500));
        }
    }

    /**
     * Retrieve multiple employees by IDs (GET /employees/multiple?ids=1,2,3)
     */
    public function multiple(Request $request)
    {
        \Log::info('EmployeeController@multiple called');
        $ids = $request->query('ids');
        \Log::info('Requested IDs:', ['ids' => $ids]);
        if (!$ids) {
            \Log::warning('No IDs provided');
            return response()->json(['message' => 'No IDs provided.'], 400);
        }
        $idArray = array_map('intval', explode(',', $ids));
        \Log::info('Parsed ID array:', ['idArray' => $idArray]);
        $employees = Employee::whereIn('id', $idArray)->get();
        \Log::info('Employees found:', $employees->toArray());
        if ($employees->isEmpty()) {
            \Log::warning('No employees found for IDs', ['idArray' => $idArray]);
            if ($request->accepts('application/xml')) {
                $xml = XmlResponseHelper::toXml('employees', []);
                return response($xml, 404)->header('Content-Type', 'application/xml');
            }
            return response()->json(['message' => 'Employee not found.'], 404);
        }
        if ($request->accepts('application/xml')) {
            $xml = XmlResponseHelper::toXml('employees', $employees->toArray());
            return response($xml, 200)->header('Content-Type', 'application/xml');
        }
        return response()->json($employees);
    }
}

