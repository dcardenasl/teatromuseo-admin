<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-3"><?= lang('Dashboard.system_status') ?></h3>
    <div class="divide-y divide-gray-100">
        <?php foreach ($healthServices as $svc): ?>
            <?= view('dashboard/partials/health_card', ['health' => $svc['health'], 'name' => $svc['name']]) ?>
        <?php endforeach; ?>
    </div>
</section>
