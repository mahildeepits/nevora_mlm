import * as esbuild from 'esbuild';

async function build() {
    try {
        console.log('Building WalletConnect bundle...');
        await esbuild.build({
            entryPoints: ['resources/js/walletconnect-entry.js'],
            bundle: true,
            minify: true,
            format: 'iife',
            globalName: 'NevoraWCBundle',
            outfile: 'public/assets/js/walletconnect.bundle.js',
            platform: 'browser',
            target: ['es2020'],
            define: {
                'process.env.NODE_ENV': '"production"',
                'global': 'window'
            }
        });
        console.log('WalletConnect bundle built successfully!');
    } catch (e) {
        console.error('Build failed:', e);
        process.exit(1);
    }
}

build();

