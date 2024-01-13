@extends("emails.layouts.layout")

@section("content")
<table cellpadding="0" cellspacing="0" width="100%" bgcolor="#FFFFFF" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:separate;border-spacing:0px;border-width:10px;border-style:solid;border-color:transparent;background-color:#ffffff;border-radius:32px">
    <tr>
        <td align="center" style="padding:0;Margin:0;padding-bottom:15px;padding-top:40px;font-size:0px">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48" fill="none">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M10 24C8.89543 24 8 24.8954 8 26V40C8 41.1046 8.89543 42 10 42H38C39.1046 42 40 41.1046 40 40V26C40 24.8954 39.1046 24 38 24H10ZM4 26C4 22.6863 6.68629 20 10 20H38C41.3137 20 44 22.6863 44 26V40C44 43.3137 41.3137 46 38 46H10C6.68629 46 4 43.3137 4 40V26Z" fill="#FF0090"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M24 6C21.8783 6 19.8434 6.84285 18.3431 8.34315C16.8429 9.84344 16 11.8783 16 14V22C16 23.1046 15.1046 24 14 24C12.8954 24 12 23.1046 12 22V14C12 10.8174 13.2643 7.76516 15.5147 5.51472C17.7652 3.26428 20.8174 2 24 2C27.1826 2 30.2348 3.26428 32.4853 5.51472C34.7357 7.76516 36 10.8174 36 14V22C36 23.1046 35.1046 24 34 24C32.8954 24 32 23.1046 32 22V14C32 11.8783 31.1571 9.84344 29.6569 8.34315C28.1566 6.84285 26.1217 6 24 6Z" fill="#FF0090"/>
            </svg>
        </td>   
    </tr>
    <tr>
        <td align="center" style="Margin:0;padding-top:15px;padding-left:20px;padding-right:20px;padding-bottom:30px ;border-bottom: 1px solid #D9EEF2;">
            <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:36px;color:#0a1b33;font-size:24px">
                <strong>Cambio de contraseña</strong>
            </p>
        </td>
    </tr>
    <tr>
        <td align="center" style="padding:0;Margin:0;padding-bottom:15px;padding-top:30px;">
            <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:19px;color:#0a1b33;font-size:16px">
                A continuación te compartiremos un enlace para que puedas reestablecer tu contraseña de manera segura.
            </p>
        </td>
    </tr>
    <tr>
        <td align="center" style="padding:0;Margin:0;padding-bottom:40px;padding-left:40px;padding-right:40px">
            <span class="es-button-border" style="border-style:solid;border-color:#2CB543;background:#ff0090;border-width:0px 0px 2px 0px;display:block;border-radius:32px;width:auto;border-bottom-width:0px">
                <a href="{{ $buttonLink }}" class="es-button" target="_blank" style="mso-style-priority:100 !important;text-decoration:none;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;color:#FFFFFF;font-size:16px;padding:10px 20px 10px 20px;display:block;background:#ff0090;border-radius:32px;font-family:arial, 'helvetica neue', helvetica, sans-serif;font-weight:normal;font-style:normal;line-height:19px;width:auto;text-align:center;mso-padding-alt:0;mso-border-alt:10px solid #ff0090;padding-left:5px;padding-right:5px">
                    Cambiar contraseña
                </a>
            </span>
        </td>
    </tr>
</table>
@endsection
        