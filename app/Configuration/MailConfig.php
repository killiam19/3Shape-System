<?php 
header('Content-Type: text/html; charset=UTF-8');
?>
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/autoload.php';

class MailConfig {
    private $mail;
    
    public function __construct() {
        $this->mail = new PHPMailer(true);
        
        try {
            // Configuración del servidor
            $this->mail->isSMTP();
            $this->mail->Host = 'smtp.gmail.com';
            $this->mail->SMTPAuth = true;
            $this->mail->Username = 'killiam1119@gmail.com';
            $this->mail->Password = 'oqon pjgg ekvm yptj';
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port = 587;
            
            // Configuración de caracteres
            $this->mail->CharSet = 'UTF-8';
            $this->mail->Encoding = 'base64';
            
            // Configuración adicional para SSL/TLS
            $this->mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            // Configuración del remitente
            $this->mail->setFrom('killiam1119@gmail.com', 'Sistema de Gestión de Activos');
            $this->mail->isHTML(true);
            
            // Habilitar modo debug
            $this->mail->SMTPDebug = 2;
            $this->mail->Debugoutput = function($str, $level) {
                error_log("PHPMailer Debug: $str");
            };
        } catch (Exception $e) {
            error_log("Error en la configuración de PHPMailer: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function sendPasswordResetEmail($to, $resetToken) {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($to);
            $this->mail->Subject = 'Recuperación de Contraseña';
            
            // Obtener la URL base del proyecto
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'];
            $projectFolder = '/3Shape_project';
            
            // Construir la URL completa
            $resetLink = $protocol . $host . $projectFolder . "./app/View/reset_password.php?token=" . $resetToken;
            
            // Plantilla HTML con caracteres especiales
            $this->mail->Body = "
                <!DOCTYPE html>
                <html lang='es'>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <title>Recuperación de Contraseña</title>
                </head>
                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                        <h2 style='color: #0d6efd;'>Recuperación de Contraseña</h2>
                        <p>Has solicitado restablecer tu contraseña. Haz clic en el siguiente enlace para continuar:</p>
                        <p style='margin: 20px 0;'>
                            <a href='{$resetLink}' style='display: inline-block; padding: 10px 20px; background-color: #0d6efd; color: white; text-decoration: none; border-radius: 5px;'>
                                Restablecer Contraseña
                            </a>
                        </p>
                        <p>O copia y pega este enlace en tu navegador:</p>
                        <p style='word-break: break-all;'>{$resetLink}</p>
                        <p><strong>Importante:</strong> Si no solicitaste este cambio, por favor ignora este correo.</p>
                        <p>Este enlace expirará en 1 hora.</p>
                        <hr style='border: 1px solid #eee; margin: 20px 0;'>
                        <p style='font-size: 12px; color: #666;'>
                            Este es un correo automático, por favor no respondas a este mensaje.
                        </p>
                    </div>
                </body>
                </html>
            ";
            
            // Versión en texto plano para clientes que no soporten HTML
            $this->mail->AltBody = "
                Recuperación de Contraseña
                
                Has solicitado restablecer tu contraseña. 
                Para continuar, visita el siguiente enlace:
                
                {$resetLink}
                
                Si no solicitaste este cambio, por favor ignora este correo.
                Este enlace expirará en 1 hora.
                
                Este es un correo automático, por favor no respondas a este mensaje.
            ";
            
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Error al enviar correo: " . $e->getMessage());
            throw new Exception("Error al enviar el correo: " . $e->getMessage());
        }
    }
} 