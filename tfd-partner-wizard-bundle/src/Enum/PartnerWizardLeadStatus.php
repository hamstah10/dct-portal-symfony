<?php

namespace Tfd\PartnerWizardBundle\Enum;

enum PartnerWizardLeadStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case ToolConsulting = 'tool_consulting';
    case DealerReview = 'dealer_review';
    case Won = 'won';
    case Lost = 'lost';
}
