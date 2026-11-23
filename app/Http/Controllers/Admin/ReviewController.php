<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::with(['order', 'user', 'approver'])->latest()->paginate(15);
        return view('admin.reviews.index', compact('reviews'));
    }

    public function toggleApproval(Review $review)
    {
        $review->update([
            'is_approved' => ! $review->is_approved,
            'approved_by' => Auth::id(),
        ]);

        $statusText = $review->is_approved ? 'ditampilkan ke publik' : 'disembunyikan dari publik';
        return back()->with('success', "Status ulasan berhasil diubah menjadi {$statusText}.");
    }

    public function destroy(Review $review)
    {
        $review->delete();
        return back()->with('success', 'Ulasan berhasil dihapus.');
    }
}
