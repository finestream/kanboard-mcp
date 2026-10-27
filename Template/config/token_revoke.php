<div class="page-header">
    <h2><?= t('Revoke MCP token') ?></h2>
</div>

<div class="confirm">
    <p class="alert alert-info">
        <?= t('Do you really want to revoke this token: "%s"? Clients using it stop working immediately.', $row['label'] !== '' ? $row['label'] : t('token #%d', $row['id'])) ?>
        <?php if ($user !== null): ?>
            (<?= t('acts as %s', $user['username']) ?>)
        <?php endif ?>
    </p>

    <?= $this->modal->confirmButtons(
        'ModelContextProtocolController',
        'revokeToken',
        array('plugin' => 'ModelContextProtocol', 'token_id' => $row['id']),
        t('Revoke')
    ) ?>
</div>
