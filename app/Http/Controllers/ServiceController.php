<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    private $jsonPath;

    public function __construct()
    {
        $this->jsonPath = public_path('data/services.json');
    }

    public function index()
    {
        return view('admin.service-management');
    }

    public function getServices()
    {
        try {
            $services = $this->readServices();
            return response()->json($services);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error reading services'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $services = $this->readServices();
            $newId = 1;
            if (!empty($services['services'])) {
                $newId = max(array_column($services['services'], 'id')) + 1;
            }

            // Handle image upload
            $imagePath = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images/services'), $imageName);
                $imagePath = '/images/services/' . $imageName;
            }

            $newService = [
                'id' => $newId,
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'image' => $imagePath,
                'status' => 'active',
                'created_at' => date('Y-m-d')
            ];

            $services['services'][] = $newService;
            $this->writeServices($services);

            return response()->json(['message' => 'Service added successfully', 'service' => $newService]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error adding service: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $services = $this->readServices();
            $index = array_search($id, array_column($services['services'], 'id'));

            if ($index === false) {
                return response()->json(['error' => 'Service not found'], 404);
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if it exists
                $oldImage = $services['services'][$index]['image'] ?? null;
                if ($oldImage && file_exists(public_path($oldImage))) {
                    unlink(public_path($oldImage));
                }

                // Upload new image
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images/services'), $imageName);
                $services['services'][$index]['image'] = '/images/services/' . $imageName;
            }

            $services['services'][$index]['name'] = $request->input('name');
            $services['services'][$index]['description'] = $request->input('description');

            $this->writeServices($services);

            return response()->json(['message' => 'Service updated successfully', 'service' => $services['services'][$index]]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error updating service: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $services = $this->readServices();
            $index = array_search($id, array_column($services['services'], 'id'));

            if ($index === false) {
                return response()->json(['error' => 'Service not found'], 404);
            }

            // Delete service image if it exists
            $image = $services['services'][$index]['image'] ?? null;
            if ($image && file_exists(public_path($image))) {
                unlink(public_path($image));
            }

            array_splice($services['services'], $index, 1);
            $this->writeServices($services);

            return response()->json(['message' => 'Service deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error deleting service: ' . $e->getMessage()], 500);
        }
    }

    private function readServices()
    {
        if (!File::exists($this->jsonPath)) {
            return ['services' => []];
        }

        $jsonContent = File::get($this->jsonPath);
        $data = json_decode($jsonContent, true);
        return is_array($data) ? $data : ['services' => []];
    }

    private function writeServices($services)
    {
        if (!is_array($services)) {
            $services = ['services' => []];
        }

        // Ensure the data directory exists
        $dataDir = dirname($this->jsonPath);
        if (!File::exists($dataDir)) {
            File::makeDirectory($dataDir, 0755, true);
        }

        // Write the services to the JSON file
        File::put($this->jsonPath, json_encode($services, JSON_PRETTY_PRINT));
    }
}
