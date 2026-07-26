<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
/** @var string $blockKey */
?>
<div class="border-2 border-dashed border-yellow-400 bg-yellow-50 rounded-lg p-6 text-center">
    <p class="text-yellow-700 font-semibold">Bloque desconocido: <code class="font-mono"><?= esc($blockKey) ?></code></p>
    <p class="text-yellow-600 text-sm mt-1">No existe un template de preview para este tipo de bloque.</p>
</div>
