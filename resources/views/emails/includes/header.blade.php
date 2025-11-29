{{-- <tr>
    <td align="center" style="background:#1a73e8; padding:20px;">
        <img src="{{ asset('web-assets/images/logo/1-logo.png') }}" alt="Logo"
            style="max-height:60px; display:block; margin-bottom:10px;">
        <h2 style="color:#ffffff; margin:0; font-weight:500;">
            @yield('header', 'Notification')
        </h2>
    </td>
</tr> --}}
<tr>
  <td align="center" style="background:#062e39; padding:20px;">
    <!-- Logo -->
    <img src="{{ asset('web-assets/images/logo/1-logo.png') }}" 
         alt="Logo"
         style="max-height:60px; display:block; margin-bottom:10px;">

    <!-- Heading -->
    <h2 style="color:#ffffff; margin:0; font-weight:500;">
      @yield('header', 'Notification')
    </h2>

    <!-- WhatsApp Icon -->
    {{-- <a href="https://wa.me/17134706157" 
       style="display:inline-block; margin-top:12px; text-decoration:none;">
      <img src="{{ asset('web-assets/images/icons/whatsapp.png') }}" 
           alt="WhatsApp"
           width="28"
           style="display:block; border:0;">
    </a> --}}
  </td>
</tr>
