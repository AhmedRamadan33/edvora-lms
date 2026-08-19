<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 0; }
    html, body { margin: 0; padding: 0; }
    body {
        font-family: Helvetica, sans-serif;
        color: #0c1222;
    }
    .frame {
        position: absolute;
        top: 12mm; left: 12mm; right: 12mm; bottom: 12mm;
        border: 2px solid #c9a227;
    }
    .frame-inner {
        position: absolute;
        top: 4mm; left: 4mm; right: 4mm; bottom: 4mm;
        border: 1px solid #0f8f85;
    }
    .content {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        width: 100%;
    }
    .content-inner {
        padding: 34mm 26mm;
        text-align: center;
    }
    .brand-name {
        font-family: Times, serif;
        font-size: 19px;
        font-weight: bold;
        color: #0c1222;
        letter-spacing: 3px;
        text-transform: uppercase;
    }
    .kicker {
        font-size: 11px;
        letter-spacing: 4px;
        text-transform: uppercase;
        color: #c9a227;
        margin-top: 18px;
    }
    .title {
        font-family: Times, serif;
        font-size: 36px;
        color: #0c1222;
        margin-top: 6px;
        letter-spacing: 1px;
    }
    .divider {
        width: 90px;
        height: 3px;
        background: #c9a227;
        margin: 14px auto;
    }
    .presented-to {
        font-size: 11px;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: #5b657a;
        margin-top: 14px;
    }
    .student-name {
        font-family: "DejaVu Sans", Times, serif;
        font-size: 26px;
        font-weight: bold;
        color: #0f8f85;
        margin-top: 8px;
        padding-bottom: 8px;
        border-bottom: 1px solid #d9dee6;
        display: inline-block;
        min-width: 360px;
    }
    .completion-text {
        font-size: 12px;
        color: #5b657a;
        margin-top: 16px;
        line-height: 1.6;
    }
    .course-title {
        font-family: Times, serif;
        font-size: 20px;
        font-weight: bold;
        color: #0c1222;
        margin-top: 6px;
    }
    .signatures { margin-top: 28px; width: 100%; border-collapse: collapse; }
    .signatures td { width: 33%; text-align: center; vertical-align: bottom; }
    .sig-line { border-top: 1px solid #0c1222; margin: 0 26px; padding-top: 6px; }
    .sig-name { font-family: "DejaVu Sans", Helvetica, sans-serif; font-size: 12px; font-weight: bold; }
    .sig-role { font-size: 9px; color: #5b657a; text-transform: uppercase; letter-spacing: 1px; margin-top: 2px; }
    .seal {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        border: 3px double #c9a227;
        text-align: center;
        font-family: Times, serif;
        font-size: 9px;
        color: #c9a227;
        font-weight: bold;
        letter-spacing: 1px;
        margin: 0 auto;
        padding-top: 20px;
    }
    .footer {
        margin-top: 22px;
        font-size: 9px;
        color: #9aa3b2;
        letter-spacing: 0.3px;
    }
    .footer strong { color: #5b657a; }
</style>
</head>
<body>
    <div class="frame"></div>
    <div class="frame-inner"></div>
    <div class="content">
    <div class="content-inner">
        <div class="brand-name">{{ $platform }}</div>

        <div class="kicker">Certificate of Completion</div>
        <div class="title">Certificate of Achievement</div>
        <div class="divider"></div>

        <div class="presented-to">This certificate is proudly presented to</div>
        <div class="student-name">{{ $certificate->user->name }}</div>

        <div class="completion-text">for successfully completing all requirements of the course</div>
        <div class="course-title">{{ $courseTitle }}</div>

        <table class="signatures">
            <tr>
                <td>
                    <div class="sig-line">
                        <div class="sig-name">{{ $instructorName }}</div>
                        <div class="sig-role">Instructor</div>
                    </div>
                </td>
                <td>
                    <div class="seal">OFFICIAL<br>SEAL</div>
                </td>
                <td>
                    <div class="sig-line">
                        <div class="sig-name">{{ $platform }}</div>
                        <div class="sig-role">Platform</div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="footer">
            Issued on <strong>{{ $certificate->issued_at->format('F d, Y') }}</strong>
            &nbsp;&middot;&nbsp;
            Certificate ID <strong>{{ $certificate->code }}</strong>
            &nbsp;&middot;&nbsp;
            Verify at <strong>{{ $verificationUrl }}</strong>
        </div>
    </div>
    </div>
</body>
</html>
