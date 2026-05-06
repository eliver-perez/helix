<?php

declare(strict_types=1);

namespace App\Pdf\Consent;

use TCPDF;

class ConsentPdf extends TCPDF
{
    private array $consentHeaderData = [];

    public function setConsentHeaderData(array $data): void
    {
        $this->consentHeaderData = $data;
    }

    public function Header(): void
    {
        $clinicName = $this->consentHeaderData['clinic_name'] ?? '';
        $logo = $this->consentHeaderData['logo'] ?? null;
        $logoWidth = (float)($this->consentHeaderData['logo_width'] ?? 30);

        if (
            is_array($logo) &&
            !empty($logo['tmp_name']) &&
            is_uploaded_file($logo['tmp_name'])
        ) {
            $this->Image($logo['tmp_name'], 20, 10, $logoWidth);
        }

        $this->SetFont('helvetica', 'B', 12);
        $this->SetTextColor(0, 0, 0);

        $safeClinicName = htmlspecialchars((string)$clinicName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $html = '
            <div style="text-align:center;">
                <strong>' . $safeClinicName . '</strong><br>
                <span style="font-size:10pt;">Consentimiento Informado</span>
            </div>
        ';

        $this->writeHTMLCell(
            0,
            0,
            45,
            16,
            $html,
            0,
            1,
            false,
            true,
            'C',
            true
        );
    }

    public function Footer(): void
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 7);

        $this->Cell(
            0,
            10,
            'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(),
            0,
            false,
            'R'
        );
    }
}