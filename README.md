<p align="center">
    <a href="https://www.supportpal.com" target="_blank"><img src="https://www.supportpal.com/assets/img/logo_blue_small.png" /></a>
    <br>
    A PHP port of <a href="https://github.com/opentable/accept-language-parser">npm:accept-language-parser</a> and an equivalent to the ext-intl locale_accept_from_http function.
</p>

<p align="center">
<a href="https://github.com/supportpal/accept-language-parser/actions"><img src="https://img.shields.io/github/workflow/status/supportpal/accept-language-parser/ci" alt="Build Status"></a>
<a href="https://packagist.org/packages/supportpal/accept-language-parser"><img src="https://img.shields.io/packagist/dt/supportpal/accept-language-parser" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/supportpal/accept-language-parser"><img src="https://img.shields.io/packagist/v/supportpal/accept-language-parser" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/supportpal/accept-language-parser"><img src="https://img.shields.io/packagist/l/supportpal/accept-language-parser" alt="License"></a>
</p>

----

# Install

```bash
composer require supportpal/accept-language-parser
```

# Usage

## Parse

Parses an ACCEPT_LANGUAGE header and returns all parsed locales.

```php
$parser = new \SupportPal\AcceptLanguageParser\Parser($_SERVER['http_accept_language']);
foreach ($parser->parse() as $component) {
    echo $component->code();
}
```

## Pick

Parses an ACCEPT_LANGUAGE header and returns only locales which match those requested.

```php
$parser = new \SupportPal\AcceptLanguageParser\Parser('en-GB;q=0.8');
foreach ($parser->pick(array('en')) as $component) {
    echo $component->code();
}
```

# License

This package is licensed under the <a href="https://github.com/supportpal/accept-language-parser/blob/master/LICENSE">MIT License</a>.
