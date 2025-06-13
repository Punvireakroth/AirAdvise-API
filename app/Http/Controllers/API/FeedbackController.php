<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Feedback;
use Illuminate\Support\Facades\Auth;
use App\Models\FeedbackResponse;

class FeedbackController extends Controller
{
    /**
     * Display a listing of the feedbacks.
     */

    public function index(Request $request)
    {
        $query = Feedback::with('user');

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('subject', 'like', "%{$searchTerm}%")
                    ->orWhere('message', 'like', "%{$searchTerm}%")
                    ->orWhereHas('user', function ($query) use ($searchTerm) {
                        $query->where('name', 'like', "%{$searchTerm}%")
                            ->orWhere('email', 'like', "%{$searchTerm}%");
                    });
            });
        }

        $feedbacks = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.feedback.index', compact('feedbacks'));
    }

    /**
     * Display a specific feedback.
     */
    public function show(Feedback $feedback)
    {
        $feedback->load('user', 'responses.admin');

        return view('admin.feedback.show', compact('feedback'));
    }

    /** Store a response to the feedback. */
    public function response(Request $request, Feedback $feedback)
    {
        $validated = $request->validate([
            'response' => 'required|string|max:255',
        ]);

        FeedbackResponse::create([
            'feedback_id' => $feedback->id,
            'admin_id' => request()->user()->id,
            'response' => $validated['response'],
        ]);

        $feedback->update([
            'status' => 'responded',
        ]);

        return redirect()->route('admin.feedback.show', $feedback)
            ->with('success', 'Response sent successfully.');
    }

    /** Update the status of the feedback. */
    public function updateStatus(Request $request, Feedback $feedback)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,responded,resolved,archived',
        ]);

        $feedback->update([
            'status' => $validated['status'],
        ]);

        return redirect()->route('admin.feedback.show', $feedback)
            ->with('success', 'Feedback status updated successfully.');
    }

    /**
     * Remove the specified feedback.
     */
    public function destroy(Feedback $feedback)
    {
        $feedback->delete();

        return redirect()->route('admin.feedback.index')
            ->with('success', 'Feedback deleted successfully.');
    }


    /**
     * API for the feedbacks ------------------------------------------------------------
     */

    public function storeUserFeedback(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $feedback = Feedback::create([
            'user_id' => Auth::id(),
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Feedback submitted successfully',
            'feedback' => $feedback,
        ], 201);
    }

    /**
     * Get feedbacks responses from admin for authenticated user ------------------------------------------------------------
     */
    public function getUserFeedback()
    {
        $feedback = Feedback::where('user_id', Auth::id())
            ->with('responses')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'feedback' => $feedback
        ]);
    }
}
