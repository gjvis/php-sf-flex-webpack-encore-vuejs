<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\InMemoryUserProvider;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;

class LoginJwtController extends Controller
{
    /**
     * The route that generate token for a couple login/password
     * It works with Basi HTTP auth or with formData using login/password where path are store in parameters: login_username_path/login_password_path
     *
     * @Route("/demo/security/login/jwt/tokens")
     * @Method({"POST"})
     */
    public function newTokenAction(Request $request, InMemoryUserProvider $provider, JWTEncoderInterface $encoder)
    {
        $username = $request->getUser() ? : $request->request->get($this->getParameter('login_username_path'));
        $password = $request->getPassword() ? : $request->request->get($this->getParameter('login_password_path'));

        if (!$username) {
            $json = json_decode($request->getContent(), true);
            if (!json_last_error()) {
                $username = $json[$this->getParameter('login_username_path')];
                $password = $json[$this->getParameter('login_password_path')];
            }
        }

        $user = $provider->loadUserByUsername($username);

        if (!$user) {
            throw $this->createNotFoundException();
        }

        $isValid = $this->get('security.password_encoder')
            ->isPasswordValid($user, $password);

        if (!$isValid) {
            throw new BadCredentialsException();
        }

        $token = $encoder->encode([
            'username' => $username,
            'exp' => time() * $this->getParameter('token_jwt_ttl')
        ]);

        return new JsonResponse(['token' => $token]);
    }

    /**
     * New Json authentification system from Symfony 3.3
     * It relies on App\Security\ApiKeyAuthenticator for CSRF checks
     *
     * @Route("/demo/security/login/json", name="demo_login_json")
     *
     * @return JsonResponse
     */
    public function loginJson()
    {
        return new JsonResponse();
    }

    /**
     * Try to test this security when the one on the bottom works Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * call it with .json extension and check if you have a 200
     *
     * @todo: should we let it as is, or always return a 200 and in the Json content set the isLoggedIn to 0 or 1 ?
     * For instance i stay on my first choice
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @Route(
     *     "/demo/security/login/json/isloggedin",
     *     name="demo_secured_page_is_logged_in",
     *     )
     * @Method({"GET"})
     */
    public function isLoggedIn()
    {
        // will be usefull if we decide to return always 200 + the real Json content represented by isLoggedIn: 0|1
        $authenticated = $this->isGranted('IS_AUTHENTICATED_FULLY');
        $data = ['isLoggedIn' => (int)$authenticated, ];

        if ($authenticated) {
            $user = $this->getUser();
            $data['me'] = [
                'username' => $user->getUsername(),
                'roles' => $user->getRoles(),
                ];
        }

        return new JsonResponse($data);
    }
}
