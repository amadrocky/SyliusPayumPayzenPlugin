<?php

namespace Akki\SyliusPayumPayzenPlugin\Action;

use Akki\SyliusPayumPayzenPlugin\Request\Request;
use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Sync;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;

/**
 * Class CaptureAction
 * @package Akki\SyliusPayumPayzenPlugin\Action
 */
class CaptureAction implements ActionInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface
{
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param Capture $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());


        if ($request->getToken()) {
            // Done redirections
            $targetUrl = $request->getToken()->getTargetUrl();
            $doneUrlFields = [
                'vads_url_cancel',   // Annuler et retourner à la boutique
                'vads_url_error',    // Erreur de traitement interne
                'vads_url_referral', // 02 contacter l'émetteur de la carte
                'vads_url_refused',  // Refus autre que 02
                'vads_url_success',  // 00 Success
                'vads_url_return',   // Retour à la boutique
            ];
            foreach ($doneUrlFields as $field) {
                if (false == $model[$field]) {
                    $model[$field] = $targetUrl;
                }
            }

            // Notify url
            if (empty($model['vads_url_check']) && $this->tokenFactory) {
                $notifyToken = $this->tokenFactory->createNotifyToken(
                    $request->getToken()->getGatewayName(),
                    $request->getToken()->getDetails()
                );
                $model['vads_url_check'] = $notifyToken->getTargetUrl();
            }

            $model['vads_redirect_success_timeout'] = 0;
            $model['vads_redirect_success_message'] = 'Redirection vers Kiosquemag dans quelques secondes...';
            $model['vads_redirect_error_timeout'] = 1;
            $model['vads_redirect_error_message'] = 'Redirection vers Kiosquemag dans quelques secondes...';
        }

        if (false == $model['vads_trans_id']) {
            $this->gateway->execute(new Request($model));
        }

        $this->gateway->execute(new Sync($model));
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request): bool
    {
        return $request instanceof Capture
            && $request->getModel() instanceof ArrayAccess;
    }
}
