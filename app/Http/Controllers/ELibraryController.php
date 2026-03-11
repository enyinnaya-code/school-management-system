<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Resource;
use Illuminate\Support\Facades\Storage;

class ELibraryController extends Controller
{
    /**
     * Show the form for adding a new e-Library resource.
     */
    public function createResource()
    {
        return view('library.e_library.add_resource');
    }

    /**
     * Store a new e-Library resource in the database.
     */
    public function storeResource(Request $request)
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'author'           => 'required|string|max:255',
            'description'      => 'nullable|string',
            'resource_type'    => 'required|in:pdf,docx,xlsx,pptx,ebook,link',
            // File only required when resource_type is not 'link'
            'file'             => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,epub',
                'max:20480', // 20MB
                'required_if:resource_type,pdf',
                'required_if:resource_type,docx',
                'required_if:resource_type,xlsx',
                'required_if:resource_type,pptx',
                'required_if:resource_type,ebook',
            ],
            // URL required when resource_type is 'link'
            'url'              => 'nullable|url|required_if:resource_type,link',
            'publisher'        => 'nullable|string|max:255',
            'publication_year' => 'nullable|integer|min:1800|max:' . date('Y'),
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $file      = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $filename  = time() . '_' . uniqid() . '.' . $extension;
            $filePath  = $file->storeAs('resources', $filename, 'public');
        }

        Resource::create([
            'title'            => $validated['title'],
            'author'           => $validated['author'],
            'description'      => $validated['description'] ?? null,
            'resource_type'    => $validated['resource_type'],
            'file_path'        => $filePath,
            'url'              => $validated['url'] ?? null,
            'publisher'        => $validated['publisher'] ?? null,
            'publication_year' => $validated['publication_year'] ?? null,
        ]);

        return redirect()->route('e_library.manage_resources')->with('success', 'Resource added successfully.');
    }

    /**
     * Display a listing of the e-Library resources with filtering and pagination.
     */
    public function indexResources(Request $request)
    {
        $query = Resource::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('publisher', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('resource_type', $request->type);
        }

        if ($request->filled('year')) {
            $query->where('publication_year', $request->year);
        }

        $sort = $request->get('sort', 'newest');
        match ($sort) {
            'oldest'     => $query->orderBy('created_at', 'asc'),
            'title_asc'  => $query->orderBy('title', 'asc'),
            'title_desc' => $query->orderBy('title', 'desc'),
            'author_asc' => $query->orderBy('author', 'asc'),
            default      => $query->orderBy('created_at', 'desc'),
        };

        $perPage   = $request->get('per_page', 25);
        $resources = $query->paginate($perPage)->withQueryString();

        return view('library.e_library.manage_resources', compact('resources'));
    }

    /**
     * Show the form for editing the specified e-Library resource.
     */
    public function editResource($id)
    {
        $resource = Resource::findOrFail($id);
        return view('library.e_library.edit_resource', compact('resource'));
    }

    /**
     * Update the specified e-Library resource in the database.
     */
    public function updateResource(Request $request, $id)
    {
        $resource = Resource::findOrFail($id);

        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'author'           => 'required|string|max:255',
            'description'      => 'nullable|string',
            'resource_type'    => 'required|in:pdf,docx,xlsx,pptx,ebook,link',
            'file'             => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,epub,mp4,mp3|max:20480', // 20MB
            'url'              => 'nullable|url|required_if:resource_type,link',
            'publisher'        => 'nullable|string|max:255',
            'publication_year' => 'nullable|integer|min:1800|max:' . date('Y'),
        ]);

        $filePath = $resource->file_path;
        if ($request->hasFile('file')) {
            if ($filePath) {
                Storage::disk('public')->delete($filePath);
            }
            $file      = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $filename  = time() . '_' . uniqid() . '.' . $extension;
            $filePath  = $file->storeAs('resources', $filename, 'public');
        }

        $resource->update([
            'title'            => $validated['title'],
            'author'           => $validated['author'],
            'description'      => $validated['description'] ?? null,
            'resource_type'    => $validated['resource_type'],
            'file_path'        => $filePath,
            'url'              => $validated['url'] ?? null,
            'publisher'        => $validated['publisher'] ?? null,
            'publication_year' => $validated['publication_year'] ?? null,
        ]);

        return redirect()->route('e_library.manage_resources')->with('success', 'Resource updated successfully.');
    }

    /**
     * Remove the specified e-Library resource from the database.
     */
    public function destroyResource($id)
    {
        $resource = Resource::findOrFail($id);

        if ($resource->file_path) {
            Storage::disk('public')->delete($resource->file_path);
        }

        $resource->delete();

        return redirect()->route('e_library.manage_resources')->with('success', 'Resource deleted successfully.');
    }

    /**
     * Display resources for browsing (read-only view for all users).
     */
    public function viewResources(Request $request)
    {
        $query = Resource::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('publisher', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('resource_type', $request->type);
        }

        if ($request->filled('year')) {
            $query->where('publication_year', $request->year);
        }

        $sort = $request->get('sort', 'newest');
        match ($sort) {
            'oldest'     => $query->orderBy('created_at', 'asc'),
            'title_asc'  => $query->orderBy('title', 'asc'),
            'title_desc' => $query->orderBy('title', 'desc'),
            'author_asc' => $query->orderBy('author', 'asc'),
            default      => $query->orderBy('created_at', 'desc'),
        };

        $perPage   = $request->get('per_page', 12);
        $resources = $query->paginate($perPage)->withQueryString();

        return view('library.e_library.view_resources', compact('resources'));
    }

    /**
     * Display a listing of e-Library members.
     */
    public function members()
    {
        $members = \App\Models\User::where(function ($q) {
            $q->whereIn('user_type', [3, 4, 5])
              ->orWhere('is_librarian', true);
        })->get();

        return view('library.e_library.members', compact('members'));
    }

    /**
     * View / stream a single resource file or redirect to URL.
     */
    public function viewResource($id)
    {
        $resource = Resource::findOrFail($id);

        if ($resource->resource_type === 'link' && $resource->url) {
            return redirect()->away($resource->url);
        }

        if ($resource->file_path && Storage::disk('public')->exists($resource->file_path)) {
            $filePath = storage_path('app/public/' . $resource->file_path);

            $mime = match ($resource->resource_type) {
                'pdf'   => 'application/pdf',
                'docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'pptx'  => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'ebook' => 'application/epub+zip',
                'video' => 'video/mp4',
                'audio' => 'audio/mpeg',
                default => 'application/octet-stream',
            };

            return response()->file($filePath, ['Content-Type' => $mime]);
        }

        abort(404, 'Resource not found.');
    }
}