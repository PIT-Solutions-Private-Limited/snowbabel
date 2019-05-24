
# Module configuration
module.tx_snowbabel_user_snowbabeltranslation {
    persistence {
        storagePid = {$module.tx_snowbabel_translation.persistence.storagePid}
    }
    view {
        templateRootPaths.0 = EXT:snowbabel/Resources/Private/Backend/Templates/
        templateRootPaths.1 = {$module.tx_snowbabel_translation.view.templateRootPath}
        partialRootPaths.0 = EXT:snowbabel/Resources/Private/Backend/Partials/
        partialRootPaths.1 = {$module.tx_snowbabel_translation.view.partialRootPath}
        layoutRootPaths.0 = EXT:snowbabel/Resources/Private/Backend/Layouts/
        layoutRootPaths.1 = {$module.tx_snowbabel_translation.view.layoutRootPath}
    }
}

# Module configuration
module.tx_snowbabel_user_snowbabelsettings {
    persistence {
        storagePid = {$module.tx_snowbabel_settings.persistence.storagePid}
    }
    view {
        templateRootPaths.0 = EXT:snowbabel/Resources/Private/Backend/Templates/
        templateRootPaths.1 = {$module.tx_snowbabel_settings.view.templateRootPath}
        partialRootPaths.0 = EXT:snowbabel/Resources/Private/Backend/Partials/
        partialRootPaths.1 = {$module.tx_snowbabel_settings.view.partialRootPath}
        layoutRootPaths.0 = EXT:snowbabel/Resources/Private/Backend/Layouts/
        layoutRootPaths.1 = {$module.tx_snowbabel_settings.view.layoutRootPath}
    }
}
