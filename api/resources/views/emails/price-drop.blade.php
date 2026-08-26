<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ __('emails.price_drop.badge', [], $locale) }}</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">

  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f9;padding:40px 0;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">

          <!-- HEADER -->
          <tr>
            <td style="background:#111827;border-radius:16px 16px 0 0;padding:28px 40px;text-align:center;">
              <span style="font-size:22px;font-weight:800;color:#ffffff;letter-spacing:-0.5px;">
                {{ config('app.name') }}
              </span>
            </td>
          </tr>

          <!-- BADGE -->
          <tr>
            <td style="background:#059669;padding:14px 40px;text-align:center;">
              <span style="font-size:13px;font-weight:700;color:#ffffff;letter-spacing:1.5px;text-transform:uppercase;">
                🎉 &nbsp;{{ __('emails.price_drop.badge', [], $locale) }}
              </span>
            </td>
          </tr>

          <!-- BODY -->
          <tr>
            <td style="background:#ffffff;padding:40px;">

              <!-- Greeting -->
              <p style="margin:0 0 24px;font-size:16px;color:#374151;">
                {{ __('emails.price_drop.greeting_prefix', [], $locale) }} <strong>{{ $userName }}</strong>!
              </p>

              <!-- Product name -->
              <h2 style="margin:0 0 28px;font-size:22px;font-weight:800;color:#111827;line-height:1.3;">
                {{ $productName }}
              </h2>

              <!-- Price comparison card -->
              <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;background:#f9fafb;border-radius:12px;border:1px solid #e5e7eb;overflow:hidden;">
                <tr>
                  <!-- Old price -->
                  <td width="50%" style="padding:20px 24px;text-align:center;border-right:1px solid #e5e7eb;">
                    <div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">{{ __('emails.price_drop.was', [], $locale) }}</div>
                    <div style="font-size:24px;font-weight:800;color:#9ca3af;text-decoration:line-through;">{{ number_format($oldPrice, 0, '.', ' ') }} ₴</div>
                  </td>
                  <!-- New price -->
                  <td width="50%" style="padding:20px 24px;text-align:center;background:#ecfdf5;">
                    <div style="font-size:11px;font-weight:700;color:#059669;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">{{ __('emails.price_drop.now', [], $locale) }}</div>
                    <div style="font-size:28px;font-weight:800;color:#059669;">{{ number_format($newPrice, 0, '.', ' ') }} ₴</div>
                  </td>
                </tr>
              </table>

              <!-- Savings highlight -->
              <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:32px;">
                <tr>
                  <td style="background:linear-gradient(135deg,#059669,#10b981);border-radius:10px;padding:16px 24px;text-align:center;">
                    <span style="font-size:15px;font-weight:700;color:#ffffff;">
                      {{ __('emails.price_drop.savings', [], $locale) }}&nbsp;
                      <span style="font-size:18px;">{{ number_format($saving, 0, '.', ' ') }} ₴</span>
                      &nbsp;({{ $dropPercent }}%)
                    </span>
                  </td>
                </tr>
              </table>

              <!-- CTA Button -->
              <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:32px;">
                <tr>
                  <td align="center">
                    <a href="{{ $productUrl }}"
                       style="display:inline-block;background:#111827;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;padding:14px 40px;border-radius:10px;letter-spacing:0.3px;">
                      {{ __('emails.price_drop.button', [], $locale) }}
                    </a>
                  </td>
                </tr>
              </table>

              <!-- Note -->
              <p style="margin:0;font-size:13px;color:#9ca3af;text-align:center;line-height:1.6;">
                {{ __('emails.price_drop.note_1', [], $locale) }}<br>
                {{ __('emails.price_drop.note_2', [], $locale) }} <a href="{{ $productUrl }}" style="color:#059669;text-decoration:none;">{{ __('emails.price_drop.wishlist', [], $locale) }}</a>.
              </p>

            </td>
          </tr>

          <!-- FOOTER -->
          <tr>
            <td style="background:#f9fafb;border-top:1px solid #e5e7eb;border-radius:0 0 16px 16px;padding:24px 40px;text-align:center;">
              <p style="margin:0;font-size:12px;color:#9ca3af;">
                © {{ date('Y') }} {{ config('app.name') }}. {{ __('emails.price_drop.rights', [], $locale) }}
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>
