<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Order Confirmed – Agrony</title>
  <style>
    /* Reset */
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      background-color: #f2f5f2;
      font-family: 'Georgia', serif;
      color: #1a2e1a;
      -webkit-font-smoothing: antialiased;
    }

    /* Wrapper */
    .wrapper {
      max-width: 620px;
      margin: 40px auto;
      background: #ffffff;
      border-radius: 4px;
      overflow: hidden;
      box-shadow: 0 2px 16px rgba(4, 91, 48, 0.10);
    }

    /* Header */
    .header {
      background-color: #045b30;
      padding: 36px 40px 28px;
      text-align: center;
    }
    .header .logo-img {
      height: 52px;
      width: auto;
      display: block;
      margin: 0 auto;
    }
    .header .tagline {
      font-family: Arial, sans-serif;
      font-size: 12px;
      color: rgba(255,255,255,0.65);
      letter-spacing: 1.5px;
      text-transform: uppercase;
      margin-top: 10px;
    }

    /* Confirmation banner */
    .confirm-banner {
      background-color: #eaf5ee;
      border-left: 4px solid #045b30;
      padding: 20px 40px;
      display: flex;
      align-items: center;
      gap: 14px;
    }
    .confirm-icon {
      width: 42px;
      height: 42px;
      background: #045b30;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }
    .confirm-icon svg {
      display: block;
    }
    .confirm-text h2 {
      font-family: Georgia, serif;
      font-size: 18px;
      color: #045b30;
      font-weight: bold;
    }
    .confirm-text p {
      font-family: Arial, sans-serif;
      font-size: 13px;
      color: #4a6a4a;
      margin-top: 2px;
    }

    /* Body content */
    .body {
      padding: 32px 40px;
    }
    .greeting {
      font-family: Georgia, serif;
      font-size: 16px;
      color: #1a2e1a;
      margin-bottom: 10px;
    }
    .intro {
      font-family: Arial, sans-serif;
      font-size: 14px;
      line-height: 1.7;
      color: #3d4d3d;
      margin-bottom: 28px;
    }

    /* Section heading */
    .section-label {
      font-family: Arial, sans-serif;
      font-size: 11px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 1.5px;
      color: #045b30;
      margin-bottom: 12px;
    }

    /* Order details card */
    .order-card {
      background: #f8fcf9;
      border: 1px solid #c8e6d4;
      border-radius: 4px;
      overflow: hidden;
      margin-bottom: 28px;
    }
    .order-row {
      display: flex;
      padding: 13px 18px;
      border-bottom: 1px solid #e0f0e6;
      align-items: flex-start;
      gap: 12px;
    }
    .order-row:last-child {
      border-bottom: none;
    }
    .order-row .label {
      font-family: Arial, sans-serif;
      font-size: 12px;
      color: #6a8a6a;
      min-width: 130px;
      padding-top: 1px;
    }
    .order-row .value {
      font-family: Arial, sans-serif;
      font-size: 13px;
      color: #1a2e1a;
      font-weight: 600;
      flex: 1;
    }
    .cod-badge {
      display: inline-block;
      background: #045b30;
      color: #fff;
      font-size: 11px;
      font-weight: bold;
      padding: 2px 9px;
      border-radius: 20px;
      letter-spacing: 0.5px;
    }

    /* Steps */
    .steps {
      margin-bottom: 28px;
    }
    .step {
      display: flex;
      gap: 14px;
      align-items: flex-start;
      margin-bottom: 14px;
    }
    .step-num {
      width: 26px;
      height: 26px;
      background: #045b30;
      color: #fff;
      border-radius: 50%;
      font-family: Arial, sans-serif;
      font-size: 12px;
      font-weight: bold;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      margin-top: 1px;
    }
    .step-text {
      font-family: Arial, sans-serif;
      font-size: 14px;
      line-height: 1.6;
      color: #3d4d3d;
    }

    /* Important note */
    .note-box {
      background: #fff8e6;
      border: 1px solid #f0d890;
      border-left: 4px solid #d4a017;
      border-radius: 4px;
      padding: 16px 18px;
      margin-bottom: 28px;
    }
    .note-box .note-title {
      font-family: Arial, sans-serif;
      font-size: 12px;
      font-weight: bold;
      color: #7a5c00;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 6px;
    }
    .note-box p {
      font-family: Arial, sans-serif;
      font-size: 13px;
      color: #5a4400;
      line-height: 1.6;
    }

    /* Facebook link */
    .social-row {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 28px;
    }
    .fb-link {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: #1877f2;
      color: #fff;
      font-family: Arial, sans-serif;
      font-size: 13px;
      font-weight: bold;
      padding: 9px 18px;
      border-radius: 4px;
      text-decoration: none;
    }
    .social-label {
      font-family: Arial, sans-serif;
      font-size: 13px;
      color: #6a8a6a;
    }

    /* Support */
    .support {
      font-family: Arial, sans-serif;
      font-size: 13px;
      color: #3d4d3d;
      line-height: 1.7;
      border-top: 1px solid #e0f0e6;
      padding-top: 20px;
      margin-bottom: 24px;
    }
    .support a {
      color: #045b30;
      font-weight: bold;
      text-decoration: none;
    }

    /* Sign-off */
    .signoff {
      font-family: Georgia, serif;
      font-size: 14px;
      color: #1a2e1a;
      line-height: 1.8;
    }
    .signoff strong {
      color: #045b30;
    }

    /* Footer */
    .footer {
      background-color: #045b30;
      padding: 22px 40px;
      text-align: center;
    }
    .footer a {
      color: #7ed957;
      font-family: Arial, sans-serif;
      font-size: 13px;
      text-decoration: none;
      font-weight: bold;
      letter-spacing: 0.5px;
    }
    .footer p {
      font-family: Arial, sans-serif;
      font-size: 11px;
      color: rgba(255,255,255,0.5);
      margin-top: 8px;
      line-height: 1.6;
    }

    /* Mobile */
    @media (max-width: 640px) {
      .wrapper { margin: 0; border-radius: 0; }
      .header { padding: 28px 24px 22px; }
      .confirm-banner { padding: 16px 24px; flex-direction: row; }
      .body { padding: 24px 24px; }
      .footer { padding: 18px 24px; }
      .order-row { flex-direction: column; gap: 4px; }
      .order-row .label { min-width: unset; }
      .social-row { flex-direction: column; align-items: flex-start; }
    }
  </style>
</head>
<body>

<div class="wrapper">

  <!-- Header -->
  <div class="header">
    <img src="{{ uploaded_asset(get_setting('system_logo_black')) }}" alt="Agrony" class="logo-img" />
     </div>

  <!-- Confirmation Banner -->
  <div class="confirm-banner">
    <div class="confirm-icon">
      <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M4 11.5L8.5 16L18 7" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </div>
    <div class="confirm-text">
      <h2>Your Order is Confirmed!</h2>
      <p>We've received your order and it's being processed.</p>
    </div>
  </div>

  <!-- Body -->
  <div class="body">

    <p class="greeting">Hi {{ json_decode($order->customer_address)->name }},</p>
    <p class="intro">
      Thank you for shopping with <strong>Agrony.com</strong>. Your order has been successfully placed and is now being processed. We're excited to deliver fresh products right to your doorstep.
    </p>

    <!-- Order Details -->
    <div class="section-label">Order Details</div>
    <div class="order-card">
      <div class="order-row">
        <span class="label">Order Code</span>
        <span class="value">{{ $order->code }}</span>
      </div>
      <div class="order-row">
        <span class="label">Order Date</span>
        <span class="value">{{ $order->created_at->format('F j, Y') }}</span>
      </div>
      <div class="order-row">
        <span class="label">Payment Method</span>
        <span class="value"><span class="cod-badge">Cash on Delivery</span></span>
      </div>
      <div class="order-row">
        <span class="label">Delivery Address</span>
        <span class="value">{{ json_decode($order->customer_address)->address }}</span>
      </div>
    </div>

    <!-- What Happens Next -->
    <div class="section-label">What Happens Next</div>
    <div class="steps">
      <div class="step">
        <div class="step-num">1</div>
        <div class="step-text">We are preparing your order for dispatch.</div>
      </div>
      <div class="step">
        <div class="step-num">2</div>
        <div class="step-text">Once shipped, you will receive a confirmation email with delivery updates.</div>
      </div>
      <div class="step">
        <div class="step-num">3</div>
        <div class="step-text">Pay the total amount in cash when your order arrives at your door.</div>
      </div>
    </div>

    <!-- Important Note -->
    <div class="note-box">
      <div class="note-title">⚠ Important Note</div>
      <p>Please keep the <strong>exact amount ready</strong> at the time of delivery to ensure a smooth and quick handover.</p>
    </div>

    <!-- Facebook -->
    <div class="social-row">
      <span class="social-label">Stay connected with us:</span>
      <a href="https://www.facebook.com/agronybd/" class="fb-link">
        <svg width="16" height="16" fill="white" viewBox="0 0 24 24"><path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.41c0-3.025 1.792-4.697 4.533-4.697 1.312 0 2.686.236 2.686.236v2.97h-1.513c-1.491 0-1.956.93-1.956 1.874v2.25h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/></svg>
        Agrony on Facebook
      </a>
    </div>

    <!-- Support -->
    <div class="support">
      If you have any questions, feel free to contact our support team at<br>
      <b>info@agrony.com / 01898879990</b>
    </div>

    <!-- Sign-off -->
    <div class="signoff">
      Thank you for choosing <strong>Agrony.com</strong>.<br><br>
      Best regards,<br>
      <strong>Team Agrony</strong>
    </div>

  </div>

  <!-- Footer -->
  <div class="footer">
    <a href="https://www.agrony.com">www.agrony.com</a>
    <p>© 2025 Agrony.com · All rights reserved.<br>
    You're receiving this email because you placed an order on Agrony.</p>
  </div>

</div>

</body>
</html>