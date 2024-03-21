declare global {
    interface Window {
        _oc_theme_l10n_overrides?: ThemeTranslations
        _oc_config: { version: string } & Record<string, any>
        OCA: { Theming: {cacheBuster: string }, Viewer: any, Files: any }
        OC: { requestToken: string }
    }
    const utag: {
        view: (data?: any) => boolean
    }
}

export default global
