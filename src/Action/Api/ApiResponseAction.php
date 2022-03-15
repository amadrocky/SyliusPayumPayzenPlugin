<?php

namespace Akki\SyliusPayumPayzenPlugin\Action\Api;

use Akki\SyliusPayumPayzenPlugin\Request\Response;
use ArrayAccess;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHttpRequest;

/**
 * Class ResponseAction
 * @package Akki\SyliusPayumPayzenPlugin\Action\Api
 */
class ApiResponseAction extends AbstractApiAction
{
    /**
     * @inheritdoc
     */
    public function execute($request): void
    {
        /** @var Response $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute($httpRequest = new GetHttpRequest());

        if (isset($httpRequest->request['vads_result'])) {
            $data = $httpRequest->request;
        } elseif (isset($httpRequest->query['vads_result'])) {
            $data = $httpRequest->query;
        } else {
            return;
        }

        $this->logResponseData($data);

        // Check amount
        if ($model['vads_amount'] !== $data['vads_amount']) {
            return;
        }

        // Check the response signature
        if ($this->api->checkResponseIntegrity($data)) {
            // Update the payment details
            $model->replace($data);
            $request->setModel($model);
        }
    }

    /**
     * Logs the response data.
     *
     * @param array $data
     */
    private function logResponseData(array $data): void
    {
        $this->logData("[Payzen] Response", $data, [
            'vads_order_id',
            'vads_trans_id',
            'vads_amount',
            'vads_auth_result',
            'vads_auth_mode',
            'vads_auth_number',
            'vads_validation_mode',
            'vads_result',
            'vads_extra_result',
            'vads_warranty_result',
        ]);
    }

    /**
     * @inheritdec
     * @param $request
     * @return bool
     */
    public function supports($request): bool
    {
        return $request instanceof Response
            && $request->getModel() instanceof ArrayAccess;
    }
}
