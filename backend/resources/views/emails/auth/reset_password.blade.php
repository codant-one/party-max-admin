@extends("emails.layouts.layout")

@section("content")
<table cellpadding="0" cellspacing="0" width="100%" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
  <tr>
    <td align="center" valign="top" style="padding:0;margin:0;width:520px">
      <table cellpadding="0" cellspacing="0" width="100%" bgcolor="#FFFFFF" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:separate;border-spacing:0px;border-width:10px;border-style:solid;border-color:transparent;background-color:#ffffff;border-radius:32px">
        <tr>
          <td align="center" style="padding:0;margin:0;padding-bottom:15px;padding-top:40px;font-size:0px">
          <img src="{{ asset('/images/icon-check-circle.png') }}" alt="icon-check" title="check" width="48" height="48" style="display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic">
          </td>
        </tr>
        <tr>
          <td align="center" style="margin:0;padding-top:15px;padding-left:20px;padding-right:20px;padding-bottom:30px">
            <p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:36px;color:#0a1b33;font-size:24px">
              <strong>Se cambio tu contraseña correctamente</strong>
            </p>
          </td>
        </tr>
        <tr>
          <td align="center" style="padding:0;margin:0;padding-bottom:15px;padding-top:30px">
            <p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:19px;color:#0a1b33;font-size:16px">
              Explora nuestro marketplace
            </p>
          </td>
        </tr>
        <tr>
          <td align="center" style="padding:0;margin:0;padding-bottom:40px;padding-left:40px;padding-right:40px">
            <span class="es-button-border" style="border-style:solid;border-color:#2CB543;background:#ff0090;border-width:0px 0px 2px 0px;display:block;border-radius:32px;width:auto;border-bottom-width:0px">
              <a href="{{ $buttonLink }}" class="es-button" target="_blank" style="mso-style-priority:100 !important;text-decoration:none;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;color:#FFFFFF;font-size:16px;padding:10px 20px 10px 20px;display:block;background:#ff0090;border-radius:32px;font-family:arial, 'helvetica neue', helvetica, sans-serif;font-weight:normal;font-style:normal;line-height:19px;width:auto;text-align:center;mso-padding-alt:0;mso-border-alt:10px solid #ff0090;padding-left:5px;padding-right:5px">
                www.partymax.co
              </a>
            </span>
          </td>
        </tr>
      </table>
    </td>
</tr>
</table>

@endsection