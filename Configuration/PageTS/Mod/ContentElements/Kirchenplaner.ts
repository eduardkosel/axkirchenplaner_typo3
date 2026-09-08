mod {
    wizards.newContentElement.wizardItems.extra {
        header = Kirchenplaner
        elements {
            axkirchenplaner_termine {
                iconIdentifier = kirchenplaner
                icon = ../typo3conf/ext/axkirchenplaner/Resources/Public/Icons/Kirchenplaner.png
                title = Kirchenplaner
                description = www.kirchenplaner.de
                tt_content_defValues {
                    CType = axkirchenplaner_termine
                }
            }
        }
        show = *
    }
}