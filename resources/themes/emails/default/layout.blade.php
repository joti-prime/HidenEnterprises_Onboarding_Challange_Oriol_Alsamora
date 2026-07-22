<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <style type="text/css" rel="stylesheet" media="all">
        @media only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
            }
        }
    </style>
</head>

<?php

$mailColor           = '#ea580c';
$mailBackgroundColor = '#f5f5ff';
$mailMode            = 'dark';
$mailLogo            = 'https://www.hidencloud.com/hidencloudstorage/LogoHidenCloud.png';
$mailLogoFull        = true;

$mailDiscord   = 'https://discord.hidencloud.com';
$mailTwitter   = 'https://x.com/hiden_cloud';
$mailFacebook  = '';
$mailInstagram = 'https://www.instagram.com/hiden_cloud/';
$mailLinkedin  = 'https://www.linkedin.com/company/hidencloud/';
$mailYoutube   = 'https://www.youtube.com/@hiden_cloud';
$mailStatus    = 'https://status.hidencloud.com/';
$mailBilling   = rtrim(config('app.url'), '/');
$mailSupport   = 'https://discord.hidencloud.com';

$style = [
    'body' => 'margin: 0; padding: 15px 5px; width: 100%; background-color: ' . $mailBackgroundColor . ';',
    'email-wrapper' => 'width: 100%; max-width: 570px; margin: 0 auto; padding: 0; display: block;',

    'email-masthead' => 'padding: 25px 0;',
    'email-masthead_name' => 'font-size: 20px; font-weight: 500; text-decoration: none; color: ' . ($mailMode == 'dark' ? '#FFFFFF' : '#000000') . ';',

    'email-body' => 'box-sizing: border-box; -moz-box-sizing: border-box; -webkit-box-sizing: border-box; width: 570px; display: block; padding: 20px 25px; border-radius: 10px; border: 1px solid #D0D0FF; background-color: #fff; border-top: 3px solid ' . $mailColor . ';',

    'email-footer' => 'padding: 25px 0;',
    'email-footer_top' => 'padding-bottom: 25px;',
    'email-footer_bottom' => 'padding-top: 25px;',
    'email-footer_divider' => 'height: 1px; line-height: 1px; font-size: 1px; background-color: ' . ($mailMode == 'dark' ? '#3D3D53' : '#C4C4CC') . ';',
    'email-footer_links' => 'color: ' . ($mailMode == 'dark' ? '#9AA6C1' : '#576072') . '; text-decoration: none;',
    'email-footer_copyright' => 'padding-top: 10px; color: ' . ($mailMode == 'dark' ? '#576072' : '#9AA6C2') . ';',
    'email-footer_small' => 'font-size: 12px;',

    'body_action' => 'width: 100%; margin: 30px auto; padding: 0; text-align: center;',
    'body_sub' => 'margin-top: 25px; display: block; padding: 10px 15px; border-radius: 7px; background-color: rgb(0, 0, 0, 0.02); border: 1px solid #D0D0FF;',

    'anchor' => 'color: ' . $mailColor . ';',
    'header-1' => 'margin-top: 0; color: #212127; font-size: 19px; font-weight: bold; text-align: left;',
    'paragraph' => 'margin-top: 0; color: #484858; font-size: 16px; line-height: 1.5em;',
    'paragraph-sub' => 'margin-top: 0; color: #5B5B71; font-size: 12px; line-height: 1.5em;',
    'paragraph-center' => 'text-align: center;',
    'mb-0' => 'margin-bottom: 0;',

    'button' => 'display: block; display: inline-block; width: 200px; min-height: 20px; padding: 10px;
                 background-color: ' . $mailColor . '; border-radius: 3px; color: #ffffff; font-size: 15px; line-height: 25px;
                 text-align: center; text-decoration: none; -webkit-text-size-adjust: none;',
];

$iconColor = $mailMode == 'dark' ? '#9AA6C1' : '#576072';

$fontFamily = 'font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif;';
?>

<body style="{{ $style['body'] }}">

    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="{{ $style['email-wrapper'] }}" align="center">
                <table width="570" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="{{ $style['email-masthead'] }}">
                            <a style="{{ $fontFamily }} {{ $style['email-masthead_name'] }}" href="{{ url('/') }}" target="_blank">
                                <img src="{{ $mailLogo }}" style="height: 36px; vertical-align: middle;" alt="Logo" />
                                @if (!$mailLogoFull)
                                    <span style="vertical-align: middle; margin-left: 10px;">{{ settings('app_name', 'HidenCloud') }}</span>
                                @endif
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td style="{{ $fontFamily }} {{ $style['email-body'] }}" width="570">

                            <h1 style="{{ $style['header-1'] }}">
                                {!! __('client.hello') !!} {{ $name }},
                            </h1>

                            <p style="{{ $style['paragraph'] }}">
                                {!! $intro !!}
                            </p>

                            @if (isset($button))
                                <table style="{{ $style['body_action'] }}" align="center" width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td align="center">
                                            <a href="{{ $button['url'] }}"
                                                style="{{ $fontFamily }} {{ $style['button'] }}"
                                                class="button"
                                                target="_blank">
                                                {{ $button['name'] }}
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            <p style="{{ $style['paragraph'] }}">
                                @if(!isset($button)) <br> @endif {!! __('client.email_template_content') !!}
                            </p>

                            <p style="{{ $style['paragraph'] }} {{ $style['mb-0'] }}">
                                {!! __('client.regards') !!},<br>{{ settings('app_name', 'HidenCloud') }}
                            </p>

                            @if (isset($button))
                                <table style="{{ $style['body_sub'] }}">
                                    <tr>
                                        <td style="{{ $fontFamily }}">
                                            <p style="{{ $style['paragraph-sub'] }}">
                                                {!! __('client.email_button_desc', ['button' => $button['name']]) !!}
                                            </p>

                                            <p style="{{ $style['paragraph-sub'] }}">
                                                <a style="{{ $style['anchor'] }}" href="{{ $button['url'] }}" target="_blank">
                                                    {{ $button['url'] }}
                                                </a>
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            @endif
                        </td>
                    </tr>
                </table>


                <table width="570" style="width: 570px;" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="{{ $style['email-footer'] }}" width="570">
                            <table width="570" style="width: 570px;" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="{{ $style['email-footer_top'] }}" width="570">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td align="left" style="{{ $fontFamily }} vertical-align: middle;">
                                                    <a style="{{ $fontFamily }} {{ $style['email-footer_links'] }}" href="{{ url('/') }}" target="_blank">
                                                        {{ settings('app_name', 'HidenCloud') }}
                                                    </a>
                                                </td>
                                                <td align="right" style="vertical-align: middle;">
                                                    <table cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            @if ($mailDiscord)
                                                                <td style="padding-left: 10px; vertical-align: middle; line-height: 0;">
                                                                    <a href="{{ $mailDiscord }}" target="_blank" style="color: {{ $iconColor }}; text-decoration: none;">
                                                                        <img src="https://img.icons8.com/color/48/discord-new-logo.png" width="20" height="20" alt="Discord" style="display: block; border: 0;" />
                                                                    </a>
                                                                </td>
                                                            @endif
                                                            @if ($mailTwitter)
                                                                <td style="padding-left: 10px; vertical-align: middle; line-height: 0;">
                                                                    <a href="{{ $mailTwitter }}" target="_blank" style="color: {{ $iconColor }}; text-decoration: none;">
                                                                        <img src="https://img.icons8.com/color/48/twitterx--v2.png" width="20" height="20" alt="X" style="display: block; border: 0;" />
                                                                    </a>
                                                                </td>
                                                            @endif
                                                            @if ($mailInstagram)
                                                                <td style="padding-left: 10px; vertical-align: middle; line-height: 0;">
                                                                    <a href="{{ $mailInstagram }}" target="_blank" style="color: {{ $iconColor }}; text-decoration: none;">
                                                                        <img src="https://img.icons8.com/color/48/instagram-new.png" width="20" height="20" alt="Instagram" style="display: block; border: 0;" />
                                                                    </a>
                                                                </td>
                                                            @endif
                                                            @if ($mailLinkedin)
                                                                <td style="padding-left: 10px; vertical-align: middle; line-height: 0;">
                                                                    <a href="{{ $mailLinkedin }}" target="_blank" style="color: {{ $iconColor }}; text-decoration: none;">
                                                                        <img src="https://img.icons8.com/color/48/linkedin.png" width="20" height="20" alt="LinkedIn" style="display: block; border: 0;" />
                                                                    </a>
                                                                </td>
                                                            @endif
                                                            @if ($mailYoutube)
                                                                <td style="padding-left: 10px; vertical-align: middle; line-height: 0;">
                                                                    <a href="{{ $mailYoutube }}" target="_blank" style="color: {{ $iconColor }}; text-decoration: none;">
                                                                        <img src="https://img.icons8.com/color/48/youtube-play.png" width="20" height="20" alt="YouTube" style="display: block; border: 0;" />
                                                                    </a>
                                                                </td>
                                                            @endif
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="{{ $style['email-footer_divider'] }}" width="570">&nbsp;</td>
                                </tr>

                                <tr>
                                    <td style="{{ $style['email-footer_bottom'] }}">
                                        <table cellpadding="0" cellspacing="0">
                                            <tr>
                                                @if ($mailBilling)
                                                    <td style="padding-right: 20px;">
                                                        <a style="{{ $fontFamily }} {{ $style['email-footer_links'] }} {{ $style['email-footer_small'] }}" href="{{ $mailBilling }}" target="_blank">
                                                            Billing area
                                                        </a>
                                                    </td>
                                                @endif
                                                @if ($mailSupport)
                                                    <td style="padding-right: 20px;">
                                                        <a style="{{ $fontFamily }} {{ $style['email-footer_links'] }} {{ $style['email-footer_small'] }}" href="{{ $mailSupport }}" target="_blank">
                                                            Support
                                                        </a>
                                                    </td>
                                                @endif
                                                @if ($mailStatus)
                                                    <td>
                                                        <a style="{{ $fontFamily }} {{ $style['email-footer_links'] }} {{ $style['email-footer_small'] }}" href="{{ $mailStatus }}" target="_blank">
                                                            Status page
                                                        </a>
                                                    </td>
                                                @endif
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="{{ $fontFamily }} {{ $style['email-footer_copyright'] }} {{ $style['email-footer_small'] }}">
                                        &copy; {{ date('Y') }} {{ settings('app_name', 'HidenCloud') }}. {!! __('client.all_rights_reserved') !!}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>
