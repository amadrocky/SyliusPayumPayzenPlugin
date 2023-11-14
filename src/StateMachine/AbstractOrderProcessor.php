<?php

declare(strict_types=1);

namespace Akki\SyliusPayumPayzenPlugin\StateMachine;

use Payum\Core\Payum;
use Payum\Core\Security\TokenFactoryInterface;
use Payum\Core\Security\TokenInterface;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;

abstract class AbstractOrderProcessor
{
    public const HANDLEABLE_GATEWAY = 'payzen';

    /** @var Payum */
    protected $payum;

    public function __construct(Payum $payum)
    {
        $this->payum = $payum;
    }

    protected function getGatewayNameFromPayment(PaymentInterface $payment): ?string
    {
        /** @var PaymentMethodInterface|null $paymentMethod */
        $paymentMethod = $payment->getMethod();
        if (null === $paymentMethod) {
            return null;
        }

        /** @var GatewayConfigInterface|null $gatewayConfig */
        $gatewayConfig = $paymentMethod->getGatewayConfig();
        if (null === $gatewayConfig) {
            return null;
        }

        $config = $gatewayConfig->getConfig();
        $factory = $config['factory'] ?? $gatewayConfig->getFactoryName();

        if ($factory !== self::HANDLEABLE_GATEWAY) {
            return null;
        }


        return $gatewayConfig->getGatewayName();
    }

    protected function buildToken(string $gatewayName, PaymentInterface $payment): TokenInterface
    {
        /** @var TokenFactoryInterface $tokenFactory */
        $tokenFactory = $this->payum->getTokenFactory();
        $token = $tokenFactory->createToken($gatewayName, $payment, 'sylius_shop_order_after_pay');

        return $token;
    }

    abstract public function __invoke(PaymentInterface $payment): void;
}