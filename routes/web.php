<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CourseReviewController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\InstructorApprovalController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\PayoutController as AdminPayoutController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TestimonialController as AdminTestimonialController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\Catalog\CourseCatalogController;
use App\Http\Controllers\CertificateVerificationController;
use App\Http\Controllers\Commerce\CartController;
use App\Http\Controllers\Commerce\CheckoutController;
use App\Http\Controllers\Commerce\WebhookController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Instructor\BankQuestionController;
use App\Http\Controllers\Instructor\CourseController as InstructorCourseController;
use App\Http\Controllers\Instructor\CurriculumController;
use App\Http\Controllers\Instructor\DashboardController as InstructorDashboardController;
use App\Http\Controllers\Instructor\EarningController;
use App\Http\Controllers\Instructor\ExamAttemptController;
use App\Http\Controllers\Instructor\ExamController;
use App\Http\Controllers\Instructor\IntegrationController;
use App\Http\Controllers\Instructor\LiveClassController;
use App\Http\Controllers\Instructor\OrderController as InstructorOrderController;
use App\Http\Controllers\Instructor\ProfileController as InstructorProfileController;
use App\Http\Controllers\Instructor\PublicProfileController;
use App\Http\Controllers\Instructor\SubjectController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student\CertificateController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\ExamController as StudentExamController;
use App\Http\Controllers\Student\LearnController;
use App\Http\Controllers\Student\ReviewController;
use App\Http\Controllers\Student\WishlistController;
use App\Http\Controllers\VdoCipherWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/locale/{locale}', LocaleController::class)->name('locale.switch');
Route::get('/courses', [CourseCatalogController::class, 'index'])->name('courses.index');
Route::get('/courses/{slug}', [CourseCatalogController::class, 'show'])->name('courses.show');
Route::get('/instructors/{user}', [PublicProfileController::class, 'show'])->name('instructors.show');
Route::get('/pages/{slug}', [PageController::class, 'show'])->name('pages.show');
Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::get('/testimonials', [TestimonialController::class, 'index'])->name('testimonials.index');
Route::get('/certificates/verify/{code}', [CertificateVerificationController::class, 'show'])->name('certificates.verify');

Route::post('/webhooks/stripe', [WebhookController::class, 'stripe'])->name('webhooks.stripe');
Route::post('/webhooks/paymob', [WebhookController::class, 'paymob'])->name('webhooks.paymob');
Route::post('/webhooks/paytabs', [WebhookController::class, 'paytabs'])->name('webhooks.paytabs');
Route::post('/webhooks/paypal', [WebhookController::class, 'paypal'])->name('webhooks.paypal');
Route::post('/webhooks/fawry', [WebhookController::class, 'fawry'])->name('webhooks.fawry');
Route::post('/webhooks/vdocipher', [VdoCipherWebhookController::class, 'handle'])->name('webhooks.vdocipher');
Route::get('/checkout/paymob/return', [CheckoutController::class, 'paymobReturn'])->name('checkout.paymob.return');
Route::post('/checkout/paytabs/return', [CheckoutController::class, 'paytabsReturn'])->name('checkout.paytabs.return');
Route::get('/checkout/paypal/return', [CheckoutController::class, 'paypalReturn'])->name('checkout.paypal.return');
Route::get('/checkout/fawry/return', [CheckoutController::class, 'fawryReturn'])->name('checkout.fawry.return');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/{course}', [CartController::class, 'store'])->name('cart.store')->middleware('student_only');
    Route::delete('/cart/{course}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show')->middleware('student_only');
    Route::post('/checkout/coupon', [CheckoutController::class, 'applyCoupon'])->name('checkout.coupon')->middleware('student_only');
    Route::post('/checkout/pay', [CheckoutController::class, 'pay'])->name('checkout.pay')->middleware('student_only');
    Route::get('/checkout/{order}/success', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/{order}/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');
    Route::get('/checkout/{order}/paymob-demo', [CheckoutController::class, 'paymobDemo'])->name('checkout.paymob.demo');
    Route::get('/checkout/{order}/paytabs-demo', [CheckoutController::class, 'paytabsDemo'])->name('checkout.paytabs.demo');
    Route::get('/checkout/{order}/paypal-demo', [CheckoutController::class, 'paypalDemo'])->name('checkout.paypal.demo');
    Route::get('/checkout/{order}/fawry-demo', [CheckoutController::class, 'fawryDemo'])->name('checkout.fawry.demo');

    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{course}', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{course}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');

    Route::post('/courses/{course}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/courses/{course}/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/recent', [NotificationController::class, 'recent'])->name('recent');
        Route::get('/{notification}/read', [NotificationController::class, 'read'])->name('read');
        Route::post('/read-all', [NotificationController::class, 'readAll'])->name('read-all');
    });

    Route::prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', StudentDashboardController::class)->name('dashboard');
        Route::get('/certificates', [CertificateController::class, 'index'])->name('certificates.index');
        Route::get('/certificates/{certificate}/download', [CertificateController::class, 'download'])->name('certificates.download');
    });

    Route::prefix('learn')->name('learn.')->group(function () {
        Route::get('/{course}', [LearnController::class, 'show'])->name('course');
        Route::get('/{course}/lessons/{lesson}', [LearnController::class, 'lesson'])->name('lesson');
        Route::post('/{course}/lessons/{lesson}/complete', [LearnController::class, 'complete'])->name('complete');
        Route::post('/{course}/lessons/{lesson}/quiz', [LearnController::class, 'submitQuiz'])->name('quiz');
        Route::post('/{course}/lessons/{lesson}/ask', [LearnController::class, 'ask'])->name('ask');
        Route::post('/{course}/questions/{question}/answer', [LearnController::class, 'answer'])->name('answer');
    });

    Route::prefix('exams')->name('exams.')->group(function () {
        Route::get('/', [StudentExamController::class, 'index'])->name('index');
        Route::get('/{exam}', [StudentExamController::class, 'show'])->name('show');
        Route::post('/{exam}/start', [StudentExamController::class, 'start'])->name('start');
        Route::get('/{exam}/attempt', [StudentExamController::class, 'attempt'])->name('attempt');
        Route::post('/{exam}/attempt', [StudentExamController::class, 'submit'])->name('submit');
        Route::get('/{exam}/result', [StudentExamController::class, 'result'])->name('result');
    });

    Route::middleware('role:instructor,admin')->prefix('instructor')->name('instructor.')->group(function () {
        Route::get('/dashboard', InstructorDashboardController::class)->name('dashboard');
        Route::get('/profile', [InstructorProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [InstructorProfileController::class, 'update'])->name('profile.update');
        Route::get('/courses', [InstructorCourseController::class, 'index'])->name('courses.index');
        Route::get('/courses/create', [InstructorCourseController::class, 'create'])->name('courses.create');
        Route::post('/courses', [InstructorCourseController::class, 'store'])->name('courses.store');
        Route::get('/courses/{course}/edit', [InstructorCourseController::class, 'edit'])->name('courses.edit');
        Route::put('/courses/{course}', [InstructorCourseController::class, 'update'])->name('courses.update');
        Route::post('/courses/{course}/submit', [InstructorCourseController::class, 'submit'])->name('courses.submit');
        Route::post('/courses/{course}/sections', [CurriculumController::class, 'storeSection'])->name('sections.store');
        Route::post('/courses/{course}/sections/{section}/lessons', [CurriculumController::class, 'storeLesson'])->name('lessons.store');
        Route::delete('/courses/{course}/lessons/{lesson}', [CurriculumController::class, 'destroyLesson'])->name('lessons.destroy');
        Route::post('/courses/{course}/videos/credentials', [CurriculumController::class, 'videoUploadCredentials'])->name('videos.credentials');
        Route::post('/courses/{course}/lessons/{lesson}/check-status', [CurriculumController::class, 'checkVideoStatus'])->name('lessons.check-status');
        Route::post('/courses/{course}/live-classes', [LiveClassController::class, 'store'])->name('live-classes.store');
        Route::put('/live-classes/{liveClass}', [LiveClassController::class, 'update'])->name('live-classes.update');
        Route::delete('/live-classes/{liveClass}', [LiveClassController::class, 'destroy'])->name('live-classes.destroy');
        Route::get('/integrations', [IntegrationController::class, 'index'])->name('integrations.index');
        Route::get('/integrations/zoom/connect', [IntegrationController::class, 'redirectToZoom'])->name('integrations.zoom.connect');
        Route::get('/integrations/zoom/callback', [IntegrationController::class, 'zoomCallback'])->name('integrations.zoom.callback');
        Route::post('/integrations/zoom/disconnect', [IntegrationController::class, 'disconnectZoom'])->name('integrations.zoom.disconnect');
        Route::get('/integrations/google/connect', [IntegrationController::class, 'redirectToGoogle'])->name('integrations.google.connect');
        Route::get('/integrations/google/callback', [IntegrationController::class, 'googleCallback'])->name('integrations.google.callback');
        Route::post('/integrations/google/disconnect', [IntegrationController::class, 'disconnectGoogle'])->name('integrations.google.disconnect');
        Route::prefix('courses/{course}/question-bank')->name('question-bank.')->group(function () {
            Route::get('/', [BankQuestionController::class, 'index'])->name('index');
            Route::post('/', [BankQuestionController::class, 'store'])->name('store');
            Route::put('/{bankQuestion}', [BankQuestionController::class, 'update'])->name('update');
            Route::delete('/{bankQuestion}', [BankQuestionController::class, 'destroy'])->name('destroy');
            Route::post('/{bankQuestion}/toggle', [BankQuestionController::class, 'toggleActive'])->name('toggle');
        });
        Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
        Route::get('/subjects/create', [SubjectController::class, 'create'])->name('subjects.create');
        Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
        Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');
        Route::get('/exams', [ExamController::class, 'index'])->name('exams.index');
        Route::get('/exams/create', [ExamController::class, 'create'])->name('exams.create');
        Route::post('/exams', [ExamController::class, 'store'])->name('exams.store');
        Route::get('/exams/{exam}', [ExamController::class, 'show'])->name('exams.show');
        Route::get('/exams/{exam}/edit', [ExamController::class, 'edit'])->name('exams.edit');
        Route::put('/exams/{exam}', [ExamController::class, 'update'])->name('exams.update');
        Route::delete('/exams/{exam}', [ExamController::class, 'destroy'])->name('exams.destroy');
        Route::post('/exams/{exam}/status', [ExamController::class, 'toggleStatus'])->name('exams.status');
        Route::post('/exams/{exam}/questions', [ExamController::class, 'addQuestions'])->name('exams.questions.store');
        Route::delete('/exams/{exam}/questions/{examQuestion}', [ExamController::class, 'removeQuestion'])->name('exams.questions.destroy');
        Route::get('/exams/{exam}/attempts', [ExamAttemptController::class, 'index'])->name('exams.attempts.index');
        Route::get('/exams/{exam}/attempts/{attempt}', [ExamAttemptController::class, 'show'])->name('exams.attempts.show');
        Route::post('/exams/{exam}/attempts/{attempt}/grade', [ExamAttemptController::class, 'grade'])->name('exams.attempts.grade');
        Route::get('/earnings', [EarningController::class, 'index'])->name('earnings.index');
        Route::post('/earnings/payout', [EarningController::class, 'requestPayout'])->name('earnings.payout');
        Route::get('/orders', [InstructorOrderController::class, 'index'])->name('orders.index');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::resource('categories', AdminCategoryController::class)->except(['show']);
        Route::get('/courses', [CourseReviewController::class, 'index'])->name('courses.index');
        Route::get('/courses/{course}', [CourseReviewController::class, 'show'])->name('courses.show');
        Route::post('/courses/{course}/approve', [CourseReviewController::class, 'approve'])->name('courses.approve');
        Route::post('/courses/{course}/reject', [CourseReviewController::class, 'reject'])->name('courses.reject');
        Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::post('/reviews/{review}/approve', [AdminReviewController::class, 'approve'])->name('reviews.approve');
        Route::post('/reviews/{review}/reject', [AdminReviewController::class, 'reject'])->name('reviews.reject');
        Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/users/{user}/toggle', [AdminUserController::class, 'toggle'])->name('users.toggle');
        Route::get('/students', [StudentController::class, 'index'])->name('students.index');
        Route::get('/instructors', [InstructorApprovalController::class, 'index'])->name('instructors.index');
        Route::get('/instructors/create', [InstructorApprovalController::class, 'create'])->name('instructors.create');
        Route::post('/instructors', [InstructorApprovalController::class, 'store'])->name('instructors.store');
        Route::post('/instructors/{profile}/approve', [InstructorApprovalController::class, 'approve'])->name('instructors.approve');
        Route::post('/instructors/{profile}/reject', [InstructorApprovalController::class, 'reject'])->name('instructors.reject');
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/coupons', [CouponController::class, 'index'])->name('coupons.index');
        Route::post('/coupons', [CouponController::class, 'store'])->name('coupons.store');
        Route::delete('/coupons/{coupon}', [CouponController::class, 'destroy'])->name('coupons.destroy');
        Route::get('/payouts', [AdminPayoutController::class, 'index'])->name('payouts.index');
        Route::post('/payouts/{payout}/approve', [AdminPayoutController::class, 'approve'])->name('payouts.approve');
        Route::post('/payouts/{payout}/reject', [AdminPayoutController::class, 'reject'])->name('payouts.reject');
        Route::get('/reports', ReportController::class)->name('reports.index');
        Route::get('/pages', [AdminPageController::class, 'index'])->name('pages.index');
        Route::post('/pages', [AdminPageController::class, 'store'])->name('pages.store');
        Route::put('/pages/{page}', [AdminPageController::class, 'update'])->name('pages.update');
        Route::get('/contacts', [AdminContactMessageController::class, 'index'])->name('contacts.index');
        Route::get('/contacts/{contact}', [AdminContactMessageController::class, 'show'])->name('contacts.show');
        Route::delete('/contacts/{contact}', [AdminContactMessageController::class, 'destroy'])->name('contacts.destroy');
        Route::get('/testimonials', [AdminTestimonialController::class, 'index'])->name('testimonials.index');
        Route::post('/testimonials', [AdminTestimonialController::class, 'store'])->name('testimonials.store');
        Route::put('/testimonials/{testimonial}', [AdminTestimonialController::class, 'update'])->name('testimonials.update');
        Route::delete('/testimonials/{testimonial}', [AdminTestimonialController::class, 'destroy'])->name('testimonials.destroy');
        Route::get('/activity', ActivityLogController::class)->name('activity.index');
    });
});

require __DIR__ . '/auth.php';
