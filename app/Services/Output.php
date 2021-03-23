<?php


namespace App\Services;


use Illuminate\Support\Facades\Log;
use Mpdf\Mpdf;
use Spatie\PdfToImage\Pdf;

class Output
{
    private string $batalha;

    public function __construct(string $batalha)
    {
        $this->batalha = $batalha;
    }

    public function getBatalhaOutput(): array
    {
        $this->getBatalhaPDF();
        return $this->getBatalhaPJG();
    }

    public function deleteOutputs(array $caminhoIMG)
    {
        unlink ( '/tmp/batalha.pdf');
        foreach ($caminhoIMG as $img){
            unlink ($img);
        }
    }

    private function getBatalhaPDF(): void
    {
        $batalhaCompleta = $this->htmlBatalha();
        $mpdf = new Mpdf();
        $mpdf->WriteHTML($batalhaCompleta);
        $mpdf->Output('/tmp/batalha.pdf','F');
    }

    private function getBatalhaPJG()
    {
        try {
            $files = ['status' => true];
            $pdf = new Pdf('/tmp/batalha.pdf');

            foreach (range(1, $pdf->getNumberOfPages()) as $pageNumber) {
                $pdf->setPage($pageNumber)
                    ->setCompressionQuality(100)
                    ->saveImage('/tmp/imgBatalha/batalha_'.$pageNumber.'.jpg');

                array_push($files, '/tmp/imgBatalha/batalha_'.$pageNumber.'.jpg');
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
