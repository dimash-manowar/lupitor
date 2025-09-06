<?php
declare(strict_types=1);

final class Pdf
{
    /**
     * Renderiza y sirve un PDF en el navegador.
     * @param string $html
     * @param string $filename      Nombre del archivo.
     * @param string $paper         Tamaño papel ('A4','A5','letter','legal', etc).
     * @param string $orientation   'portrait'|'landscape'
     * @param bool   $inline        true = inline (Attachment=false), false = descarga.
     */
    public static function stream(
        string $html,
        string $filename = 'documento.pdf',
        string $paper = 'A4',
        string $orientation = 'portrait',
        bool $inline = true
    ): void {
        // Asegura autoload de Composer si hace falta
        if (!class_exists(\Dompdf\Dompdf::class)) {
            $auto = (defined('BASE_PATH') ? rtrim(BASE_PATH,'/\\') : __DIR__.'/..') . '/vendor/autoload.php';
            if (is_file($auto)) require_once $auto;
        }

        $opt = new \Dompdf\Options();
        $opt->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($opt);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper($paper, $orientation);
        $dompdf->render();
        $dompdf->stream($filename, ['Attachment' => $inline ? false : true]);
    }
}
