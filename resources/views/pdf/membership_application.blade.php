<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Membership application</title>
    <style>
        @page { margin: 34px 42px; }
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #1f2937; font-size: 11px; line-height: 1.5; }
        .header { border-bottom: 2px solid #C9A961; padding-bottom: 12px; margin-bottom: 18px; }
        .club { font-size: 20px; letter-spacing: 1px; color: #1A2332; margin: 0; }
        .subtitle { color: #7A1F2E; font-size: 12px; margin: 4px 0 0; text-transform: uppercase; letter-spacing: 1.5px; }
        .office { color: #4b5563; font-size: 10px; margin-top: 6px; }
        h2 { font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #7A1F2E; border-bottom: 1px solid #E0D5B8; padding-bottom: 4px; margin: 18px 0 10px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 6px 0; vertical-align: top; }
        .label { width: 30%; color: #4b5563; font-size: 10px; text-transform: uppercase; letter-spacing: .5px; }
        .line { border-bottom: 1px solid #9ca3af; height: 20px; }
        .note { font-size: 10px; color: #6b7280; }
        .box { border: 1px solid #9ca3af; padding: 10px; min-height: 70px; }
        .declaration { font-size: 10px; color: #374151; margin-top: 8px; }
        .signature { margin-top: 26px; }
        .footer { position: fixed; bottom: -18px; left: 0; right: 0; font-size: 9px; color: #6b7280; text-align: center; border-top: 1px solid #E5E7EB; padding-top: 6px; }
        .check { font-family: DejaVu Sans, sans-serif; }
    </style>
</head>
<body>
    <div class="header">
        <p class="club">{{ $club['name'] }}</p>
        <p class="subtitle">Membership application form</p>
        <p class="office">
            {{ $club['address'] }}<br>
            Phone: {{ $club['phone'] }} &nbsp;·&nbsp; Email: {{ $club['email'] }}
        </p>
    </div>

    <p class="note">Please complete this form in block capitals and bring it to the office together with an accepted original ID.</p>

    <h2>Applicant details</h2>
    <table>
        <tr>
            <td class="label">Full name</td>
            <td class="line">{{ $user->name }}</td>
        </tr>
        <tr>
            <td class="label">Email address</td>
            <td class="line">{{ $user->email }}</td>
        </tr>
        <tr>
            <td class="label">Phone number</td>
            <td class="line">&nbsp;</td>
        </tr>
        <tr>
            <td class="label">Address</td>
            <td class="line">&nbsp;</td>
        </tr>
        <tr>
            <td class="label">&nbsp;</td>
            <td class="line">&nbsp;</td>
        </tr>
        <tr>
            <td class="label">Town / city</td>
            <td class="line">&nbsp;</td>
        </tr>
        <tr>
            <td class="label">Postcode</td>
            <td class="line">&nbsp;</td>
        </tr>
    </table>

    <h2>Professional details</h2>
    <table>
        <tr>
            <td class="label">Profession</td>
            <td class="line">&nbsp;</td>
        </tr>
        <tr>
            <td class="label">Curriculum vitae</td>
            <td class="note">Please attach a copy of your CV to this form.</td>
        </tr>
    </table>

    <h2>Identification</h2>
    <p class="note">Accepted ID (tick one):</p>
    <table>
        <tr>
            <td class="check" style="width: 4%;">☐</td>
            <td>National ID card</td>
            <td class="check" style="width: 4%;">☐</td>
            <td>Driver's licence</td>
            <td class="check" style="width: 4%;">☐</td>
            <td>International passport</td>
        </tr>
    </table>

    <h2>Declaration</h2>
    <div class="box">
        <p class="declaration" style="margin: 0;">
            I confirm that the information provided in this application and any attached documents is true and accurate.
            I agree to abide by the rules and constitution of {{ $club['name'] }}. I understand that the club may verify
            my identity documents and may refuse or withdraw membership in accordance with its rules.
        </p>
    </div>

    <table class="signature">
        <tr>
            <td class="label">Signature</td>
            <td class="line">&nbsp;</td>
            <td class="label" style="width: 18%; text-align: right; padding-right: 8px;">Date</td>
            <td class="line" style="width: 22%;">&nbsp;</td>
        </tr>
    </table>

    <div class="footer">
        {{ $club['name'] }} · {{ $club['address'] }} · {{ $club['phone'] }} · {{ $club['email'] }}
    </div>
</body>
</html>
