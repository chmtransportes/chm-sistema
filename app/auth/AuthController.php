<?php
/**
 * CHM Sistema - Controller de Autenticação
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 * @version 1.0.0
 */

namespace CHM\Auth;

use CHM\Core\Controller;
use CHM\Core\Session;
use CHM\Core\Validator;
use CHM\Core\Helpers;

class AuthController extends Controller
{
    private UserModel $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();
    }

    // Página de login
    public function showLogin(): void
    {
        if (Session::isAuthenticated()) {
            $this->redirect(APP_URL . 'dashboard');
        }
        $this->setTitle('Login');
        $this->view('auth.login', [], false);
    }

    // Processar login
    public function login(): void
    {
        if (!$this->validateCsrf()) {
            $this->error('Token de segurança inválido.');
            return;
        }

        $validator = new Validator($this->all());
        $validator->rule('email', 'required|email', 'E-mail');
        $validator->rule('password', 'required|min:6', 'Senha');

        if (!$validator->validate()) {
            $this->error($validator->getFirstError(), 400, $validator->getErrors());
            return;
        }

        $email = $this->sanitize($this->input('email'));
        $password = $this->input('password');

        // Busca usuário com senha
        $user = $this->userModel->findByEmailWithPassword($email);

        if (!$user) {
            Helpers::logAction('Login falhou - usuário não encontrado', 'auth', null, ['email' => $email]);
            $this->error('E-mail ou senha incorretos.');
            return;
        }

        // Verifica se está bloqueado
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            $minutes = ceil((strtotime($user['locked_until']) - time()) / 60);
            $this->error("Conta bloqueada. Tente novamente em {$minutes} minutos.");
            return;
        }

        // Verifica status
        if ($user['status'] !== 'active') {
            $this->error('Conta desativada. Entre em contato com o administrador.');
            return;
        }

        // Verifica senha
        if (!password_verify($password, $user['password'])) {
            $this->userModel->incrementLoginAttempts($user['id']);
            
            $attempts = $user['login_attempts'] + 1;
            if ($attempts >= MAX_LOGIN_ATTEMPTS) {
                $this->userModel->lockAccount($user['id']);
                Helpers::logAction('Conta bloqueada por tentativas excessivas', 'auth', $user['id']);
                $this->error('Conta bloqueada por excesso de tentativas. Tente novamente em 15 minutos.');
                return;
            }

            Helpers::logAction('Login falhou - senha incorreta', 'auth', $user['id']);
            $this->error('E-mail ou senha incorretos.');
            return;
        }

        // Login bem sucedido
        $this->userModel->resetLoginAttempts($user['id']);
        $this->userModel->updateLastLogin($user['id']);

        // Configura sessão
        Session::set('user_id', $user['id']);
        Session::set('user_name', $user['name']);
        Session::set('user_email', $user['email']);
        Session::set('user_profile', $user['profile']);
        Session::set('user_avatar', $user['avatar']);

        Helpers::logAction('Login realizado com sucesso', 'auth', $user['id']);

        // Retorna URL de redirecionamento baseado no perfil
        $redirectUrl = match($user['profile']) {
            PROFILE_ADMIN => APP_URL . 'dashboard',
            PROFILE_DRIVER => APP_URL . 'driver/dashboard',
            PROFILE_CLIENT => APP_URL . 'client/dashboard',
            default => APP_URL . 'dashboard'
        };

        $this->success('Login realizado com sucesso!', ['redirect' => $redirectUrl]);
    }

    // Logout
    public function logout(): void
    {
        $userId = Session::getUserId();
        if ($userId) {
            Helpers::logAction('Logout realizado', 'auth', $userId);
        }
        
        Session::destroy();
        $this->redirect(APP_URL . 'login');
    }

    // Página esqueci minha senha
    public function showForgotPassword(): void
    {
        $this->setTitle('Esqueci minha senha');
        $this->view('auth.forgot-password');
    }

    // Processar esqueci minha senha
    public function forgotPassword(): void
    {
        if (!$this->validateCsrf()) {
            $this->error('Token de segurança inválido.');
            return;
        }

        $email = $this->sanitize($this->input('email'));

        $validator = new Validator(['email' => $email]);
        $validator->rule('email', 'required|email', 'E-mail');

        if (!$validator->validate()) {
            $this->error($validator->getFirstError());
            return;
        }

        $user = $this->userModel->findBy('email', $email);

        if ($user) {
            $token = Helpers::generateToken(64);
            $expires = date('Y-m-d H:i:s', time() + TOKEN_LIFETIME);
            
            $this->userModel->setResetToken($user['id'], $token, $expires);

            // TODO: Enviar e-mail com link de recuperação
            // Por enquanto, logamos o token
            Helpers::logAction('Solicitação de recuperação de senha', 'auth', $user['id'], ['token' => $token]);
        }

        // Sempre retorna sucesso para não revelar se o e-mail existe
        $this->success('Se o e-mail estiver cadastrado, você receberá as instruções para redefinir sua senha.');
    }

    // Página de redefinição de senha
    public function showResetPassword(string $token): void
    {
        $user = $this->userModel->findByResetToken($token);

        if (!$user || strtotime($user['reset_token_expires']) < time()) {
            Session::flash('error', 'Link de recuperação inválido ou expirado.');
            $this->redirect(APP_URL . 'forgot-password');
            return;
        }

        $this->setTitle('Redefinir senha');
        $this->setData('token', $token);
        $this->view('auth.reset-password');
    }

    // Processar redefinição de senha
    public function resetPassword(): void
    {
        if (!$this->validateCsrf()) {
            $this->error('Token de segurança inválido.');
            return;
        }

        $token = $this->input('token');
        $password = $this->input('password');
        $passwordConfirmation = $this->input('password_confirmation');

        $user = $this->userModel->findByResetToken($token);

        if (!$user || strtotime($user['reset_token_expires']) < time()) {
            $this->error('Link de recuperação inválido ou expirado.');
            return;
        }

        $validator = new Validator([
            'password' => $password,
            'password_confirmation' => $passwordConfirmation
        ]);
        $validator->rule('password', 'required|min:6', 'Senha');
        $validator->rule('password', 'confirmed', 'Senha');

        if (!$validator->validate()) {
            $this->error($validator->getFirstError());
            return;
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
        $this->userModel->updatePassword($user['id'], $hashedPassword);
        $this->userModel->clearResetToken($user['id']);

        Helpers::logAction('Senha redefinida com sucesso', 'auth', $user['id']);

        $this->success('Senha redefinida com sucesso! Faça login com sua nova senha.', ['redirect' => APP_URL . 'login']);
    }

    // Página de troca de senha (usuário logado)
    public function showChangePassword(): void
    {
        $this->requireAuth();
        $this->setTitle('Alterar senha');
        $this->view('auth.change-password');
    }

    // Processar troca de senha
    public function changePassword(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            $this->error('Token de segurança inválido.');
            return;
        }

        $currentPassword = $this->input('current_password');
        $newPassword = $this->input('new_password');
        $newPasswordConfirmation = $this->input('new_password_confirmation');

        $validator = new Validator([
            'current_password' => $currentPassword,
            'new_password' => $newPassword,
            'new_password_confirmation' => $newPasswordConfirmation
        ]);
        $validator->rule('current_password', 'required', 'Senha atual');
        $validator->rule('new_password', 'required|min:6', 'Nova senha');
        $validator->rule('new_password', 'confirmed', 'Nova senha');

        if (!$validator->validate()) {
            $this->error($validator->getFirstError());
            return;
        }

        $user = $this->userModel->findByIdWithPassword(Session::getUserId());

        if (!password_verify($currentPassword, $user['password'])) {
            $this->error('Senha atual incorreta.');
            return;
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
        $this->userModel->updatePassword($user['id'], $hashedPassword);

        Helpers::logAction('Senha alterada pelo usuário', 'auth', $user['id']);

        $this->success('Senha alterada com sucesso!');
    }
}
