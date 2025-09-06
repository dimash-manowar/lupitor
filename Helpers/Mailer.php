<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function enviarCorreo(
  string $toEmail,
  string $toName,
  string $subject,
  string $html,
  string $textAlt = '',
  array $embedded = [],    // admite ['path'=>..., 'cid'=>..., 'name'=>..., 'type'=>...]
  array $attachments = []  // admite ['path'=>...]/['string'=>..., 'name'=>..., 'type'=>...]
): bool {
  // Asegura autoload de Composer si no está cargado
  if (!class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
    $auto = __DIR__ . '/../vendor/autoload.php';
    if (is_file($auto)) require_once $auto;
  }

  $mail = new PHPMailer(true);
  try {
    // SMTP básico desde constantes (Config/Config.php)
    $mail->isSMTP();
    $mail->Host       = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = defined('SMTP_USER') ? SMTP_USER : '';
    $mail->Password   = defined('SMTP_PASS') ? SMTP_PASS : '';
    $mail->CharSet    = 'UTF-8';

    $secure = defined('SMTP_SECURE') ? strtolower((string)SMTP_SECURE) : 'tls';
    if ($secure === 'ssl') {
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
      $mail->Port       = defined('SMTP_PORT') ? (int)SMTP_PORT : 465;
    } else {
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // tls
      $mail->Port       = defined('SMTP_PORT') ? (int)SMTP_PORT : 587;
    }

    $from      = defined('SMTP_FROM') ? SMTP_FROM : (defined('SMTP_USER') ? SMTP_USER : 'no-reply@example.com');
    $fromName  = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'Club de Ajedrez';
    $replyTo   = defined('SMTP_REPLYTO') ? SMTP_REPLYTO : null;

    $mail->setFrom($from, $fromName);
    if (!empty($replyTo)) $mail->addReplyTo($replyTo, $fromName);
    $mail->addAddress($toEmail, $toName !== '' ? $toName : $toEmail);

    // Embebidos (logo/QR). Ahora soporta FILE y STRING
    foreach ($embedded as $emb) {
      if (!empty($emb['path']) && !empty($emb['cid']) && is_file($emb['path'])) {
        $name = $emb['name'] ?? basename($emb['path']);
        $type = $emb['type'] ?? (function_exists('mime_content_type') ? mime_content_type($emb['path']) : 'application/octet-stream');
        $mail->addEmbeddedImage($emb['path'], $emb['cid'], $name, 'base64', $type);
      } elseif (!empty($emb['string']) && !empty($emb['cid'])) {
        $name = $emb['name'] ?? 'image.png';
        $type = $emb['type'] ?? 'image/png';
        // PHPMailer soporta imágenes embebidas en memoria:
        $mail->addStringEmbeddedImage($emb['string'], $emb['cid'], $name, 'base64', $type);
      }
    }

    // Adjuntos (fichero o en memoria)
    foreach ($attachments as $att) {
      if (!empty($att['path']) && is_file($att['path'])) {
        $name = $att['name'] ?? basename($att['path']);
        $type = $att['type'] ?? (function_exists('mime_content_type') ? mime_content_type($att['path']) : 'application/octet-stream');
        $mail->addAttachment($att['path'], $name, PHPMailer::ENCODING_BASE64, $type);
      } elseif (!empty($att['string']) && !empty($att['name'])) {
        $type = $att['type'] ?? 'application/octet-stream';
        $mail->addStringAttachment($att['string'], $att['name'], PHPMailer::ENCODING_BASE64, $type);
      }
    }

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $html;
    $mail->AltBody = $textAlt !== '' ? $textAlt : strip_tags($html);

    $mail->send();
    return true;
  } catch (Exception $e) {
    error_log('Mailer error: ' . $mail->ErrorInfo);
    return false;
  }
}

/**
 * Plantilla de email: Reset de contraseña (bonita, modo dark, con botón).
 * @return array [$subject, $html, $text]
 */
function buildResetEmailTemplate(string $link, ?string $userName = null, ?string $cidLogo = null): array
{
  $subject   = 'Recupera tu contraseña';
  $preheader = 'Usa este enlace para restablecer tu contraseña del Club de Ajedrez de Berriozar.';
  $hello     = $userName ? "Hola, " . htmlspecialchars($userName) . " 👋" : "Hola 👋";
  $logoUrl   = defined('BASE_URL') ? (BASE_URL . 'Assets/img/logo-ajedrez.png') : '';
  $logoImg   = $cidLogo
    ? '<img src="cid:' . $cidLogo . '" alt="Club de Ajedrez de Berriozar" width="64" height="64" style="display:block;">'
    : ($logoUrl ? '<img src="' . $logoUrl . '" alt="Club de Ajedrez de Berriozar" width="64" height="64" style="display:block;">' : '');
  $year = date('Y');

  $html = <<<HTML
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="color-scheme" content="dark light">
<meta name="supported-color-schemes" content="dark light">
<title>{$subject}</title>
<style>
.container{max-width:620px;margin:0 auto;background:#0f1115;color:#e8eaf0;border-radius:16px;overflow:hidden;
           box-shadow:0 10px 30px rgba(0,0,0,.35);border:1px solid #1b1f2a}
.header{padding:24px 28px;background:linear-gradient(135deg,#0f1115 0%,#161a22 100%)}
.brand{display:flex;align-items:center;gap:12px}
.brand h1{margin:0;font-size:18px;letter-spacing:.5px;color:#8fd3ff}
.content{padding:28px}
.hi{font-size:18px;margin:0 0 12px}
.p{margin:0 0 12px;line-height:1.55;color:#cfd6e4}
.box{margin:18px 0;padding:18px;background:linear-gradient(135deg,#141926,#0f1115);
     border:1px solid #202637;border-radius:12px}
.btn{display:inline-block;background:#2ecc71;color:#0b0d12 !important;text-decoration:none;
     padding:12px 18px;border-radius:10px;font-weight:700;border:1px solid #22a85b}
.small{color:#a6b0c3;font-size:12px}
.footer{padding:18px 28px;color:#98a6bd;font-size:12px;background:#0b0d12;border-top:1px solid #1b2130}
.chessline{font-size:14px;color:#b7c9ff}
.preheader{display:none !important;opacity:0;color:transparent;height:0;width:0;overflow:hidden}
</style>
</head>
<body style="background:#0b0d12;margin:0;padding:24px;font-family:Segoe UI,Roboto,Helvetica,Arial,sans-serif;">
  <div class="preheader">{$preheader}</div>
  <div class="container">
    <div class="header">
      <div class="brand">
        {$logoImg}
        <h1>Club de Ajedrez de Berriozar</h1>
      </div>
    </div>
    <div class="content">
      <p class="hi">{$hello}</p>
      <p class="p">Hemos recibido una solicitud para <strong>restablecer tu contraseña</strong>.</p>
      <div class="box">
        <p class="p" style="margin-top:0">Haz clic en el botón para continuar:</p>
        <p style="margin:18px 0;">
          <a class="btn" href="{$link}" target="_blank" rel="noopener">Restablecer contraseña</a>
        </p>
        <p class="small">Este enlace caduca en 1 hora.</p>
      </div>
      <p class="p">Si el botón no funciona, copia y pega este enlace en tu navegador:</p>
      <p class="p" style="word-break:break-all;"><a href="{$link}" style="color:#8fd3ff" target="_blank" rel="noopener">{$link}</a></p>
      <p class="chessline">♟️ Consejo: refuerza tu apertura con una buena defensa… ¡y una contraseña fuerte! 😉</p>
      <p class="small">Si no solicitaste este cambio, ignora este mensaje.</p>
    </div>
    <div class="footer">
      © {$year} Club de Ajedrez de Berriozar · Este es un correo automático.
    </div>
  </div>
</body>
</html>
HTML;

  $text = "{$hello}\n\n" .
    "Para restablecer tu contraseña, visita:\n{$link}\n\n" .
    "El enlace caduca en 1 hora. Si no solicitaste este cambio, ignora este mensaje.\n";

  return [$subject, $html, $text];
}

/**
 * Plantilla: Bienvenida tras registro.
 * @return array [$subject, $html, $text]
 */
function buildWelcomeEmailTemplate(string $loginLink, ?string $userName = null, ?string $cidLogo = null): array
{
  $subject   = '¡Bienvenido/a al Club de Ajedrez de Berriozar!';
  $hello     = $userName ? "Hola, " . htmlspecialchars($userName) . " 👋" : "Hola 👋";
  $preheader = 'Tu cuenta ha sido creada. Entra y completa tu perfil.';
  $logoUrl   = defined('BASE_URL') ? (BASE_URL . 'Assets/img/logo-ajedrez.png') : '';
  $logoImg   = $cidLogo
    ? '<img src="cid:' . $cidLogo . '" alt="Logo" width="64" height="64" style="display:block;">'
    : ($logoUrl ? '<img src="' . $logoUrl . '" alt="Logo" width="64" height="64" style="display:block;">' : '');
  $year = date('Y');

  $html = <<<HTML
<!doctype html><html lang="es"><head><meta charset="utf-8">
<meta name="color-scheme" content="dark light"><meta name="supported-color-schemes" content="dark light">
<title>{$subject}</title>
<style>
.container{max-width:620px;margin:0 auto;background:#0f1115;color:#e8eaf0;border-radius:16px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,.35);border:1px solid #1b1f2a}
.header{padding:24px 28px;background:linear-gradient(135deg,#0f1115 0%,#161a22 100%)}
.brand{display:flex;align-items:center;gap:12px}
.brand h1{margin:0;font-size:18px;letter-spacing:.5px;color:#8fd3ff}
.content{padding:28px}
.btn{display:inline-block;background:#2ecc71;color:#0b0d12 !important;text-decoration:none;padding:12px 18px;border-radius:10px;font-weight:700;border:1px solid #22a85b}
.p{margin:0 0 12px;line-height:1.55;color:#cfd6e4}
.footer{padding:18px 28px;color:#98a6bd;font-size:12px;background:#0b0d12;border-top:1px solid #1b2130}
.preheader{display:none!important;opacity:0;color:transparent;height:0;width:0;overflow:hidden}
</style></head>
<body style="background:#0b0d12;margin:0;padding:24px;font-family:Segoe UI,Roboto,Helvetica,Arial,sans-serif;">
  <div class="preheader">{$preheader}</div>
  <div class="container">
    <div class="header"><div class="brand">{$logoImg}<h1>Club de Ajedrez de Berriozar</h1></div></div>
    <div class="content">
      <p class="p" style="font-size:18px;">{$hello}</p>
      <p class="p">¡Gracias por registrarte! Ya puedes acceder a tu cuenta y completar tu perfil.</p>
      <p style="margin:18px 0;"><a class="btn" href="{$loginLink}" target="_blank" rel="noopener">Iniciar sesión</a></p>
      <p class="p">Si el botón no funciona, copia y pega este enlace en tu navegador:</p>
      <p class="p" style="word-break:break-all;"><a href="{$loginLink}" style="color:#8fd3ff">{$loginLink}</a></p>
      <p class="p">¡Nos vemos en el tablero! ♟️</p>
    </div>
    <div class="footer">© {$year} Club de Ajedrez de Berriozar</div>
  </div>
</body></html>
HTML;

  $text = ($userName ? "Hola, $userName\n\n" : "Hola,\n\n")
    . "Tu cuenta ha sido creada. Inicia sesión en: $loginLink\n\n¡Nos vemos en el tablero!";
  return [$subject, $html, $text];
}

/**
 * Plantilla: Confirmación de inscripción a torneo.
 * $datos = ['jugador'=>'','torneo'=>'','fecha'=>'YYYY-mm-dd HH:ii','lugar'=>'','basesUrl'=>'','detalleUrl'=>'']
 * @return array [$subject, $html, $text]
 */
function buildTournamentSignupEmailTemplate(array $datos, ?string $cidLogo = null): array
{
  $subject   = 'Confirmación de inscripción · ' . htmlspecialchars($datos['torneo'] ?? 'Torneo');
  $hello     = !empty($datos['jugador']) ? "Hola, " . htmlspecialchars($datos['jugador']) . " 👋" : "Hola 👋";
  $preheader = 'Inscripción confirmada. Revisa fecha, lugar y bases.';
  $logoUrl   = defined('BASE_URL') ? (BASE_URL . 'Assets/img/logo-ajedrez.png') : '';
  $logoImg   = $cidLogo
    ? '<img src="cid:' . $cidLogo . '" alt="Logo" width="64" height="64" style="display:block;">'
    : ($logoUrl ? '<img src="' . $logoUrl . '" alt="Logo" width="64" height="64" style="display:block;">' : '');
  $fecha  = !empty($datos['fecha']) ? date('d/m/Y H:i', strtotime($datos['fecha'])) : 'Por confirmar';
  $lugar  = htmlspecialchars($datos['lugar'] ?? 'Por confirmar');
  $torneo = htmlspecialchars($datos['torneo'] ?? 'Torneo');
  $year   = date('Y');

  $btn = '';
  if (!empty($datos['detalleUrl'])) {
    $btn = '<p style="margin:18px 0;"><a class="btn" style="background:#f1c40f;color:#0b0d12!important;border:1px solid #d4ac0d" href="' . $datos['detalleUrl'] . '" target="_blank" rel="noopener">Ver detalles</a></p>';
  }
  $bases = '';
  if (!empty($datos['basesUrl'])) {
    $bases = '<p class="p">Bases: <a href="' . $datos['basesUrl'] . '" style="color:#8fd3ff" target="_blank" rel="noopener">' . $datos['basesUrl'] . '</a></p>';
  }

  $html = <<<HTML
<!doctype html><html lang="es"><head><meta charset="utf-8">
<meta name="color-scheme" content="dark light"><meta name="supported-color-schemes" content="dark light">
<title>{$subject}</title>
<style>
.container{max-width:620px;margin:0 auto;background:#0f1115;color:#e8eaf0;border-radius:16px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,.35);border:1px solid #1b1f2a}
.header{padding:24px 28px;background:linear-gradient(135deg,#0f1115 0%,#161a22 100%)}
.brand{display:flex;align-items:center;gap:12px}
.brand h1{margin:0;font-size:18px;letter-spacing:.5px;color:#8fd3ff}
.content{padding:28px}
.p{margin:0 0 12px;line-height:1.55;color:#cfd6e4}
.footer{padding:18px 28px;color:#98a6bd;font-size:12px;background:#0b0d12;border-top:1px solid #1b1f2a}
.details{margin:18px 0;padding:16px;border:1px solid #202637;border-radius:12px;background:linear-gradient(135deg,#141926,#0f1115)}
.row{margin:6px 0}
.preheader{display:none!important;opacity:0;color:transparent;height:0;width:0;overflow:hidden}
.btn{display:inline-block;background:#f1c40f;color:#0b0d12 !important;text-decoration:none;padding:12px 18px;border-radius:10px;font-weight:700;border:1px solid #d4ac0d}
</style></head>
<body style="background:#0b0d12;margin:0;padding:24px;font-family:Segoe UI,Roboto,Helvetica,Arial,sans-serif;">
  <div class="preheader">{$preheader}</div>
  <div class="container">
    <div class="header"><div class="brand">{$logoImg}<h1>Club de Ajedrez de Berriozar</h1></div></div>
    <div class="content">
      <p class="p" style="font-size:18px;">{$hello}</p>
      <p class="p">Tu inscripción ha sido <strong>confirmada</strong> para:</p>
      <div class="details">
        <div class="row"><strong>Torneo:</strong> {$torneo}</div>
        <div class="row"><strong>Fecha:</strong> {$fecha}</div>
        <div class="row"><strong>Lugar:</strong> {$lugar}</div>
      </div>
      {$btn}
      {$bases}
      <p class="p">¡Te deseamos buenas partidas y mejores combinaciones! ♟️</p>
    </div>
    <div class="footer">© {$year} Club de Ajedrez de Berriozar</div>
  </div>
</body></html>
HTML;

  $text = "$hello\n\n"
    . "Inscripción confirmada.\n"
    . "Torneo: $torneo\n"
    . "Fecha: $fecha\n"
    . "Lugar: $lugar\n";
  if (!empty($datos['detalleUrl'])) $text .= "Detalles: {$datos['detalleUrl']}\n";
  if (!empty($datos['basesUrl']))   $text .= "Bases: {$datos['basesUrl']}\n";
  return [$subject, $html, $text];
}

/**
 * Genera un evento .ics para adjuntar (calendarios).
 * $startIso/$endIso en formato 'YYYY-mm-dd HH:ii'
 */
function buildIcsEvent(string $summary, string $description, string $startIso, string $endIso, string $location): string
{
  $uid   = bin2hex(random_bytes(8)) . '@berriozar-ajedrez';
  $sum   = addcslashes($summary, ",;\\");
  $desc  = addcslashes($description, ",;\\n");
  $loc   = addcslashes($location, ",;\\");
  $dtS   = date('Ymd\THis\Z', strtotime($startIso));
  $dtE   = date('Ymd\THis\Z', strtotime($endIso));
  $now   = date('Ymd\THis\Z');

  return "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Berriozar Ajedrez//ES\r\nBEGIN:VEVENT\r\nUID:$uid\r\nDTSTAMP:$now\r\nDTSTART:$dtS\r\nDTEND:$dtE\r\nSUMMARY:$sum\r\nDESCRIPTION:$desc\r\nLOCATION:$loc\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n";
}
/**
 * Plantilla: Aviso de nuevo mensaje.
 * @return array [$subject, $html, $text]
 */
function buildNewMessageEmailTemplate(
  string $loginLink,
  ?string $destName = null,
  ?string $senderName = null,
  ?string $snippet = null,
  ?string $cidLogo = null
): array {
  $subject   = 'Nuevo mensaje' . ($senderName ? ' de ' . htmlspecialchars($senderName) : '');
  $hello     = $destName ? "Hola, " . htmlspecialchars($destName) . " 👋" : "Hola 👋";
  $preheader = 'Tienes un nuevo mensaje en el Club de Ajedrez de Berriozar.';
  $logoUrl   = defined('BASE_URL') ? (BASE_URL . 'Assets/img/logo-ajedrez.png') : '';
  $logoImg   = $cidLogo
    ? '<img src="cid:' . $cidLogo . '" alt="Club de Ajedrez de Berriozar" width="64" height="64" style="display:block;">'
    : ($logoUrl ? '<img src="' . $logoUrl . '" alt="Club de Ajedrez de Berriozar" width="64" height="64" style="display:block;">' : '');
  $year = date('Y');

  $snippetHtml = $snippet ? '<div class="box"><div class="p"><strong>Vista previa:</strong><br>' . nl2br(htmlspecialchars($snippet)) . '</div></div>' : '';

  $html = <<<HTML
<!doctype html><html lang="es"><head><meta charset="utf-8">
<meta name="color-scheme" content="dark light"><meta name="supported-color-schemes" content="dark light">
<title>{$subject}</title>
<style>
.container{max-width:620px;margin:0 auto;background:#0f1115;color:#e8eaf0;border-radius:16px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,.35);border:1px solid #1b1f2a}
.header{padding:24px 28px;background:linear-gradient(135deg,#0f1115 0%,#161a22 100%)}
.brand{display:flex;align-items:center;gap:12px}
.brand h1{margin:0;font-size:18px;letter-spacing:.5px;color:#8fd3ff}
.content{padding:28px}
.hi{font-size:18px;margin:0 0 12px}
.p{margin:0 0 12px;line-height:1.55;color:#cfd6e4}
.box{margin:18px 0;padding:14px 16px;background:linear-gradient(135deg,#141926,#0f1115);border:1px solid #202637;border-radius:12px}
.btn{display:inline-block;background:#2ecc71;color:#0b0d12 !important;text-decoration:none;padding:12px 18px;border-radius:10px;font-weight:700;border:1px solid #22a85b}
.small{color:#a6b0c3;font-size:12px}
.footer{padding:18px 28px;color:#98a6bd;font-size:12px;background:#0b0d12;border-top:1px solid #1b2130}
.preheader{display:none!important;opacity:0;color:transparent;height:0;width:0;overflow:hidden}
</style></head>
<body style="background:#0b0d12;margin:0;padding:24px;font-family:Segoe UI,Roboto,Helvetica,Arial,sans-serif;">
  <div class="preheader">{$preheader}</div>
  <div class="container">
    <div class="header">
      <div class="brand">{$logoImg}<h1>Club de Ajedrez de Berriozar</h1></div>
    </div>
    <div class="content">
      <p class="hi">{$hello}</p>
      <p class="p">Tienes un <strong>nuevo mensaje</strong> en la web del Club de Ajedrez de Berriozar.</p>
      <p class="p">Remitente: <strong>{$senderName}</strong></p>
      {$snippetHtml}
      <p style="margin:18px 0;">
        <a class="btn" href="{$loginLink}" target="_blank" rel="noopener">Iniciar sesión y ver mensaje</a>
      </p>
      <p class="small">Si el botón no funciona, copia y pega este enlace en tu navegador:<br>
        <a href="{$loginLink}" style="color:#8fd3ff" target="_blank" rel="noopener">{$loginLink}</a>
      </p>
    </div>
    <div class="footer">© {$year} Club de Ajedrez de Berriozar</div>
  </div>
</body></html>
HTML;

  $text = ($destName ? "Hola, $destName\n\n" : "Hola,\n\n")
    . "Tienes un nuevo mensaje"
    . ($senderName ? " de $senderName" : "")
    . ". Accede para verlo:\n$loginLink\n";
  if ($snippet) $text .= "\nVista previa:\n" . $snippet . "\n";

  return [$subject, $html, $text];
}
function buildAlertEmailTemplate(
  string $loginLink,
  string $titulo,
  ?string $cuerpo = null,
  ?string $ctaUrl = null,
  ?string $destName = null,
  ?string $cidLogo = null
): array {
  $subject   = 'Aviso: ' . $titulo;
  $hello     = $destName ? "Hola, " . htmlspecialchars($destName) . " 👋" : "Hola 👋";
  $preheader = 'Tienes un nuevo aviso en el Club de Ajedrez de Berriozar.';
  $logoUrl   = defined('BASE_URL') ? (BASE_URL . 'Assets/img/logo-ajedrez.png') : '';
  $logoImg   = $cidLogo
    ? '<img src="cid:' . $cidLogo . '" alt="Club de Ajedrez de Berriozar" width="64" height="64" style="display:block;">'
    : ($logoUrl ? '<img src="' . $logoUrl . '" alt="Club de Ajedrez de Berriozar" width="64" height="64" style="display:block;">' : '');
  $year = date('Y');
  $bodyHtml = $cuerpo ? nl2br(htmlspecialchars($cuerpo)) : 'Tienes un nuevo aviso en la web.';

  $ctaBtn = $ctaUrl ? '<a class="btn" href="' . htmlspecialchars($ctaUrl) . '" target="_blank" rel="noopener">Ver detalle</a>' : '';
  $html = <<<HTML
<!doctype html><html lang="es"><head><meta charset="utf-8">
<meta name="color-scheme" content="dark light"><meta name="supported-color-schemes" content="dark light">
<title>{$subject}</title>
<style>
.container{max-width:620px;margin:0 auto;background:#0f1115;color:#e8eaf0;border-radius:16px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,.35);border:1px solid #1b1f2a}
.header{padding:24px 28px;background:linear-gradient(135deg,#0f1115 0%,#161a22 100%)}
.brand{display:flex;align-items:center;gap:12px}
.brand h1{margin:0;font-size:18px;letter-spacing:.5px;color:#8fd3ff}
.content{padding:28px}
.hi{font-size:18px;margin:0 0 12px}
.p{margin:0 0 12px;line-height:1.55;color:#cfd6e4}
.box{margin:18px 0;padding:14px 16px;background:linear-gradient(135deg,#141926,#0f1115);border:1px solid #202637;border-radius:12px}
.btn{display:inline-block;background:#2ecc71;color:#0b0d12 !important;text-decoration:none;padding:12px 18px;border-radius:10px;font-weight:700;border:1px solid #22a85b}
.small{color:#a6b0c3;font-size:12px}
.footer{padding:18px 28px;color:#98a6bd;font-size:12px;background:#0b0d12;border-top:1px solid #1b1f2a}
.preheader{display:none!important;opacity:0;color:transparent;height:0;width:0;overflow:hidden}
</style></head>
<body style="background:#0b0d12;margin:0;padding:24px;font-family:Segoe UI,Roboto,Helvetica,Arial,sans-serif;">
  <div class="preheader">{$preheader}</div>
  <div class="container">
    <div class="header"><div class="brand">{$logoImg}<h1>Club de Ajedrez de Berriozar</h1></div></div>
    <div class="content">
      <p class="hi">{$hello}</p>
      <p class="p"><strong>{$titulo}</strong></p>
      <div class="box"><div class="p">{$bodyHtml}</div></div>
      <p style="margin:18px 0;">
        <a class="btn" href="{$loginLink}" target="_blank" rel="noopener">Iniciar sesión</a>
        {$ctaBtn}
      </p>
      <p class="small">Si el botón no funciona, copia y pega este enlace: <a href="{$loginLink}" style="color:#8fd3ff">{$loginLink}</a></p>
    </div>
    <div class="footer">© {$year} Club de Ajedrez de Berriozar</div>
  </div>
</body></html>
HTML;

  $text = ($destName ? "Hola, $destName\n\n" : "Hola,\n\n")
        . "Aviso: $titulo\n\n"
        . ($cuerpo ? ($cuerpo . "\n\n") : "")
        . "Inicia sesión: $loginLink\n"
        . ($ctaUrl ? ("Ver detalle: $ctaUrl\n") : "");

  return [$subject, $html, $text];
}
