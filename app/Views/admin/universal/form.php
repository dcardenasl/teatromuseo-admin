<?= view('components/display/admin_page_header', [
    'backUrl' => '/admin/universal/' . rawurlencode((string) $resource),
    'backLabel' => 'Back to ' . (string) ($schema['title'] ?? ucfirst((string) $resource)) . ' List',
    'eyebrow' => 'Zero-Code Dynamic Administrative CRUD Console',
    'title' => (string) $title,
]) ?>

<form method="post" action="<?= $mode === 'create' ? "/admin/universal/" . esc($resource) : "/admin/universal/" . esc($resource) . "/" . esc($recordId) ?>" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <?= csrf_field() ?>

    <div class="lg:col-span-2">
        <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-bold text-gray-900"><?= esc($title) ?></h3>
            <div class="mt-6 space-y-5">

        <?php foreach ($schema['fields'] ?? [] as $field): ?>
            <?php
                $name = $field['name'] ?? '';
            if ($name === 'id' || $name === 'created_at' || $name === 'updated_at' || $name === 'deleted_at') {
                continue;
            }
            $type = $field['type'] ?? 'string';
            $titleAttr = $field['title'] ?? ucfirst($name);
            $required = ($field['required'] ?? false) ? 'required' : '';
            $val = old($name, $record[$name] ?? ($field['default'] ?? ''));
            $maxLength = $field['max_length'] ?? $field['maxlength'] ?? $field['maxLength'] ?? null;
            $maxLength = is_numeric($maxLength) && (int) $maxLength > 0 ? (int) $maxLength : null;
            ?>

            <div>
                <?php if ($type === 'boolean'): ?>
                    <label class="inline-flex items-center gap-3 rounded-lg border border-gray-200 w-full px-4 py-3 hover:bg-gray-50 cursor-pointer <?= esc(field_error_class($name, 'border-red-500 bg-red-50/40'), 'attr') ?>">
                        <input type="checkbox" name="<?= esc($name) ?>" value="1" <?= $val ? 'checked' : '' ?>
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4 <?= esc(field_error_class($name), 'attr') ?>"
                               <?= field_aria_attrs($name) ?>>
                        <div>
                            <span class="block text-sm font-semibold text-gray-900"><?= esc($titleAttr) ?></span>
                            <?php if (isset($field['description'])): ?>
                                <span class="block text-xs text-gray-500"><?= esc($field['description']) ?></span>
                            <?php endif; ?>
                        </div>
                    </label>
                <?php elseif ($type === 'text'): ?>
                    <label class="block text-sm font-medium text-gray-700" for="<?= esc($name) ?>"><?= esc($titleAttr) ?></label>
                    <textarea id="<?= esc($name) ?>" name="<?= esc($name) ?>" <?= $required ?> rows="4"
                              class="<?= esc(input_class($name)) ?>"
                              <?= $maxLength !== null ? 'maxlength="' . $maxLength . '"' : '' ?>
                              <?= field_aria_attrs($name, $required !== '') ?>><?= esc($val) ?></textarea>
                <?php elseif ($type === 'integer' || $type === 'number'): ?>
                    <label class="block text-sm font-medium text-gray-700" for="<?= esc($name) ?>"><?= esc($titleAttr) ?></label>
                    <input id="<?= esc($name) ?>" name="<?= esc($name) ?>" type="number" value="<?= esc($val) ?>" <?= $required ?>
                           class="<?= esc(input_class($name)) ?>"
                           <?= field_aria_attrs($name, $required !== '') ?>>
                <?php else: ?>
                    <label class="block text-sm font-medium text-gray-700" for="<?= esc($name) ?>"><?= esc($titleAttr) ?></label>
                    <input id="<?= esc($name) ?>" name="<?= esc($name) ?>" type="text" value="<?= esc($val) ?>" <?= $required ?>
                           class="<?= esc(input_class($name)) ?>"
                           <?= $maxLength !== null ? 'maxlength="' . $maxLength . '"' : '' ?>
                           <?= field_aria_attrs($name, $required !== '') ?>>
                <?php endif; ?>
                <?= render_field_error($name) ?>
            </div>
        <?php endforeach; ?>
            </div>
        </section>
    </div>

    <aside class="space-y-6">
        <?= view('components/display/admin_actions_panel', [
            'content' => '<button type="submit" class="' . esc(action_button_class('primary'), 'attr') . '">' . esc($mode === 'create' ? 'Create' : 'Save Changes') . '</button>'
                . '<a href="/admin/universal/' . esc((string) $resource, 'attr') . '" class="' . esc(action_button_class(), 'attr') . '">Cancel</a>',
        ]) ?>
    </aside>
</form>
