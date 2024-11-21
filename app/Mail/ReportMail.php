<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $filePath;

    /**
     * Создание нового экземпляра письма.
     */
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Построение письма.
     */
    public function build()
    {
        $htmlContent = "
            <!DOCTYPE html>
            <html>
            <head>
                <title>Отчет по активности</title>
            </head>
            <body>
                <h1>Отчет по активности</h1>
                <p>Здравствуйте!</p>
                <p>К вашему вниманию отчет по активности за указанный период.</p>
                <p>Файл отчета прикреплен к этому письму.</p>
                <p>С уважением, команда приложения.</p>
            </body>
            </html>
        ";

        return $this->html($htmlContent)
            ->subject('Отчет по активности')
            ->attach($this->filePath, [
                'as' => 'report.xlsx',
                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
    }
}