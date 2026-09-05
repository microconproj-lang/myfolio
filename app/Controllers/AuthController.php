<?php

namespace MyFolio\Controllers;

use MyFolio\Core\Auth;
use MyFolio\Core\Controller;
use MyFolio\Core\Database;
use MyFolio\Models\User;

final class AuthController extends Controller
{
    public function login(): void
    {
        $this->view('auth/login', ['title' => 'เข้าสู่ระบบ']);
    }

    public function googleRedirect(): never
    {
        $oauth = require __DIR__ . '/../Config/oauth.php';
        if ($oauth['client_id'] === '' || $oauth['client_secret'] === '' || str_starts_with($oauth['client_id'], '{{') || str_starts_with($oauth['client_secret'], '{{') || !class_exists('Google\\Client')) {
            http_response_code(503);
            exit('Google OAuth is not configured. Set a real GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET in .env.');
        }

        $client = new \Google\Client();
        $client->setClientId($oauth['client_id']);
        $client->setClientSecret($oauth['client_secret']);
        $client->setRedirectUri($oauth['redirect_uri']);
        $client->setScopes(['openid', 'email', 'profile']);
        $_SESSION['oauth_state'] = bin2hex(random_bytes(24));
        $client->setState($_SESSION['oauth_state']);
        header('Location: ' . $client->createAuthUrl());
        exit;
    }

    public function googleCallback(): never
    {
        if (!isset($_GET['code'], $_GET['state']) || !hash_equals($_SESSION['oauth_state'] ?? '', (string) $_GET['state']) || !class_exists('Google\\Client')) {
            http_response_code(400);
            exit('Invalid Google OAuth callback.');
        }

        $oauth = require __DIR__ . '/../Config/oauth.php';
        $client = new \Google\Client();
        $client->setClientId($oauth['client_id']);
        $client->setClientSecret($oauth['client_secret']);
        $client->setRedirectUri($oauth['redirect_uri']);
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        if (isset($token['error'])) {
            http_response_code(401);
            exit('Google authentication failed.');
        }

        $client->setAccessToken($token);
        $profile = (new \Google\Service\Oauth2($client))->userinfo->get();
        $email = strtolower((string) $profile->email);
        $allowedDomain = strtolower((string) (getenv('ALLOWED_EMAIL_DOMAIN') ?: 'pccpl.ac.th'));
        if (!(bool) $profile->verifiedEmail || !str_ends_with($email, '@' . ltrim($allowedDomain, '@'))) {
            http_response_code(403);
            exit('บัญชี Google นี้ไม่ได้รับอนุญาตให้เข้าใช้งาน');
        }
        $adminEmail = strtolower((string) (getenv('ADMIN_EMAIL') ?: ''));
        $role = $email === $adminEmail ? 'admin' : 'public';
        if ($role !== 'admin') {
            try {
                $statement = Database::connection()->prepare('SELECT 1 FROM committee_emails WHERE email = :email LIMIT 1');
                $statement->execute(['email' => $email]);
                $role = $statement->fetchColumn() ? 'committee' : 'public';
            } catch (\PDOException) {
                $role = 'public';
            }
        }
        unset($_SESSION['oauth_state']);
        $storedUser = User::upsertGoogle((string) $profile->id, $email, (string) $profile->name, $profile->picture, $role);
        Auth::login(['id' => $storedUser['id'], 'name' => $storedUser['name'], 'email' => $storedUser['email'], 'avatar' => $storedUser['avatar_url'], 'role' => $role]);
        $this->redirect('/dashboard');
    }

    public function logout(): never
    {
        Auth::logout();
        $this->redirect('/');
    }
}