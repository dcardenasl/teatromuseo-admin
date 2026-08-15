<form method="post" action="<?= site_url('reset-password') ?>" class="space-y-4">
    <?= csrf_field() ?>
    <input type="hidden" name="token" value="<?= esc($token ?? old('token')) ?>">
    <input type="hidden" name="email" value="<?= esc($email ?? old('email')) ?>">

    <?= view('components/form/password', [
        'name' => 'password',
        'label' => 'Auth.new_password',
        'autocomplete' => 'new-password',
        'required' => true,
    ]) ?>

    <?= view('components/form/password', [
        'name' => 'password_confirmation',
        'label' => 'Auth.confirm_password',
        'autocomplete' => 'new-password',
        'required' => true,
    ]) ?>

    <button type="submit" class="w-full rounded-lg bg-brand-600 text-white px-4 py-2 font-medium hover:bg-brand-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500"><?= lang('Auth.reset_button') ?></button>
</form>
