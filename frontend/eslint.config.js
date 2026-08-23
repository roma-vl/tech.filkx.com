import pluginVue from "eslint-plugin-vue";
import tseslint from "typescript-eslint";
import eslintConfigPrettier from "eslint-config-prettier";

export default [
  { ignores: ["dist/**", "coverage/**", "**/*.d.ts"] },
  ...pluginVue.configs["flat/recommended"],
  {
    // Plain .ts files aren't matched by eslint-plugin-vue's config, so they need
    // the TS parser wired up directly as their main parser.
    files: ["**/*.ts"],
    languageOptions: {
      parser: tseslint.parser,
    },
  },
  {
    // .vue files are already parsed by vue-eslint-parser (via flat/recommended above);
    // this only swaps the parser it delegates <script>/<script setup> blocks to.
    files: ["**/*.vue"],
    languageOptions: {
      parserOptions: {
        parser: tseslint.parser,
      },
    },
  },
  {
    rules: {
      "vue/multi-word-component-names": "off",
      "vue/no-unused-vars": "warn",
      "no-unused-vars": "warn",
    },
  },
  // Must stay last: disables eslint-plugin-vue's stylistic rules (html-indent,
  // html-self-closing, max-attributes-per-line, etc.) that otherwise fight Prettier
  // and get undone/reapplied in a fix-loop between `eslint --fix` and `prettier --write`.
  eslintConfigPrettier,
];
