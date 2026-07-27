<?php $booking = $booking ?? []; ?>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($booking)): ?>
<?php
    $itemId = (string) ($booking['id'] ?? '');
    $bookingLabel = ! empty($booking['guest_email'])
        ? lang('Bookings.bookings_title') . ' · ' . $booking['guest_email']
        : lang('Bookings.bookings_details');
    ?>

    <?= view('components/display/admin_page_header', [
            'backUrl' => route_to('admin.bookings.bookings'),
            'backLabel' => 'Bookings.bookings_title',
            'eyebrow' => 'Bookings.bookings_details',
            'title' => $bookingLabel,
        ]) ?>

    <?php ob_start(); ?>
    <section class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-700"><?= esc(lang('Bookings.bookings_details')) ?></h3>
        </div>
        <dl class="divide-y divide-gray-100">
            <?= view('components/display/field_row', [
                    'label' => 'Bookings.field_uuid',
                    'value' => $booking['uuid'] ?? '—'
                ]) ?>
            <?= view('components/display/field_row', [
                    'label' => 'Bookings.field_user_id',
                    'value' => $booking['user_id'] ?? '—'
                ]) ?>
            <?= view('components/display/field_row', [
                    'label' => 'Bookings.field_guest_email',
                    'value' => $booking['guest_email'] ?? '—'
                ]) ?>
            <?= view('components/display/field_row', [
                    'label' => 'Bookings.field_total_amount',
                    'value' => $booking['total_amount'] ?? '—'
                ]) ?>
            <?= view('components/display/field_row', [
                    'label' => 'Bookings.field_status',
                    'value' => ! empty($booking['status']) ? '<span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">' . esc($booking['status']) . '</span>' : '—',
                    'isHtml' => true
                ]) ?>
            <?= view('components/display/field_row', [
                    'label' => 'Bookings.field_reserved_until',
                    'value' => $booking['reserved_until'] ?? '—'
                ]) ?>
            <?= view('components/display/field_row', [
                    'label' => 'TableColumns.created_at',
                    'value' => $booking['created_at'] ?? '—',
                ]) ?>
        </dl>
    </section>
    <?php $mainContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <a href="<?= route_to('admin.bookings.bookings.edit', $itemId) ?>" class="<?= esc(action_button_class('primary')) ?>"><?= lang('App.edit') ?></a>

    <?php $actionsContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <form method="post" action="<?= route_to('admin.bookings.bookings.delete', $itemId) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($bookingLabel), 'js') ?>', () => $el.submit())">
        <?= csrf_field() ?>
        <button type="submit" class="<?= esc(action_button_class('danger')) ?>">
            <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
            <?= esc(lang('App.delete')) ?>
        </button>
    </form>
    <?php $dangerContent = ob_get_clean(); ?>

    <?= view('components/display/admin_resource_layout', [
            'main' => $mainContent,
            'aside' => view('components/display/admin_actions_panel', [
                'content' => $actionsContent,
                'dangerContent' => $dangerContent,
            ]),
        ]) ?>
<?php endif; ?>
