<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Email')</title>
</head>
{{-- <body style="margin:0; padding:0; font-family: 'Segoe UI', Arial, sans-serif; background-color:#f4f5f7; color:#333;">
    <table width="100%" cellpadding="0" cellspacing="0" style="padding:20px;">
        <tr>
            <td align="center">
                <table width="650" cellpadding="0" cellspacing="0"
                    style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.1);"> --}}
<body style="margin:0; padding:0; font-family:'Poppins', Arial, sans-serif; background-color:#f4f5f7; color:#7c858c;">

  <table width="100%" cellpadding="0" cellspacing="0" style="padding:20px;">
    <tr>
      <td align="center">

        <table width="650" cellpadding="0" cellspacing="0" 
          style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.1);">

                    {{-- Header --}}
                    @include('emails.includes.header')

                    {{-- Body --}}
                    {{-- <tr>
                        <td style="padding:25px; font-size:15px; line-height:1.6;"> --}}
                    <tr>
                        <td style="padding:30px; font-size:15px; line-height:22px; color:#333;">
                            @yield('content')
                        </td>
                    </tr>

                    {{-- Footer --}}
                    @include('emails.includes.footer')
        </table>

      </td>
    </tr>
  </table>

</body>
                {{-- </table>
            </td>
        </tr>
    </table>
</body> --}}
</html>
