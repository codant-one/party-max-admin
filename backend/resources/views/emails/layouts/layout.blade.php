<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link href="https://fonts.googleapis.com/css2?family=Oxygen:wght@300;400&family=Raleway:wght@700&display=swap" rel="stylesheet">
    </head>
    <body>
        <table style="font-family: Poppins,Helvetica,sans-serif; clear: both; margin-top: 6px!important; margin-bottom: 6px!important;  max-width: none!important; border-collapse: separate!important;  border-spacing: 0; background-color: white; width: 100%; padding: 0 5px;" align="center">
            <tr>
                <td>
                    <table style="font-family: Poppins,Helvetica,sans-serif; clear: both; margin-top: 6px!important; margin-bottom: 6px!important;  max-width: none!important; border-collapse: separate!important;  border-spacing: 0; background-color: white; width: 100%; padding: 0 5px;" align="center">
                        @php
                            //Si no esta definida la variable lang se inicializa en es (espanol).
                            $lang = $lang ?? (session()->get('locale') ?? 'es');
                        @endphp
                        @include("emails.layouts.header")

                        @yield("content")
                                            
                        @include("emails.layouts.footer")
                    </table> 
                </td>
            </tr> 
        </table>
    </body>
</html>
