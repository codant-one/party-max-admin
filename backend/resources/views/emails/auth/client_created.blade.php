@extends("emails.layouts.layout")

@section("content")
<tr>
    <td colspan="2">
        <table style="font-family: Poppins,Helvetica,sans-serif; clear: both; margin-top: 6px!important; margin-bottom: 6px!important;  max-width: none!important; border-collapse: separate!important;  border-spacing: 0; background-color: white; width: 100%; padding: 0 5px;" align="center">
            <tr>
                <td style="padding-bottom: 1.5rem!important;" align="center">
                    <p style="font-size: 18px; font-weight: 600;">{{$title}}</p>
                </td>
            </tr>
            <tr>
                <td style="padding-bottom: 1.5rem!important; font-size: 14px;" align="left">
                    <p>Hola: {{$user}}</p>

                    <p>{!! $text !!}</p>
                </td>
            </tr>
            <tr>
                <td style="padding-bottom: 1.5rem!important; font-size: 12px;" align="center">
                    <a href="{{$buttonLink}}" class="btn btn-primary" style="color: #FFFFFF; background-color: #FF0090; border-color: #FF0090; line-height: 1.5; cursor: pointer; border-radius: 0.475rem; padding-top: 0.75rem; padding-bottom: 0.75rem; padding-left: 1.5rem; padding-right: 1.5rem; text-decoration: none;">
                        {{$buttonText}}
                    </a>
                </td>
            </tr>
        </table>
    </td>
</tr>
@endsection
        