<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><style>body{font-family: DejaVu Sans, sans-serif;text-align:center;padding:40px} .box{border:8px solid #0f172a;padding:40px}</style></head>
<body>
<div class="box">
<h1>{{ $platform }}</h1>
<p>Certificate of Completion</p>
<h2>{{ $certificate->user->name }}</h2>
<p>has successfully completed</p>
<h3>{{ $certificate->course->translation('en')?->title }}</h3>
<p>Code: {{ $certificate->code }}</p>
<p>{{ $certificate->issued_at->format('F d, Y') }}</p>
</div>
</body>
</html>
