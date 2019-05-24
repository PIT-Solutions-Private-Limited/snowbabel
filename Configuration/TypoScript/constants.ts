
module.tx_snowbabel_translation {
    view {
        # cat=module.tx_snowbabel_translation/file; type=string; label=Path to template root (BE)
        templateRootPath = EXT:snowbabel/Resources/Private/Backend/Templates/
        # cat=module.tx_snowbabel_translation/file; type=string; label=Path to template partials (BE)
        partialRootPath = EXT:snowbabel/Resources/Private/Backend/Partials/
        # cat=module.tx_snowbabel_translation/file; type=string; label=Path to template layouts (BE)
        layoutRootPath = EXT:snowbabel/Resources/Private/Backend/Layouts/
    }
    persistence {
        # cat=module.tx_snowbabel_translation//a; type=string; label=Default storage PID
        storagePid =
    }
}

module.tx_snowbabel_settings {
    view {
        # cat=module.tx_snowbabel_settings/file; type=string; label=Path to template root (BE)
        templateRootPath = EXT:snowbabel/Resources/Private/Backend/Templates/
        # cat=module.tx_snowbabel_settings/file; type=string; label=Path to template partials (BE)
        partialRootPath = EXT:snowbabel/Resources/Private/Backend/Partials/
        # cat=module.tx_snowbabel_settings/file; type=string; label=Path to template layouts (BE)
        layoutRootPath = EXT:snowbabel/Resources/Private/Backend/Layouts/
    }
    persistence {
        # cat=module.tx_snowbabel_settings//a; type=string; label=Default storage PID
        storagePid =
    }
}
