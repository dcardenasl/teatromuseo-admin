const fs = require('fs');
const path = require('path');

const destDir = path.join(__dirname, '../public/assets/vendor');

if (!fs.existsSync(destDir)) {
    fs.mkdirSync(destDir, { recursive: true });
}

const filesToCopy = [
    { src: 'node_modules/alpinejs/dist/cdn.min.js', dest: 'alpine.min.js' },
    { src: 'node_modules/lucide/dist/umd/lucide.min.js', dest: 'lucide.min.js' },
    { src: 'node_modules/sortablejs/Sortable.min.js', dest: 'sortable.min.js' }
];

filesToCopy.forEach(f => {
    const srcPath = path.join(__dirname, '..', f.src);
    const destPath = path.join(destDir, f.dest);
    try {
        fs.copyFileSync(srcPath, destPath);
        console.log(`Successfully copied ${f.src} to public/assets/vendor/${f.dest}`);
    } catch (err) {
        console.error(`Error copying ${f.src}:`, err);
        process.exit(1);
    }
});

// Bundle Tiptap via esbuild JS API (avoids shell/pnpm interception)
console.log('Bundling Tiptap...');
const esbuild = require('esbuild');
esbuild.build({
    entryPoints: [path.join(__dirname, '../src/js/tiptap-bundle.js')],
    bundle: true,
    format: 'iife',
    outfile: path.join(destDir, 'tiptap.bundle.js'),
    minify: true,
}).then(() => {
    console.log('Successfully bundled Tiptap to public/assets/vendor/tiptap.bundle.js');
}).catch(err => {
    console.error('Error bundling Tiptap:', err);
    process.exit(1);
});
