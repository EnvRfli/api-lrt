<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\reports;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ReportsController extends Controller
{
    /**
     * Store a new report (User).
     * Endpoint: POST /api/reports
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'program_name' => 'required|in:PKH,BLT,Bansos',
        'province_code' => 'required|string|max:50',
        'district_code' => 'required|string|max:50',
        'subdistrict_code' => 'required|string|max:50',
        'recipient_count' => 'required|integer|min:1',
        'distribution_date' => 'required|date',
        'distribution_proof' => 'required|file|mimes:jpg,png,pdf|max:2048',
        'notes' => 'nullable|string',
    ]);

    // Upload the file to Cloudinary
    $uploadResult = cloudinary()->upload($request->file('distribution_proof')->getRealPath());
    
    // Get the secure URL and public ID from the upload result
    $uploadedFileUrl = $uploadResult->getSecurePath();
    $publicId = $uploadResult->getPublicId(); // Get the public ID

    // Optionally, you can get the URL using the public ID, but it's not necessary since you already have the secure URL
    // $fileUrl = cloudinary()->getUrl($publicId);

    // Create the report
    $report = reports::create([
        'user_id' => Auth::id(),
        'program_name' => $validated['program_name'],
        'province_code' => $validated['province_code'],
        'district_code' => $validated['district_code'],
        'subdistrict_code' => $validated['subdistrict_code'],
        'recipient_count' => $validated['recipient_count'],
        'distribution_date' => $validated['distribution_date'],
        'distribution_proof' => $uploadedFileUrl, // Save the Cloudinary file URL
        'notes' => $validated['notes'] ?? null,
    ]);

    return response()->json($report, 201);
}
    

    /**
     * Update a report (User).
     * Endpoint: PATCH /api/reports/{id}
     */
    public function update(Request $request, $id)
{
    $validated = $request->validate([
        'program_name' => 'required|in:PKH,BLT,Bansos',
        'province_code' => 'required|string|max:50',
        'district_code' => 'required|string|max:50',
        'subdistrict_code' => 'required|string|max:50',
        'recipient_count' => 'required|integer|min:1',
        'distribution_date' => 'required|date',
        'distribution_proof' => 'nullable|file|mimes:jpg,png,pdf|max:2048',
        'notes' => 'nullable|string',
    ]);

    $report = reports::findOrFail($id);

    if ($report->status !== 'Pending') {
        return response()->json(['message' => 'Cannot update processed report.'], 400);
    }

    $fileUrl = $report->distribution_proof; // Default to the existing file URL

    if ($request->hasFile('distribution_proof')) {
        // Upload new proof file to Cloudinary
        $uploadedFile = $request->file('distribution_proof');
        $cloudinaryUpload = Cloudinary::upload($uploadedFile->getRealPath(), [
            'folder' => 'proofs',
            'resource_type' => 'auto', // Supports both images and PDFs
        ]);
        $fileUrl = $cloudinaryUpload->getSecurePath(); // Get the new file URL
    }

    $report->update([
        'program_name' => $validated['program_name'],
        'province_code' => $validated['province_code'],
        'district_code' => $validated['district_code'],
        'subdistrict_code' => $validated['subdistrict_code'],
        'recipient_count' => $validated['recipient_count'],
        'distribution_date' => $validated['distribution_date'],
        'distribution_proof' => $fileUrl, // Save the Cloudinary file URL
        'notes' => $validated['notes'] ?? null,
    ]);

    return response()->json(['message' => 'Report updated successfully.']);
}


    /**
     * List all reports (Admin).
     * Endpoint: GET /api/admin/reports
     */
    public function index()
    {
        $reports = reports::with('user')->get();
        return response()->json($reports);
    }


    /**
     * List all reports by user (User).
     * Endpoint: GET /api/reports
     */
    public function indexUser()
    {
        $reports = reports::where('user_id', Auth::id())->get();
        return response()->json($reports);
    }

    /**
     * Approve a report (Admin).
     * Endpoint: PATCH /api/admin/reports/{id}/approve
     */
    public function approve($id)
    {
        $report = reports::findOrFail($id);

        if ($report->status !== 'Pending') {
            return response()->json(['message' => 'reports already processed.'], 400);
        }

        $report->update(['status' => 'Disetujui']);

        return response()->json(['message' => 'reports approved successfully.']);
    }

    /**
     * Reject a report (Admin).
     * Endpoint: PATCH /api/admin/reports/{id}/reject
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        $report = reports::findOrFail($id);

        if ($report->status !== 'Pending') {
            return response()->json(['message' => 'reports already processed.'], 400);
        }

        $report->update([
            'status' => 'Ditolak',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return response()->json(['message' => 'reports rejected successfully.']);
    }

    /**
     * Show report details.
     * Endpoint: GET /api/reports/{id}
     */
    public function show($id)
    {
        $report = reports::with('user')->findOrFail($id);
        return response()->json($report);
    }

    /**
     * Show report details by user.
     * Endpoint: GET /api/reports/{id}
     */
    public function showUser($id)
    {
        $report = reports::where('user_id', Auth::id())->findOrFail($id);
        return response()->json($report);
    }

    /**
     * Delete a report (User/Admin).
     * Endpoint: DELETE /api/reports/{id}
     */
    public function destroy($id)
    {
        $report = reports::findOrFail($id);

        if ($report->status !== 'Pending') {
            return response()->json(['message' => 'Cannot delete processed report.'], 400);
        }

        // Delete proof file
        if ($report->distribution_proof) {
            Storage::delete($report->distribution_proof);
        }

        $report->delete();

        return response()->json(['message' => 'reports deleted successfully.']);
    }
}