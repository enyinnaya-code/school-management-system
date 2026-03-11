<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Resource;
use Illuminate\Support\Facades\Storage;

class ELibraryController extends Controller
{
    /**
     * Show the form for adding a new e-Library resource.
     *
     * @return \Illuminate\View\View
     */
    public function createResource()
    {
        return view('library.e_library.add_resource');
    }

    /**
     * Store a new e-Library resource in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeResource(Request $request)
    {
        // Validate the form inputs
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'description' => 'nullable|string',
            'resource_type' => 'required|in:pdf,docx,xlsx,pptx,ebook,link',
            'file' => 'nullable|file|mimes:pdf,docx,xlsx,pptx,epub|max:5120', // 5MB
            'url' => 'nullable|url',
            'publisher' => 'nullable|string|max:255',
            'publication_year' => 'nullable|integer|min:1800|max:' . date('Y'),
        ]);

        // Handle file upload if provided
        $filePath = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $filePath = $file->storeAs('resources', $filename, 'public');
        }

        // Create the resource in the database
        Resource::create([
            'title' => $validated['title'],
            'author' => $validated['author'],
            'description' => $validated['description'],
            'resource_type' => $validated['resource_type'],
            'file_path' => $filePath,
            'url' => $validated['url'],
            'publisher' => $validated['publisher'],
            'publication_year' => $validated['publication_year'],
        ]);

        // Redirect with success message
        return redirect()->route('e_library.manage_resources')->with('success', 'Resource added successfully.');
    }

    /**
     * Display a listing of the e-Library resources with filtering and pagination.
     *
     * @return \Illuminate\View\View
     */
    public function indexResources(Request $request)
    {
        $query = Resource::query();

        // Search filter (title, author, publisher)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('publisher', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Resource type filter
        if ($request->filled('type')) {
            $query->where('resource_type', $request->type);
        }

        // Publication year filter
        if ($request->filled('year')) {
            $query->where('publication_year', $request->year);
        }

        // Sorting
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'title_asc':
                $query->orderBy('title', 'asc');
                break;
            case 'title_desc':
                $query->orderBy('title', 'desc');
                break;
            case 'author_asc':
                $query->orderBy('author', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Pagination with intelligent per page
        $perPage = $request->get('per_page', 25);
        $resources = $query->paginate($perPage)->withQueryString();

        return view('library.e_library.manage_resources', compact('resources'));
    }

    /**
     * Show the form for editing the specified e-Library resource.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function editResource($id)
    {
        $resource = Resource::findOrFail($id);
        return view('library.e_library.edit_resource', compact('resource'));
    }

    /**
     * Update the specified e-Library resource in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateResource(Request $request, $id)
    {
        $resource = Resource::findOrFail($id);

        // Validate the form inputs
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'description' => 'nullable|string',
            'resource_type' => 'required|in:pdf,docx,xlsx,pptx,ebook,link',
            'file' => 'nullable|file|mimes:pdf,docx,xlsx,pptx,epub,mp4,mp3|max:5120', // Max 5MB
            'url' => 'nullable|url',
            'publisher' => 'nullable|string|max:255',
            'publication_year' => 'nullable|integer|min:1800|max:' . date('Y'),
        ]);

        // Handle file upload if provided
        $filePath = $resource->file_path;
        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($filePath) {
                Storage::disk('public')->delete($filePath);
            }
            
            // Store new file with proper extension
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $filePath = $file->storeAs('resources', $filename, 'public');
        }

        // Update the resource
        $resource->update([
            'title' => $validated['title'],
            'author' => $validated['author'],
            'description' => $validated['description'],
            'resource_type' => $validated['resource_type'],
            'file_path' => $filePath,
            'url' => $validated['url'],
            'publisher' => $validated['publisher'],
            'publication_year' => $validated['publication_year'],
        ]);

        // Redirect with success message
        return redirect()->route('e_library.manage_resources')->with('success', 'Resource updated successfully.');
    }

    /**
     * Remove the specified e-Library resource from the database.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyResource($id)
    {
        $resource = Resource::findOrFail($id);

        // Delete file if exists
        if ($resource->file_path) {
            Storage::disk('public')->delete($resource->file_path);
        }

        $resource->delete();

        // Redirect with success message
        return redirect()->route('e_library.manage_resources')->with('success', 'Resource deleted successfully.');
    }

    /**
     * Display resources for browsing (read-only view for all users)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function viewResources(Request $request)
    {
        $query = Resource::query();

        // Search filter (title, author, publisher)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('publisher', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Resource type filter
        if ($request->filled('type')) {
            $query->where('resource_type', $request->type);
        }

        // Publication year filter
        if ($request->filled('year')) {
            $query->where('publication_year', $request->year);
        }

        // Sorting
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'title_asc':
                $query->orderBy('title', 'asc');
                break;
            case 'title_desc':
                $query->orderBy('title', 'desc');
                break;
            case 'author_asc':
                $query->orderBy('author', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Pagination with intelligent per page (default 12 for grid view)
        $perPage = $request->get('per_page', 12);
        $resources = $query->paginate($perPage)->withQueryString();

        return view('library.e_library.view_resources', compact('resources'));
    }

    /**
     * Display a listing of e-Library members (assuming users with is_librarian or students/parents).
     *
     * @return \Illuminate\View\View
     */
    public function members()
    {
        // Assuming members are users who are students (user_type might indicate, e.g., assuming student user_type is 3 or similar; adjust as needed)
        // For now, fetching all users; refine based on your User model logic (e.g., students, librarians)
        $members = \App\Models\User::whereIn('user_type', [3, 4, 5]) // Example: adjust user_types for students/parents/librarians
            ->orWhere('is_librarian', true)
            ->get();
        return view('library.e_library.members', compact('members'));
    }


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
                default => 'application/octet-stream'
            };

            return response()->file($filePath, ['Content-Type' => $mime]);
        }

        abort(404, 'Resource not found.');
    }
}