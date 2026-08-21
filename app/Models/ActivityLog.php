<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 'action', 'subject_type', 'subject_id', 'properties', 'ip_address',
    ];

    protected function casts(): array
    {
        return ['properties' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public static function record(string $action, ?Model $subject = null, array $properties = []): void
    {
        static::query()->create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'properties' => $properties,
            'ip_address' => request()->ip(),
        ]);
    }

    protected function prop(string $key, mixed $default = null): mixed
    {
        return $this->properties[$key] ?? $default;
    }

    public function description(): string
    {
        $actor = $this->user?->name ?? __('System');
        $p = fn (string $key, mixed $default = null) => $this->prop($key, $default);
        $dash = '—';

        return match ($this->action) {
            'auth.registered' => __(':user registered a new account.', ['user' => $actor]),
            'auth.login' => __(':user logged in.', ['user' => $actor]),
            'auth.logout' => __(':user logged out.', ['user' => $actor]),

            'course.created' => __(':user created the course ":title".', ['user' => $actor, 'title' => $p('title', $dash)]),
            'course.updated' => __(':user updated the course ":title".', ['user' => $actor, 'title' => $p('title', $dash)]),
            'course.submitted' => __(':user submitted the course ":title" for review.', ['user' => $actor, 'title' => $p('title', $dash)]),
            'course.approved' => __(':user approved the course ":title".', ['user' => $actor, 'title' => $p('title') ?? $this->subject?->translation()?->title ?? $dash]),
            'course.rejected' => __(':user rejected the course ":title". Reason: :reason', ['user' => $actor, 'title' => $p('title') ?? $this->subject?->translation()?->title ?? $dash, 'reason' => $p('reason', $dash)]),

            'section.created' => __(':user added the section ":title" to ":course".', ['user' => $actor, 'title' => $p('title', $dash), 'course' => $p('course', $dash)]),
            'lesson.created' => __(':user added the lesson ":title" to ":course".', ['user' => $actor, 'title' => $p('title', $dash), 'course' => $p('course', $dash)]),
            'lesson.deleted' => __(':user deleted the lesson ":title" from ":course".', ['user' => $actor, 'title' => $p('title', $dash), 'course' => $p('course', $dash)]),

            'bank_question.created' => __(':user added a question to the question bank for ":course".', ['user' => $actor, 'course' => $p('course', $dash)]),
            'bank_question.updated' => __(':user updated a question bank question in ":course".', ['user' => $actor, 'course' => $p('course', $dash)]),
            'bank_question.deleted' => __(':user deleted a question bank question from ":course".', ['user' => $actor, 'course' => $p('course', $dash)]),
            'bank_question.toggled' => $p('active')
                ? __(':user enabled a question in the question bank for ":course".', ['user' => $actor, 'course' => $p('course', $dash)])
                : __(':user disabled a question in the question bank for ":course".', ['user' => $actor, 'course' => $p('course', $dash)]),

            'subject.created' => __(':user created the subject ":name" in ":course".', ['user' => $actor, 'name' => $p('name', $dash), 'course' => $p('course', $dash)]),
            'subject.deleted' => __(':user deleted the subject ":name" from ":course".', ['user' => $actor, 'name' => $p('name', $dash), 'course' => $p('course', $dash)]),

            'exam.created' => __(':user created the exam ":title" for ":course".', ['user' => $actor, 'title' => $p('title', $dash), 'course' => $p('course', $dash)]),
            'exam.updated' => __(':user updated the exam ":title".', ['user' => $actor, 'title' => $p('title', $dash)]),
            'exam.questions_added' => __(':user added :count question(s) to the exam ":title".', ['user' => $actor, 'count' => $p('count', 0), 'title' => $p('title', $dash)]),
            'exam.question_removed' => __(':user removed a question from the exam ":title".', ['user' => $actor, 'title' => $p('title', $dash)]),
            'exam.status_toggled' => __(':user set the exam ":title" to :status.', ['user' => $actor, 'title' => $p('title', $dash), 'status' => $p('status', $dash)]),
            'exam.deleted' => __(':user deleted the exam ":title".', ['user' => $actor, 'title' => $p('title', $dash)]),
            'exam_attempt.graded' => __(":user graded :student's attempt on \":exam\".", ['user' => $actor, 'student' => $p('student', $dash), 'exam' => $p('exam', $dash)]),

            'live_class.scheduled' => __(':user scheduled the live class ":title" for ":course" via :provider.', ['user' => $actor, 'title' => $p('title', $dash), 'course' => $p('course', $dash), 'provider' => $p('provider', $dash)]),
            'live_class.rescheduled' => __(':user rescheduled the live class ":title".', ['user' => $actor, 'title' => $p('title', $dash)]),
            'live_class.deleted' => __(':user cancelled the live class ":title".', ['user' => $actor, 'title' => $p('title', $dash)]),
            'oauth.connected' => __(':user connected their :provider account.', ['user' => $actor, 'provider' => $p('provider', $dash)]),
            'oauth.disconnected' => __(':user disconnected their :provider account.', ['user' => $actor, 'provider' => $p('provider', $dash)]),

            'instructor_profile.updated' => __(':user updated their instructor profile.', ['user' => $actor]),
            'payout.requested' => __(':user requested a payout of :amount.', ['user' => $actor, 'amount' => $p('amount', $dash)]),
            'payout.paid' => __(':user marked a payout of :amount as paid.', ['user' => $actor, 'amount' => $p('amount') ?? $this->subject?->amount ?? $dash]),
            'payout.rejected' => __(':user rejected a payout of :amount. Reason: :reason', ['user' => $actor, 'amount' => $p('amount') ?? $this->subject?->amount ?? $dash, 'reason' => $p('reason', $dash)]),

            'instructor.approved' => __(':user approved the instructor application of :name.', ['user' => $actor, 'name' => $p('name') ?? $this->subject?->user?->name ?? $dash]),
            'instructor.rejected' => __(':user rejected the instructor application of :name. Reason: :reason', ['user' => $actor, 'name' => $p('name') ?? $this->subject?->user?->name ?? $dash, 'reason' => $p('reason', $dash)]),

            'cart.item_added' => __(':user added ":course" to their cart.', ['user' => $actor, 'course' => $p('course', $dash)]),
            'cart.item_removed' => __(':user removed ":course" from their cart.', ['user' => $actor, 'course' => $p('course', $dash)]),

            'order.created' => __(':user started checkout for order :number.', ['user' => $actor, 'number' => $p('number', $dash)]),
            'order.paid' => __('Order :number was paid via :provider.', ['number' => $p('number') ?? $this->subject?->number ?? $dash, 'provider' => $p('provider', $dash)]),
            'order.failed' => __('Payment failed for order :number via :provider.', ['number' => $p('number') ?? $this->subject?->number ?? $dash, 'provider' => $p('provider', $dash)]),
            'coupon.applied' => __(':user applied the coupon ":code".', ['user' => $actor, 'code' => $p('code', $dash)]),

            'wishlist.item_added' => __(':user added ":course" to their wishlist.', ['user' => $actor, 'course' => $p('course', $dash)]),
            'wishlist.item_removed' => __(':user removed ":course" from their wishlist.', ['user' => $actor, 'course' => $p('course', $dash)]),

            'review.saved' => __(':user reviewed ":course" (:rating/5).', ['user' => $actor, 'course' => $p('course', $dash), 'rating' => $p('rating', $dash)]),
            'review.deleted' => __(':user deleted their review on ":course".', ['user' => $actor, 'course' => $p('course', $dash)]),
            'review.approved' => __(':user approved a review on ":course".', ['user' => $actor, 'course' => $p('course', $dash)]),
            'review.rejected' => __(':user rejected a review on ":course".', ['user' => $actor, 'course' => $p('course', $dash)]),
            'review.deleted_by_admin' => __(':user deleted a review on ":course".', ['user' => $actor, 'course' => $p('course', $dash)]),

            'lesson.completed' => __(':user completed the lesson ":title" in ":course".', ['user' => $actor, 'title' => $p('title', $dash), 'course' => $p('course', $dash)]),
            'quiz.submitted' => __(':user submitted the quiz ":title" (:score%).', ['user' => $actor, 'title' => $p('title', $dash), 'score' => $p('score', $dash)]),
            'question.asked' => __(':user asked a question on ":course".', ['user' => $actor, 'course' => $p('course', $dash)]),
            'question.answered' => __(':user answered a question on ":course".', ['user' => $actor, 'course' => $p('course', $dash)]),

            'exam_attempt.started' => __(':user started an attempt on the exam ":exam".', ['user' => $actor, 'exam' => $p('exam', $dash)]),
            'exam_attempt.submitted' => __(':user submitted their attempt on ":exam" (:score%).', ['user' => $actor, 'exam' => $p('exam', $dash), 'score' => $p('score', $dash)]),

            'certificate.issued' => __(':user earned a certificate for ":course".', ['user' => $actor, 'course' => $p('course', $dash)]),
            'certificate.downloaded' => __(':user downloaded their certificate for ":course".', ['user' => $actor, 'course' => $p('course', $dash)]),

            'category.created' => __(':user created the category ":name".', ['user' => $actor, 'name' => $p('name', $dash)]),
            'category.updated' => __(':user updated the category ":name".', ['user' => $actor, 'name' => $p('name', $dash)]),
            'category.deleted' => __(':user deleted the category ":name".', ['user' => $actor, 'name' => $p('name', $dash)]),

            'coupon.created' => __(':user created the coupon ":code".', ['user' => $actor, 'code' => $p('code', $dash)]),
            'coupon.deleted' => __(':user deleted the coupon ":code".', ['user' => $actor, 'code' => $p('code', $dash)]),

            'testimonial.created' => __(':user added a testimonial from ":name".', ['user' => $actor, 'name' => $p('name', $dash)]),
            'testimonial.updated' => __(':user updated the testimonial from ":name".', ['user' => $actor, 'name' => $p('name', $dash)]),
            'testimonial.deleted' => __(':user deleted the testimonial from ":name".', ['user' => $actor, 'name' => $p('name', $dash)]),

            'contact_message.read' => __(':user marked the message from ":name" as read.', ['user' => $actor, 'name' => $p('name', $dash)]),
            'contact_message.deleted' => __(':user deleted the message from ":name".', ['user' => $actor, 'name' => $p('name', $dash)]),

            'page.created' => __(':user created the page ":title".', ['user' => $actor, 'title' => $p('title', $dash)]),
            'page.updated' => __(':user updated the page ":title".', ['user' => $actor, 'title' => $p('title', $dash)]),

            'user.activated' => __(':user activated the account of ":name".', ['user' => $actor, 'name' => $p('name', $dash)]),
            'user.deactivated' => __(':user deactivated the account of ":name".', ['user' => $actor, 'name' => $p('name', $dash)]),

            'settings.updated' => __(':user updated the platform settings.', ['user' => $actor]),

            default => __(':user performed :action.', ['user' => $actor, 'action' => $this->action]),
        };
    }
}
