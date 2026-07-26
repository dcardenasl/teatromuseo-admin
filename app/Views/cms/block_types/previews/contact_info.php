<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$addressLabel = esc($data['address_label'] ?? 'Address');
$address      = esc($data['address'] ?? '');
$phoneLabel   = esc($data['phone_label'] ?? 'Phone');
$phone        = esc($data['phone'] ?? '');
$emailLabel   = esc($data['email_label'] ?? 'Email');
$email        = esc($data['email'] ?? '');
$hoursLabel   = esc($data['hours_label'] ?? 'Office Hours');
$hours        = esc($data['hours'] ?? '');
$cssClass     = esc($config['css_class'] ?? '');
?>
<section class="py-10 <?= $cssClass ?>">
    <div class="max-w-5xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Contact details -->
            <div class="space-y-6">
                <?php if ($address): ?>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                            </svg>
                        </div>
                        <div>
                            <?php if ($addressLabel): ?>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide"><?= $addressLabel ?></p>
                            <?php endif; ?>
                            <p class="text-sm text-gray-800 mt-0.5"><?= $address ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($phone): ?>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/>
                            </svg>
                        </div>
                        <div>
                            <?php if ($phoneLabel): ?>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide"><?= $phoneLabel ?></p>
                            <?php endif; ?>
                            <p class="text-sm text-gray-800 mt-0.5"><?= $phone ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($email): ?>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5A2.25 2.25 0 0 1 19.5 19.5h-15a2.25 2.25 0 0 1-2.25-2.25V6.75"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 6.75 8.954 5.97a1.5 1.5 0 0 0 1.592 0l8.954-5.97"/>
                            </svg>
                        </div>
                        <div>
                            <?php if ($emailLabel): ?>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide"><?= $emailLabel ?></p>
                            <?php endif; ?>
                            <p class="text-sm text-gray-800 mt-0.5"><?= $email ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($hours): ?>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                        </div>
                        <div>
                            <?php if ($hoursLabel): ?>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide"><?= $hoursLabel ?></p>
                            <?php endif; ?>
                            <p class="text-sm text-gray-800 mt-0.5 whitespace-pre-line"><?= $hours ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (! $address && ! $phone && ! $email && ! $hours): ?>
                    <div class="p-4 bg-gray-50 border border-dashed border-gray-200 rounded-lg text-sm text-gray-400 text-center">
                        Configure address, phone, email, and hours in the form.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
