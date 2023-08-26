/** @type {import('ts-jest').JestConfigWithTsJest} */
module.exports = {
    preset: 'ts-jest',
    collectCoverageFrom: ['src/**/*.ts'],
    testEnvironment: 'jsdom',
    preset: 'ts-jest/presets/js-with-ts',
    transform: {
        // '^.+\\.[tj]sx?$' to process js/ts with `ts-jest`
        '^.+\\.[tj]sx?$': [
            'ts-jest', { tsconfig: 'src/tests/tsconfig.json' }
        ]
    },
};