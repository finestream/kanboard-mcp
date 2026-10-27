<?php

namespace Kanboard\Plugin\ModelContextProtocol\Model;

use Kanboard\Core\Base;

/**
 * MCP Token Model
 *
 * Only a SHA-256 hash of the token is stored; the token itself is shown
 * once, right after it has been generated.
 *
 * @package  Kanboard\Plugin\ModelContextProtocol\Model
 * @author   Plugin Author
 */
class McpTokenModel extends Base
{
    const TABLE = 'mcp_tokens';

    /**
     * Hash a token for storage and comparison
     *
     * @param  string $token
     * @return string
     */
    public static function hashToken($token)
    {
        return hash('sha256', (string) $token);
    }

    /**
     * Get all active token rows without the secret, oldest first
     *
     * @return array[]  each with id, user_id, label, created_at
     */
    public function getAllActive()
    {
        return $this->db->table(self::TABLE)
            ->columns('id', 'user_id', 'label', 'created_at')
            ->eq('is_active', 1)
            ->asc('id')
            ->findAll();
    }

    /**
     * Get one active token row without the secret
     *
     * @param  integer $id
     * @return array|null
     */
    public function getActiveById($id)
    {
        $row = $this->db->table(self::TABLE)
            ->columns('id', 'user_id', 'label', 'created_at')
            ->eq('id', (int) $id)
            ->eq('is_active', 1)
            ->findOne();

        return empty($row) ? null : $row;
    }

    /**
     * Find the active token row matching the presented token
     *
     * @param  mixed $token
     * @return array|null  ['id' => int, 'user_id' => int] or null when invalid
     */
    public function findActiveTokenRow($token)
    {
        if (empty($token) || ! is_string($token)) {
            return null;
        }

        $hash = self::hashToken($token);

        $rows = $this->db->table(self::TABLE)
            ->columns('id', 'user_id', 'token')
            ->eq('is_active', 1)
            ->findAll();

        foreach ($rows as $row) {
            if (hash_equals((string) $row['token'], $hash)) {
                return ['id' => (int) $row['id'], 'user_id' => (int) ($row['user_id'] ?? 0)];
            }
        }

        return null;
    }
    
    /**
     * Validate token
     */
    public function validateToken($token)
    {
        return $this->findActiveTokenRow($token) !== null;
    }
    
    /**
     * Generate an additional token; existing tokens stay valid
     *
     * @param  integer $userId  Kanboard user the token acts as (0 = unrestricted)
     * @param  string  $label   Free text to tell tokens apart (e.g. the client name)
     * @return string|false     The new token (not stored anywhere else), or false
     */
    public function generateToken($userId = 0, $label = '')
    {
        $token = bin2hex(random_bytes(32));

        $saved = $this->db->table(self::TABLE)->persist([
            'token' => self::hashToken($token),
            'user_id' => (int) $userId,
            'label' => mb_substr(trim((string) $label), 0, 100),
            'is_active' => 1,
            'created_at' => time(),
        ]);

        return $saved ? $token : false;
    }

    /**
     * Revoke one token
     *
     * @param  integer $id
     * @return boolean  false when there is no such active token
     */
    public function revoke($id)
    {
        if ($this->getActiveById($id) === null) {
            return false;
        }

        return $this->db->table(self::TABLE)
            ->eq('id', (int) $id)
            ->update(['is_active' => 0]);
    }
    
    /**
     * Check if token exists
     */
    public function tokenExists()
    {
        return $this->db->table(self::TABLE)
            ->eq('is_active', 1)
            ->exists();
    }
} 
