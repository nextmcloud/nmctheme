declare global {
    interface Window {
        _oc_theme_l10n_overrides?: ThemeTranslations
        _oc_config: { version: string } & Record<string, any>
    }
}

export default global
