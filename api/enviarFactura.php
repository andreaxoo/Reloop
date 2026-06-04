<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once(__DIR__ . '/../libs/PHPMailer/Exception.php');
require_once(__DIR__ . '/../libs/PHPMailer/PHPMailer.php');
require_once(__DIR__ . '/../libs/PHPMailer/SMTP.php');

function EnviarFactura(
    $correoDestino,
    $nombreCliente,
    $rutaPDF,
    $idPedido
)
{
    $mail = new PHPMailer(true);

    try {

        $mail->isSMTP();

        $mail->Host = 'smtp.gmail.com';

        $mail->SMTPAuth = true;

        $mail->Username = 'ulicampos2@gmail.com';

        $mail->Password = 'hqobadqdcnzbdxau';

        $mail->SMTPSecure =
            PHPMailer::ENCRYPTION_STARTTLS;

        $mail->Port = 587;

        $mail->setFrom(
            'TU_CORREO@gmail.com',
            'RE-LOOP'
        );

        $mail->addAddress(
            $correoDestino,
            $nombreCliente
        );

        $mail->addAttachment($rutaPDF);

        $mail->isHTML(true);

        $mail->Subject =
            'Comprobante de compra RE-LOOP';

        $mail->Body = "
            <h2>Gracias por tu compra</h2>

            <p>
                Tu pedido #{$idPedido}
                fue registrado correctamente.
            </p>

            <p>
                Adjuntamos tu comprobante en PDF.
            </p>
        ";

        $mail->send();

        return true;

    } catch (Exception $e) {

        return false;

    }
}