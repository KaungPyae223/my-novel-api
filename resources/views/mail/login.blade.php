<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>New sign-in to your account</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>

<body style="margin:0; padding:0; font-family: Arial, Helvetica, sans-serif; background-color: #f4f4f4;">
  <table align="center" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px; margin:40px auto; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
    <!-- Header -->
    <tr>
      <td style="background:#1447e6; padding:20px; text-align:center; color:#fff;">
        <h1 style="margin:0; font-size:22px;">My Novel</h1>
        <div style="font-size:13px; opacity:0.9; margin-top:6px;">Security alert — new sign-in</div>
      </td>
    </tr>

    <!-- Body -->
    <tr>
      <td style="padding:28px; color:#333;">
        <h2 style="font-size:18px; margin:0 0 10px 0;">Hello {{ $user->full_name }},</h2>

        <p style="font-size:15px; line-height:1.6; margin:0 0 18px 0;">
          We noticed a sign-in to your account. If this was you, you can safely ignore this email. If you don't recognize this activity, please secure your account immediately.
        </p>

        <!-- Info card -->
        <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #eef2ff; border-radius:8px; padding:14px; background:#fbfdff;">
          <tr>
            <td style="padding:8px 12px; font-size:14px; color:#222;">
              <strong>Device:</strong> {{ $device_info }}<br>
              <strong>IP address:</strong> {{ $ip_address }}<br>
              @php($currentTime = now()->format('Y-m-d H:i:s'))
              <strong>Time:</strong> {{ $currentTime }}
            </td>
          </tr>
        </table>

        <!-- Buttons -->
        <table width="100%" cellpadding="0" cellspacing="0" style="margin:22px 0;">
          <tr>
            <td style="text-align:center;">
              <!-- Primary safe action (if it's not you) -->
              <a href="https://mynovel.com" style="display:inline-block; padding:12px 22px; border-radius:6px; background:#e53e3e; color:#fff; text-decoration:none; font-weight:600; font-size:15px; margin-right:8px;">
                It is not me
              </a>


            </td>
          </tr>
        </table>

        <p style="font-size:13px; color:#666; line-height:1.5; margin:12px 0 0 0;">
          If you clicked the button by mistake, nothing else will happen. For your safety, these links expire after 24 hours.
        </p>

        <hr style="border:none; height:1px; background:#eef2ff; margin:22px 0;" />

        <p style="font-size:13px; color:#888; margin:0;">
          If you prefer, copy & paste this URL into your browser: <br>
          <a href="https://mynovel.com" style="color:#1447e6; word-break:break-all;"> Action Url </a>
        </p>
      </td>
    </tr>

    <!-- Footer -->
    <tr>
      <td style="background:#f4f4f4; text-align:center; padding:14px; font-size:12px; color:#999;">
        &copy; {{ date('Y') }} My Novel. All rights reserved. <br>
        If you didn't create an account or believe this is abusive, contact <a href="mailto:support@mynovel.example" style="color:#1447e6; text-decoration:none;">support</a>.
      </td>
    </tr>
  </table>
</body>

</html>