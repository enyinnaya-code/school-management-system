<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use App\Models\Section;
use App\Models\SchoolClass;
use Illuminate\Support\Facades\DB;
use App\Models\Course;
use App\Models\Session;
use App\Models\Term;

class UserController extends Controller
{
    public function create()
    {
        return view('add_user');
    }

    public function createTeacher()
    {
        $sections = Section::all();
        $courses = Course::all();

        return view('add_teacher', compact('sections', 'courses'));
    }

    public function teacherDashboard()
    {
        $teacher = Auth::user();

        // Current session & term – same logic as student dashboard
        $currentSession = Session::where('is_current', true)->first();
        $currentTerm    = Term::where('is_current', true)->first();

        // Classes the teacher is currently teaching
        $assignedClasses = $teacher->classes()->with('section')->get();

        // Unique courses the teacher is teaching (across all classes)
        $assignedCourses = $teacher->courses()->distinct()->get();

        // Count of classes & courses
        $classesCount  = $assignedClasses->count();
        $coursesCount  = $assignedCourses->count();

        // Check if teacher is a form teacher
        $formClass = null;
        if ($teacher->is_form_teacher && $teacher->form_class_id) {
            $formClass = SchoolClass::with('section')->find($teacher->form_class_id);
        }

        return view('teacher_dashboard', compact(
            'teacher',
            'currentSession',
            'currentTerm',
            'assignedClasses',
            'assignedCourses',
            'classesCount',
            'coursesCount',
            'formClass'
        ));
    }


    public function getClassesBySections(Request $request)
    {
        $sectionIds = $request->input('section_ids', []);

        $classes = SchoolClass::whereIn('section_id', $sectionIds)
            ->get()
            ->map(function ($class) {
                return [
                    'id' => $class->id,
                    'name' => $class->name ?? $class->class_name ?? 'Unnamed Class ' . $class->id,
                    'section_id' => $class->section_id
                ];
            });

        return response()->json($classes);
    }



    public function getAssignedFormClassesWithTeachers()
    {
        $assignments = User::where('is_form_teacher', true)
            ->whereNotNull('form_class_id')
            ->select('form_class_id', 'name')
            ->get()
            ->keyBy('form_class_id');

        return response()->json($assignments);
    }

    public function getSubjectsBySection($sectionId)
    {
        $user = Auth::user();

        if ($user->user_type == 1 || $user->user_type == 2) {
            $subjects = Course::where('section_id', $sectionId)
                ->select('id', 'course_name')
                ->get();
        } else {
            $subjects = Course::where('section_id', $sectionId)
                ->join('course_user', 'courses.id', '=', 'course_user.course_id')
                ->where('course_user.user_id', $user->id)
                ->select('courses.id', 'courses.course_name')
                ->get();
        }

        return response()->json($subjects);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'user_type' => 'required|integer',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => $request->user_type,
            'added_by' => Auth::id(),
        ]);

        return redirect()->route('user.add')->with('success', 'User added successfully.');
    }

    public function storeTeacher(Request $request)
    {
        $userType = $request->input('user_type', 3);

        // Roles that can teach: Teacher(3), Principal(7), Vice-Principal(8), Dean of Studies(9)
        $teachingCapableRoles = [3, 7, 8, 9, 10];
        $isTeachingCapable = in_array($userType, $teachingCapableRoles);

        // Base rules
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'user_type' => 'required|integer|in:3,6,7,8,9,10',
        ];

        // Optional teaching fields for capable roles
        if ($isTeachingCapable) {
            $rules['section_ids'] = 'nullable|array';
            $rules['section_ids.*'] = 'exists:sections,id';
            $rules['class_ids'] = 'nullable|array';
            $rules['class_ids.*'] = 'exists:school_classes,id';
            $rules['course_ids'] = 'nullable|array';
            $rules['course_ids.*'] = 'exists:courses,id';
            $rules['is_form_teacher'] = 'nullable|in:0,1';
            $rules['form_class_id'] = 'nullable|exists:school_classes,id';
        }

        $request->validate($rules);

        // Handle form teacher assignment
        $isFormTeacher = $isTeachingCapable && $request->filled('is_form_teacher') && $request->is_form_teacher == 1;

        if ($isFormTeacher) {
            $request->validate([
                'form_class_id' => 'required|exists:school_classes,id'
            ]);

            // Remove existing form teacher assignment
            $existing = User::where('is_form_teacher', true)
                ->where('form_class_id', $request->form_class_id)
                ->first();

            if ($existing) {
                $existing->is_form_teacher = false;
                $existing->form_class_id = null;
                $existing->save();
            }
        }

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => strtoupper($request->name),
                'email' => $request->email,
                'password' => Hash::make('123456'),
                'user_type' => $userType,
                'added_by' => Auth::id(),
                'is_form_teacher' => $isFormTeacher,
                'form_class_id' => $isFormTeacher ? $request->form_class_id : null,
            ]);

            // Attach teaching assignments only if role allows
            if ($isTeachingCapable) {
                $user->sections()->sync($request->section_ids ?? []);
                $user->classes()->sync($request->class_ids ?? []);

                // Course assignments per class
                if ($request->filled('course_ids') && $request->filled('class_ids')) {
                    $courseAssignments = [];

                    foreach ($request->class_ids as $classId) {
                        $schoolClass = SchoolClass::find($classId);
                        if (!$schoolClass) continue;

                        foreach ($request->course_ids as $courseId) {
                            $course = Course::find($courseId);
                            if ($course && $course->section_id == $schoolClass->section_id) {
                                $courseAssignments[] = [
                                    'course_id'  => $courseId,
                                    'user_id'    => $user->id,
                                    'section_id' => $schoolClass->section_id,
                                    'class_id'   => $classId,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            }
                        }
                    }

                    if (!empty($courseAssignments)) {
                        // Clear old (none yet), then insert
                        DB::table('course_user')->insert($courseAssignments);
                    }
                }
            }

            DB::commit();

            return redirect()->route('teacher.add')->with('success', 'Staff added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('teacher.add')->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }


    public function manageUsers(Request $request)
    {
        $currentUser = Auth::user();

        $filter_name = $request->input('filter_name');
        $filter_email = $request->input('filter_email');
        $filter_status = $request->input('filter_status');
        $filter_date_from = $request->input('filter_date_from');
        $filter_date_to = $request->input('filter_date_to');

        $query = User::where('id', '!=', $currentUser->id)
            ->whereIn('user_type', [2, 10]);

        if ($filter_name) $query->where('name', 'like', '%' . $filter_name . '%');
        if ($filter_email) $query->where('email', 'like', '%' . $filter_email . '%');
        if ($filter_status !== null && $filter_status !== '') $query->where('is_active', $filter_status);
        if ($filter_date_from) $query->whereDate('created_at', '>=', $filter_date_from);
        if ($filter_date_to) $query->whereDate('created_at', '<=', $filter_date_to);

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('manage_user', compact('users'));
    }

    public function manageTeacher(Request $request)
    {
        $currentUser = Auth::user();

        $filter_name = $request->input('filter_name');
        $filter_email = $request->input('filter_email');
        $filter_user_type = $request->input('filter_teacher_type');
        $filter_status = $request->input('filter_status');
        $filter_date_from = $request->input('filter_date_from');
        $filter_date_to = $request->input('filter_date_to');
        $filter_section_ids = $request->input('filter_section_ids', []);
        $filter_class_ids = $request->input('filter_class_ids', []);
        $filter_form_teacher = $request->input('filter_form_teacher'); // Add this line

        $query = User::where('id', '!=', $currentUser->id)
            ->whereNotIn('user_type', [1, 2, 4, 5]);

        if ($currentUser->user_type == 2) {
            $query->where('user_type', '!=', 1);
        }

        if ($filter_name) {
            $query->where('name', 'like', '%' . $filter_name . '%');
        }
        if ($filter_email) {
            $query->where('email', 'like', '%' . $filter_email . '%');
        }
        if ($filter_user_type !== null && $filter_user_type !== '') {
            $query->where('user_type', $filter_user_type);
        }
        if ($filter_status !== null && $filter_status !== '') {
            $query->where('is_active', $filter_status);
        }
        if ($filter_date_from) {
            $query->whereDate('created_at', '>=', $filter_date_from);
        }
        if ($filter_date_to) {
            $query->whereDate('created_at', '<=', $filter_date_to);
        }

        // Add Form Teacher filter
        if ($filter_form_teacher !== null && $filter_form_teacher !== '') {
            if ($filter_form_teacher == '1') {
                // Show only form teachers
                $query->where('is_form_teacher', true);
            } elseif ($filter_form_teacher == '0') {
                // Show only non-form teachers
                $query->where(function ($q) {
                    $q->where('is_form_teacher', false)
                        ->orWhereNull('is_form_teacher');
                });
            }
        }

        // Filter by sections
        if (!empty($filter_section_ids)) {
            $query->whereHas('sections', function ($q) use ($filter_section_ids) {
                $q->whereIn('sections.id', $filter_section_ids);
            });
        }

        // Filter by classes
        if (!empty($filter_class_ids)) {
            $query->whereHas('classes', function ($q) use ($filter_class_ids) {
                $q->whereIn('school_classes.id', $filter_class_ids);
            });
        }

        $teachers = $query
            ->with([
                'sections:id,section_name',
                'classes:id,name',
                'courses:id,course_name',
                'formClass:id,name'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $sections = Section::all();
        $classes = SchoolClass::all();

        return view('manage_teachers', compact('teachers', 'sections', 'classes'));
    }



    public function edit($id)
    {
        try {
            $decryptedId = Crypt::decrypt($id);
            $user = User::findOrFail($decryptedId);
            return view('edit_user', compact('user'));
        } catch (\Exception $e) {
            return redirect()->route('users.index')->with('error', 'User not found or invalid ID.');
        }
    }

    public function editTeacher($encryptedId)
    {
        $id = Crypt::decrypt($encryptedId);
        $teacher = User::with(['sections', 'classes', 'courses'])->findOrFail($id);
        $sections = Section::all();

        $assignedSectionIds = $teacher->sections()->pluck('sections.id')->toArray();
        $assignedClassIds = $teacher->classes()->pluck('school_classes.id')->toArray();
        $assignedCourseIds = $teacher->courses()->pluck('courses.id')->toArray();

        // Add this to pre-load assigned form teachers globally
        $assignedFormTeachers = User::where('is_form_teacher', true)
            ->whereNotNull('form_class_id')
            ->select('form_class_id', 'name', 'id')
            ->get()
            ->keyBy('form_class_id');

        return view('edit_teacher', compact(
            'teacher',
            'sections',
            'assignedSectionIds',
            'assignedClassIds',
            'assignedCourseIds',
            'assignedFormTeachers'  // Pass this
        ));
    }


    public function updateTeacher(Request $request, $encryptedId)
    {
        $id = Crypt::decrypt($encryptedId);
        $teacher = User::findOrFail($id);

        $newUserType = $request->input('user_type', $teacher->user_type);
        $teachingCapableRoles = [3, 7, 8, 9, 10];
        $isTeachingCapable = in_array($newUserType, $teachingCapableRoles);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$id}",
            'user_type' => 'required|integer|in:3,6,7,8,9,10',
        ];

        if ($isTeachingCapable) {
            $rules['section_ids'] = 'nullable|array';
            $rules['section_ids.*'] = 'exists:sections,id';
            $rules['class_ids'] = 'nullable|array';
            $rules['class_ids.*'] = 'exists:school_classes,id';
            $rules['course_ids'] = 'nullable|array';
            $rules['course_ids.*'] = 'exists:courses,id';
            $rules['is_form_teacher'] = 'nullable|in:0,1';
            $rules['form_class_id'] = 'nullable|exists:school_classes,id';
        }

        $request->validate($rules);

        $isAssigningFormTeacher = $isTeachingCapable && $request->filled('is_form_teacher') && $request->is_form_teacher == 1;

        if ($isAssigningFormTeacher) {
            $request->validate(['form_class_id' => 'required|exists:school_classes,id']);

            $existing = User::where('is_form_teacher', true)
                ->where('form_class_id', $request->form_class_id)
                ->where('id', '!=', $teacher->id)
                ->first();

            if ($existing) {
                return redirect()->back()->withInput()->with('error', "This class is already assigned to {$existing->name} as form teacher.");
            }
        }

        $teacher->update([
            'name' => strtoupper($request->name),
            'email' => $request->email,
            'user_type' => $newUserType,
            'is_form_teacher' => $isAssigningFormTeacher,
            'form_class_id' => $isAssigningFormTeacher ? $request->form_class_id : null,
        ]);

        if ($isTeachingCapable) {
            $teacher->sections()->sync($request->section_ids ?? []);
            $teacher->classes()->sync($request->class_ids ?? []);

            // Clear and reassign courses
            $teacher->courses()->detach();

            if ($request->filled('course_ids') && $request->filled('class_ids')) {
                $courseAssignments = [];
                foreach ($request->class_ids as $classId) {
                    $schoolClass = SchoolClass::find($classId);
                    if (!$schoolClass) continue;

                    foreach ($request->course_ids as $courseId) {
                        $course = Course::find($courseId);
                        if ($course && $course->section_id == $schoolClass->section_id) {
                            $courseAssignments[] = [
                                'course_id'  => $courseId,
                                'user_id'    => $teacher->id,
                                'section_id' => $schoolClass->section_id,
                                'class_id'   => $classId,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }
                }

                if (!empty($courseAssignments)) {
                    DB::table('course_user')->insert($courseAssignments);
                }
            }
        } else {
            // If changed to non-teaching role, clear all teaching data
            $teacher->sections()->detach();
            $teacher->classes()->detach();
            $teacher->courses()->detach();
            $teacher->is_form_teacher = false;
            $teacher->form_class_id = null;
            $teacher->save();
        }

        return redirect()->route('teachers.index')->with('success', 'Staff updated successfully.');
    }


    public function getAssignedFormClassIds()
    {
        $assignedIds = User::where('is_form_teacher', true)
            ->whereNotNull('form_class_id')
            ->pluck('form_class_id')
            ->toArray();

        return response()->json($assignedIds);
    }

    public function update(Request $request, $id)
    {
        $decryptedId = Crypt::decrypt($id);
        $user = User::findOrFail($decryptedId);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $decryptedId,
            'user_type' => 'required|integer',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'user_type' => $request->user_type,
        ]);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function toggleActive($encryptedId)
    {
        try {
            $id = Crypt::decrypt($encryptedId);
            $user = User::findOrFail($id);
            $user->is_active = !$user->is_active;
            if ($user->is_active) $user->login_attempts = 3;
            $user->save();

            $status = $user->is_active ? 'activated' : 'deactivated';
            return redirect()->route('users.index')->with('success', "User has been {$status} successfully.");
        } catch (\Exception $e) {
            return redirect()->route('users.index')->with('error', 'Failed to update status.');
        }
    }

    public function toggleActiveTeacher($encryptedId)
    {
        try {
            $id = Crypt::decrypt($encryptedId);
            $user = User::findOrFail($id);
            $user->is_active = !$user->is_active;
            if ($user->is_active) $user->login_attempts = 3;
            $user->save();

            $status = $user->is_active ? 'activated' : 'deactivated';
            return redirect()->route('teachers.index')->with('success', "Staff has been {$status} successfully.");
        } catch (\Exception $e) {
            return redirect()->route('teachers.index')->with('error', 'Failed to update status.');
        }
    }

    public function resetPassword($encryptedId)
    {
        try {
            $id = Crypt::decrypt($encryptedId);
            $user = User::findOrFail($id);
            $user->password = Hash::make('123456');
            $user->save();
            return redirect()->route('teachers.index')->with('success', 'User password reset successfully.');
        } catch (\Exception $e) {
            return redirect()->route('teachers.index')->with('error', 'Failed to reset password.');
        }
    }

    public function resetPasswordTeacher($encryptedId)
    {
        try {
            $id = Crypt::decrypt($encryptedId);
            $user = User::findOrFail($id);
            $user->password = Hash::make('123456');
            $user->save();
            return redirect()->route('teachers.index')->with('success', 'Teacher password reset to 123456.');
        } catch (\Exception $e) {
            return redirect()->route('teachers.index')->with('error', 'Failed to reset password.');
        }
    }

    public function destroy($encryptedId)
    {
        try {
            $id = Crypt::decrypt($encryptedId);
            $user = User::findOrFail($id);
            $user->delete();
            return redirect()->route('users.index')->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('users.index')->with('error', 'Failed to delete user.');
        }
    }

    public function destroyTeacher($encryptedId)
    {
        try {
            $id = Crypt::decrypt($encryptedId);
            $user = User::findOrFail($id);
            $user->delete();
            return redirect()->route('teachers.index')->with('success', 'Teacher deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('teachers.index')->with('error', 'Failed to delete teacher.');
        }
    }
}
