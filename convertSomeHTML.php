<?php
define('__ROOT__', dirname(__FILE__));
require_once(__ROOT__.'/includes/dompdf/autoload.inc.php');
require_once(__ROOT__.'/includes/tcpdf/tcpdf.php');
require_once(__ROOT__.'/includes/tcpdf/tcpdi.php');
require_once(__ROOT__.'/includes/fpdi/src/autoload.php');
use Dompdf\Dompdf;

function doConvert() {
    $input = resolveInput();
    if (isset($input['authKey'])) {
        if (isAuthenticated($input['authKey'])) {
            if (isset($input['html'])) {
                convertHTMLAndOutput($input['html']);
            }
            else if (isset($input['pages']) && is_array($input['pages'])) {
                handleInputPages($input['pages']);
            }
        }
        else {
            header("HTTP/1.1 403 Unauthorized");
        }
    }
}

function resolveInput() {
    $requestBody = file_get_contents('php://input');
    if (empty($requestBody)) {
        error_log("No Input");
        return;
    }
    $inputArray = json_decode($requestBody, true);
    return $inputArray;
}

function isAuthenticated($key) {
    $authKeys = array_diff(scandir(__ROOT__.'/apiKeys'), array('..', '.'));
    if (in_array($key, $authKeys)) {
        return true;
    }
    return false;
}

function handleInputPages($pageArray) {
    $pdfArray = [];
    foreach ($pageArray as $page) {
        $pdfArray[] = domPDFConversion($page);
    }
    $combinedPDF = mergePages($pdfArray);
    echo $combinedPDF;
}

function domPDFConversion($html) {
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    return $dompdf->output();
}

function mergePages($pageArray) {
    $pdf = new TCPDI('P', 'mm', 'A4', true, 'UTF-8', false, false);
    $pdf->SetTitle('Output.pdf');
    foreach ($pageArray as $page) {
        $pdf->setSourceData($page);
        $tplIdx = $pdf->importPage(1);
        $pdf->AddPage();
        $pdf->useTemplate($tplIdx, 0, 0, 0, 0, true);
    }
    $pdf->Output('Output.pdf', 'I');
}

function convertHTMLAndOutput($html) {
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream();
}

doConvert();
?>
