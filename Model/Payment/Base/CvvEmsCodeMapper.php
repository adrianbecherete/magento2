<?php

namespace Signifyd\Connect\Model\Payment\Base;

use Signifyd\Connect\Model\Payment\DataMapper;

class CvvEmsCodeMapper extends DataMapper
{
    /**
     * Valid expected CVV codes
     *
     * @var array
     */
    protected $validCvvResponseCodes = ['M', 'N', 'P', 'S', 'U'];

    /**
     * Gets payment CVV verification code.
     *
     * @param \Magento\Sales\Model\Order $order
     * @return null|string
     */
    public function getPaymentData(\Magento\Sales\Model\Order $order)
    {
        $cidStatus = $order->getPayment()->getCcCidStatus();
        $cidStatus = $this->validate($cidStatus) ? $cidStatus : null;

        $message = 'CVV found on base mapper: ' . (empty($cidStatus) ? 'false' : $cidStatus);
        $this->logger->debug($message, ['entity' => $order]);

        return $cidStatus;
    }

    public function validate($response)
    {
        return in_array($response, $this->validCvvResponseCodes);
    }
}
