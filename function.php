use Spipu\Html2Pdf\Html2Pdf;
use setasign\Fpdi\Fpdi;

public function GetFactureParLotPDF(Request $request)
{
    $token = trim(htmlentities($request->get('token')));
    if ($token == '') {
        return $this->p->fetch_erreur('missing_param','token','');
    }

    $t = $this->p->mag_decrypt($token);
    $tiers = $this->p->getParamToken($t, 3);
    $email = $this->p->getParamToken($t, 1);
    $mois = htmlentities($request->get('mois'));
    $annee = htmlentities($request->get('annee'));

    // --- Récupération et traitement des factures ---
    $json = file_get_contents($this->p->getPath('backend').'facture/GetFacturesParLot?token='.$token.'&mois='.$mois.'&annee='.$annee.'&tiers='.$tiers);
    $factures = json_decode($json, true)["response"];

    if (empty($factures)) {
        return $this->p->fetch_erreur('facture_not_found','','');
    }

    // Données batch
    $ids_factures = array_map(fn($f) => htmlentities($f['NUM_MVT']), $factures);
    $ids_string = implode('||', $ids_factures);
    $url = $this->p->getPath('backend').'facture/GetFacturesDataBatch?token='.$token.'&ids_factures='.urlencode($ids_string).'&tiers='.$tiers;
    $json = file_get_contents($url);
    $facturesData = json_decode($json, true)["response"];
    $allTypes = $facturesData['types'] ?? [];
    $allSteGst = $facturesData['societes'] ?? [];

    $json = file_get_contents($this->p->getPath('backend').'facture/IsValideIce?token='.$token.'&tiers='.$tiers);
    $is_valide_ice = json_decode($json, true)["response"][0]['ICE'];

    // --- Génération PDF partiels ---
    $tempFiles = [];
    foreach ($factures as $r) {
        $id_facture = htmlentities($r['NUM_MVT']);
        $type = $allTypes[$id_facture]['TYPE_FACTURE'] ?? 'AUTRE';
        $ste_gst = $allSteGst[$id_facture]['TIERS_SOCIETE'] ?? '';

        $html = $this->generateFactureHtml($type, $id_facture, $tiers, $email, $token, $ste_gst, $is_valide_ice);
        $tempFile = sys_get_temp_dir() . "/facture_{$id_facture}.pdf";

        $pdf = new Html2Pdf('P','A4','fr');
        $pdf->writeHTML($html);
        $pdf->output($tempFile, 'F');

        $tempFiles[] = $tempFile;

        // Libérer mémoire
        unset($pdf);
        gc_collect_cycles();
    }

    // --- Fusion des fichiers PDF ---
    $merged = sys_get_temp_dir() . "/facture_lot_{$tiers}_{$mois}_{$annee}.pdf";
    $this->mergePdfs($tempFiles, $merged);

    // Supprimer les petits fichiers
    foreach ($tempFiles as $file) @unlink($file);

    return new Response(file_get_contents($merged), 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="factures_'.$mois.'_'.$annee.'.pdf"'
    ]);
}

private function mergePdfs(array $files, string $output)
{
    $pdf = new Fpdi();
    foreach ($files as $file) {
        $pageCount = $pdf->setSourceFile($file);
        for ($page = 1; $page <= $pageCount; $page++) {
            $tpl = $pdf->importPage($page);
            $size = $pdf->getTemplateSize($tpl);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tpl);
        }
    }
    $pdf->Output($output, 'F');
}
