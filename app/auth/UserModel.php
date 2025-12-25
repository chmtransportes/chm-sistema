<?php
/**
 * CHM Sistema - Model de Usuários
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 * @version 1.0.0
 */

namespace CHM\Auth;

use CHM\Core\Model;

class UserModel extends Model
{
    protected string $table = 'users';
    protected array $fillable = ['name', 'email', 'password', 'phone', 'profile', 'avatar', 'status'];
    protected array $hidden = ['password', 'reset_token', 'remember_token'];

    public function findByEmail(string $email): ?array
    {
        return $this->findBy('email', $email);
    }

    public function findByEmailWithPassword(string $email): ?array
    {
        $sql = "SELECT * FROM {$this->getTable()} WHERE email = :email AND deleted_at IS NULL LIMIT 1";
        return $this->db->fetchOne($sql, ['email' => $email]);
    }

    public function findByIdWithPassword(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->getTable()} WHERE id = :id LIMIT 1";
        return $this->db->fetchOne($sql, ['id' => $id]);
    }

    public function findByResetToken(string $token): ?array
    {
        $sql = "SELECT * FROM {$this->getTable()} WHERE reset_token = :token AND deleted_at IS NULL LIMIT 1";
        return $this->db->fetchOne($sql, ['token' => $token]);
    }

    public function incrementLoginAttempts(int $id): void
    {
        $sql = "UPDATE {$this->getTable()} SET login_attempts = login_attempts + 1 WHERE id = :id";
        $this->db->query($sql, ['id' => $id]);
    }

    public function resetLoginAttempts(int $id): void
    {
        $sql = "UPDATE {$this->getTable()} SET login_attempts = 0, locked_until = NULL WHERE id = :id";
        $this->db->query($sql, ['id' => $id]);
    }

    public function lockAccount(int $id): void
    {
        $lockUntil = date('Y-m-d H:i:s', time() + LOGIN_LOCKOUT_TIME);
        $sql = "UPDATE {$this->getTable()} SET locked_until = :locked_until WHERE id = :id";
        $this->db->query($sql, ['id' => $id, 'locked_until' => $lockUntil]);
    }

    public function updateLastLogin(int $id): void
    {
        $sql = "UPDATE {$this->getTable()} SET last_login = NOW() WHERE id = :id";
        $this->db->query($sql, ['id' => $id]);
    }

    public function setResetToken(int $id, string $token, string $expires): void
    {
        $sql = "UPDATE {$this->getTable()} SET reset_token = :token, reset_token_expires = :expires WHERE id = :id";
        $this->db->query($sql, ['id' => $id, 'token' => $token, 'expires' => $expires]);
    }

    public function clearResetToken(int $id): void
    {
        $sql = "UPDATE {$this->getTable()} SET reset_token = NULL, reset_token_expires = NULL WHERE id = :id";
        $this->db->query($sql, ['id' => $id]);
    }

    public function updatePassword(int $id, string $hashedPassword): void
    {
        $sql = "UPDATE {$this->getTable()} SET password = :password, updated_at = NOW() WHERE id = :id";
        $this->db->query($sql, ['id' => $id, 'password' => $hashedPassword]);
    }

    public function getByProfile(int $profile): array
    {
        return $this->where(['profile' => $profile, 'status' => 'active']);
    }

    public function getAdmins(): array
    {
        return $this->getByProfile(PROFILE_ADMIN);
    }

    public function getDriverUsers(): array
    {
        return $this->getByProfile(PROFILE_DRIVER);
    }

    public function getClientUsers(): array
    {
        return $this->getByProfile(PROFILE_CLIENT);
    }
}
