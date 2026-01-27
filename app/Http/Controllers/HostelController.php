<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hostel;
use App\Models\User;

class HostelController extends Controller
{
    /**
     * Show the form for adding a new hostel.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $teachers = User::where('user_type', 3)->get(); // Assuming user_type 3 is teachers
        return view('hostels.add', compact('teachers'));
    }

    /**
     * Store a new hostel in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'warden_ids' => 'nullable|array',
            'warden_ids.*' => 'exists:users,id,user_type,3',
        ]);

        $hostel = Hostel::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);

        if (!empty($validated['warden_ids'])) {
            $hostel->wardens()->attach($validated['warden_ids']);
        }

        return redirect()->route('hostels.manage')->with('success', 'Hostel added successfully.');
    }

    /**
     * Display a listing of hostels.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Eager load both wardens and students to avoid N+1 queries
        $hostels = Hostel::with(['wardens', 'students'])->get();
        return view('hostels.manage', compact('hostels'));
    }

    /**
     * Show the form for editing the specified hostel.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $hostel = Hostel::with('wardens')->findOrFail($id);
        $teachers = User::where('user_type', 3)->get();
        return view('hostels.edit', compact('hostel', 'teachers'));
    }

    /**
     * Update the specified hostel in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $hostel = Hostel::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'warden_ids' => 'nullable|array',
            'warden_ids.*' => 'exists:users,id,user_type,3',
        ]);

        $hostel->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);

        $hostel->wardens()->sync($validated['warden_ids'] ?? []);

        return redirect()->route('hostels.manage')->with('success', 'Hostel updated successfully.');
    }

    /**
     * Remove the specified hostel from the database.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $hostel = Hostel::findOrFail($id);

        // Detach all wardens
        $hostel->wardens()->detach();

        // Deallocate all students from this hostel
        $hostel->students()->update(['hostel_id' => null]);

        // Delete the hostel
        $hostel->delete();

        return redirect()->route('hostels.manage')->with('success', 'Hostel deleted successfully.');
    }

    /**
     * Show the form for allocating hostels to students.
     *
     * @return \Illuminate\View\View
     */
    public function allocate()
    {
        $hostels = Hostel::all();

        // Get students - adjust user_type as needed (3 or 4 based on your system)
        $students = User::where('user_type', 4) // Change to 4 if students are user_type 4
            ->with('hostel') // Eager load hostel relationship
            ->get();

        return view('hostels.allocate', compact('hostels', 'students'));
    }

    /**
     * Allocate hostels to students.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function allocateStore(Request $request)
    {
        $validated = $request->validate([
            'hostel_id' => 'required|exists:hostels,id',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:users,id'
        ]);

        $hostelId = $validated['hostel_id'];
        $studentIds = $validated['student_ids'];

        // Get the hostel name for the success message
        $hostel = Hostel::findOrFail($hostelId);

        // Check if any students are already allocated
        $alreadyAllocated = User::whereIn('id', $studentIds)
            ->whereNotNull('hostel_id')
            ->get();

        if ($alreadyAllocated->count() > 0) {
            $allocatedNames = $alreadyAllocated->pluck('name')->toArray();
            $allocatedHostels = $alreadyAllocated->map(function ($student) {
                return $student->name . ' (in ' . ($student->hostel->name ?? 'unknown hostel') . ')';
            })->toArray();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Some students are already allocated: ' . implode(', ', $allocatedHostels));
        }

        // Allocate students to the hostel
        User::whereIn('id', $studentIds)->update(['hostel_id' => $hostelId]);

        $studentCount = count($studentIds);

        // Get the names of allocated students for better feedback
        $allocatedStudentNames = User::whereIn('id', $studentIds)->pluck('name')->toArray();

        return redirect()
            ->route('hostels.students', $hostelId)
            ->with('success', "{$studentCount} student(s) allocated to {$hostel->name} successfully: " . implode(', ', $allocatedStudentNames));
    }

    /**
     * Display all students allocated to a specific hostel.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function students($id)
    {
        $hostel = Hostel::with(['wardens', 'students.class'])->findOrFail($id);
        $students = $hostel->students;

        return view('hostels.students', compact('hostel', 'students'));
    }


    /**
     * Deallocate a student from their hostel.
     *
     * @param  int  $studentId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deallocate($studentId)
    {
        $student = User::findOrFail($studentId);
        $hostelId = $student->hostel_id;

        if (!$hostelId) {
            return redirect()
                ->back()
                ->with('error', 'Student is not allocated to any hostel.');
        }

        $studentName = $student->name;
        $student->hostel_id = null;
        $student->save();

        return redirect()
            ->route('hostels.students', $hostelId)
            ->with('success', "{$studentName} has been deallocated successfully.");
    }
}
