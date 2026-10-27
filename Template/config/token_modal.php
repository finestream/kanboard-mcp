<div class="page-header">
    <h2><?= t('Add MCP token') ?></h2>
</div>
<form method="post" action="<?= $this->url->href('ModelContextProtocolController', 'generateToken', array('plugin' => 'ModelContextProtocol')) ?>" autocomplete="off">
    <?= $this->form->csrf() ?>

    <?= $this->form->label(t('Label'), 'label') ?>
    <?= $this->form->text('label', $values, array(), array('autofocus', 'maxlength="100"', 'placeholder="' . t('e.g. Claude Desktop on my laptop') . '"')) ?>

    <?= $this->form->label(t('Act as user'), 'user_id') ?>
    <?= $this->form->select('user_id', $users, $values) ?>
    <p class="form-help"><?= t('The MCP client gets exactly the project access and roles of the selected user. Tasks and comments are attributed to that user.') ?></p>

    <div class="form-actions">
        <button type="submit" class="btn btn-blue"><?= t('Generate Token') ?></button>
        <?= t('or') ?> <a href="#" class="js-modal-close"><?= t('cancel') ?></a>
    </div>
</form>
