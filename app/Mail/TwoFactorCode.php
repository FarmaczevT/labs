<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TwoFactorCode extends Mailable
{
    use Queueable, SerializesModels;

    public $code;

    /**
     * Создание нового экземпляра письма.
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * Построение письма.
     */
    public function build()
    {
        $htmlContent = "
        <!DOCTYPE html>
        <html lang='ru'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Код подтверждения</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f7fc;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    width: 100%;
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    background-color: #fff;
                    border-radius: 8px;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                }
                .header {
                    text-align: center;
                    margin-bottom: 30px;
                }
                .header h1 {
                    font-size: 24px;
                    color: #333;
                    margin: 0;
                }
                .content {
                    font-size: 16px;
                    color: #555;
                    line-height: 1.6;
                }
                .code {
                    font-size: 22px;
                    font-weight: bold;
                    color: #2d87f0;
                    display: inline-block;
                    background-color: #e6f0ff;
                    padding: 10px;
                    border-radius: 5px;
                    margin: 10px 0;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Код двухфакторной аутентификации</h1>
                </div>
                <div class='content'>
                    <p>Здравствуйте!</p>
                    <p>Ваш код двухфакторной аутентификации: <span class='code'>{$this->code}</span></p>
                    <p>Код действителен в течение " . env('TFA_CODE_EXPIRATION') . " секунд.</p>
                    <p>Если вы не запрашивали этот код, просто проигнорируйте это сообщение.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        return $this
            ->subject('Код подтверждения') // Тема письма
            ->html($htmlContent); // Письмо в формате HTML
    }
}