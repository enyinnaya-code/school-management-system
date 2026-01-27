<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\LibraryMember;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;

class PhysicalLibraryController extends Controller
{
    public function createBook()
    {
        return view('add_book');
    }

    public function storeBook(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'author' => 'required|string|max:255',
                'isbn' => 'nullable|string|max:13',
                'quantity' => 'required|integer|min:1',
                'description' => 'nullable|string',
                'publisher' => 'nullable|string|max:255',
                'publication_year' => 'nullable|integer|min:1800|max:' . date('Y'),
            ]);

            // Set available_quantity equal to quantity when creating
            $validated['available_quantity'] = $validated['quantity'];

            Book::create($validated);

            return redirect()->route('physical_library.add_book')->with('success', 'Book added successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to add book: ' . $e->getMessage()])->withInput();
        }
    }


    public function indexBooks()
    {
        try {
            $books = Book::query();

            // Optional filters
            if (request('filter_title')) {
                $books->where('title', 'like', '%' . request('filter_title') . '%');
            }
            if (request('filter_author')) {
                $books->where('author', 'like', '%' . request('filter_author') . '%');
            }
            if (request('filter_isbn')) {
                $books->where('isbn', 'like', '%' . request('filter_isbn') . '%');
            }
            if (request('filter_availability') !== null && request('filter_availability') !== '') {
                $books->where('available_quantity', request('filter_availability') == '1' ? '>' : '=', 0);
            }
            if (request('filter_date_from')) {
                $books->whereDate('created_at', '>=', request('filter_date_from'));
            }
            if (request('filter_date_to')) {
                $books->whereDate('created_at', '<=', request('filter_date_to'));
            }

            $books = $books->orderBy('created_at', 'desc')->paginate(15);


            return view('manage_books', compact('books'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to load books: ' . $e->getMessage()]);
        }
    }

    public function editBook($encryptedId)
    {
        try {
            $id = Crypt::decrypt($encryptedId);
            $book = Book::findOrFail($id);
            return view('library.physical.edit_book', compact('book'));
        } catch (\Exception $e) {
            return redirect()->route('physical_library.manage_books')
                ->withErrors(['error' => 'Invalid book link or book not found.']);
        }
    }


    public function updateBook(Request $request, $encryptedId)
    {
        try {
            $id = Crypt::decrypt($encryptedId);
            $book = Book::findOrFail($id);

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'author' => 'required|string|max:255',
                'isbn' => 'nullable|string|max:13',
                'quantity' => 'required|integer|min:1',
                'description' => 'nullable|string',
                'publisher' => 'nullable|string|max:255',
                'publication_year' => 'nullable|integer|min:1800|max:' . date('Y'),
            ]);

            // If total quantity is increased, increase available_quantity accordingly
            // If decreased, only allow if not below currently borrowed copies
            $currentlyBorrowed = $book->borrowings()->whereNull('returned_at')->count();
            $newQuantity = $validated['quantity'];

            if ($newQuantity < $book->quantity) {
                // Trying to reduce total quantity
                if ($newQuantity < $currentlyBorrowed) {
                    return redirect()->back()
                        ->withErrors(['quantity' => 'Cannot reduce quantity below the number of currently borrowed copies (' . $currentlyBorrowed . ').'])
                        ->withInput();
                }
                // Reduce available_quantity by the difference
                $difference = $book->quantity - $newQuantity;
                $validated['available_quantity'] = $book->available_quantity - $difference;
            } else {
                // Increasing total quantity → add the difference to available
                $difference = $newQuantity - $book->quantity;
                $validated['available_quantity'] = $book->available_quantity + $difference;
            }

            $book->update($validated);

            return redirect()->route('physical_library.manage_books')
                ->with('success', 'Book updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update book: Invalid link or book not found.'])
                ->withInput();
        }
    }


    public function destroyBook($id)
    {
        try {
            $book = Book::findOrFail($id);
            // Check if book is currently borrowed
            if ($book->borrowings()->whereNull('returned_at')->exists()) {
                return redirect()->route('physical_library.manage_books')->withErrors(['error' => 'Cannot delete book with active borrowings.']);
            }
            $book->delete();
            return redirect()->route('physical_library.manage_books')->with('success', 'Book deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('physical_library.manage_books')->withErrors(['error' => 'Failed to delete book: ' . $e->getMessage()]);
        }
    }

    public function borrowingReturns()
    {
        try {
            $query = Borrowing::with(['user.schoolClass', 'book', 'approver']);

            // Filter by User Name
            if (request('filter_user_name')) {
                $query->whereHas('user', function ($q) {
                    $q->where('name', 'like', '%' . request('filter_user_name') . '%');
                });
            }

            // Filter by Book Title
            if (request('filter_book_title')) {
                $query->whereHas('book', function ($q) {
                    $q->where('title', 'like', '%' . request('filter_book_title') . '%');
                });
            }

            // Filter by User Type
            if (request('filter_user_type')) {
                $query->whereHas('user', function ($q) {
                    $q->where('user_type', request('filter_user_type'));
                });
            }

            // Filter by Class (for students)
            if (request('filter_class')) {
                $query->whereHas('user', function ($q) {
                    $q->where('class_id', request('filter_class'))
                        ->where('user_type', 4); // Only students
                });
            }

            // Filter by Status
            if (request('filter_status')) {
                $query->where('status', request('filter_status'));
            }

            // Filter by Request Date From
            if (request('filter_request_from')) {
                $query->whereDate('created_at', '>=', request('filter_request_from'));
            }

            // Filter by Request Date To
            if (request('filter_request_to')) {
                $query->whereDate('created_at', '<=', request('filter_request_to'));
            }

            // Filter by Borrowed Date From
            if (request('filter_borrowed_from')) {
                $query->whereDate('borrowed_at', '>=', request('filter_borrowed_from'));
            }

            // Filter by Borrowed Date To
            if (request('filter_borrowed_to')) {
                $query->whereDate('borrowed_at', '<=', request('filter_borrowed_to'));
            }

            // Filter by Due Date From
            if (request('filter_due_from')) {
                $query->whereDate('due_date', '>=', request('filter_due_from'));
            }

            // Filter by Due Date To
            if (request('filter_due_to')) {
                $query->whereDate('due_date', '<=', request('filter_due_to'));
            }

            // Filter by Returned Date From
            if (request('filter_returned_from')) {
                $query->whereDate('returned_at', '>=', request('filter_returned_from'));
            }

            // Filter by Returned Date To
            if (request('filter_returned_to')) {
                $query->whereDate('returned_at', '<=', request('filter_returned_to'));
            }

            // Filter by ISBN
            if (request('filter_isbn')) {
                $query->whereHas('book', function ($q) {
                    $q->where('isbn', 'like', '%' . request('filter_isbn') . '%');
                });
            }

            // Filter by Author
            if (request('filter_author')) {
                $query->whereHas('book', function ($q) {
                    $q->where('author', 'like', '%' . request('filter_author') . '%');
                });
            }

            // Filter by Approver
            if (request('filter_approver')) {
                $query->whereHas('approver', function ($q) {
                    $q->where('name', 'like', '%' . request('filter_approver') . '%');
                });
            }

            // Order by status priority (pending first) then by date
            $borrowings = $query->orderByRaw("FIELD(status, 'pending', 'approved', 'overdue', 'returned')")
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            // Get all classes for filter dropdown
            $classes = \App\Models\SchoolClass::orderBy('name')->get();

            return view('borrowing_returns', compact('borrowings', 'classes'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to load borrowings: ' . $e->getMessage()]);
        }
    }




    public function returnBook($encryptedId)
    {
        try {
            $id = Crypt::decrypt($encryptedId);
            $borrowing = Borrowing::with('book')->findOrFail($id);

            if ($borrowing->status === 'returned') {
                return redirect()->back()->withErrors(['error' => 'This book has already been returned.']);
            }

            if ($borrowing->status === 'pending') {
                return redirect()->back()->withErrors(['error' => 'Cannot return a book that hasn\'t been approved yet.']);
            }

            DB::beginTransaction();

            $borrowing->book->increment('available_quantity');

            $borrowing->update([
                'status' => 'returned',
                'returned_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('physical_library.borrowing_returns')
                ->with('success', 'Book marked as returned successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Failed to return book: ' . $e->getMessage()]);
        }
    }



    // Add method to undo return (if mistake was made)
    public function undoReturn($encryptedId)
    {
        try {
            $id = Crypt::decrypt($encryptedId);
            $borrowing = Borrowing::with('book')->findOrFail($id);

            if ($borrowing->status !== 'returned') {
                return redirect()->back()->withErrors(['error' => 'This book has not been returned yet.']);
            }

            DB::beginTransaction();

            $borrowing->book->decrement('available_quantity');

            $borrowing->update([
                'status' => 'approved',
                'returned_at' => null,
            ]);

            DB::commit();

            return redirect()->route('physical_library.borrowing_returns')
                ->with('success', 'Return undone successfully. Book is now marked as borrowed again.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Failed to undo return: ' . $e->getMessage()]);
        }
    }




    public function borrowBook(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'book_id' => 'required|exists:books,id',
            ]);

            $book = Book::findOrFail($validated['book_id']);
            // Check if book is available
            if ($book->quantity <= $book->borrowings()->whereNull('returned_at')->count()) {
                return redirect()->back()->withErrors(['error' => 'No copies of this book are available for borrowing.'])->withInput();
            }
            // Check if user already borrowed this book
            if (Borrowing::where('user_id', $validated['user_id'])->where('book_id', $validated['book_id'])->whereNull('returned_at')->exists()) {
                return redirect()->back()->withErrors(['error' => 'This user has already borrowed this book.'])->withInput();
            }

            Borrowing::create([
                'user_id' => $validated['user_id'],
                'book_id' => $validated['book_id'],
                'borrowed_at' => now(),
            ]);

            return redirect()->route('physical_library.borrowing_returns')->with('success', 'Book borrowed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to borrow book: ' . $e->getMessage()])->withInput();
        }
    }



    public function members(Request $request)
    {
        $query = LibraryMember::with('user');

        // Apply filters
        if ($request->filled('filter_name')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->filter_name . '%');
            });
        }
        if ($request->filled('filter_membership_id')) {
            $query->where('membership_id', 'like', '%' . $request->filter_membership_id . '%');
        }
        if ($request->filled('filter_user_type')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('user_type', $request->filter_user_type);
            });
        }
        if ($request->filled('filter_status')) {
            $query->where('status', $request->filter_status);
        }

        // Paginate results (10 per page for performance)
        $members = $query->orderBy('membership_id')->paginate(10);

        return view('members', compact('members'));
    }


    public function createBorrow()
    {
        $members = LibraryMember::where('status', 'active')
            ->with('user')
            ->orderBy('membership_id')
            ->take(100)
            ->get();
        $books = Book::where('available_quantity', '>', 0)
            ->orderBy('title')
            ->take(100)
            ->get();
        return view('borrow_book', compact('members', 'books'));
    }

    public function assignLibrarian(Request $request)
    {
        try {
            $query = User::where('user_type', 3);

            // Apply filters
            if ($request->filled('filter_name')) {
                $query->where('name', 'like', '%' . $request->filter_name . '%');
            }

            if ($request->filled('filter_status')) {
                if ($request->filter_status == 'assigned') {
                    $query->where('is_librarian', 1);
                } elseif ($request->filter_status == 'not_assigned') {
                    $query->where('is_librarian', 0);
                }
            }

            $teachers = $query->orderBy('name')->get();

            return view('library.physical.assign_librarian', compact('teachers'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to load teachers: ' . $e->getMessage()]);
        }
    }

    public function storeAssignLibrarian(Request $request)
    {
        try {
            $validated = $request->validate([
                'librarians' => 'nullable|array',
                'librarians.*' => 'exists:users,id'
            ]);

            $selected = $request->input('librarians', []); // array of selected teacher IDs

            // Reset all teachers to non-librarian
            User::where('user_type', 3)->update(['is_librarian' => 0]);

            // Set selected as librarians
            if (!empty($selected)) {
                User::whereIn('id', $selected)
                    ->where('user_type', 3)
                    ->update(['is_librarian' => 1]);
            }

            return redirect()->route('physical_library.assign_librarian')
                ->with('success', 'Librarians assigned/removed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update assignments: ' . $e->getMessage()])
                ->withInput();
        }
    }


    // Show form for user to request borrowing a book
    public function requestBorrow(Request $request)
    {
        $query = Book::query();

        // Only show books with at least one available copy
        $query->where('available_quantity', '>', 0);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%")
                    ->orWhere('isbn', 'like', "%{$search}%")
                    ->orWhere('publisher', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $books = $query->orderBy('title')->get();

        return view('library.physical.request_borrow', compact('books'));
    }


    // Store borrow request (pending approval)
    public function storeBorrowRequest(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'due_date' => 'required|date|after:today',
        ]);

        $book = Book::findOrFail($request->book_id);

        // Check availability
        $activeBorrowings = Borrowing::where('book_id', $book->id)
            ->whereNull('returned_at')
            ->count();

        if ($book->quantity <= $activeBorrowings) {
            return back()->withErrors(['book_id' => 'This book is currently not available.']);
        }

        // Prevent duplicate pending/active request
        $existing = Borrowing::where('user_id', Auth::id())
            ->where('book_id', $book->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($existing) {
            return back()->withErrors(['book_id' => 'You already have a pending or active borrow for this book.']);
        }

        Borrowing::create([
            'user_id' => Auth::id(),
            'book_id' => $book->id,
            'due_date' => $request->due_date,
            'status' => 'pending',
            'borrowed_at' => null,
        ]);

        return redirect()->route('physical_library.my_borrows')
            ->with('success', 'Borrow request submitted. Awaiting librarian approval.');
    }

    // View user's own borrowings
    public function myBorrows()
    {
        $borrowings = Borrowing::with('book')
            ->where('user_id', Auth::id())
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'overdue', 'returned')")
            ->orderBy('due_date')
            ->paginate(10);

        return view('library.physical.my_borrows', compact('borrowings'));
    }

    // Librarian: Approve a borrow request
    public function approveBorrow(Request $request, $id)
    {
        $request->validate([
            'due_date' => 'required|date|after:today',
        ]);

        DB::beginTransaction();
        try {
            $borrowing = Borrowing::with('book')->findOrFail($id);

            if ($borrowing->status !== 'pending') {
                return back()->withErrors(['error' => 'This request cannot be approved.']);
            }

            // Check if still available
            if ($borrowing->book->available_quantity <= 0) {
                return back()->withErrors(['error' => 'This book is no longer available.']);
            }

            // Reduce available quantity
            $borrowing->book->decrement('available_quantity');

            // Update borrowing
            $borrowing->update([
                'status' => 'approved',
                'borrowed_at' => now(),
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'due_date' => $request->due_date,
            ]);

            DB::commit();

            return back()->with('success', 'Borrow request approved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to approve: ' . $e->getMessage()]);
        }
    }

    // Librarian: Reject a borrow request
    public function rejectBorrow(Request $request, $id)
    {
        $borrowing = Borrowing::findOrFail($id);

        if ($borrowing->status !== 'pending') {
            return back()->withErrors(['error' => 'This request cannot be rejected.']);
        }

        $borrowing->update([
            'status' => 'rejected', // New status
        ]);

        return back()->with('success', 'Borrow request rejected.');
    }
}
