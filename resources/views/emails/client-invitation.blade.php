<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="it">
<head>
<title>Benvenuto su NutrizionistApp</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<style>
@media only screen and (max-width: 600px) {
    .email-wrapper { width: 100% !important; }
    .email-body { width: 100% !important; }
    .btn-cta { width: 90% !important; }
    .feature-col { display: block !important; width: 100% !important; }
}
</style>
</head>
<body style="margin:0;padding:0;background-color:#f0f7f3;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#f0f7f3;padding:40px 20px;">
<tr>
<td align="center">

    <!-- Email container -->
    <table class="email-wrapper" width="580" cellpadding="0" cellspacing="0" role="presentation" style="max-width:580px;width:100%;">

        <!-- Logo header -->
        <tr>
            <td align="center" style="padding:0 0 28px 0;">
                <a href="{{ $appUrl }}" style="display:inline-block;text-decoration:none;">
                    <img src="{{ $appUrl }}/images/logo-nutrizionistapp.png"
                         alt="{{ $appName }}"
                         style="height:38px;width:auto;display:block;">
                </a>
            </td>
        </tr>

        <!-- Main card -->
        <tr>
            <td style="background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.08);">

                <!-- Green top band -->
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                    <td style="background:linear-gradient(135deg,#2d7a4f 0%,#3d9962 100%);padding:36px 40px 32px;text-align:center;">
                        <!-- Icon -->
                        <div style="display:inline-block;background:rgba(255,255,255,0.2);border-radius:50%;width:64px;height:64px;line-height:64px;text-align:center;margin-bottom:16px;font-size:28px;">
                            🥗
                        </div>
                        <h1 style="margin:0 0 8px;color:#ffffff;font-size:24px;font-weight:700;line-height:1.3;">
                            Benvenuto/a, {{ $clientName }}!
                        </h1>
                        <p style="margin:0;color:rgba(255,255,255,0.88);font-size:15px;line-height:1.5;">
                            Il tuo percorso nutrizionale inizia adesso
                        </p>
                    </td>
                </tr>
                </table>

                <!-- Body content -->
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                    <td style="padding:36px 40px 20px;">

                        <!-- Intro text -->
                        <p style="margin:0 0 20px;color:#374151;font-size:16px;line-height:1.6;">
                            Ciao <strong>{{ $clientName }}</strong>,
                        </p>
                        <p style="margin:0 0 20px;color:#374151;font-size:16px;line-height:1.6;">
                            <strong>{{ $nutritionistName }}</strong> ti ha aggiunto come paziente su <strong>{{ $appName }}</strong>, la piattaforma che ti accompagnerà nel tuo percorso di benessere e nutrizione.
                        </p>
                        <p style="margin:0 0 28px;color:#374151;font-size:16px;line-height:1.6;">
                            Per accedere alla tua area riservata, scegli la tua password personale cliccando sul pulsante qui sotto:
                        </p>

                        <!-- CTA Button -->
                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                        <tr>
                            <td align="center" style="padding:0 0 32px;">
                                <a href="{{ $resetUrl }}"
                                   class="btn-cta"
                                   style="display:inline-block;background-color:#2d7a4f;color:#ffffff;font-size:16px;font-weight:700;text-decoration:none;padding:15px 36px;border-radius:8px;letter-spacing:0.2px;">
                                    Scegli la tua password →
                                </a>
                            </td>
                        </tr>
                        </table>

                        <!-- Divider -->
                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                        <tr>
                            <td style="border-top:1px solid #e5e7eb;padding-bottom:28px;"></td>
                        </tr>
                        </table>

                        <!-- Features section -->
                        <p style="margin:0 0 20px;color:#111827;font-size:15px;font-weight:700;text-align:center;text-transform:uppercase;letter-spacing:0.5px;">
                            Cosa puoi fare nella tua area riservata
                        </p>

                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                        <tr>
                            <!-- Feature 1 -->
                            <td class="feature-col" width="50%" valign="top" style="padding:0 8px 16px 0;">
                                <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td style="background:#f0f7f3;border-radius:10px;padding:18px 16px;">
                                        <p style="margin:0 0 6px;font-size:22px;">🥦</p>
                                        <p style="margin:0 0 4px;color:#111827;font-size:14px;font-weight:700;">Piano nutrizionale</p>
                                        <p style="margin:0;color:#6b7280;font-size:13px;line-height:1.5;">Consulta i tuoi pasti giornalieri e i macronutrienti.</p>
                                    </td>
                                </tr>
                                </table>
                            </td>
                            <!-- Feature 2 -->
                            <td class="feature-col" width="50%" valign="top" style="padding:0 0 16px 8px;">
                                <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td style="background:#f0f7f3;border-radius:10px;padding:18px 16px;">
                                        <p style="margin:0 0 6px;font-size:22px;">📊</p>
                                        <p style="margin:0 0 4px;color:#111827;font-size:14px;font-weight:700;">Traccia i progressi</p>
                                        <p style="margin:0;color:#6b7280;font-size:13px;line-height:1.5;">Invia i tuoi check-in settimanali con misurazioni e foto.</p>
                                    </td>
                                </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <!-- Feature 3 -->
                            <td class="feature-col" width="50%" valign="top" style="padding:0 8px 0 0;">
                                <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td style="background:#f0f7f3;border-radius:10px;padding:18px 16px;">
                                        <p style="margin:0 0 6px;font-size:22px;">💬</p>
                                        <p style="margin:0 0 4px;color:#111827;font-size:14px;font-weight:700;">Messaggi diretti</p>
                                        <p style="margin:0;color:#6b7280;font-size:13px;line-height:1.5;">Comunica direttamente con il tuo nutrizionista.</p>
                                    </td>
                                </tr>
                                </table>
                            </td>
                            <!-- Feature 4 -->
                            <td class="feature-col" width="50%" valign="top" style="padding:0 0 0 8px;">
                                <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td style="background:#f0f7f3;border-radius:10px;padding:18px 16px;">
                                        <p style="margin:0 0 6px;font-size:22px;">📅</p>
                                        <p style="margin:0 0 4px;color:#111827;font-size:14px;font-weight:700;">Appuntamenti</p>
                                        <p style="margin:0;color:#6b7280;font-size:13px;line-height:1.5;">Visualizza i tuoi appuntamenti programmati.</p>
                                    </td>
                                </tr>
                                </table>
                            </td>
                        </tr>
                        </table>

                    </td>
                </tr>

                <!-- Notice box -->
                <tr>
                    <td style="padding:0 40px 32px;">
                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                        <tr>
                            <td style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:14px 18px;">
                                <p style="margin:0;color:#92400e;font-size:13px;line-height:1.5;">
                                    ⚠️ Il link per impostare la password è valido per <strong>60 minuti</strong>. Se non lo usi entro questo tempo, chiedi al tuo nutrizionista di inviarne uno nuovo.
                                </p>
                            </td>
                        </tr>
                        </table>
                    </td>
                </tr>

                <!-- Divider + subcopy -->
                <tr>
                    <td style="padding:0 40px 28px;border-top:1px solid #f3f4f6;">
                        <p style="margin:16px 0 0;color:#9ca3af;font-size:12px;line-height:1.6;word-break:break-all;">
                            Se il pulsante non funziona, copia e incolla questo link nel browser:<br>
                            <a href="{{ $resetUrl }}" style="color:#2d7a4f;">{{ $resetUrl }}</a>
                        </p>
                    </td>
                </tr>

            </table><!-- end card inner -->
            </td>
        </tr><!-- end main card -->

        <!-- Footer -->
        <tr>
            <td align="center" style="padding:24px 0 0;">
                <p style="margin:0;color:#9ca3af;font-size:12px;text-align:center;">
                    © {{ date('Y') }} {{ $appName }} · Tutti i diritti riservati
                </p>
                <p style="margin:6px 0 0;color:#9ca3af;font-size:11px;text-align:center;">
                    Hai ricevuto questa email perché {{ $nutritionistName }} ti ha aggiunto come paziente.
                </p>
            </td>
        </tr>

    </table><!-- end email-wrapper -->

</td>
</tr>
</table>

</body>
</html>
