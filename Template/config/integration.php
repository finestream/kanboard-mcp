<div class="panel">
    <h3><i class="fa fa-cog fa-fw"></i>&nbsp;<?= t('Model Context Protocol') ?></h3>
    
    <p><?= t('Control Kanboard via Model Context Protocol.') ?></p>
    
    <?php 
    // A freshly generated token is handed over through the session and shown
    // exactly once; only its hash is stored in the database.
    $newToken = session_get('mcpNewToken');
    session_remove('mcpNewToken');
    $tokens = $this->task->mcpTokenModel->getAllActive();
    $usersById = array();
    foreach ($this->task->userModel->getAll() as $u) {
        $usersById[(int) $u['id']] = $u;
    }
    $hasUnrestricted = false;
    foreach ($tokens as $row) {
        if ((int) $row['user_id'] === 0) {
            $hasUnrestricted = true;
        }
    }
    $mcpUrl = $this->helper->url->base() . '?controller=ModelContextProtocolController&action=handle&plugin=ModelContextProtocol';
    ?>
    
    <?php if ($newToken !== null): ?>
    <div class="alert alert-success">
        <p><?= t('New token generated. Copy it now: it is stored hashed and will not be shown again.') ?></p>
    </div>
    <?php endif ?>

    <?php if ($newToken !== null || $tokens !== array()): ?>
    <div class="form-group">
        <label><strong><?= t('MCP Client Configuration') ?></strong></label>
        <textarea readonly 
                  class="form-control" 
                  style="font-family: monospace; height: 180px; resize: vertical; width: 100%;"
                  onclick="this.select();">{
  "mcpServers": {
    "kanboard": {
      "url": "<?= $this->text->e($mcpUrl) ?>",
      "headers": {
        "Authorization": "Bearer <?= $newToken !== null ? $this->text->e($newToken) : 'YOUR_TOKEN' ?>"
      }
    }
  }
}</textarea>
        <p class="form-help"><?= t('Copy this configuration to your MCP client settings (e.g., Claude Desktop app config). The token is sent as an Authorization header.') ?></p>
    </div>
    <?php endif ?>

    <?php if ($tokens === array()): ?>
    <p><?= t('No MCP token has been generated yet. Generate a token to enable MCP access.') ?></p>
    <?php else: ?>
    <table class="table-striped table-scrolling">
        <tr>
            <th><?= t('Label') ?></th>
            <th><?= t('Acts as') ?></th>
            <th><?= t('Created') ?></th>
            <th></th>
        </tr>
        <?php foreach ($tokens as $row): ?>
        <?php $boundUser = isset($usersById[(int) $row['user_id']]) ? $usersById[(int) $row['user_id']] : null ?>
        <tr>
            <td><?= $row['label'] !== '' ? $this->text->e($row['label']) : '<em>' . t('token #%d', $row['id']) . '</em>' ?></td>
            <td>
                <?php if ((int) $row['user_id'] === 0): ?>
                    <strong><?= t('Unrestricted') ?></strong>
                <?php elseif ($boundUser === null): ?>
                    <em><?= t('deleted user #%d', $row['user_id']) ?></em>
                <?php else: ?>
                    <?= $this->text->e($boundUser['name'] ?: $boundUser['username']) ?>
                    <?php if ((int) $boundUser['is_active'] !== 1): ?><em>(<?= t('disabled') ?>)</em><?php endif ?>
                <?php endif ?>
            </td>
            <td><?= $this->dt->datetime((int) $row['created_at']) ?></td>
            <td><?= $this->modal->confirm('trash-o', t('Revoke'), 'ModelContextProtocolController', 'confirmRevokeToken', array('plugin' => 'ModelContextProtocol', 'token_id' => $row['id'])) ?></td>
        </tr>
        <?php endforeach ?>
    </table>
    <?php endif ?>

    <?php if ($hasUnrestricted): ?>
    <div class="alert alert-warning">
        <p><strong><?= t('Security Note:') ?></strong> <?= t('An unrestricted token can read and change every project without any user permission check. Prefer tokens bound to a user.') ?></p>
    </div>
    <?php endif ?>

    <p>
        <?= $this->modal->mediumButton('plus', t('Add token'), 'ModelContextProtocolController', 'generateToken', array('plugin' => 'ModelContextProtocol')) ?>
    </p>
</div>
