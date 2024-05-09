<?php
namespace App\Suspensions;

use App\Abstracts\AbstractSuspension;

/**
 * Class that define a general pending suspension message
 */
class GeneralPendingSuspension extends AbstractSuspension{

    public function __construct()
    {
        $this->reason_en = "Pending Approval";
        $this->reason_ar = "بانتظار الموافقة";
        $this->type = "pending";
    }
}
?>
