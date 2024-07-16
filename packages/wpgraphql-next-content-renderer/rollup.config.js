import { typescriptPaths } from 'rollup-plugin-typescript-paths';
import resolve from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';
import typescript from '@rollup/plugin-typescript';
import { dts } from "rollup-plugin-dts";
import terser from "@rollup/plugin-terser";

const packageJson = require("./package.json");

export default [
  {
    input: 'src/index.ts',
    output: [
      {
        file: packageJson.exports["."].require,
        format: "cjs",
        sourcemap: true,
        exports: 'named',
      },
      {
        file: packageJson.exports["."].import,
        format: "esm",
        sourcemap: true,
        exports: 'named',
      },
    ],
    plugins: [
      typescript({ tsconfig: './tsconfig.json' }), // This tells the plugin to prefer built-in modules (like 'path') over local ones
      resolve({ extensions: [".tsx", ".ts"] }),
      commonjs(),
      terser(),
      typescriptPaths()
    ],
    external: [
      'next',
      'next/script',
      'next/server',
      'react',
      'react-dom',
      'lodash',
    ],
  },
  {
    input: "src/index.ts",
    output: [{
      file: packageJson.exports["."].types,
      format: "es",
    }],
    plugins: [
      dts(),
      typescript({ tsconfig: './tsconfig.json' }),
      resolve({ extensions: [".tsx", ".ts"] }),
      typescriptPaths()
    ],
    external: [
      'next',
      'next/script',
      'next/server',
      'react',
      'react-dom',
      'lodash',
    ],
  },
  {
    input: 'src/withWCR/index.ts',
    output: [
      {
        file: packageJson.exports["./withWCR"].require,
        format: "cjs",
        sourcemap: true,
        exports: 'named',
      },
      {
        file: packageJson.exports["./withWCR"].import,
        format: "esm",
        sourcemap: true,
        exports: 'named',
      },
    ],
    plugins: [
      typescript({ tsconfig: './tsconfig.json' }), // This tells the plugin to prefer built-in modules (like 'path') over local ones
      resolve({ extensions: [".tsx", ".ts"] }),
      commonjs(),
      terser(),
      typescriptPaths()
    ],
  },
  {
    input: "src/withWCR/index.ts",
    output: [{
      file: packageJson.exports["./withWCR"].types,
      format: "es",
    }],
    plugins: [
      dts(),
      typescript({ tsconfig: './tsconfig.json' }),
      resolve({ extensions: [".tsx", ".ts"] }),
      typescriptPaths()
    ],
  },
];