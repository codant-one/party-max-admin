<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta charset="UTF-8">
        <meta content="width=device-width, initial-scale=1" name="viewport">
        <meta name="x-apple-disable-message-reformatting">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content="telephone=no" name="format-detection">
        <title>E-mails PARTYMAX</title>

        <style type="text/css">
            #outlook a {
                padding:0;
            }

            .es-button {
                mso-style-priority:100!important;
                text-decoration:none!important;
            }

            a[x-apple-data-detectors] {
                color:inherit!important;
                text-decoration:none!important;
                font-size:inherit!important;
                font-family:inherit!important;
                font-weight:inherit!important;
                line-height:inherit!important;
            }

            .es-desk-hidden {
                display:none;
                float:left;
                overflow:hidden;
                width:0;
                max-height:0;
                line-height:0;
                mso-hide:all;
            }

            .title {
                font-size: 36px;
                padding-top:30px;
            }

            .subtitle {
                font-size: 24px;
                padding-top: 15px;
                padding-bottom: 20px;
            }

            @media only screen and (max-width:600px) { 
                .title {
                    font-size: 25px;
                    padding-top:10px
                }

                .subtitle {
                    font-size: 18px;
                    padding-top: 5px;
                    padding-bottom: 15px;
                }
            }
        </style>
    </head>
    <body style="width:100%;font-family:arial, 'helvetica neue', helvetica, sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;padding:0;margin:0">
        <div dir="ltr" class="es-wrapper-color" lang="und" style="background-color:#F6F6F6">
            <table class="es-wrapper" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;border-spacing:0px;padding:0;margin:0;width:100%;height:100%;background-repeat:repeat;background-position:center top;background-color:#F6F6F6">
                <tr>
                    <td align="center" bgcolor="#E2F8FC" style="padding:20px;margin:0;background-color:#e2f8fc">
                        @include("emails.layouts.header")
                    </td>
                </tr>  
                <tr>
                    <td align="center" bgcolor="#E2F8FC" style="margin:0;padding-left:40px;padding-right:40px;background-color:#e2f8fc">
                    @yield("content")
                    </td>     
                </tr>                                
                <tr>
                    <td align="center" bgcolor="#E2F8FC" style="margin:0;padding-top:25px;padding-bottom:25px;padding-left:40px;padding-right:40px;background-color:#e2f8fc">
                        @include("emails.layouts.footer")
                    </td>
                </tr>
                <tr>
                    <td align="center" bgcolor="#E2F8FC" style="margin:0;padding-top:25px;padding-bottom:25px;">
                        <p style="margin:0;-webkit-text-size-adjust:none;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:17px;color:#0a1b33;font-size:14px">Nunca envíes tu clave o datos de tu cuenta por e-mail.<br>Conoce cómo cuidamos tu Privacidad y visita los Términos y Condiciones.</p>
                    </td>
                </tr>
            </table>
        </div>
    </body>
</html>
