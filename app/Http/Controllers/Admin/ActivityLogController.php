<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function __invoke(Request $request): View
    {
        $logs = ActivityLog::query()
            ->with(['user', 'subject'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = $request->string('search')->trim()->toString();
                $query->where('action', 'like', "%{$term}%");
            })
            ->when($request->filled('action'), fn ($query) => $query->where('action', $request->string('action')->toString()))
            ->when($request->filled('user'), function ($query) use ($request) {
                $term = $request->string('user')->trim()->toString();
                $query->whereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$term}%"));
            })
            ->when($request->filled('from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('to')))
            ->latest()
            ->paginate(40)
            ->withQueryString();

        return view('admin.activity.index', [
            'logs' => $logs,
            'actionOptions' => $this->actionOptions(),
        ]);
    }

    protected function actionOptions(): array
    {
        return [
            'Auth' => ['auth.registered', 'auth.login', 'auth.logout'],
            'Course' => ['course.created', 'course.updated', 'course.submitted', 'course.approved', 'course.rejected'],
            'Curriculum' => ['section.created', 'lesson.created', 'lesson.deleted'],
            'Question bank' => ['bank_question.created', 'bank_question.updated', 'bank_question.deleted', 'bank_question.toggled'],
            'Subject' => ['subject.created', 'subject.deleted'],
            'Exam' => ['exam.created', 'exam.updated', 'exam.questions_added', 'exam.question_removed', 'exam.status_toggled', 'exam.deleted', 'exam_attempt.graded', 'exam_attempt.started', 'exam_attempt.submitted'],
            'Live Class' => ['live_class.scheduled', 'live_class.rescheduled', 'live_class.deleted', 'oauth.connected', 'oauth.disconnected'],
            'Instructor' => ['instructor_profile.updated', 'instructor.approved', 'instructor.rejected', 'instructor.created_by_admin'],
            'Payout' => ['payout.requested', 'payout.paid', 'payout.rejected'],
            'Cart & Wishlist' => ['cart.item_added', 'cart.item_removed', 'wishlist.item_added', 'wishlist.item_removed'],
            'Order' => ['order.created', 'order.paid', 'order.failed', 'coupon.applied'],
            'Review' => ['review.saved', 'review.deleted', 'review.approved', 'review.rejected', 'review.deleted_by_admin'],
            'Learning' => ['lesson.completed', 'quiz.submitted', 'question.asked', 'question.answered'],
            'Certificate' => ['certificate.issued', 'certificate.downloaded'],
            'Catalog' => ['category.created', 'category.updated', 'category.deleted', 'coupon.created', 'coupon.deleted'],
            'Content' => ['testimonial.created', 'testimonial.updated', 'testimonial.deleted', 'contact_message.read', 'contact_message.deleted', 'page.created', 'page.updated'],
            'Users & Settings' => ['user.activated', 'user.deactivated', 'settings.updated'],
        ];
    }
}
