<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends Controller
{
    /**
     * Try to test this security when the one on the bottom works Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @Route("/demo/security/login/standard/secured", name="demo_secured_page_standard")
     * @Method({"GET"})
     */
    public function index()
    {
        $user = $this->getUser();

        return $this->render('login/index.html.twig', ['user' => $user, ]);
    }

    /**
     * Standard Symfony authentification system for a fronted in PHP
     *
     * @Route("/demo/security/login/standard", name="demo_login_standard")
     *
     * @param AuthenticationUtils $authUtils
     * @param CsrfTokenManagerInterface $tokenManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginStandard(AuthenticationUtils $authUtils, CsrfTokenManagerInterface $tokenManager)
    {
        // get the login error if there is one
        $error = $authUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authUtils->getLastUsername();

        // token for csrf protection (no need to check validity from request coz it's up to Symfony to do this with
        // internal mecanisms
        $tokenId = $this->getParameter('csrf_token_id');
        $token = $tokenManager->getToken($tokenId);

        return $this->render('login/login.html.twig', array(
            'last_username' => $lastUsername,
            'token'         => $token,
            'error'         => $error,
        ));
    }
}
