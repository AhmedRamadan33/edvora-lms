<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CourseReviewController;
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
use App\Http\Controllers\Commerce\CartController;
use App\Http\Controllers\Commerce\CheckoutController;
use App\Http\Controllers\Commerce\WebhookController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Instructor\CourseController as InstructorCourseController;
use App\Http\Controllers\Instructor\CurriculumController;
use App\Http\Controllers\Instructor\DashboardController as InstructorDashboardController;
use App\Http\Controllers\Instructor\EarningController;
use App\Http\Controllers\Instructor\OrderController as InstructorOrderController;
use App\Http\Controllers\Instructor\ProfileController as InstructorProfileController;
use App\Http\Controllers\Instructor\PublicProfileController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student\CertificateController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\LearnController;
use App\Http\Controllers\Student\ReviewController;
use App\Http\Controllers\Student\WishlistController;
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

Route::post('/webhooks/stripe', [WebhookController::class, 'stripe'])->name('webhooks.stripe');
Route::post('/webhooks/paymob', [WebhookController::class, 'paymob'])->name('webhooks.paymob');
Route::get('/checkout/paymob/return', [CheckoutController::class, 'paymobReturn'])->name('checkout.paymob.return');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/{course}', [CartController::class, 'store'])->name('cart.store');
    Route::delete('/cart/{course}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout/coupon', [CheckoutController::class, 'applyCoupon'])->name('checkout.coupon');
    Route::post('/checkout/pay', [CheckoutController::class, 'pay'])->name('checkout.pay');
    Route::get('/checkout/{order}/success', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/{order}/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');
    Route::get('/checkout/{order}/paymob-demo', [CheckoutController::class, 'paymobDemo'])->name('checkout.paymob.demo');

    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{course}', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{course}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');

    Route::post('/courses/{course}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

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
        Route::post('/courses/{course}/lessons/{lesson}/ready', [CurriculumController::class, 'markVideoReady'])->name('lessons.ready');
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
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/users/{user}/toggle', [AdminUserController::class, 'toggle'])->name('users.toggle');
        Route::get('/students', [StudentController::class, 'index'])->name('students.index');
        Route::get('/instructors', [InstructorApprovalController::class, 'index'])->name('instructors.index');
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

require __DIR__.'/auth.php';
