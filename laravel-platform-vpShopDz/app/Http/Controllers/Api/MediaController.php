<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Upload file(s)
     * @route POST /api/v1/dashboard/media/upload
     */
    public function upload(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $validator = Validator::make($request->all(), [
            'files' => 'required|array|max:10',
            'files.*' => 'required|file|mimes:jpg,jpeg,png,gif,webp,svg,mp4,pdf|max:10240',
            'folder' => 'nullable|string|in:products,categories,logo,banners,pages,general',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check storage limit
        $plan = $store->plan;
        if ($plan && $plan->storage_limit_mb > 0) {
            $currentUsage = $store->storageUsedMb();
            $uploadSize = collect($request->file('files'))->sum(fn($f) => $f->getSize()) / (1024 * 1024);

            if (($currentUsage + $uploadSize) > $plan->storage_limit_mb) {
                return response()->json([
                    'success' => false,
                    'message' => 'مساحة التخزين غير كافية. الحد الأقصى: ' . $plan->storage_limit_mb . ' ميجابايت',
                    'upgrade_required' => true,
                ], 403);
            }
        }

        $folder = $request->get('folder', 'general');
        $uploadedFiles = [];

        foreach ($request->file('files') as $file) {
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = "stores/{$store->id}/{$folder}/{$filename}";

            // Store file
            $stored = Storage::disk('public')->putFileAs(
                "stores/{$store->id}/{$folder}",
                $file,
                $filename
            );

            if ($stored) {
                $uploadedFiles[] = [
                    'filename' => $filename,
                    'original_name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'url' => Storage::disk('public')->url($path),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'folder' => $folder,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'تم رفع ' . count($uploadedFiles) . ' ملف بنجاح',
            'data' => $uploadedFiles,
        ], 201);
    }

    /**
     * Get media library
     * @route GET /api/v1/dashboard/media
     */
    public function index(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $folder = $request->get('folder', null);
        $basePath = "stores/{$store->id}";

        if ($folder) {
            $basePath .= "/{$folder}";
        }

        $files = [];
        $directories = Storage::disk('public')->directories($basePath);
        $allFiles = Storage::disk('public')->files($basePath);

        // Get folders
        foreach ($directories as $dir) {
            $files[] = [
                'type' => 'folder',
                'name' => basename($dir),
                'path' => $dir,
                'files_count' => count(Storage::disk('public')->files($dir)),
            ];
        }

        // Get files
        foreach ($allFiles as $file) {
            $files[] = [
                'type' => 'file',
                'name' => basename($file),
                'path' => $file,
                'url' => Storage::disk('public')->url($file),
                'size' => Storage::disk('public')->size($file),
                'last_modified' => Storage::disk('public')->lastModified($file),
            ];
        }

        // Storage usage
        $usage = $store->storageUsedMb();
        $limit = $store->plan->storage_limit_mb ?? 0;

        return response()->json([
            'success' => true,
            'data' => [
                'files' => $files,
                'storage' => [
                    'used_mb' => round($usage, 2),
                    'limit_mb' => $limit,
                    'percentage' => $limit > 0 ? round(($usage / $limit) * 100, 1) : 0,
                ],
            ],
        ]);
    }

    /**
     * Delete file
     * @route DELETE /api/v1/dashboard/media
     */
    public function destroy(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        $validator = Validator::make($request->all(), [
            'path' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $path = $request->path;

        // Security: ensure path belongs to this store
        if (!str_starts_with($path, "stores/{$store->id}/")) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح بحذف هذا الملف',
            ], 403);
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);

            return response()->json([
                'success' => true,
                'message' => 'تم حذف الملف',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'الملف غير موجود',
        ], 404);
    }
}
