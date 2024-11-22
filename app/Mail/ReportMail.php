<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $filePath;
    public $fileName;

    /**
     * Создание нового экземпляра письма.
     */
    public function __construct($filePath, $fileName)
    {
        $this->filePath = $filePath;
        $this->fileName = $fileName;
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
            <title>Отчет по активности</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    background-color: #f9f9f9;
                    color: #333;
                    margin: 0;
                    padding: 20px;
                }
                .container {
                    max-width: 600px;
                    margin: 20px auto;
                    background: #fff;
                    border-radius: 8px;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                    padding: 20px;
                    border: 2px solid #4F81BD; /* Добавлена рамка */
                }
                h1 {
                    color: #4F81BD;
                    text-align: center;
                }
                p {
                    margin: 10px 0;
                }
                .footer {
                    margin-top: 20px;
                    font-size: 0.9em;
                    color: #555;
                    text-align: center;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <h1>Отчет по активности</h1>
                <p>Здравствуйте!</p>
                <p>К вашему вниманию отчет по активности за указанный период.</p>
                <p>Файл отчета прикреплен к этому письму.</p>
                <div class='footer'>
                    <p>С уважением,<br>Горн Денис, 1511б</p>
                </div>
            </div>
        </body>
        </html>
        ";

        return $this->html($htmlContent) // письмо в формате html
            ->subject('Отчет по активности') // Тема письма
            ->attach($this->filePath, [
                'as' => $this->fileName,
                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // MIME-тип файла, указывающий, что это Excel-документ
            ]);
    }
}