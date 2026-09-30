# Kirchenplaner for TYPO3 14

TYPO3 extension for integrating the Kirchenplaner API.

## Requirements

- TYPO3 14
- PHP 8.2 or newer

## Composer installation

Until the package is published on Packagist, add the GitHub repository once:

```bash
composer config repositories.axkirchenplaner vcs https://github.com/eduardkosel/axkirchenplaner_typo3.git
composer require axist/axkirchenplaner:^3.0
vendor/bin/typo3 extension:setup
```

Include the static TypoScript template **Kirchenplaner**, then configure the API key in the extension configuration.

## Classic installation

Download `axkirchenplaner_3.0.1.zip` from the GitHub release and upload it in the TYPO3 Extension Manager. The custom release archive has the extension files directly at its root, as required by the Extension Manager.
