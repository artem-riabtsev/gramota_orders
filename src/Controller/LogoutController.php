<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;

final class LogoutController
{
    #[Route('/logout', name: 'app_logout')]
    public function logout(): never
    {
        throw new \LogicException();
    }
}