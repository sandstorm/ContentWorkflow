const esbuild = require('esbuild');
const isWatch = process.argv.includes('--watch');

/** @type {import("esbuild").BuildOptions} */
const options = {
    logLevel: "info",
    bundle: true,
    target: "es2020",
    entryPoints: { "Plugin": "src/index.js" },
    // add this loader mapping,
    // in case youre "missusing" javascript files as typescript-react files
    // - eg with `@neos` or `@connect` decorators
    //loader: { ".s": "tsx" },
    outdir: "../../Public/NeosUserInterface",
    //alias: extensibilityMap
}

if (isWatch) {
    esbuild.context(options).then((ctx) => ctx.watch())
} else {
    esbuild.build(options)
}
