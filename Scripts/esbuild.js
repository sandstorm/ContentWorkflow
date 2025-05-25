const {cssModules} = require('esbuild-plugin-lightningcss-modules');

const esbuild = require('esbuild')
const extensibilityMap = require("@neos-project/neos-ui-extensibility/extensibilityMap.json");
const isWatch = process.argv.includes('--watch')

/** @type {import('esbuild').BuildOptions} */
const options = {
    logLevel: 'info',
    bundle: true,
    target: 'es2020',
    entryPoints: {
        //'BackendModule': 'Resources/Private/JavaScript/BackendModule.ts',
        'ContentModule': 'Resources/Private/JavaScript/ContentModule.tsx',
    },
    // external: ['@neos-project/positional-array-sorter'],


    // add this loader mapping,
    // in case youre "missusing" javascript files as typescript-react files
    // - eg with `@neos` or `@connect` decorators
    loader: { '.js': 'tsx' },
    outdir: 'Resources/Public/built',
    alias: extensibilityMap,
    plugins: [
        cssModules({
            // Add your own or other plugins in the "visitor" section see
            // https://lightningcss.dev/transforms.html
            // visitor: myLightningcssPlugin(),
            // customAtRules: { myLigningCssRule1: {...} }
            targets: {
                chrome: 80 // aligns somewhat to es2020
            },
            drafts: {
                nesting: true
            },
            // You can set here your own settings for cssModules
            // https://lightningcss.dev/css-modules.html#local-css-variables
            // https://lightningcss.dev/css-modules.html#custom-naming-patterns
            // cssModules: {
            //    dashedIdents: true,
            //    pattern: 'my-company-[name]-[hash]-[local]'
            // },
        })
    ]
}

if (isWatch) {
    esbuild.context(options).then((ctx) => ctx.watch())
} else {
    esbuild.build(options)
}
