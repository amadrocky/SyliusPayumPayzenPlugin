<?php

namespace Akki\SyliusPayumPayzenPlugin\Action;

use Akki\SyliusPayumPayzenPlugin\Request\GetHumanStatus;
use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Cancel;

/**
 * Class CancelAction
 * @package Akki\SyliusPayumPayzenPlugin\Action
 */
class CancelAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param Cancel $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute($status = new GetHumanStatus($model));

        if ($status->isNew()) {
            $model['state_override'] = 'canceled';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request): bool
    {
        return $request instanceof Cancel
            && $request->getModel() instanceof ArrayAccess;
    }
}
