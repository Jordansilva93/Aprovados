<?php

class Home extends CI_Controller {

    function __construct() {
        parent::__construct();
    }

    public function index() {
        $dados["head"] = "html/head_view";
        $dados["pagina"] = "index";
        $dados["script"] = "html/script_view";

        $this->load->view("html/index_view.php", $dados);
    }

    public function extrair() {
        if (isset($_FILES['arquivo'])) {
            $arquivo = $_FILES['arquivo'];
            $extensao = @end(explode('.', $arquivo["name"]));
            if (($extensao === "PDF") || ($extensao === "pdf")) {

                $montaExcel = $this->montaExcel($arquivo['tmp_name']);
                if ($montaExcel == true) {
                    $retorno["status"] = true;
                } else {
                    $retorno["status"] = false;
                    $retorno["mensagem"] = "PDF fora do padrao";
                    $retorno["notify"] = "warning";
                }
            } else {
                $retorno["status"] = false;
                $retorno["mensagem"] = "Extenção não corresponde";
                $retorno["notify"] = "warning";
            }
        } else {
            $retorno["status"] = false;
            $retorno["mensagem"] = "Arquivo Vazio";
            $retorno["notify"] = "warning";
        }
        echo (json_encode($retorno));
    }

    public function montaExcel($caminhoArquivo) {
        include 'vendor/autoload.php';

        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($caminhoArquivo);

        $regexp = '~\d{8},\s*[0-9a-zA-ZÀ-Úà-ú\s*]*,\s*[0-9a-zA-ZÀ-Úà-ú\s*]*,\s*[0-9a-zA-ZÀ-Úà-ú\s*.]*~';
        $total = preg_match_all($regexp, $pdf->getText(), $matches);

        if ($total > 0) {
            //Nova instancia da biblioteca
            $this->load->library('Excel');

            //Selecionando pasta de trabalho
            $this->excel->setActiveSheetIndex(0);

            //Titulo da pasta de trabalho
            $this->excel->getActiveSheet(0)->setTitle('Defeitos');
            $this->excel->getActiveSheet(0)->setCellValue('A1', 'Matricula');
            $this->excel->getActiveSheet(0)->setCellValue('B1', 'Nome');
            $this->excel->getActiveSheet(0)->setCellValue('C1', 'Número de Acertos');
            $this->excel->getActiveSheet(0)->setCellValue('D1', 'Nota Provisória');

            $contador_letra = 2;
            $PDC = false;
            for ($i = 0; $i < count($matches[0]); $i++) {
                $linha = explode(",", $matches[0][$i]);
                $matricula = $linha[0];
                $nome = str_replace(" ", "", str_replace("\n", "", $linha[1]));
                $acertos = str_replace(" ", "", str_replace("\n", "", $linha[2]));
                $nota = substr(str_replace(" ", "", str_replace("\n", "", $linha[3])), 0, 8);
                $divisor = substr(str_replace(" ", "", str_replace("\n", "", $linha[3])), 8, 1);
                $this->excel->getActiveSheet(0)->setCellValue('A' . $contador_letra, $matricula);
                $this->excel->getActiveSheet(0)->setCellValue('B' . $contador_letra, $nome);
                $this->excel->getActiveSheet(0)->setCellValue('C' . $contador_letra, $acertos);
                $this->excel->getActiveSheet(0)->setCellValue('D' . $contador_letra, $nota);

                if ($divisor == ".") {
                    if ($PDC == false) {
                        $PDC = true;
                        $contador_letra++;
                        $this->excel->getActiveSheet(0)->setCellValue('A' . $contador_letra, "cadidatos PDC");
                    }
                }
                $contador_letra++;
            }



            //$filename = 'aprovados.xls'; //save our workbook as this file name
            //header('Content-Type: application/vnd.ms-excel'); //mime type
            //header('Content-Disposition: attachment;filename="' . $filename . '"'); //tell browser what's the file name
            //header('Cache-Control: max-age=0'); //no cache
            //save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007' (and adjust the filename extension, also the header mime type)
            //if you want to save it as .XLSX Excel 2007 format
            $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
            //force user to download the Excel file without writing it to server's HD
            $objWriter->save('aprovados.xls');
            return TRUE;
        } else {
            return FALSE;
        }
    }

}
