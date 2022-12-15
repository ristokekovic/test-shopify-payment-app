<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Packlink PRO Shipping Platform</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined">
    <link rel="stylesheet" href="{{\Packlink\Middleware\Utility\Asset::getBrandAssetUrl('/admin/css/app.css')}}">
    <link rel="stylesheet" href="{{\Packlink\Middleware\Utility\Asset::getAssetUrl('css/packlink.css')}}">
</head>
<body>
<div id="pl-page">
    <header id="pl-main-header">
        <div class="pl-main-logo">
            <img src="https://cdn.packlink.com/apps/giger/logos/packlink-pro.svg" alt="logo">
        </div>
        <div class="pl-header-holder" id="pl-header-section"></div>
    </header>
    <main id="pl-maintenance-page" class="pl-flex-expand pl-center pl-page-padding pl-page-content pl-no-margin">
        <img src="{{\Packlink\Middleware\Utility\Asset::getAssetUrl('img/maintenance.png')}}" alt="">
        <div>
            <div id="pl-maintenance-content">
                <h1>{{ \Packlink\BusinessLogic\Language\Translator::translate('maintenance.title') }}</h1>
                <p>{{ \Packlink\BusinessLogic\Language\Translator::translate('maintenance.description_line_1') }}</p>
                <p>{{ \Packlink\BusinessLogic\Language\Translator::translate('maintenance.description_line_2') }}</p>
            </div>
            <hr/>
            <div id="pl-maintenance-footer">
                <p>
                    <strong>{{ \Packlink\BusinessLogic\Language\Translator::translate('maintenance.help') }}</strong>
                    {{ \Packlink\BusinessLogic\Language\Translator::translate('maintenance.footer_line_1') }}
                </p>
                <p>{{ \Packlink\BusinessLogic\Language\Translator::translate('maintenance.footer_line_2') }}</p>
                <a href="mailto:business@packlink.com">{{\Packlink\BusinessLogic\Language\Translator::translate('configuration.contactUrl')}}</a>
            </div>
        </div>
    </main>
</div>
</body>
</html>