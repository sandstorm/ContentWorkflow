const esbuild = require('esbuild')
//const extensibilityMap = require("@neos-project/neos-ui-extensibility/extensibilityMap.json");
const isWatch = process.argv.includes('--watch')

/** @type {import('esbuild').BuildOptions} */
const options = {
    logLevel: 'info',
    bundle: true,
    target: 'es2020',
    entryPoints: {
        'BackendModule': 'Resources/Private/JavaScript/BackendModule.ts',
    },

    // add this loader mapping,
    // in case youre "missusing" javascript files as typescript-react files
    // - eg with `@neos` or `@connect` decorators
    loader: { '.js': 'tsx' },
    outdir: 'Resources/Public/built',
//    alias: extensibilityMap
}

if (isWatch) {
    esbuild.context(options).then((ctx) => ctx.watch())
} else {
    esbuild.build(options)
}
