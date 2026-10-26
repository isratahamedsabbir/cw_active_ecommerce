<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="x-apple-disable-message-reformatting" />
  <title>Order Confirmed – Agrony</title>

  <!--[if mso]>
  <noscript>
    <xml>
      <o:OfficeDocumentSettings>
        <o:AllowPNG/>
        <o:PixelsPerInch>96</o:PixelsPerInch>
      </o:OfficeDocumentSettings>
    </xml>
  </noscript>
  <![endif]-->

  <style type="text/css">
    /* ===== CLIENT RESETS ===== */
    body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
    table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
    img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
    body { margin: 0 !important; padding: 0 !important; width: 100% !important; }

    /* ===== GENERAL ===== */
    body {
      background-color: #f2f5f2;
      font-family: Georgia, 'Times New Roman', Times, serif;
      color: #1a2e1a;
    }

    /* ===== MOBILE ===== */
    @media screen and (max-width: 640px) {
      .email-wrapper { width: 100% !important; }
      .email-body { padding: 24px 20px !important; }
      .header-cell { padding: 28px 20px 22px !important; }
      .banner-cell { padding: 16px 20px !important; }
      .footer-cell { padding: 18px 20px !important; }
      .order-label { display: block !important; width: 100% !important; }
      .order-value { display: block !important; width: 100% !important; padding-top: 2px !important; }
      .step-text { font-size: 14px !important; }
      .mobile-block { display: block !important; width: 100% !important; }
      .mobile-stack { display: block !important; }
      .fb-btn { display: block !important; margin-top: 10px !important; }
    }
  </style>
</head>

<body style="margin:0;padding:0;background-color:#f2f5f2;font-family:Georgia,'Times New Roman',Times,serif;color:#1a2e1a;">

<!-- Outer wrapper -->
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color:#f2f5f2;">
  <tr>
    <td align="center" style="padding:40px 16px;">

      <!-- Email card -->
      <table role="presentation" class="email-wrapper" cellspacing="0" cellpadding="0" border="0" width="620" style="max-width:620px;width:100%;background-color:#ffffff;border-radius:4px;overflow:hidden;box-shadow:0 2px 16px rgba(4,91,48,0.10);">

        <!-- ===== HEADER ===== -->
        <tr>
          <td class="header-cell" align="center" bgcolor="#045b30" style="background-color:#045b30;padding:36px 40px 28px;">
            <img src="{{ uploaded_asset(get_setting('system_logo_black')) }}" alt="Agrony" height="52" style="height:52px;width:auto;display:block;border:0;" />
          </td>
        </tr>

        <!-- ===== CONFIRMATION BANNER ===== -->
        <tr>
          <td class="banner-cell" bgcolor="#eaf5ee" style="background-color:#eaf5ee;border-left:4px solid #045b30;padding:20px 40px;">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
              <tr>
                <!-- Check icon -->
                <td width="42" valign="middle" style="padding-right:14px;">
                  <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="42" height="42">
                    <tr>
                      <td align="center" valign="middle" bgcolor="#045b30" width="42" height="42" style="background-color:#045b30;border-radius:50%;width:42px;height:42px;text-align:center;vertical-align:middle;font-family:Arial,Helvetica,sans-serif;font-size:20px;font-weight:bold;color:#ffffff;line-height:42px;">
                        <!--[if mso]><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" style="height:42px;v-text-anchor:middle;width:42px;" arcsize="50%" stroke="f" fillcolor="#045b30"><w:anchorlock/><center style="color:#ffffff;font-family:Arial,sans-serif;font-size:20px;font-weight:bold;">&#10003;</center></v:roundrect><![endif]-->
                        <!--[if !mso]><!--><span style="color:#ffffff;font-family:Arial,Helvetica,sans-serif;font-size:20px;font-weight:bold;line-height:42px;">&#10003;</span><!--<![endif]-->
                      </td>
                    </tr>
                  </table>
                </td>
                <!-- Confirm text -->
                <td valign="middle">
                  <p style="margin:0;font-family:Georgia,'Times New Roman',Times,serif;font-size:18px;font-weight:bold;color:#045b30;line-height:1.3;">Your Order is Confirmed!</p>
                  <p style="margin:3px 0 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#4a6a4a;">We've received your order and it's being processed.</p>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- ===== BODY ===== -->
        <tr>
          <td class="email-body" style="padding:32px 40px;">

            <!-- Greeting -->
            <p style="margin:0 0 10px;font-family:Georgia,'Times New Roman',Times,serif;font-size:16px;color:#1a2e1a;">Hi {{ json_decode($order->customer_address)->name }},</p>
            <p style="margin:0 0 28px;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.7;color:#3d4d3d;">
              Thank you for shopping with <strong style="color:#1a2e1a;">Agrony.com</strong>. Your order has been successfully placed and is now being processed. We're excited to deliver fresh products right to your doorstep.
            </p>

            <!-- ===== ORDER DETAILS ===== -->
            <p style="margin:0 0 12px;font-family:Arial,Helvetica,sans-serif;font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:1.5px;color:#045b30;">Order Details</p>

            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color:#f8fcf9;border:1px solid #c8e6d4;border-radius:4px;margin-bottom:28px;border-collapse:collapse;">

              <!-- Row: Order Code -->
              <tr>
                <td class="order-label" width="140" valign="top" style="padding:13px 18px;border-bottom:1px solid #e0f0e6;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#6a8a6a;vertical-align:top;">Order Code</td>
                <td class="order-value" valign="top" style="padding:13px 18px;border-bottom:1px solid #e0f0e6;font-family:Arial,Helvetica,sans-serif;font-size:13px;font-weight:600;color:#1a2e1a;vertical-align:top;">{{ $order->code }}</td>
              </tr>

              <!-- Row: Order Date -->
              <tr>
                <td class="order-label" width="140" valign="top" style="padding:13px 18px;border-bottom:1px solid #e0f0e6;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#6a8a6a;vertical-align:top;">Order Date</td>
                <td class="order-value" valign="top" style="padding:13px 18px;border-bottom:1px solid #e0f0e6;font-family:Arial,Helvetica,sans-serif;font-size:13px;font-weight:600;color:#1a2e1a;vertical-align:top;">{{ $order->created_at->format('F j, Y') }}</td>
              </tr>

              <!-- Row: Payment Method -->
              <tr>
                <td class="order-label" width="140" valign="top" style="padding:13px 18px;border-bottom:1px solid #e0f0e6;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#6a8a6a;vertical-align:top;">Payment Method</td>
                <td class="order-value" valign="top" style="padding:13px 18px;border-bottom:1px solid #e0f0e6;vertical-align:top;">
                  <!--[if mso]>
                  <span style="font-family:Arial,Helvetica,sans-serif;font-size:11px;font-weight:bold;color:#ffffff;background-color:#045b30;padding:2px 9px;border-radius:20px;letter-spacing:0.5px;">Cash on Delivery</span>
                  <![endif]-->
                  <!--[if !mso]><!-->
                  <span style="display:inline-block;background-color:#045b30;color:#ffffff;font-family:Arial,Helvetica,sans-serif;font-size:11px;font-weight:bold;padding:3px 10px;border-radius:20px;letter-spacing:0.5px;">Cash on Delivery</span>
                  <!--<![endif]-->
                </td>
              </tr>

              <!-- Row: Delivery Address -->
              <tr>
                <td class="order-label" width="140" valign="top" style="padding:13px 18px;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#6a8a6a;vertical-align:top;">Delivery Address</td>
                <td class="order-value" valign="top" style="padding:13px 18px;font-family:Arial,Helvetica,sans-serif;font-size:13px;font-weight:600;color:#1a2e1a;vertical-align:top;">{{ json_decode($order->customer_address)->address }}</td>
              </tr>

            </table>

            <!-- ===== WHAT HAPPENS NEXT ===== -->
            <p style="margin:0 0 14px;font-family:Arial,Helvetica,sans-serif;font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:1.5px;color:#045b30;">What Happens Next</p>

            <!-- Step 1 -->
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom:14px;">
              <tr>
                <td width="26" valign="top" style="padding-right:14px;">
                  <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="26" height="26">
                    <tr>
                      <td align="center" valign="middle" width="26" height="26" bgcolor="#045b30" style="background-color:#045b30;border-radius:50%;font-family:Arial,Helvetica,sans-serif;font-size:12px;font-weight:bold;color:#ffffff;width:26px;height:26px;text-align:center;">1</td>
                    </tr>
                  </table>
                </td>
                <td valign="middle" style="font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.6;color:#3d4d3d;">We are preparing your order for dispatch.</td>
              </tr>
            </table>

            <!-- Step 2 -->
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom:14px;">
              <tr>
                <td width="26" valign="top" style="padding-right:14px;">
                  <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="26" height="26">
                    <tr>
                      <td align="center" valign="middle" width="26" height="26" bgcolor="#045b30" style="background-color:#045b30;border-radius:50%;font-family:Arial,Helvetica,sans-serif;font-size:12px;font-weight:bold;color:#ffffff;width:26px;height:26px;text-align:center;">2</td>
                    </tr>
                  </table>
                </td>
                <td valign="middle" style="font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.6;color:#3d4d3d;">Once shipped, you will receive a confirmation email with delivery updates.</td>
              </tr>
            </table>

            <!-- Step 3 -->
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom:28px;">
              <tr>
                <td width="26" valign="top" style="padding-right:14px;">
                  <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="26" height="26">
                    <tr>
                      <td align="center" valign="middle" width="26" height="26" bgcolor="#045b30" style="background-color:#045b30;border-radius:50%;font-family:Arial,Helvetica,sans-serif;font-size:12px;font-weight:bold;color:#ffffff;width:26px;height:26px;text-align:center;">3</td>
                    </tr>
                  </table>
                </td>
                <td valign="middle" style="font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.6;color:#3d4d3d;">Pay the total amount in cash when your order arrives at your door.</td>
              </tr>
            </table>

            <!-- ===== IMPORTANT NOTE ===== -->
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom:28px;">
              <tr>
                <td style="background-color:#fff8e6;border:1px solid #f0d890;border-left:4px solid #d4a017;border-radius:4px;padding:16px 18px;">
                  <p style="margin:0 0 6px;font-family:Arial,Helvetica,sans-serif;font-size:12px;font-weight:bold;color:#7a5c00;text-transform:uppercase;letter-spacing:1px;">&#9888; Important Note</p>
                  <p style="margin:0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#5a4400;line-height:1.6;">Please keep the <strong>exact amount ready</strong> at the time of delivery to ensure a smooth and quick handover.</p>
                </td>
              </tr>
            </table>

            <!-- ===== FACEBOOK ===== -->
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom:28px;">
              <tr>
                <td>
                  <p style="margin:0 0 10px;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#6a8a6a;display:inline-block;">Stay connected with us:</p>
                  <br />
                  <!--[if mso]><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="https://www.facebook.com/agronybd/" style="height:38px;v-text-anchor:middle;width:200px;" arcsize="7%" stroke="f" fillcolor="#1877f2"><w:anchorlock/><center style="color:#ffffff;font-family:Arial,sans-serif;font-size:13px;font-weight:bold;">f &nbsp; Agrony on Facebook</center></v:roundrect><![endif]-->
                  <!--[if !mso]><!-->
                  <a href="https://www.facebook.com/agronybd/" class="fb-btn" style="display:inline-block;background-color:#1877f2;color:#ffffff;font-family:Arial,Helvetica,sans-serif;font-size:13px;font-weight:bold;padding:9px 18px;border-radius:4px;text-decoration:none;">
                    <span style="display:inline-block;width:18px;height:18px;background-color:#ffffff;border-radius:3px;color:#1877f2;font-family:Arial,Helvetica,sans-serif;font-size:13px;font-weight:900;line-height:18px;text-align:center;vertical-align:middle;margin-right:7px;">f</span><span style="vertical-align:middle;">Agrony on Facebook</span>
                  </a>
                  <!--<![endif]-->
                </td>
              </tr>
            </table>

            <!-- ===== SUPPORT ===== -->
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom:24px;">
              <tr>
                <td style="border-top:1px solid #e0f0e6;padding-top:20px;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#3d4d3d;line-height:1.7;">
                  If you have any questions, feel free to contact our support team at<br />
                  <strong><a href="mailto:info@agrony.com" style="color:#045b30;text-decoration:none;">info@agrony.com</a> / <a href="tel:+8801898879990" style="color:#045b30;text-decoration:none;">01898879990</a></strong>
                </td>
              </tr>
            </table>

            <!-- ===== SIGN-OFF ===== -->
            <p style="margin:0;font-family:Georgia,'Times New Roman',Times,serif;font-size:14px;color:#1a2e1a;line-height:1.8;">
              Thank you for choosing <strong style="color:#045b30;">Agrony.com</strong>.<br /><br />
              Best regards,<br />
              <strong style="color:#045b30;">Team Agrony</strong>
            </p>

          </td>
        </tr>

        <!-- ===== FOOTER ===== -->
        <tr>
          <td class="footer-cell" align="center" bgcolor="#045b30" style="background-color:#045b30;padding:22px 40px;">
            <a href="https://www.agrony.com" style="color:#7ed957;font-family:Arial,Helvetica,sans-serif;font-size:13px;text-decoration:none;font-weight:bold;letter-spacing:0.5px;">www.agrony.com</a>
            <p style="margin:8px 0 0;font-family:Arial,Helvetica,sans-serif;font-size:11px;color:rgba(255,255,255,0.5);line-height:1.6;">
              &copy; 2025 Agrony.com &middot; All rights reserved.<br />
              You're receiving this email because you placed an order on Agrony.
            </p>
          </td>
        </tr>

      </table>
      <!-- /Email card -->

    </td>
  </tr>
</table>
<!-- /Outer wrapper -->

</body>
</html>