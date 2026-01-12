module.exports = {
  customSyntax: 'postcss-scss',
  plugins: ['stylelint-scss'],
  rules: {
    // Beispiel: SCSS-spezifische Regeln
    'scss/at-rule-no-unknown': true,

    // Optional: allgemeine CSS-Regeln, die auch in SCSS Sinn machen
    'block-no-empty': true,
  },
  ignoreFiles: [
    '**/*.min.css',
    '**/dist/**',
    '**/vendor/**',
  ],
}