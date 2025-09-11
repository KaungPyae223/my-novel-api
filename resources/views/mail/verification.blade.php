<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Your Email</title>
</head>
<body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table align="center" width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 40px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <tr>
            <td style="background-color: #4CAF50; padding: 20px; text-align: center; color: #ffffff;">
                <h1 style="margin: 0; font-size: 24px;">My Novel</h1>
            </td>
        </tr>
        <tr>
            <td style="padding: 30px; color: #333333;">
                <h2 style="font-size: 20px; margin-top: 0;">Hello {{ $user->full_name }},</h2>
                <p style="font-size: 16px; line-height: 1.5;">
                    Thank you for registering! Please click the button below to verify your email and activate your account:
                </p>
                <p style="text-align: center; margin: 30px 0;">
                    <a href="{{ $verificationUrl }}" style="background-color: #4CAF50; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 5px; font-size: 16px; display: inline-block;">
                        Verify Email
                    </a>
                </p>
                <p style="font-size: 14px; color: #888888;">
                    If you did not create an account, please ignore this email.
                </p>
            </td>
        </tr>
        <tr>
            <td style="background-color: #f4f4f4; text-align: center; padding: 15px; font-size: 12px; color: #999999;">
                &copy; 2025 My Novel. All rights reserved.
            </td>
        </tr>
    </table>
</body>
</html>
