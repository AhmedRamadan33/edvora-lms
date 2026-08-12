<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Services\CertificateService;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CertificateController extends Controller
{
    public function index(): View
    {
        $certificates = Certificate::query()
            ->where('user_id', auth()->id())
            ->with('course.translations')
            ->latest()
            ->get();

        return view('student.certificates', compact('certificates'));
    }

    public function download(Certificate $certificate, CertificateService $service)
    {
        abort_unless($certificate->user_id === auth()->id() || auth()->user()->hasRole('admin'), 403);

        return $service->download($certificate);
    }
}
