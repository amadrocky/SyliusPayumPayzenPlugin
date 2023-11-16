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
