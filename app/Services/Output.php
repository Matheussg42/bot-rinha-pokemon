<?php


namespace App\Services;


use Illuminate\Support\Facades\Log;
use Mpdf\Mpdf;
use Spatie\PdfToImage\Pdf;

class Output
{
    private string $batalha;
    private string $folder;

    public function __construct(string $batalha)
    {
        $this->batalha = $batalha;
        $this->folder = env('RESOURCE_FOLDER');
    }

    public function getBatalhaOutput(): array
    {
        $this->getBatalhaPDF();
        return $this->getBatalhaPJG();
    }

    public function deleteOutputs(array $caminhoIMG)
    {
        unlink ( "{$this->folder}/batalha.pdf");
        foreach ($caminhoIMG as $img){
            unlink ($img);
        }
    }

    private function getBatalhaPDF(): void
    {
        $batalhaCompleta = $this->htmlBatalha();
        $mpdf = new Mpdf();
        $mpdf->WriteHTML($batalhaCompleta);
        $mpdf->Output("{$this->folder}/batalha.pdf",'F');
    }

    private function getBatalhaPJG()
    {
        try {
            $files = ['status' => true];
            $pdf = new Pdf("{$this->folder}/batalha.pdf");

            foreach (range(1, $pdf->getNumberOfPages()) as $pageNumber) {
                $pdf->setPage($pageNumber)
                    ->setCompressionQuality(100)
                    ->saveImage("{$this->folder}/img/batalha_{$pageNumber}.jpg");

                array_push($files, "{$this->folder}/img/batalha_{$pageNumber}.jpg");
            }

            return $files;
        }catch (\Throwable $throwable){
            Log::Warning("Error no getBatalhaPJG: {$throwable}");
            var_dump($throwable);
            return ['status' => false];
        }
    }

    private function htmlBatalha(): string
    {
        return "<!DOCTYPE html>
            <html lang='pt-BR'>
            <head style='background: #b5b5b5;'>
                <!-- Required meta tags -->
                <meta charset='utf-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1'>

                <title>Batalha</title>

                <!-- CSS only -->
                <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css' rel='stylesheet' integrity='sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl' crossorigin='anonymous'>
                <!-- <style>
                @page {
                    background: url('') no-repeat 0 0;
                    background-image-resize: 6;
                }
                </style> -->
            </head>
            <body style='background-color: #b5b5b5; font-size: 17px;'>
                <div class='container' style='font-weight: bold;'>
                    <div class='text-center'>
                        {$this->batalha}
                    </div>
                </div>

            </body>
        </html>";
    }

}
