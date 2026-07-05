<?php

declare(strict_types=1);

namespace App\UI\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class LocaleSwitchController extends AbstractController
{
    private const SUPPORTED_LOCALES = ['fr', 'en', 'es', 'pt'];

    #[Route('/langue/{locale}', name: 'locale_switch', requirements: ['locale' => 'fr|en|es|pt'], methods: ['GET'])]
    public function __invoke(string $locale, Request $request): RedirectResponse
    {
        if (!in_array($locale, self::SUPPORTED_LOCALES, true)) {
            throw $this->createNotFoundException();
        }

        $request->getSession()->set('_locale', $locale);
        $target = $request->headers->get('referer') ?: $this->generateUrl('home');
        $response = new RedirectResponse($target);
        $response->headers->setCookie(Cookie::create('monpetittour_locale', $locale, '+1 year'));

        return $response;
    }
}
