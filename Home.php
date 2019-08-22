<?php

if (isset($_FILES['arquivo'])) {
    $arquivo = $_FILES['arquivo'];
    $extensao = @end(explode('.', $arquivo["name"]));
    if (($extensao === "PDF") || ($extensao === "pdf")) {

        include 'vendor/autoload.php';

        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($arquivo["tmp_name"]);

        $regexp = '~\d{8},\s*[0-9a-zA-ZÀ-Úà-ú\s*]*,\s*[0-9a-zA-ZÀ-Úà-ú\s*]*,\s*[0-9a-zA-ZÀ-Úà-ú\s*.]*~';
        $total = preg_match_all($regexp, $pdf->getText(), $matches);

        if ($total > 0) {
            //Nova instancia da biblioteca
            include("PHPExcel/Classes/PHPExcel.php");
            $objReader = new PHPExcel();

            //Selecionando pasta de trabalho
            $objReader->setActiveSheetIndex(0);

            //Titulo da pasta de trabalho
            $objReader->getActiveSheet(0)->setTitle('Defeitos');
            $objReader->getActiveSheet(0)->setCellValue('A1', 'Matricula');
            $objReader->getActiveSheet(0)->setCellValue('B1', 'Nome');
            $objReader->getActiveSheet(0)->setCellValue('C1', 'Número de Acertos');
            $objReader->getActiveSheet(0)->setCellValue('D1', 'Nota Provisória');

            $contador_letra = 2;
            $PDC = false;
            for ($i = 0; $i < count($matches[0]); $i++) {
                $linha = explode(",", $matches[0][$i]);
                $matricula = $linha[0];
                $nome = str_replace(" ", "", str_replace("\n", "", $linha[1]));
                $acertos = str_replace(" ", "", str_replace("\n", "", $linha[2]));
                $nota = substr(str_replace(" ", "", str_replace("\n", "", $linha[3])), 0, 8);
                $divisor = substr(str_replace(" ", "", str_replace("\n", "", $linha[3])), 8, 1);
                $objReader->getActiveSheet(0)->setCellValue('A' . $contador_letra, $matricula);
                $objReader->getActiveSheet(0)->setCellValue('B' . $contador_letra, $nome);
                $objReader->getActiveSheet(0)->setCellValue('C' . $contador_letra, $acertos);
                $objReader->getActiveSheet(0)->setCellValue('D' . $contador_letra, $nota);

                if ($divisor == ".") {
                    if ($PDC == false) {
                        $PDC = true;
                        $contador_letra++;
                        $objReader->getActiveSheet(0)->setCellValue('A' . $contador_letra, "cadidatos PDC");
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
            $objWriter = PHPExcel_IOFactory::createWriter($objReader, 'Excel5');
            //force user to download the Excel file without writing it to server's HD
            $objWriter->save('aprovados.xls');
            $montaExcel = true;
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


