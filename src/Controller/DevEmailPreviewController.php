<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

class DevEmailPreviewController
{
    public function __construct(private readonly Environment $twig) {}

    #[Route('/dev/email/preview/verification', name: 'dev_email_preview_verification')]
    public function verificationCode(): Response
    {
        return new Response($this->twig->render('emails/verification_code.html.twig', [
            'code' => '847291',
        ]));
    }
}
