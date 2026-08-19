<?php

namespace App\Http\Controllers;

use App\Services\CertificateService;
use Illuminate\View\View;

class CertificateVerificationController extends Controller
{
    public function show(string $code, CertificateService $certificates): View
    {
        return view('certificates.verify', [
            'code' => $code,
            'certificate' => $certificates->findByCode($code),
        ]);
    }
}
