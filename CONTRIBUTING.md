# Contributing

Use the standard package commands before sending changes:

```bash
composer fix
composer test
composer test-bc
```

What they do:

- `composer fix` runs Rector, PHP CS Fixer, and composer normalization.
- `composer test` installs the regular dependency set and runs the validation suite.
- `composer test-bc` installs the lowest supported dependency set and runs the same validation suite.

Please report bugs and feature requests through GitHub issues and send code changes as pull requests.
