<?php

namespace App\Enums;

enum MoroccanBank: string
{
    case ATTIJARI = 'ATTIJARI';
    case CIH = 'CIH';
    case BP = 'BP';
    case BMCE = 'BMCE';
    case CREDIT_DU_MAROC = 'CREDIT DU MAROC';
    case BARID_BANK = 'BARID BANK';
    case CREDIT_AGRICOLE = 'CREDIT AGRICOLE';
    case SOCIETE_GENERALE = 'SOCIETE GENERALE';
    case BMCI = 'BMCI';
    case CFG_BANK = 'CFG BANK';
    case AL_AKHDAR_BANK = 'AL AKHDAR BANK';
    case BANK_OF_AFRICA = 'BANK OF AFRICA';
    case UMNIA_BANK = 'UMNIA BANK';
    case BTI_BANK = 'BTI BANK';
    case CASH_PLUS = 'CASH PLUS';

    public function label(): string
    {
        return $this->value;
    }
}
